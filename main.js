const { app, BrowserWindow, ipcMain, screen, } = require('electron');

const path = require('path');
const { spawn } = require('child_process');

app.setName('MyTime2Desktop');
app.setAppUserModelId('MyTime2Desktop');

const phpPorts = [9000, 9001, 9002, 9003, 9004];
const appPort = 3001;

const { log, startWebSocketClient, runInstaller, spawnWrapper, spawnPhpCgiWorker, cloneMultipleRepos, downloadMultipleRepos, ipUpdaterForDotNetSDK, ipv4Address } = require('./helpers');

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
let nginxWindow;

let dotnetSDKProcess;


function createWindow() {

  const { width, height } = screen.getPrimaryDisplay().workAreaSize;

  mainWindow = new BrowserWindow({
    width,
    height,
    show: false, // enable to hide the window
    webPreferences: {
      nodeIntegration: true,
      contextIsolation: false,
    },
  });

  mainWindow.loadFile('index.html');

  mainWindow.webContents.once('did-finish-load', async () => {
    // const repositories = [
    //   {
    //     url: 'https://github.com/akildevs1000/dotnet_sdk',
    //     folder: 'dotnet_sdk',
    //   },
    // ];

    // await cloneMultipleRepos(mainWindow, repositories);

    const repos = [
      {
        folder: 'dotnet_sdk',
        url: 'https://backend.mytime2cloud.com/dotnet_sdk.zip'
      },
      {
        folder: 'java_sdk',
        url: 'https://backend.mytime2cloud.com/java_sdk.zip'
      },
    ];

    downloadMultipleRepos(mainWindow, repos);

    ipUpdaterForDotNetSDK(mainWindow, jsonPath);

    startServices(mainWindow);
  });
}

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
  spawn('cmd.exe', ['/c', batFile], { windowsHide: true });
  log(mainWindow, `kill-processes.bat`, ' kill-processes.bat executed.');
}

function nginxWorker() {
  spawnWrapper(null, "[Nginx]", nginxPath, { cwd: appDir });

  const address = `http://${ipv4Address}:${appPort}`;
  console.log("🚀 ~ nginxWorker ~ address:", address)

  log(null, `APPLICATION`, `started on ${address}`);

  const { width, height } = screen.getPrimaryDisplay().workAreaSize;

  // ✅ Only create a new window if it's not already open
  if (!nginxWindow || nginxWindow.isDestroyed()) {
    nginxWindow = new BrowserWindow({
      width,
      height,
      frame: true,
      fullscreen: false,
      webPreferences: {
        nodeIntegration: false,
        contextIsolation: true
      }
    });

    nginxWindow.loadURL(address);

    nginxWindow.maximize();

    nginxWindow.on('closed', () => {
      nginxWindow = null;
    });
  } else {
    nginxWindow.focus(); // ✅ Bring existing window to front
  }
}

app.whenReady().then(async () => {

  await runInstaller(path.join(appDir, `vs_redist.exe`));

  phpPorts.forEach(port => {
    spawnPhpCgiWorker(phpCGi, port);
    console.log("🚀 ~ phpCGi, port:", phpCGi, port)
  });

  nginxWorker();

  createWindow();
});
let isQuitting = false;

app.on('before-quit', (e) => {
  if (!isQuitting) {
    e.preventDefault(); // prevent quit
    log(mainWindow, `kill-processes`, "Stopping services before quitting...");
    stopServices(mainWindow); // assume this is sync or finishes quickly
    isQuitting = true;
    app.quit(); // trigger quit again
  }
});
app.on('window-all-closed', () => {
  if (process.platform !== 'darwin') app.quit();
});