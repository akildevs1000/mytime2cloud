<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessSDKCommand implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $url;
    protected $preparedJson;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($url, $preparedJson)
    {
        $this->url = $url;
        $this->preparedJson = $preparedJson;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            return Http::timeout(60)->withoutVerifying()->withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->url, $this->preparedJson);
        } catch (\Exception $e) {
            return [
                "status" => 102,
                "message" => $e->getMessage(),
            ];
            // You can log the error or perform any other necessary actions here
        }
        // return (new Controller)->SDKCommand($this->url, $this->preparedJson);
    }
}
