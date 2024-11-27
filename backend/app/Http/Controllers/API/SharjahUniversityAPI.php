<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\Company;
use App\Models\Employee;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class SharjahUniversityAPI extends Controller
{


    public function readAttendanceAfterRender($attendanceArray)
    {


        foreach ($attendanceArray as $key => $attendance) {

            $data = collect($attendance)->only([
                'employee_id',
                'logDate',
                'in',
                'out',
                'device_id_in',
                'device_id_out'
            ])->toArray();

            // if ($attendance['company_id'] == 13) 
            {
                $logFile = "sharjah_attendance_api_logs/{$attendance['company_id']}-" . now()->format('d-m-Y') . ".log";
                Storage::append($logFile, json_encode($data) . PHP_EOL);
            }
        }
    }
}
