const net = require('net');

const HOST = '139.59.69.241'; // Change to your server IP
const PORT = 2266;

const client = new net.Socket();

client.connect(PORT, HOST, () => {
    console.log('Connected to Resource Monitor Server');
    console.log('Listening for high-resource processes...\n');
});

client.on('data', (data) => {
    try {
        const messages = data.toString().split('\n').filter(msg => msg.trim());
        
        messages.forEach(msg => {
            const parsed = JSON.parse(msg);
            
            switch(parsed.type) {
                case 'connected':
                    console.log(`✓ ${parsed.message}`);
                    break;
                    
                case 'system_info':
                    console.log('\n═══ SYSTEM INFORMATION ═══');
                    console.log(`Hostname: ${parsed.data.hostname}`);
                    console.log(`Platform: ${parsed.data.platform} (${parsed.data.arch})`);
                    console.log(`CPU: ${parsed.data.cpuModel} (${parsed.data.cpuCount} cores)`);
                    console.log(`Memory: ${parsed.data.usedMemory} / ${parsed.data.totalMemory} (${parsed.data.memoryUsagePercent})`);
                    console.log(`Uptime: ${Math.floor(parsed.data.uptime / 3600)}h ${Math.floor((parsed.data.uptime % 3600) / 60)}m`);
                    console.log('═══════════════════════════\n');
                    break;
                    
                case 'process_update':
                    if (parsed.count > 0) {
                        console.log(`\n⚠️  ${parsed.count} HIGH RESOURCE PROCESSES (${new Date(parsed.timestamp).toLocaleTimeString()})`);
                        console.log('─'.repeat(100));
                        
                        parsed.processes.forEach(proc => {
                            const cpuFlag = proc.cpu > 50 ? '🔴' : '  ';
                            const memFlag = proc.mem > 50 ? '🔴' : '  ';
                            console.log(`${cpuFlag} CPU: ${proc.cpu.toFixed(1)}% ${memFlag} MEM: ${proc.mem.toFixed(1)}% | PID: ${proc.pid} | ${proc.command.substring(0, 60)}`);
                        });
                        console.log('─'.repeat(100));
                    }
                    break;
            }
        });
    } catch (error) {
        console.error('Error parsing data:', error);
    }
});

client.on('close', () => {
    console.log('\nConnection closed');
    process.exit(0);
});

client.on('error', (err) => {
    console.error('Connection error:', err.message);
    process.exit(1);
});

// Handle Ctrl+C
process.on('SIGINT', () => {
    console.log('\nDisconnecting...');
    client.destroy();
});