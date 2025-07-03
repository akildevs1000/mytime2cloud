const { app, BrowserWindow, ipcMain, screen } = require('electron');
const path = require('path');
const { spawn } = require('child_process');
const isDev = !app.isPackaged;

const { log, spawnWrapper, stopProcess } = require('./app/helpers');

let appDir;
if (isDev) {
  appDir = path.join(__dirname, 'app');
} else {
  appDir = path.join(app.getAppPath(), 'app');
}
const nginxPath = path.join(appDir, 'nginx.exe');
const srcDirectory = path.join(appDir, 'backend');
const phpPath = path.join(srcDirectory, 'php');
const phpPathCli = path.join(phpPath, 'php.exe');

let mainWindow;
let NginxProcess;
let ScheduleProcess;
let QueueProcess;


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

  mainWindow.loadFile('app/index.html');

  mainWindow.webContents.once('did-finish-load', () => {
    // cloneTheRepoIfRequired(mainWindow, appDir, targetDir, srcDirectory, phpPathCli, repoUrl);
  });
}

app.whenReady().then(() => {

  createWindow();

  ipcMain.on('start-server', (event) => {

    spawn(path.join(phpPath, 'php-cgi.exe'), ['-b', `127.0.0.1:9000`], { cwd: appDir });

    NginxProcess = spawnWrapper(mainWindow, nginxPath, { cwd: appDir });

    log(mainWindow, `Application Server started on http://localhost:3001`);

    ScheduleProcess = spawnWrapper(mainWindow, phpPathCli, ['artisan', 'schedule:work'], {
      cwd: srcDirectory
    });

    QueueProcess = spawnWrapper(mainWindow, phpPathCli, ['artisan', 'queue:work'], {
      cwd: srcDirectory
    });
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