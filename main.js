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

const { log, startWebSocketClient, runInstaller, spawnWrapper, spawnPhpCgiWorker, cloneMultipleRepos, downloadMultipleRepos, ipUpdaterForDotNetSDK, ipv4Address, setShuttingDown } = require('./helpers');

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

  ScheduleProcess = spawnWrapper(mainWindow, "APPLICATION", phpPathCli, ['artisan', 'schedule:work'], {
    cwd: srcDirectory
  });

  QueueProcess = spawnWrapper(mainWindow, "APPLICATION", phpPathCli, ['artisan', 'queue:work'], {
    cwd: srcDirectory
  });

  javaSDKProcess = spawnWrapper(mainWindow, "JAVA", javaExe, ['-jar', jarPath], {
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

function waitForServer(address, onReady) {
  const startTime = Date.now();

  const finish = () => {
    const elapsed = Date.now() - startTime;
    const remaining = Math.max(0, MIN_SPLASH_MS - elapsed);
    setTimeout(onReady, remaining);
  };

  const tryOnce = () => {
    const req = http.get(address, (res) => {
      res.resume();
      if (res.statusCode === 200) {
        finish();
      } else {
        setTimeout(tryOnce, 500);
      }
    });
    req.on('error', () => setTimeout(tryOnce, 500));
    req.setTimeout(2000, () => req.destroy());
  };
  tryOnce();
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
    });

    mainWindow.on('closed', () => {
      mainWindow = null;
    });
  } else {
    mainWindow.focus();
  }
}

app.whenReady().then(async () => {

  await runInstaller(path.join(appDir, `vs_redist.exe`));

  const repos = [
    { folder: 'dotnet_sdk', url: 'https://backend.mytime2cloud.com/dotnet_sdk.zip' },
    { folder: 'java_sdk', url: 'https://backend.mytime2cloud.com/java_sdk.zip' },
  ];

  downloadMultipleRepos(null, repos);
  ipUpdaterForDotNetSDK(null, jsonPath);

  phpPorts.forEach(port => {
    spawnPhpCgiWorker(phpCGi, port);
  });

  startServices(null);

  createMainWindow();
});
let isQuitting = false;

app.on('before-quit', (e) => {
  if (!isQuitting) {
    e.preventDefault(); // prevent quit
    log(mainWindow, `kill-processes`, "Stopping services before quitting...");
    setShuttingDown(true);          // disable php-cgi auto-restart
    stopServices(mainWindow);       // spawnSync — blocks until bat finishes
    isQuitting = true;
    app.quit();                     // now safe to exit
  }
});
app.on('window-all-closed', () => {
  if (process.platform !== 'darwin') app.quit();
});
