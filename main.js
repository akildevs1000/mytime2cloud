const { app, BrowserWindow, ipcMain, screen } = require('electron');
const path = require('path');
const { spawn } = require('child_process');
const fs = require("fs")
const WebSocket = require("ws");
const { log, spawnWrapper, stopProcess, getFormattedDate, verification_methods, reasons, ipv4Address } = require('./helpers');

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

const nginxPath = path.join(appDir, 'nginx.exe');
const phpPathCli = path.join(phpPath, 'php.exe');
const dotnetExe = path.join(dotnetSDK, 'dotnet', 'dotnet.exe');
const javaExe = path.join(javaSDK, 'bin', 'java.exe');

let mainWindow;
let NginxProcess;
let ScheduleProcess;
let QueueProcess;
let dotnetSDKProcess;
let javaSDKProcess;


function startWebSocketClient(mainWindow) {

  const SOCKET_ENDPOINT = `ws://${ipv4Address}:8080/WebSocket`;
  const logFilePathAlarm = `./backend/storage/app/alarm/alarm-logs-${getFormattedDate().date}.csv`;

  function connect() {

    log(mainWindow, `Attempting to connect to ${SOCKET_ENDPOINT}...`);

    const socket = new WebSocket(SOCKET_ENDPOINT);

    socket.onopen = () => {
      log(mainWindow, `Connected to ${SOCKET_ENDPOINT}...`);
    };

    socket.onerror = (error) => {
      log(mainWindow, "WebSocket error:", error.message || error);
      // Retry connection after 3 seconds
      setTimeout(connect, 3000);
    };

    socket.onclose = (event) => {
      log(mainWindow, `WebSocket connection closed with code ${event.code} at ${getFormattedDate().date} ${getFormattedDate().time}`);
      // Retry connection after 3 seconds
      setTimeout(connect, 3000);
    };

    socket.onmessage = ({ data }) => {
      let logFilePath = `./backend/storage/app/logs-${getFormattedDate().date}.csv`;
      try {
        const jsonData = JSON.parse(data).Data;
        const { UserCode, SN, RecordDate, RecordNumber, RecordCode } = jsonData;

        if (UserCode > 0) {
          let status = RecordCode > 15 ? "Access Denied" : "Allowed";
          let mode = verification_methods[RecordCode] ?? "---";
          let reason = reasons[RecordCode] ?? "---";
          const logEntry = `${UserCode},${SN},${RecordDate},${RecordNumber},${status},${mode},${reason}`;
          fs.appendFileSync(logFilePath, logEntry + "\n");
          log(mainWindow, logEntry);
        }

        if (UserCode == 0 && RecordCode == 19) {
          const alarm_logEntry = `${SN},${RecordDate}`;
          fs.appendFileSync(logFilePathAlarm, alarm_logEntry + "\n");
          log(mainWindow, "Error processing message: " + alarm_logEntry);
        }
      } catch (error) {
        log(mainWindow, "Error processing message: " + error.message);
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
    webPreferences: {
      nodeIntegration: true,
      contextIsolation: false
    }
  });

  mainWindow.loadFile('index.html');

  mainWindow.webContents.once('did-finish-load', () => {
    // cloneTheRepoIfRequired(mainWindow, appDir, targetDir, srcDirectory, phpPathCli, repoUrl);
  });
}

app.whenReady().then(() => {

  createWindow();

  ipcMain.on('start-server', (event) => {

    const jsonPath = path.join(dotnetSDK, 'appsettings.json');
    const jsonData = fs.readFileSync(jsonPath, 'utf-8'); // read as string
    const data = JSON.parse(jsonData);

    data.urls = `http://${ipv4Address}:8080`;
    data.Options.LocalIP = ipv4Address;

    const updatedJsonData = JSON.stringify(data, null, 2);

    // Write the updated JSON data to the file
    fs.writeFile(jsonPath, updatedJsonData, (err) => {
      if (err) throw err;
      console.log();
      log(mainWindow, `"JSON file has been updated!"`);

    });

    spawn(path.join(phpPath, 'php-cgi.exe'), ['-b', `127.0.0.1:9000`], { cwd: appDir });

    NginxProcess = spawnWrapper(mainWindow, nginxPath, { cwd: appDir });

    log(mainWindow, `Application Server started on http://${ipv4Address}:3001`);

    javaSDKProcess = spawnWrapper(mainWindow, javaExe, ['-jar', jarPath], {
      cwd: javaSDK
    });

    ScheduleProcess = spawnWrapper(mainWindow, phpPathCli, ['artisan', 'schedule:work'], {
      cwd: srcDirectory
    });

    QueueProcess = spawnWrapper(mainWindow, phpPathCli, ['artisan', 'queue:work'], {
      cwd: srcDirectory
    });

    dotnetSDKProcess = spawnWrapper(mainWindow, dotnetExe, ['FCardProtocolAPI.dll'], {
      cwd: dotnetSDK
    });

    startWebSocketClient(mainWindow);

  });

  ipcMain.on('stop-server', () => {

    stopProcess(mainWindow, ScheduleProcess);
    stopProcess(mainWindow, QueueProcess);
    stopProcess(mainWindow, NginxProcess);
    stopProcess(mainWindow, dotnetSDKProcess);
    stopProcess(mainWindow, javaSDKProcess);


    const forFullStop = spawn('taskkill', ['/F', '/IM', 'nginx.exe']);
    forFullStop.on('close', () => {
      log(mainWindow, 'Server stopped.');
    });
  });
});

app.on('window-all-closed', () => {
  if (process.platform !== 'darwin') app.quit();
});