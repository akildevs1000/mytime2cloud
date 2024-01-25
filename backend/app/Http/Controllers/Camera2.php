<?php

namespace App\Http\Controllers;

use App\Http\Requests\Announcement\StoreRequest;
use App\Http\Requests\Announcement\UpdateRequest;
use App\Models\Announcement;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log as Logger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Camera2 extends Controller
{

    public function camera2PushEvents(Request $request)
    {

        try {
            $device_sn = $request->device_sn;
            $card_number = $request->card_number;
            $timestamp = $request->timestamp / 1000;
            $recognition_score = $request->recognition_score;
            //`${UserCode},${DeviceID},${RecordDate},${RecordNumber}`;
            // /`../backend/storage/app/camera/camera-logs-${formattedDate}.csv`
            if ($card_number > 0 && $device_sn != '') {
                $file_name = "camera/camera-logs-" . date("d-m-Y") . ".csv";
                $message = $card_number . "," . $device_sn . "," . date("Y-m-d H:i:s", $timestamp) . "," . $recognition_score;
                Storage::append($file_name, $message);
            } else {
                $file_name = "camera/camera2-error-logs-" . date("d-m-Y") . ".log";
                Logger::channel("custom")->error('Error occured while inserting Camera2 logs logs.');
                return $this->getMeta("Sync Attenance Camera2 Logs", " Error occured." . "\n");
            }
        } catch (\Throwable $th) {

            Logger::channel("custom")->error('Error occured while inserting Camera2 logs logs.');
            Logger::channel("custom")->error('Error Details: ' . $th);
            return $this->getMeta("Sync Attenance Camera2 Logs", " Error occured." . "\n");
        }
    }
}
