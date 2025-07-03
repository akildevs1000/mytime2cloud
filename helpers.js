const simpleGit = require('simple-git');
const fs = require('fs');
const path = require('path');
const { spawn } = require('child_process');

const appDir = path.join(__dirname, 'app');

function tailLogFile(logFilePath) {
    const tail = spawn('powershell.exe', [
        '-Command',
        `Get-Content -Path "${logFilePath}" -Wait -Tail 10`
    ]);

    tail.stdout.on('data', (data) => {
        log(`[NGINX] ${data.toString()}`);
    });

    tail.stderr.on('data', (data) => {
        log(`[NGINX-ERROR] ${data.toString()}`);
    });
}

// const accessLogPath = path.join(appDir, 'logs', 'access.log');
// const errorLogPath = path.join(appDir, 'logs', 'error.log');

// tailLogFile(accessLogPath);
// tailLogFile(errorLogPath);


// Flexible spawn wrapper
function spawnWrapper(mainWindow, command, argsOrOptions, maybeOptions) {
    let args = [];
    let options = {};

    if (Array.isArray(argsOrOptions)) {
        args = argsOrOptions;
        options = maybeOptions || {};
    } else {
        options = argsOrOptions || {};
    }

    const child = spawn(command, args, options);

    child.stdout.on('data', (data) => {
        log(mainWindow, data.toString());
    });

    child.stderr.on('data', (data) => {
        log(mainWindow, data.toString());
    });

    child.on('close', (code) => {
        console.log(`exited with code ${code} for  ${JSON.stringify(argsOrOptions)}`);
    });

    child.on('error', (err) => {
        log(mainWindow, err.message);
    });

    return child;
}

function log(mainWindow, message) {
    const now = new Date();

    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');

    const timestamp = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;

    mainWindow.webContents.send('log', `[${timestamp}] ${message}\n`);
}

function stopProcess(mainWindow, Process) {

    if (!Process) {
        log(mainWindow, `Something went wrong ${Process}.`);
        return;
    }

    Process.kill();
    Process = null;
    log(mainWindow, `${Process} has been stopped.`);

}

function cloneTheRepoIfRequired(mainWindow, appDir, targetDir, backendDir, phpPath, repoUrl) {
    const composerPhar = path.join(appDir, 'composer.phar');

    if (!fs.existsSync(targetDir) || fs.readdirSync(targetDir).length === 0) {
        log(mainWindow, `Cloning repository from ${repoUrl}`);
        const git = simpleGit();

        git.clone(repoUrl, targetDir)
            .then(() => {
                log(mainWindow, 'Repository cloned successfully.');

                // Run `composer install`
                return spawnWrapper(mainWindow, phpPath, [composerPhar, 'install'], {
                    cwd: backendDir
                });
            })
            .then(() => {
                // Run `php artisan migrate --force`
                log(mainWindow, 'Running php artisan migrate --force');
                return spawnWrapper(mainWindow, phpPath, ['artisan', 'migrate', '--force'], {
                    cwd: backendDir
                });
            })
            .then(() => {
                log(mainWindow, 'Migration completed successfully.');
            })
            .catch(err => {
                log(mainWindow, `Error: ${err.message}`);
            });
    } else {
        // log(mainWindow, 'Repository already cloned.');
    }
}

module.exports = {
    log,
    tailLogFile,
    spawnWrapper,
    stopProcess,
    cloneTheRepoIfRequired
}