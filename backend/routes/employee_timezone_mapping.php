<?php

use App\Http\Controllers\EmployeeTimezoneMappingController;
use Illuminate\Support\Facades\Route;

Route::apiResource('/employee_timezone_mapping', EmployeeTimezoneMappingController::class);
