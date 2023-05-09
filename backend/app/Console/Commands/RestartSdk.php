<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RestartSdk extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'restart_sdk';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'to restart sdk';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        // info('restart sdk');
        echo exec('pm2 restart 0');

        // return 0;
    }
}