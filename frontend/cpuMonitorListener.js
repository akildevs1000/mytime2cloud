const net = require('net');

const HOST = '139.59.69.241'; 
const PORT = 2266;

const client = new net.Socket();

// Buffer to handle fragmented TCP packets
let dataBuffer = '';

client.connect(PORT, HOST, () => {
    console.log('✅ Connected to Resource Monitor Server');
    console.log(`🕒 Local Time (Dubai): ${new Date().toLocaleString('en-AE', { timeZone: 'Asia/Dubai' })}`);
    console.log('Listening for high-resource processes...\n');
});

client.on('data', (data) => {
    try {
        // TCP doesn't guarantee a single "data" event is one full message.
        // We append to a buffer and split by newline.
        dataBuffer += data.toString();
        const lines = dataBuffer.split('\n');
        
        // Keep the last partial line in the buffer
        dataBuffer = lines.pop();

        lines.forEach(line => {
            if (!line.trim()) return;
            const parsed = JSON.parse(line);
            
            // Format timestamp for Dubai
            const dubaiTime = new Date(parsed.timestamp).toLocaleTimeString('en-AE', {
                timeZone: 'Asia/Dubai',
                hour12: true,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });

            switch(parsed.type) {
                case 'connected':
                    console.log(`✓ ${parsed.message}`);
                    break;
                    
                case 'system_info':
                    console.log('\n═══ SYSTEM INFORMATION ═══');
                    console.log(`Hostname: ${parsed.data.hostname}`);
                    console.log(`Platform: ${parsed.data.platform} (${parsed.data.arch})`);
                    console.log(`CPU:      ${parsed.data.cpuModel} (${parsed.data.cpuCount} cores)`);
                    console.log(`Memory:   ${parsed.data.usedMemory} / ${parsed.data.totalMemory} (${parsed.data.memoryUsagePercent})`);
                    console.log(`Uptime:   ${Math.floor(parsed.data.uptime / 3600)}h ${Math.floor((parsed.data.uptime % 3600) / 60)}m`);
                    console.log('═══════════════════════════\n');
                    break;
                    
                case 'process_update':
                    if (parsed.count > 0) {
                        console.log(`\n⚠️  ${parsed.count} HIGH RESOURCE PROCESSES | Dubai Time: ${dubaiTime}`);
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
        console.error('❌ Error parsing data:', error.message);
    }
});

client.on('close', () => {
    console.log('\n📡 Connection closed by server.');
    process.exit(0);
});

client.on('error', (err) => {
    console.error('❌ Connection error:', err.message);
    process.exit(1);
});

// Graceful Shutdown
process.on('SIGINT', () => {
    console.log('\n🔌 Disconnecting...');
    client.destroy();
});