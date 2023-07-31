<?php

namespace Database\Seeders;

use App\Models\VisitorAttendance;
use Illuminate\Database\Seeder;

class VisitorAttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // php artisan db:seed --class=VisitorAttendanceSeeder
        VisitorAttendance::factory()->count(10)->create();
    }
}
