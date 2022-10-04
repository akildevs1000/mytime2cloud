<?php

namespace App\Console\Commands;

use App\Models\AttendanceLog;
use App\Models\Device;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log as Logger;


class UpdateCompanyIds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:update_company_ids';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Company Ids';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        // return 282+499+245+257+335+209;
        // get device ids with company ids = 0
        $model = AttendanceLog::query();
        $model->distinct('DeviceID');
        $model->where("company_id", 0);
        $model->take(10);
        $free_device_ids = $model->pluck("DeviceID");

        // get company ids against found device ids
        $rows = Device::whereIn("device_id", $free_device_ids)->get(["company_id", "device_id as DeviceID"])->toArray();

        if (count($rows) == 0 || count($free_device_ids) == 0) {
            Logger::channel("custom")->info('No new record found while updating company ids for device');
            return 'No new record found while updating company ids for device';
        }

        foreach ($rows as $arr) {
            try {
                AttendanceLog::where("company_id", 0)->where("DeviceID", $arr["DeviceID"])->update($arr);
            } catch (\Throwable $th) {
                Logger::channel("custom")->error('Error occured while updating company ids.');
                Logger::channel("custom")->error('Error Details: ' . $th);
                $th;
            }
        }
        Logger::channel("custom")->info("Company IDS has been updated. Details: " . json_encode($rows));

        return "Company IDS has been updated. Details: " . json_encode($rows);
    }
}
