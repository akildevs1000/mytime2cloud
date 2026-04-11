const net = require('net');
const { exec } = require('child_process');
const os = require('os');

const HOST = '0.0.0.0';
const PORT = 2266;
const UPDATE_INTERVAL = 2000; // Send updates every 2 seconds

// Get system resource usage
function getSystemResources() {
    return new Promise((resolve, reject) => {
        exec('ps aux --sort=-%cpu,-%mem', (error, stdout, stderr) => {
            if (error) {
                reject(error);
                return;
            }

            const lines = stdout.trim().split('\n');
            const processes = [];
            
            // Skip header line
            for (let i = 1; i < lines.length; i++) {
                const line = lines[i].trim().split(/\s+/);
                
                if (line.length >= 11) {
                    const cpuPercent = parseFloat(line[2]);
                    const memPercent = parseFloat(line[3]);
                    
                    // Only include processes using more than 50% CPU or MEM
                    if (cpuPercent > 50 || memPercent > 50) {
                        processes.push({
                            pid: line[1],
                            user: line[0],
                            cpu: cpuPercent,
                            mem: memPercent,
                            vsz: line[4],
                            rss: line[5],
                            time: line[9],
                            command: line.slice(10).join(' ')
                        });
                    }
                }
            }
            
            resolve(processes);
        });
    });
}

// Get system info
function getSystemInfo() {
    const totalMem = os.totalmem();
    const freeMem = os.freemem();
    const usedMem = totalMem - freeMem;
    const cpus = os.cpus();
    
    return {
        hostname: os.hostname(),
        platform: os.platform(),
        arch: os.arch(),
        uptime: os.uptime(),
        totalMemory: (totalMem / 1024 / 1024 / 1024).toFixed(2) + ' GB',
        usedMemory: (usedMem / 1024 / 1024 / 1024).toFixed(2) + ' GB',
        freeMemory: (freeMem / 1024 / 1024 / 1024).toFixed(2) + ' GB',
        memoryUsagePercent: ((usedMem / totalMem) * 100).toFixed(2) + '%',
        cpuCount: cpus.length,
        cpuModel: cpus[0].model
    };
}

// Create server
const server = net.createServer((socket) => {
    console.log(`Client connected: ${socket.remoteAddress}:${socket.remotePort}`);
    
    // Send welcome message
    socket.write(JSON.stringify({
        type: 'connected',
        message: 'Connected to Resource Monitor Server',
        timestamp: new Date().toISOString()
    }) + '\n');
    
    // Send system info once
    const sysInfo = getSystemInfo();
    socket.write(JSON.stringify({
        type: 'system_info',
        data: sysInfo,
        timestamp: new Date().toISOString()
    }) + '\n');
    
    // Set up interval to send process updates
    const interval = setInterval(async () => {
        try {
            const processes = await getSystemResources();
            
            const data = {
                type: 'process_update',
                count: processes.length,
                processes: processes,
                timestamp: new Date().toISOString()
            };
            
            socket.write(JSON.stringify(data) + '\n');
            
            if (processes.length > 0) {
                console.log(`Sent ${processes.length} high-resource processes to client`);
            }
        } catch (error) {
            console.error('Error getting resources:', error);
        }
    }, UPDATE_INTERVAL);
    
    // Handle client disconnect
    socket.on('end', () => {
        console.log('Client disconnected');
        clearInterval(interval);
    });
    
    socket.on('error', (err) => {
        console.error('Socket error:', err);
        clearInterval(interval);
    });
});

// Start server
server.listen(PORT, HOST, () => {
    console.log(`Resource Monitor Socket Server running on ${HOST}:${PORT}`);
    console.log(`Monitoring processes with >50% CPU or Memory usage`);
    console.log(`Updates every ${UPDATE_INTERVAL}ms`);
    console.log('\nConnect using: telnet <server-ip> 2266 or nc <server-ip> 2266');
});

server.on('error', (err) => {
    console.error('Server error:', err);
    process.exit(1);
});

// Graceful shutdown
process.on('SIGINT', () => {
    console.log('\nShutting down server...');
    server.close(() => {
        console.log('Server closed');
        process.exit(0);
    });
});