const simpleGit = require('simple-git');
const fs = require('fs');
const path = require('path');
const { spawn } = require('child_process');
const os = require("os");

const networkInterfaces = os.networkInterfaces();

let ipv4Address = "localhost";

Object.keys(networkInterfaces).forEach((interfaceName) => {
    networkInterfaces[interfaceName].forEach((networkInterface) => {
        // Only consider IPv4 addresses, ignore internal and loopback addresses
        if (networkInterface.family === "IPv4" && !networkInterface.internal) {
            ipv4Address = networkInterface.address;
        }
    });
});

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

// Flexible spawn wrapper
function spawnWrapper(mainWindow, processType, command, argsOrOptions, maybeOptions) {
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
        log(mainWindow, `${processType} ${data.toString()}`);
    });

    child.stderr.on('data', (data) => {
        log(mainWindow, `${processType} ${data.toString()}`);
    });

    child.on('close', (code) => {
        log(mainWindow, `${processType} exited with code ${code} for ${JSON.stringify(argsOrOptions)}`);
    });

    child.on('error', (err) => {
        log(mainWindow, `${processType} ${err.message}`);
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

function ipUpdaterForDotNetSDK(mainWindow, jsonPath) {
    const jsonData = fs.readFileSync(jsonPath, 'utf-8'); // read as string
    const data = JSON.parse(jsonData);

    data.urls = `http://${ipv4Address}:8080`;
    data.Options.LocalIP = ipv4Address;

    const updatedJsonData = JSON.stringify(data, null, 2);

    // Write the updated JSON data to the file
    fs.writeFile(jsonPath, updatedJsonData, (err) => {
        if (err) throw err;
        log(mainWindow, `[Device] JSON file has been updated! `);
    });
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
                return spawnWrapper(mainWindow, "Repo", phpPath, [composerPhar, 'install'], {
                    cwd: backendDir
                });
            })
            .then(() => {
                // Run `php artisan migrate --force`
                log(mainWindow, 'Running php artisan migrate --force');
                return spawnWrapper(mainWindow, "Repo", phpPath, ['artisan', 'migrate', '--force'], {
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

const timezoneOptions = {
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

function getFormattedDate() {
    const [newDate, newTime] = new Intl.DateTimeFormat("en-US", timezoneOptions)
        .format(new Date())
        .split(",");
    const [m, d, y] = newDate.split("/");

    return {
        date: `${d.padStart(2, 0)}-${m.padStart(2, 0)}-${y}`,
        time: newTime,
    };
}

module.exports = {
    log,
    tailLogFile,
    spawnWrapper,
    stopProcess,
    cloneTheRepoIfRequired,
    getFormattedDate, ipUpdaterForDotNetSDK,
    timezoneOptions, verification_methods, reasons, ipv4Address
}