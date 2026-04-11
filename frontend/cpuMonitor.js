const { WebSocketServer } = require('ws');
const { exec } = require('child_process');
const os = require('os');

const wss = new WebSocketServer({ port: 2266 });

// The whitelist of processes you actually care about
const CRITICAL_SERVICES = ['php-fpm', 'postgres', 'node', 'dotnet'];

function getSystemMetrics() {
    // 1. Get overall Load Average (Better than CPU % for "noise" filtering)
    // Load avg for 1, 5, and 15 mins. If load < cores, you are safe.
    const loadAvg = os.loadavg()[0].toFixed(2);

    // 2. Get specific process stats (Lightweight ps call)
    // we only ask for %cpu, %mem, and the command name
    const cmd = "ps -eo %cpu,%mem,comm --sort=-%cpu";
    
    exec(cmd, (error, stdout) => {
        if (error) return;

        const lines = stdout.trim().split("\n").slice(1);
        const serviceStats = {};

        // Initialize whitelist as DOWN
        CRITICAL_SERVICES.forEach(s => serviceStats[s] = { status: 'OFFLINE', cpu: 0 });

        lines.forEach(line => {
            const [cpu, mem, ...commParts] = line.trim().split(/\s+/);
            const name = commParts.join(' ');
            const cpuVal = parseFloat(cpu);

            // Check if this process is in our critical list
            const matchedService = CRITICAL_SERVICES.find(s => name.includes(s));
            
            if (matchedService) {
                serviceStats[matchedService] = {
                    status: 'ONLINE',
                    cpu: cpuVal,
                    mem: parseFloat(mem),
                    isSpiking: cpuVal > 30 // Your specific threshold
                };
            }
        });

        const payload = JSON.stringify({
            load: loadAvg,
            services: serviceStats,
            timestamp: new Date().toLocaleTimeString('en-AE')
        });

        // Broadcast to your dashboard
        wss.clients.forEach(client => {
            if (client.readyState === 1) client.send(payload);
        });
    });
}

// Check every 5 seconds
setInterval(getSystemMetrics, 5000);

console.log('Ultra-light monitor running on ws://localhost:8080');