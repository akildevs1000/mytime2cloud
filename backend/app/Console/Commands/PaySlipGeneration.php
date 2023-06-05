<?php

namespace App\Console\Commands;

use App\Http\Controllers\Controller;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log as Logger;


class PaySlipGeneration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:payslip_generation {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'PaySlip Generation';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $id = $this->argument('id');

        $date = date("Y-m-d H:i:s");
        
        $script_name = "PaySlipGeneration";

        $meta = "[$date] Cron: $script_name.";

        try {
            $result = (new Controller())->xyz($id);
            echo $meta . " " . $result . ".\n"; //Payslips generated for Company Id = $id
        } catch (\Throwable $th) {
            Logger::channel("custom")->error('Cron: PaySlip Generation. Error Details: ' . $th);
            echo "[" . date("Y-m-d H:i:s") . "] Cron: PaySlip Generation. Error occurred while inserting logs.\n";
        }
    }
}
