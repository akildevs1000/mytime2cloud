const { WebSocketServer } = require('ws');
const { exec } = require('child_process');
const os = require('os');

const PORT = 2266;

// Binding to 0.0.0.0 allows external connections
const wss = new WebSocketServer({ 
    port: PORT, 
    host: '0.0.0.0' 
});

const CRITICAL_SERVICES = ['php-fpm', 'postgres', 'node', 'dotnet'];

function getSystemMetrics() {
    const loadAvg = os.loadavg()[0].toFixed(2);
    const cpuCores = os.cpus().length;
    const cmd = "ps -eo %cpu,%mem,args --sort=-%cpu";
    
    exec(cmd, (error, stdout) => {
        if (error) return;

        const lines = stdout.trim().split("\n").slice(1);
        const serviceStats = {};

        CRITICAL_SERVICES.forEach(s => {
            serviceStats[s] = { status: 'OFFLINE', cpu: 0, mem: 0 };
        });

        lines.forEach(line => {
            const parts = line.trim().split(/\s+/);
            const cpuVal = parseFloat(parts[0]);
            const memVal = parseFloat(parts[1]);
            const fullCommand = parts.slice(2).join(' ').toLowerCase();

            const matchedService = CRITICAL_SERVICES.find(s => 
                fullCommand.includes(s.toLowerCase())
            );
            
            if (matchedService) {
                if (serviceStats[matchedService].status === 'ONLINE') {
                    serviceStats[matchedService].cpu += cpuVal;
                    serviceStats[matchedService].mem += memVal;
                } else {
                    serviceStats[matchedService] = {
                        status: 'ONLINE',
                        cpu: cpuVal,
                        mem: memVal,
                        spiking: cpuVal > 30
                    };
                }
            }
        });

        const payload = JSON.stringify({
            load: loadAvg,
            services: serviceStats,
            timestamp: new Date().toLocaleTimeString('en-AE')
        });

        wss.clients.forEach(client => {
            if (client.readyState === 1) client.send(payload);
        });
    });
}

setInterval(getSystemMetrics, 2266);

console.log(`Monitor listening on all interfaces (0.0.0.0:${PORT})`);