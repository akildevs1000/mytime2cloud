<?php

namespace Database\Seeders;

use Database\Seeders\BranchTableSeeder;
use Database\Seeders\CompanySeeder;
use Database\Seeders\CompSeederTable;
use Database\Seeders\DepartmentTableSeeder;
use Database\Seeders\DesignationsTableSeeder;
use Database\Seeders\DeviceStatusSeeder;
use Database\Seeders\EmployeeSeederTable;
use Database\Seeders\MasterSeeder;
use Database\Seeders\ModuleSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\ShiftTypeTableSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        if (env('SEEDER_TYPE') == 'fahath') {
            $this->call([RoleSeeder::class]);
            $this->call([DepartmentTableSeeder::class]);
            $this->call([DesignationsTableSeeder::class]);
            $this->call([EmployeeSeederTable::class]);
            $this->call([PermissionSeeder::class]);
            $this->call([DeviceStatusSeeder::class]);
            $this->call([ModuleSeeder::class]);
            $this->call([ShiftTypeTableSeeder::class]);
            $this->call([CompSeederTable::class]);
            $this->call([MasterSeeder::class]);
        } else {

            // $start = strtotime("10 april 2022");
            // $end = strtotime("01 aug 2022");

            // $ids = Employee::pluck("employee_id");

            // foreach ($ids as $id) {
            //     $date_range = mt_rand($start, $end);
            //     $date = date("Y-m-d H:i:s", $date_range);

            //     $employee = AttendanceLog::create([
            //         "UserID" => $id,
            //         "DeviceID" => "OX-8862021010011",
            //         "LogTime" => $date,
            //         "company_id" => 1
            //     ]);
            // }

            $this->call([MasterSeeder::class]);
            $this->call([RoleSeeder::class]);
            $this->call([PermissionSeeder::class]);
            $this->call([DeviceStatusSeeder::class]);
            $this->call([CompanySeeder::class]);
            $this->call([ModuleSeeder::class]);

            $this->call([BranchTableSeeder::class]);
            $this->call([DepartmentTableSeeder::class]);
            $this->call([DesignationsTableSeeder::class]);

            $this->call([ShiftTypeTableSeeder::class]);

            // \App\Models\User::factory(10)->create();

            // \App\Models\User::factory()->create([
            //     'name' => 'Test User',
            //     'email' => 'test@example.com',
            // ]);

        }
    }
}
