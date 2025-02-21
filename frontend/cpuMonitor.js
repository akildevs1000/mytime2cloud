const os = require('os-utils');
const { exec } = require('child_process');

// Function to monitor overall CPU usage
function monitorCPU() {
    os.cpuUsage((usage) => {
        console.log(`CPU Usage: ${(usage * 100).toFixed(2)}%`);
        getTopProcesses(); // Fetch top CPU-consuming processes
    });
}

// Function to get top CPU-consuming processes (filtering those > 30%)
function getTopProcesses() {
    exec("ps -eo pid,%cpu,cmd --sort=-%cpu | head -10", (error, stdout, stderr) => {
        if (error) {
            console.error(`Error executing command: ${error.message}`);
            return;
        }
        if (stderr) {
            console.error(`Error output: ${stderr}`);
            return;
        }

        console.log("Top CPU-Consuming Processes (CPU > 30%):");

        // Split the output into lines, skip the first header line
        const lines = stdout.trim().split("\n").slice(1);

        // Loop through each line and filter based on CPU usage
        lines.forEach(line => {
            const parts = line.trim().split(/\s+/);
            const cpuUsage = parseFloat(parts[1]);

            if (cpuUsage > 30) {
                console.log(line); // Print only processes using more than 30% CPU
            }
        });
    });
}

// Monitor CPU every 5 seconds
setInterval(monitorCPU, 5000);
