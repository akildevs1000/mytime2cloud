<?php

namespace App\Console\Commands;

use App\Http\Controllers\AttendanceController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log as Logger;
use Symfony\Component\Console\Input\InputOption;

class AttendanceSeeder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // protected $signature = 'task:attendance_seeder';

    protected function configure()
    {
        $this->setName('task:attendance_seeder')
            ->setDescription('Description of your command')
            ->addOption('company_id', null, InputOption::VALUE_OPTIONAL, 'Seperate Company Id')
            ->addOption('employee_id', null, InputOption::VALUE_OPTIONAL, 'Employee Id to render the data')
            ->addOption('day_count', null, InputOption::VALUE_OPTIONAL, 'No of day count to generate data');
    }

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Attendance Logs';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $company_id = $this->option('company_id');
        $employee_id = $this->option('employee_id');
        $day_count = $this->option('day_count') ?? 1;

        try {
            echo (new AttendanceController)->seedDefaultData($company_id, $employee_id, $day_count);
        } catch (\Throwable $th) {
            Logger::channel("custom")->error('Cron: AttendanceSeeder. Error Details: ' . $th);
            $date = date("Y-m-d H:i:s");
            echo "[$date] Cron: SyncFiloShift. Error occured while inserting logs.\n";
        }
    }
}
