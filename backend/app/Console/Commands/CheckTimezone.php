<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;

class CheckTimezone extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-timezone';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check the application timezone and current date/time';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Retrieve the timezone from the config
        $timezone = config('app.timezone');

        // Get the current date and time in the application's timezone
        $currentDateTime = now($timezone)->format('l, F j, Y g:i A'); // Example: Wednesday, October 25, 2023 2:30 PM


        // Display the timezone and current date/time
        $this->info("The application timezone is: " . $timezone);
        $this->info("The current date and time is: " . $currentDateTime);
    }
}