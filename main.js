const { app, BrowserWindow, ipcMain, screen } = require('electron');
const path = require('path');
const { spawn } = require('child_process');
const fs = require("fs")
const os = require("os");
const WebSocket = require("ws");

const isDev = !app.isPackaged;

const { log, spawnWrapper, stopProcess } = require('./helpers');

const networkInterfaces = os.networkInterfaces();

let ipv4Address = null;

Object.keys(networkInterfaces).forEach((interfaceName) => {
  networkInterfaces[interfaceName].forEach((networkInterface) => {
    // Only consider IPv4 addresses, ignore internal and loopback addresses
    if (networkInterface.family === "IPv4" && !networkInterface.internal) {
      ipv4Address = networkInterface.address;
    }
  });
});


const options = {
  year: "numeric",
  month: "2-digit",
  day: "2-digit",
  hour: "2-digit",
  minute: "2-digit",
  second: "2-digit",
  hour12: false, // Use 24-hour format
  timeZone: "Asia/Dubai",
};

const verification_methods = {
  1: "Card",
  2: "Fing",
  3: "Face",
  4: "Fing + Card",
  5: "Face + Fing",
  6: "Face + Card",
  7: "Card + Pin",
  8: "Face + Pin",
  9: "Fing + Pin",
  10: "Manual",
  11: "Fing + Card + Pin",
  12: "Face + Card + Pin",
  13: "Face + Fing + Pin",
  14: "Face + Fing + Card",
  15: "Repeated",
};

const reasons = {
  16: "Date Expire",
  17: "Timezone Expire",
  18: "Holiday",
  19: "Unregistered",
  20: "Detection lock",
  23: "Loss Card",
  24: "Blacklisted",
  25: "Without Verification",
  26: "No Card Verification",
  27: "No Fingerprint",
};

const [newDate, newTime] = new Intl.DateTimeFormat("en-US", options)
  .format(new Date())
  .split(",");
const [m, d, y] = newDate.split("/");
const formattedDate = `${d.padStart(2, 0)}-${m.padStart(2, 0)}-${y}`;
// const logFilePath = `../backend/storage/app/logs-${formattedDate}.csv`;
// const logFilePathRawData = `../backend/storage/app/logs-data/logs-data-${formattedDate}.txt`;
// const logFilePathAlarm = `../backend/storage/app/alarm/alarm-logs-${formattedDate}.csv`;
console.log(`Current Date: ${formattedDate}`);
console.log(`Current Time: ${newTime.trim()}`);


function getFormattedDate() {

  const options = {
    year: "numeric",
    month: "2-digit",
    day: "2-digit",
    hour: "2-digit",
    minute: "2-digit",
    second: "2-digit",
    hour12: false, // Use 24-hour format
    // timeZone: "Asia/Dubai",
  };
  const [newDate, newTime] = new Intl.DateTimeFormat("en-US", options)
    .format(new Date())
    .split(",");
  const [m, d, y] = newDate.split("/");

  return {
    date: `${d.padStart(2, 0)}-${m.padStart(2, 0)}-${y}`,
    time: newTime,
  };
}


function startWebSocketClient() {
  const SOCKET_ENDPOINT = `ws://${ipv4Address}:8080/WebSocket`;
  const logFilePathAlarm = `../backend/storage/app/alarm/alarm-logs-${getFormattedDate().date}.csv`;

  function connect() {
    console.log(`Attempting to connect to ${SOCKET_ENDPOINT}...`);
    const socket = new WebSocket(SOCKET_ENDPOINT);

    socket.onopen = () => {
      console.log(`Connected to ${SOCKET_ENDPOINT}`);
    };

    socket.onerror = (error) => {
      console.error("WebSocket error:", error.message || error);
      // Retry connection after 3 seconds
      setTimeout(connect, 3000);
    };

    socket.onclose = (event) => {
      console.error(
        `WebSocket connection closed with code ${event.code} at ${getFormattedDate().date} ${getFormattedDate().time}`
      );
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
          console.log(logEntry);
        }

        if (UserCode == 0 && RecordCode == 19) {
          const alarm_logEntry = `${SN},${RecordDate}`;
          fs.appendFileSync(logFilePathAlarm, alarm_logEntry + "\n");
          console.log("Alarm", alarm_logEntry);
        }
      } catch (error) {
        console.error("Error processing message:", error.message);
      }
    };
  }

  connect();
}

let appDir;
if (isDev) {
  appDir = path.join(__dirname);
} else {
  appDir = path.join(app.getAppPath());
}
const nginxPath = path.join(appDir, 'nginx.exe');
const srcDirectory = path.join(appDir, 'backend');
const phpPath = path.join(srcDirectory, 'php');
const phpPathCli = path.join(phpPath, 'php.exe');

const dotnetSDK = path.join(appDir, 'dotnet_sdk');


const dotnetExe = path.join(dotnetSDK, 'dotnet', 'dotnet.exe');

const javaSDK = path.join(appDir, 'java_sdk');
const javaExe = path.join(javaSDK, 'bin', 'java.exe');
const jarPath = path.join(javaSDK, 'SxDeviceManager.jar');


let mainWindow;
let NginxProcess;
let ScheduleProcess;
let QueueProcess;
let dotnetSDKProcess;
let javaSDKProcess;


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

    startWebSocketClient();

  });

  ipcMain.on('stop-server', () => {

    stopProcess(mainWindow, ScheduleProcess);
    stopProcess(mainWindow, QueueProcess);
    stopProcess(mainWindow, NginxProcess);

    const forFullStop = spawn('taskkill', ['/F', '/IM', 'nginx.exe']);
    forFullStop.on('close', () => {
      log(mainWindow, 'Server stopped.');
    });
  });
});

app.on('window-all-closed', () => {
  if (process.platform !== 'darwin') app.quit();
});