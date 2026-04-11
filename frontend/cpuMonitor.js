const { WebSocketServer } = require('ws');
const { exec } = require('child_process');
const os = require('os');

// Using your specific port
const PORT = 2266;
const wss = new WebSocketServer({ port: PORT });

// The whitelist of critical services for your UAE infrastructure
const CRITICAL_SERVICES = ['php-fpm', 'postgres', 'node', 'dotnet'];

function getSystemMetrics() {
    // 1. Get overall Load Average
    const loadAvg = os.loadavg()[0].toFixed(2);
    const cpuCores = os.cpus().length;

    /**
     * 2. Get process stats
     * We use 'args' instead of 'comm' because .NET and Node processes 
     * often show as just "dotnet" or "node" in 'comm', but 'args' 
     * shows the actual project/DLL name.
     */
    const cmd = "ps -eo %cpu,%mem,args --sort=-%cpu";
    
    exec(cmd, (error, stdout) => {
        if (error) {
            console.error(`Exec Error: ${error.message}`);
            return;
        }

        const lines = stdout.trim().split("\n").slice(1);
        const serviceStats = {};

        // Initialize all as OFFLINE
        CRITICAL_SERVICES.forEach(s => {
            serviceStats[s] = { status: 'OFFLINE', cpu: 0, mem: 0, spiking: false };
        });

        lines.forEach(line => {
            const parts = line.trim().split(/\s+/);
            const cpuVal = parseFloat(parts[0]);
            const memVal = parseFloat(parts[1]);
            const fullCommand = parts.slice(2).join(' ').toLowerCase();

            // Check if this line belongs to one of our critical services
            const matchedService = CRITICAL_SERVICES.find(s => 
                fullCommand.includes(s.toLowerCase())
            );
            
            if (matchedService) {
                // If multiple processes exist (like php-fpm children), we sum them up
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

        // Prepare Payload
        const payload = JSON.stringify({
            load: loadAvg,
            cores: cpuCores,
            services: serviceStats,
            timestamp: new Date().toLocaleTimeString('en-AE')
        });

        // 3. Terminal Logging (So you see it working)
        console.clear();
        console.log(`--- Server Monitor [Port ${PORT}] ---`);
        console.log(`Time: ${new Date().toLocaleTimeString('en-AE')} | Load: ${loadAvg} / ${cpuCores} Cores`);
        console.table(serviceStats);

        // 4. Broadcast to WebSocket Clients
        wss.clients.forEach(client => {
            if (client.readyState === 1) {
                client.send(payload);
            }
        });
    });
}

// Interval set to your specific value 2266ms (~2.2 seconds)
setInterval(getSystemMetrics, 2266);

console.log(`Ultra-light monitor running on ws://localhost:${PORT}`);
console.log(`Monitoring: ${CRITICAL_SERVICES.join(', ')}`);