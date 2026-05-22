const { app, BrowserWindow, ipcMain, screen, } = require('electron');

const path = require('path');
const http = require('http');
const { spawn, spawnSync } = require('child_process');

app.setName('MyTime2Desktop');
app.setAppUserModelId('MyTime2Desktop');

// nginx.conf load-balances across these via the `php_workers` upstream block.
// Keep this list in sync with conf/nginx.conf if you change worker count.
const phpPorts = [9000, 9001, 9002, 9003];
const appPort = 3001;

const { log, startWebSocketClient, runInstaller, spawnWrapper, spawnPhpCgiWorker, ipUpdaterForDotNetSDK, ipv4Address, setShuttingDown } = require('./helpers');

const isDev = !app.isPackaged;

let appDir;
if (isDev) {
  appDir = path.join(__dirname);
} else {
  appDir = process.resourcesPath; // where extraResources are placed
}

const srcDirectory = path.join(appDir, 'backend');
const phpPath = path.join(srcDirectory, 'php');
const dotnetSDK = path.join(appDir, 'dotnet_sdk');
const javaSDK = path.join(appDir, 'java_sdk');
const jarPath = path.join(javaSDK, 'SxDeviceManager.jar');
const jsonPath = path.join(dotnetSDK, 'appsettings.json');

const nginxPath = path.join(appDir, 'nginx.exe');
const phpPathCli = path.join(phpPath, 'php.exe');
const phpCGi = path.join(phpPath, 'php-cgi.exe');


const dotnetExe = path.join(dotnetSDK, 'dotnet', 'dotnet.exe');
const javaExe = path.join(javaSDK, 'bin', 'java.exe');

let mainWindow;

let dotnetSDKProcess;


function startServices(mainWindow) {

  dotnetSDKProcess = spawnWrapper(mainWindow, "DOTNET", dotnetExe, ['FCardProtocolAPI.dll'], {
    cwd: dotnetSDK
  });

  dotnetSDKProcess.stdout.on('data', (data) => {
    if (data.includes('Now listening on')) {
      startWebSocketClient(srcDirectory);
    }
  });

  spawnWrapper(mainWindow, "APPLICATION", phpPathCli, ['artisan', 'schedule:work'], {
    cwd: srcDirectory
  });

  spawnWrapper(mainWindow, "APPLICATION", phpPathCli, ['artisan', 'queue:work'], {
    cwd: srcDirectory
  });

  spawnWrapper(mainWindow, "JAVA", javaExe, ['-jar', jarPath], {
    cwd: javaSDK
  });
}

function stopServices(mainWindow) {
  const batFile = path.join(appDir, 'kill-processes.bat');
  // Run synchronously so all children are dead before app.quit() exits the process.
  const result = spawnSync('cmd.exe', ['/c', batFile], { windowsHide: true });
  log(mainWindow, `kill-processes.bat`, ` kill-processes.bat executed (status ${result.status}).`);
}

// Keep the splash visible for at least this long so the user has time to read tips
// even when nginx comes up in <1s.
const MIN_SPLASH_MS = 6000;

// Cap at 30s — if nginx isn't serving by then, something is wrong and we
// should surface an error to the user instead of spinning forever.
const MAX_SERVER_WAIT_MS = 30000;
const POLL_INTERVAL_MS = 500;

function waitForServer(address, onReady, onTimeout) {
  const startTime = Date.now();

  const finish = () => {
    const elapsed = Date.now() - startTime;
    const remaining = Math.max(0, MIN_SPLASH_MS - elapsed);
    setTimeout(onReady, remaining);
  };

  const tryOnce = () => {
    if (Date.now() - startTime >= MAX_SERVER_WAIT_MS) {
      onTimeout();
      return;
    }
    const req = http.get(address, (res) => {
      res.resume();
      if (res.statusCode === 200) {
        finish();
      } else {
        setTimeout(tryOnce, POLL_INTERVAL_MS);
      }
    });
    req.on('error', () => setTimeout(tryOnce, POLL_INTERVAL_MS));
    req.setTimeout(2000, () => req.destroy());
  };
  tryOnce();
}

function showStartupError(window, message) {
  if (!window || window.isDestroyed()) return;
  // Stop the rotating splash status, hide the spinner, and replace the text
  // with an actionable error so the user isn't staring at a forever-spinner.
  const safeMessage = JSON.stringify(message);
  window.webContents.executeJavaScript(`
    try {
      if (window.__splashStatusInterval) clearInterval(window.__splashStatusInterval);
      const spinner = document.querySelector('.spinner');
      if (spinner) spinner.style.display = 'none';
      const title = document.querySelector('.title');
      if (title) title.textContent = 'Startup failed';
      const status = document.getElementById('status');
      if (status) { status.textContent = ${safeMessage}; status.style.opacity = '1'; }
    } catch (e) {}
  `).catch(() => {});
}

function createMainWindow() {
  spawnWrapper(null, "[Nginx]", nginxPath, { cwd: appDir });

  const address = `http://${ipv4Address}:${appPort}`;
  log(null, `APPLICATION`, `started on ${address}`);

  const { width, height } = screen.getPrimaryDisplay().workAreaSize;

  if (!mainWindow || mainWindow.isDestroyed()) {
    mainWindow = new BrowserWindow({
      width,
      height,
      frame: true,
      fullscreen: false,
      webPreferences: {
        nodeIntegration: false,
        contextIsolation: true
      }
    });

    // Show splash immediately, then navigate to nginx URL once it's serving.
    mainWindow.loadFile('index.html');
    mainWindow.maximize();

    waitForServer(address, () => {
      if (mainWindow && !mainWindow.isDestroyed()) {
        log(null, `APPLICATION`, `nginx ready, navigating to ${address}`);
        mainWindow.loadURL(address);
      }
    }, () => {
      log(null, `APPLICATION`, `nginx did not respond at ${address} within ${MAX_SERVER_WAIT_MS}ms — showing startup error.`);
      showStartupError(mainWindow, 'The web server did not start. Please check the logs folder in the install directory, then restart the app.');
    });

    mainWindow.on('closed', () => {
      mainWindow = null;
    });
  } else {
    mainWindow.focus();
  }
}

app.whenReady().then(async () => {

  try {
    await runInstaller(path.join(appDir, `vs_redist.exe`));
  } catch (err) {
    // Don't let a redist failure block app startup — the bundled SDKs may
    // still work if the user has a compatible runtime installed already.
    log(null, `VS_REDIST`, `Continuing without VS Redist: ${err.message}`);
  }

  ipUpdaterForDotNetSDK(null, jsonPath);

  phpPorts.forEach(port => {
    spawnPhpCgiWorker(phpCGi, port);
  });

  startServices(null);

  createMainWindow();
});
let isQuitting = false;

app.on('before-quit', (e) => {
  if (isQuitting) return;
  e.preventDefault();
  // mainWindow may already be destroyed by the 'closed' handler — don't rely on it.
  log(null, `kill-processes`, "Stopping services before quitting...");
  setShuttingDown(true);          // disable php-cgi auto-restart
  stopServices(null);             // spawnSync — blocks until bat finishes
  isQuitting = true;
  app.quit();                     // now safe to exit
});
app.on('window-all-closed', () => {
  if (process.platform !== 'darwin') app.quit();
});
