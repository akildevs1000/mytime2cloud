// updater.js
const { autoUpdater } = require('electron-updater');
const { log, notify, } = require('./helpers');

function initAutoUpdater(mainWindow) {

    notify("MyTime2Desktop", "Application is ready now");

    autoUpdater.on('checking-for-update', () => {
        log(mainWindow, '🔍 Checking for update...');
    });

    autoUpdater.on('update-available', (info) => {
        notify('New Update Available!', `Version ${info.version} is ready to download.`);
    });

    autoUpdater.on('update-downloaded', () => {
        notify('Update Ready!', 'Click to restart and apply the update.', () => {
            autoUpdater.quitAndInstall();
        });
    });

    autoUpdater.on('error', (err) => {
        notify('Update Error!', err?.message || 'An error occurred during update.');
    });

    autoUpdater.on('download-progress', (progress) => {
        log(mainWindow, `📦 Downloading: ${Math.floor(progress.percent)}%`);
    });
    // Start checking for updates
    autoUpdater.checkForUpdatesAndNotify();
}


module.exports = { initAutoUpdater };
