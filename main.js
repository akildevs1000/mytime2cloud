const { app, BrowserWindow, ipcMain, screen, } = require('electron');

const path = require('path');
const { spawn } = require('child_process');
// Use 'fs-extra' for robust directory operations
const fs = require("fs-extra"); 
const WebSocket = require("ws");

app.setName('MyTime2Desktop');
app.setAppUserModelId('MyTime2Desktop');

const { log, spawnWrapper, spawnPhpCgiWorker, cloneMultipleRepos, downloadMultipleRepos, getFormattedDate, ipUpdaterForDotNetSDK, verification_methods, reasons, ipv4Address } = require('./helpers');

const isDev = !app.isPackaged;

// =========================================================================
// 🔄 FIX 1: Define Static (Read-Only) and Writable (User Data) Paths
// =========================================================================

// --- Path for Static Resources (Executables, Initial Configs) ---
let staticResourcesPath;
if (isDev) {
  staticResourcesPath = path.join(__dirname);
} else {
  // process.resourcesPath is the correct path for all packaged files (read-only in Program Files)
  staticResourcesPath = process.resourcesPath; 
}

// --- Path for Writable User Data (Logs, Runtime Config) ---
// This is critical for avoiding permission errors
const userDataPath = app.getPath('userData'); 

// The 'backend' folder contains subdirectories that need to be writable (e.g., 'storage')
// We'll map the static backend content to a writable location.
const writableBackendPath = path.join(userDataPath, 'backend');
const writableDotnetSDK = path.join(userDataPath, 'dotnet_sdk');
const writableJavaSDK = path.join(userDataPath, 'java_sdk');

// Define where the services *read* their executables (STATIC)
const srcDirectory = path.join(staticResourcesPath, 'backend'); // Static read-only source
const phpPath = path.join(srcDirectory, 'php'); // Static
const dotnetSDK = path.join(staticResourcesPath, 'dotnet_sdk'); // Static
const javaSDK = path.join(staticResourcesPath, 'java_sdk'); // Static

// Executables are always static
const jarPath = path.join(javaSDK, 'SxDeviceManager.jar');
const jsonPath = path.join(dotnetSDK, 'appsettings.json');
const nginxPath = path.join(staticResourcesPath, 'nginx.exe');
const phpPathCli = path.join(phpPath, 'php.exe');
const phpCGi = path.join(phpPath, 'php-cgi.exe');
const dotnetExe = path.join(dotnetSDK, 'dotnet', 'dotnet.exe');
const javaExe = path.join(javaSDK, 'bin', 'java.exe');

let mainWindow;
let nginxWindow;
let dotnetSDKProcess;


// =========================================================================
// 🔄 FIX 2: Copy Initial Writable Data on First Run
// =========================================================================

// Folders in your extraResources that contain files that need to be *written* to at runtime.
// We are assuming 'backend', 'dotnet_sdk', and 'java_sdk' contain files that your app/services modify.

const DIRECTORIES_TO_INITIALIZE = [
  { staticName: 'backend', writablePath: writableBackendPath },
  // Assuming dotnet_sdk needs a writable copy for appsettings.json updates or logs
  { staticName: 'dotnet_sdk', writablePath: writableDotnetSDK },
  // Assuming java_sdk needs a writable copy for logs/config
  { staticName: 'java_sdk', writablePath: writableJavaSDK }, 
];

