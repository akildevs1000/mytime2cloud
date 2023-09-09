<?php

use App\Http\Controllers\AttendanceController;
use Illuminate\Support\Facades\Route;

Route::post('seed_default_data', [AttendanceController::class, "seedDefaultDataManual"]);
