const simpleGit = require('simple-git');
const fs = require('fs');
const path = require('path');
const { spawn } = require('child_process');
const os = require("os");
const { app, Notification } = require('electron');
const axios = require('axios');
const unzipper = require('unzipper');
const WebSocket = require("ws");

const isDev = !app.isPackaged;

let appDir;
if (isDev) {
    appDir = path.join(__dirname);
} else {
    appDir = process.resourcesPath; // where extraResources are placed
}

const dllsPath = path.join(appDir, 'dlls');

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
function spawnWrapper(mainWindow = null, processType, command, argsOrOptions, maybeOptions) {
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
        log(mainWindow, processType, `${data.toString()}`);
    });

    child.stderr.on('data', (data) => {
        log(mainWindow, processType, `${data.toString()}`);
    });

    child.on('close', (code) => {
        log(mainWindow, processType, `exited with code ${code} for ${JSON.stringify(argsOrOptions)}`);
    });

    child.on('error', (err) => {
        log(mainWindow, processType, `${err.message}`);
    });

    return child;
}

function spawnPhpCgiWorker(phpCGi, port) {
    const args = ['-b', `127.0.0.1:${port}`];
    const options = { cwd: appDir, env: { ...process.env, PATH: `${dllsPath};${process.env.PATH}` } };

    function start() {
        const child = spawn(phpCGi, args, options);

        child.stdout.on('data', (data) => {
            logger(`APPLICATION`, `[PHP-CGI:${port}] ${data.toString()}`);
        });

        child.stderr.on('data', (data) => {
            logger(`APPLICATION`, `[PHP-CGI:${port}] ${data.toString()}`);
        });

        child.on('close', (code) => {
            logger(`APPLICATION`, `[PHP-CGI:${port}] exited with code ${code}. Restarting in 2s...`);
            setTimeout(start, 2000); // auto-restart after 2 seconds
        });

        child.on('error', (err) => {
            logger(`APPLICATION`, `[PHP-CGI:${port}] error: ${err.message}`);
        });

        return child;
    }

    return start();
}

function spawnPhpCgiWorker_new(mainWindow, phpExe, port) {
    const args = ["-S", `127.0.0.1:${port}`]; // PHP built-in server
    const options = {
        cwd: path.dirname(phpExe),
        env: { ...process.env, PATH: `${dllsPath};${process.env.PATH}` } // Prepend DLLs folder
    };

    function start() {
        const child = spawn(phpExe, args, options);

        child.stdout.on("data", (data) => {
            log(mainWindow, "APPLICATION", `[PHP:${port}] ${data.toString()}`);
        });

        child.stderr.on("data", (data) => {
            log(mainWindow, "APPLICATION", `[PHP ERR:${port}] ${data.toString()}`);
        });

        child.on("close", (code) => {
            log(mainWindow, "APPLICATION", `[PHP:${port}] exited with code ${code}. Restarting in 2s...`);
            setTimeout(start, 2000); // auto-restart
        });

        child.on("error", (err) => {
            log(mainWindow, "APPLICATION", `[PHP:${port}] error: ${err.message}`);
        });

        return child;
    }

    return start();
}

function logger(processType, message) {
    const now = new Date();

    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');

    const timestamp = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
    const fullMessage = `[${timestamp}] ${message}\n`;

    // Write to file in logs directory within appDir
    const logDir = path.join(appDir, 'logs');
    const logFile = path.join(logDir, `${processType}-${year}-${month}-${day}.log`);

    // Create logs directory if it doesn't exist
    if (!fs.existsSync(logDir)) {
        fs.mkdirSync(logDir, { recursive: true });
    }

    fs.appendFile(logFile, fullMessage, (err) => {
        if (err) {
            console.error("❌ Failed to write log file:", err);
        }
    });
}


