<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
// use Illuminate\Support\Facades\Log as Logger;
// use Illuminate\Support\Facades\Mail;
// use App\Mail\NotifyIfLogsDoesNotGenerate;


class DbBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:db_backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Database Backup';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        echo exec("php artisan backup:run --only-db");



        // $data["email"] = "aatmaninfotech@gmail.com";
        // $data["title"] = "From ItSolutionStuff.com";
        // $data["body"] = "This is Demo";

        // $files = [
        //     public_path('files/160031367318.pdf'),
        //     public_path('files/1599882252.png'),
        // ];

        // Mail::send('emails.myTestMail', $data, function($message)use($data, $files) {
        //     $message->to($data["email"], $data["email"])
        //             ->subject($data["title"]);

        //     foreach ($files as $file){
        //         $message->attach($file);
        //     }

        // });

        // dd('Mail sent successfully');
    }
}