const { app, BrowserWindow, ipcMain, screen, } = require('electron');

const path = require('path');
const { spawn } = require('child_process');
const fs = require("fs")
const WebSocket = require("ws");

app.setName('MyTime2Desktop');
app.setAppUserModelId('MyTime2Desktop');

const { log, spawnWrapper, spawnPhpCgiWorker, cloneMultipleRepos, downloadMultipleRepos, getFormattedDate, ipUpdaterForDotNetSDK, verification_methods, reasons, ipv4Address } = require('./helpers');

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


function startWebSocketClient(mainWindow) {

  const SOCKET_ENDPOINT = `ws://${ipv4Address}:8080/WebSocket`;

  function connect() {

    log(mainWindow, `LISTENER`, `Attempting to connect to ${SOCKET_ENDPOINT}...`);

    const socket = new WebSocket(SOCKET_ENDPOINT);

    socket.onopen = () => {
      log(mainWindow, `LISTENER`, `Connected to ${SOCKET_ENDPOINT}`);
    };

    socket.onerror = (error) => {
      log(mainWindow, `LISTENER`, " WebSocket error:", error.message || error);
      // Retry connection after 3 seconds
      setTimeout(connect, 3000);
    };

    socket.onclose = (event) => {
      log(mainWindow, `LISTENER`, ` WebSocket connection closed with code ${event.code} at ${getFormattedDate().date} ${getFormattedDate().time}`);
      // Retry connection after 3 seconds
      // setTimeout(connect, 3000);
    };

    socket.onmessage = ({ data }) => {
      try {

        const storage = path.join(srcDirectory, 'storage', 'app');

        let logFilePath = path.join(storage, `logs-${getFormattedDate().date}.csv`);
        let logFilePathAlarm = path.join(storage, `alarm-logs-${getFormattedDate().date}.csv`);

        // Ensure directory exists
        fs.mkdirSync(path.dirname(logFilePath), { recursive: true });

        const jsonData = JSON.parse(data).Data;
        const { UserCode, SN, RecordDate, RecordNumber, RecordCode } = jsonData;

        if (UserCode > 0) {
          let status = RecordCode > 15 ? "Access Denied" : "Allowed";
          let mode = verification_methods[RecordCode] ?? "---";
          let reason = reasons[RecordCode] ?? "---";
          const logEntry = `${UserCode},${SN},${RecordDate},${RecordNumber},${status},${mode},${reason}`;
          fs.appendFileSync(logFilePath, logEntry + "\n");
          log(mainWindow, `LISTENER`, `${logEntry}`);
        }

        if (UserCode == 0 && RecordCode == 19) {
          const alarm_logEntry = `${SN},${RecordDate}`;
          fs.appendFileSync(logFilePathAlarm, alarm_logEntry + "\n");
          log(mainWindow, `LISTENER`, "Error processing message: " + alarm_logEntry);
        }
      } catch (error) {
        log(mainWindow, `LISTENER`, "Error processing message: " + error.message);
      }
    };

  }

  connect();
}

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

    spawnWrapper(mainWindow, "[Nginx]", nginxPath, { cwd: appDir });

    const address = `http://${ipv4Address}:3001`;
    log(mainWindow, `APPLICATION`, `started on ${address}`);

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

      nginxWindow.loadURL(`http://${ipv4Address}:3001`);
      nginxWindow.maximize();

      nginxWindow.on('closed', () => {
        nginxWindow = null;
      });
    } else {
      nginxWindow.focus(); // ✅ Bring existing window to front
    }

    const repositories = [
      {
        url: 'https://github.com/akildevs1000/dotnet_sdk',
        folder: 'dotnet_sdk',
      },
      // {
      //   url: 'https://github.com/akildevs1000/java_sdk',
      //   folder: 'java_sdk',
      // }
    ];

    await cloneMultipleRepos(mainWindow, repositories);

    // Example usage:
    const repos = [
      {
        folder: 'java_sdk',
        url: 'https://backend.mytime2cloud.com/java_sdk.zip'
      }
    ];

    downloadMultipleRepos(mainWindow, repos);

    ipUpdaterForDotNetSDK(mainWindow, jsonPath);

    startServices(mainWindow);

    // initAutoUpdater(mainWindow);
  });
}

function startServices(mainWindow) {


  const phpPorts = [9000, 9001, 9002, 9003, 9004];

  phpPorts.forEach(port => {
    spawnPhpCgiWorker(mainWindow, phpCGi, port);
  });

  dotnetSDKProcess = spawnWrapper(mainWindow, "DOTNET", dotnetExe, ['FCardProtocolAPI.dll'], {
    cwd: dotnetSDK
  });

  dotnetSDKProcess.stdout.on('data', (data) => {
    if (data.includes('Now listening on')) {
      startWebSocketClient(mainWindow);
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
  const batFile = path.join(appDir, 'stop-services.bat');
  spawn('cmd.exe', ['/c', batFile], { windowsHide: true });
  log(mainWindow, `Stop-Services.bat`, ' stop-services.bat executed.');
}

app.whenReady().then(() => {
  createWindow();
  ipcMain.on('start-server', () => startServices(mainWindow));
  ipcMain.on('stop-server', () => stopServices(mainWindow));
});
let isQuitting = false;

app.on('before-quit', (e) => {
  if (!isQuitting) {
    e.preventDefault(); // prevent quit
    log(mainWindow, `Stop-Services`, "Stopping services before quitting...");
    stopServices(mainWindow); // assume this is sync or finishes quickly
    isQuitting = true;
    app.quit(); // trigger quit again
  }
});
app.on('window-all-closed', () => {
  if (process.platform !== 'darwin') app.quit();
});