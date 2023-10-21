<?php

namespace App\Console\Commands;

use App\Mail\SimpleEmail;
use Illuminate\Support\Facades\Mail;


use Illuminate\Console\Command;

class SendTestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send_test_email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Mail::to('akildevs1000@gmail.com')->send(new SimpleEmail());
        return "Email sent successfully!";
    }
}