function initializeWritableDirectories() {
    console.log(`User Data Path: ${userDataPath}`);

    DIRECTORIES_TO_INITIALIZE.forEach(({ staticName, writablePath }) => {
        if (!fs.existsSync(writablePath)) {
            const sourcePath = path.join(staticResourcesPath, staticName);
            console.log(`Copying initial files from ${sourcePath} to ${writablePath}`);

            try {
                // fs-extra's copySync handles recursive copying and directory creation
                fs.copySync(sourcePath, writablePath); 
                console.log(`Successfully initialized ${staticName} in user data.`);
            } catch (err) {
                console.error(`FATAL: Failed to copy initial files for ${staticName}. Permission issue might still exist or disk is full.`, err);
                app.quit(); // Exit if critical directories can't be initialized
            }
        }
    });
}
// =========================================================================
// 🔄 FIX 3: Update `startWebSocketClient` to use writable paths
// =========================================================================
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
      setTimeout(connect, 3000);
    };

    socket.onclose = (event) => {
      log(mainWindow, `LISTENER`, ` WebSocket connection closed with code ${event.code} at ${getFormattedDate().date} ${getFormattedDate().time}`);
    };

    socket.onmessage = ({ data }) => {
      try {

        // Use the writable backend path for all I/O, especially 'storage/app'
        const storage = path.join(writableBackendPath, 'storage', 'app'); 

        let logFilePath = path.join(storage, `logs-${getFormattedDate().date}.csv`);
        let logFilePathAlarm = path.join(storage, `alarm-logs-${getFormattedDate().date}.csv`);

        // Ensure directory exists - this now happens in the writable user data folder!
        fs.mkdirSync(path.dirname(logFilePath), { recursive: true });

        const jsonData = JSON.parse(data).Data;
        const { UserCode, SN, RecordDate, RecordNumber, RecordCode } = jsonData;

        if (UserCode > 0) {
          let status = RecordCode > 15 ? "Access Denied" : "Allowed";
          let mode = verification_methods[RecordCode] ?? "---";
          let reason = reasons[RecordCode] ?? "---";
          const logEntry = `${UserCode},${SN},${RecordDate},${RecordNumber},${status},${mode},${reason}`;
          // fs.appendFileSync now writes to the writable path!
          fs.appendFileSync(logFilePath, logEntry + "\n"); 
          log(mainWindow, `LISTENER`, `${logEntry}`);
        }

        if (UserCode == 0 && RecordCode == 19) {
          const alarm_logEntry = `${SN},${RecordDate}`;
          // fs.appendFileSync now writes to the writable path!
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

    // Nginx executable remains static
    spawnWrapper(mainWindow, "[Nginx]", nginxPath, { cwd: staticResourcesPath }); 

    const address = `http://${ipv4Address}:3001`;
    log(mainWindow, `APPLICATION`, `started on ${address}`);

    const { width, height } = screen.getPrimaryDisplay().workAreaSize;

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
      nginxWindow.focus();
    }

    const repositories = [
      {
        url: 'https://github.com/akildevs1000/dotnet_sdk',
        folder: 'dotnet_sdk',
      },
      // {
      // 	url: 'https://github.com/akildevs1000/java_sdk',
      // 	folder: 'java_sdk',
      // }
    ];

    // FIX: cloneMultipleRepos and downloadMultipleRepos must target the writable user data paths!
    // I am assuming these helpers now use the correct writable path variables.
    // If not, you must update helpers.js to use 'writableDotnetSDK' and 'writableJavaSDK' as the destination roots.
    await cloneMultipleRepos(mainWindow, repositories); 

    const repos = [
      {
        folder: 'java_sdk',
        url: 'https://backend.mytime2cloud.com/java_sdk.zip'
      }
    ];

    downloadMultipleRepos(mainWindow, repos);

    // FIX: ipUpdaterForDotNetSDK must update the JSON file in the writable user data path
    // Update the function call to use the writable path for appsettings.json
    const writableJsonPath = path.join(writableDotnetSDK, 'appsettings.json');
    ipUpdaterForDotNetSDK(mainWindow, writableJsonPath); 

    startServices(mainWindow);

    // initAutoUpdater(mainWindow);
  });
}

// =========================================================================
// 🔄 FIX 4: Update `startServices` to use writable paths for CWD
// =========================================================================
function startServices(mainWindow) {

  const phpPorts = [9000, 9001, 9002, 9003, 9004];

  phpPorts.forEach(port => {
    // PHP CGI remains static, but the CWD for *its* operations might need adjustment 
    // depending on where it looks for files (e.g., sessions, cache). 
    // Assuming the CWD is static for now, as it's not the primary issue.
    spawnPhpCgiWorker(mainWindow, phpCGi, port);
  });

  // CWD for DOTNET must be the writable path if it needs to read/write its own files (like appsettings.json)
  dotnetSDKProcess = spawnWrapper(mainWindow, "DOTNET", dotnetExe, ['FCardProtocolAPI.dll'], {
    cwd: writableDotnetSDK
  });

  dotnetSDKProcess.stdout.on('data', (data) => {
    if (data.includes('Now listening on')) {
      startWebSocketClient(mainWindow);
    }
  });

  // CWD for the Laravel/PHP scheduler must be the writable path
  ScheduleProcess = spawnWrapper(mainWindow, "APPLICATION", phpPathCli, ['artisan', 'schedule:work'], {
    cwd: writableBackendPath
  });

  // CWD for the Laravel/PHP queue must be the writable path
  QueueProcess = spawnWrapper(mainWindow, "APPLICATION", phpPathCli, ['artisan', 'queue:work'], {
    cwd: writableBackendPath
  });

  // CWD for Java SDK must be the writable path
  javaSDKProcess = spawnWrapper(mainWindow, "JAVA", javaExe, ['-jar', jarPath], {
    cwd: writableJavaSDK
  });
}

function stopServices(mainWindow) {
  // stop-services.bat remains static
  const batFile = path.join(staticResourcesPath, 'stop-services.bat');
  spawn('cmd.exe', ['/c', batFile], { windowsHide: true });
  log(mainWindow, `Stop-Services.bat`, ' stop-services.bat executed.');
}

// =========================================================================
// 🔄 FIX 5: Run Directory Initialization before Window Creation
// =========================================================================
app.whenReady().then(() => {
  // 1. Initialize writable directories by copying from static resources
  initializeWritableDirectories(); 
  
  // 2. Create the window and start services
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