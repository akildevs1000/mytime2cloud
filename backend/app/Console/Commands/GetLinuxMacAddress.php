<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GetMacAddress extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mac:address';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get MAC address from Linux Ubuntu';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Execute shell command to get MAC address
        $macAddress = shell_exec("ifconfig | grep 'ether' | awk '{print $2}'");

        $this->info("MAC Address: $macAddress");
    }
}