function log(mainWindow = null, processType, message) {
    const now = new Date();

    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');

    const timestamp = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
    const fullMessage = `[${timestamp}] ${message}\n`;

    // Send to frontend
    if (mainWindow && mainWindow.webContents) {
        mainWindow.webContents.send('log', fullMessage);
    }

    // Write to file in logs directory within appDir
    const logDir = path.join(appDir, 'logs');
    const logFile = path.join(logDir, `${processType}-${year}-${month}-${day}.log`);

    // Create logs directory if it doesn't exist
    if (!fs.existsSync(logDir)) {
        fs.mkdirSync(logDir, { recursive: true });
    }

    fs.appendFile(logFile, fullMessage, (err) => {
        if (err) {
            console.error("❌ Failed to write log file:", err);
        }
    });
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
        let result = `JSON file has been updated! `;
        console.log("🚀 ~ ipUpdaterForDotNetSDK ~ result:", result)
        log(mainWindow, `DOTNET`, result);
    });
}

function stopProcess(mainWindow, Process) {

    if (!Process) {
        log(mainWindow, `Stop-Services`, `Something went wrong ${Process}.`);
        return;
    }

    Process.kill();
    Process = null;
    log(mainWindow, `Stop-Services`, `${Process} has been stopped.`);

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

function notify(title = "", body = "", icon = 'favicon-256x256.png', onClick = null) {
    const notification = new Notification({
        title,
        body,
        icon: path.join(appDir, 'www', icon)
    });

    if (onClick && typeof onClick === 'function') {
        notification.on('click', onClick);
    }
    notification.show();
}

async function cloneMultipleRepos(mainWindow, repos) {

    const git = simpleGit();

    for (const repo of repos) {
        const repoDir = path.join(appDir, repo.folder);

        if (!fs.existsSync(repoDir)) {
            let logMessage = `🌀 Cloning ${repo.url} into ${repoDir}...`;
            console.log(logMessage);
            log(mainWindow, `GIT`, logMessage);
            try {
                await git.clone(repo.url, repoDir);
                let logMessage = `✅ ${repo.folder} cloned successfully!`;
                console.log(logMessage);
                log(mainWindow, `GIT`, logMessage);
            } catch (error) {
                console.error(`❌ Error cloning ${repo.url}:`, error.message);
                let logMessage = `❌ Error cloning ${repo.url}:`;
                console.log(logMessage);
                log(mainWindow, `GIT`, logMessage);
            }
        } else {
            let logMessage = `📁 ${repo.folder} already exists`;
            console.log(logMessage);
            log(mainWindow, `GIT`, logMessage);
        }
    }
}

function extractZipIfNeeded(zipFilePath, targetFolderName) {
    const extractPath = path.join(appDir, targetFolderName);

    // Check if already extracted
    if (fs.existsSync(extractPath)) {
        console.log(`📁 ${targetFolderName} already extracted, skipping...`);
        return extractPath;
    }

    console.log(`Extracting ${zipFilePath} → ${extractPath}`);

    try {
        fs.createReadStream(zipFilePath)
            .pipe(unzipper.Extract({ path: extractPath }))
            .promise();

        console.log(`✅ ${targetFolderName} extracted successfully!`);
        return extractPath;
    } catch (error) {
        console.error(`❌ Error extracting ${targetFolderName}:`, error.message);
    }
}

async function downloadAndExtract(mainWindow, repo) {
    const repoDir = path.join(appDir, repo.folder);

    if (!fs.existsSync(repoDir)) {
        fs.mkdirSync(repoDir, { recursive: true });

        let logMessage = `🌀 Downloading ${repo.url} into ${repoDir}...`;
        console.log(logMessage);
        log(mainWindow, `DOWNLOAD`, logMessage);

        try {
            const response = await axios({
                method: 'GET',
                url: repo.url,
                responseType: 'stream',
            });

            const directory = unzipper.Parse();
            response.data.pipe(directory);

            await new Promise((resolve, reject) => {
                directory.on('entry', async (entry) => {
                    let entryPath = entry.path;
                    const type = entry.type; // 'Directory' or 'File'

                    // Remove the top-level folder if exists
                    const parts = entryPath.split(/[/\\]/).slice(1); // skip first folder
                    if (parts.length === 0) {
                        entry.autodrain();
                        return;
                    }
                    entryPath = path.join(repoDir, ...parts);

                    if (type === 'Directory') {
                        fs.mkdirSync(entryPath, { recursive: true });
                        entry.autodrain();
                    } else {
                        entry.pipe(fs.createWriteStream(entryPath));
                    }
                });
                directory.on('close', resolve);
                directory.on('error', reject);
            });

            logMessage = `✅ ${repo.folder} downloaded and extracted successfully!`;
            console.log(logMessage);
            log(mainWindow, `DOWNLOAD`, logMessage);
        } catch (error) {
            console.error(`❌ Error downloading ${repo.url}:`, error.message);
            log(mainWindow, `DOWNLOAD`, `❌ Error downloading ${repo.url}: ${error.message}`);
        }
    } else {
        let logMessage = `📁 ${repo.folder} already exists`;
        console.log(logMessage);
        log(mainWindow, `DOWNLOAD`, logMessage);
    }
}

async function downloadMultipleRepos(mainWindow, repos) {
    for (const repo of repos) {
        const repoDir = path.join(appDir, repo.folder);

        if (!fs.existsSync(repoDir)) {
            fs.mkdirSync(repoDir, { recursive: true });

            let logMessage = `🌀 Downloading ${repo.url} into ${repoDir}...`;
            console.log(logMessage);
            log(mainWindow, `DOWNLOAD`, logMessage);

            try {
                const response = await axios({
                    method: 'GET',
                    url: repo.url,
                    responseType: 'stream',
                });

                const directory = unzipper.Parse();
                response.data.pipe(directory);

                await new Promise((resolve, reject) => {
                    directory.on('entry', (entry) => {
                        let entryPath = entry.path;
                        const type = entry.type; // 'Directory' or 'File'

                        // Remove top-level folder in ZIP if exists
                        const parts = entryPath.split(/[/\\]/).slice(1);
                        if (parts.length === 0) {
                            entry.autodrain();
                            return;
                        }
                        entryPath = path.join(repoDir, ...parts);

                        if (type === 'Directory') {
                            fs.mkdirSync(entryPath, { recursive: true });
                            entry.autodrain();
                        } else {
                            entry.pipe(fs.createWriteStream(entryPath));
                        }
                    });

                    directory.on('close', resolve);
                    directory.on('error', reject);
                });

                logMessage = `✅ ${repo.folder} downloaded and extracted successfully!`;
                console.log(logMessage);
                log(mainWindow, `DOWNLOAD`, logMessage);

            } catch (error) {
                console.error(`❌ Error downloading ${repo.url}:`, error.message);
                log(mainWindow, `DOWNLOAD`, `❌ Error downloading ${repo.url}: ${error.message}`);
            }
        } else {
            let logMessage = `📁 ${repo.folder} already exists`;
            console.log(logMessage);
            log(mainWindow, `DOWNLOAD`, logMessage);
        }
    }
}

function startWebSocketClient(srcDirectory) {

    const SOCKET_ENDPOINT = `ws://${ipv4Address}:8080/WebSocket`;

    function connect() {

        logger(`LISTENER`, `Attempting to connect to ${SOCKET_ENDPOINT}...`);

        const socket = new WebSocket(SOCKET_ENDPOINT);

        socket.onopen = () => {
            logger(`LISTENER`, `Connected to ${SOCKET_ENDPOINT}`);
        };

        socket.onerror = (error) => {
            logger(`LISTENER`, " WebSocket error:", error.message || error);
            // Retry connection after 3 seconds
            setTimeout(connect, 3000);
        };

        socket.onclose = (event) => {
            logger(`LISTENER`, ` WebSocket connection closed with code ${event.code} at ${getFormattedDate().date} ${getFormattedDate().time}`);
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
                    logger(`LISTENER`, `${logEntry}`);
                }

                if (UserCode == 0 && RecordCode == 19) {
                    const alarm_logEntry = `${SN},${RecordDate}`;
                    fs.appendFileSync(logFilePathAlarm, alarm_logEntry + "\n");
                    logger(`LISTENER`, "Error processing message: " + alarm_logEntry);
                }
            } catch (error) {
                logger(`LISTENER`, "Error processing message: " + error.message);
            }
        };

    }

    connect();
}

module.exports = {
    log, startWebSocketClient,
    tailLogFile,
    spawnWrapper, spawnPhpCgiWorker,
    stopProcess,
    getFormattedDate, ipUpdaterForDotNetSDK, notify, cloneMultipleRepos, extractZipIfNeeded, downloadAndExtract, downloadMultipleRepos,
    timezoneOptions, verification_methods, reasons, ipv4Address
}