<?php

namespace App\Console\Commands;

use App\Models\Device;
use Illuminate\Console\Command;
// use Illuminate\Support\Facades\Log as Logger;
// use Illuminate\Support\Facades\Mail;
// use App\Mail\NotifyIfLogsDoesNotGenerate;


class CheckDeviceHealth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:check_device_health';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Device Health';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $devices = Device::pluck("device_id");

        $total_iterations = 0;
        $online_devices_count = 0;
        $offline_devices_count = 0;

        foreach ($devices as $device_id) {
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => "http://139.59.69.241:5000/CheckDeviceHealth/$device_id",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            $status = json_decode($response)->status;

            if ($status !== 200) {
                $offline_devices_count++;
            } else {
                $online_devices_count++;
            }

            Device::where("device_id", $device_id)->update(["status_id" => $status == 200 ? 1 : 2]);

            $total_iterations++;
        }

        $date = date("Y-m-d H:i:s");
        $script_name = "CheckDeviceHealth";

        $meta = "[$date] Cron: $script_name.";

        $result = "$offline_devices_count Devices offline. $online_devices_count Devices online. $total_iterations records found";

        $message =  $meta . " " . $result . ".\n";
        echo $message;
    }
}
