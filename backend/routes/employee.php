<?php

use App\Http\Controllers\EmployeeController;
use Illuminate\Support\Facades\Route;

Route::post('employee-store', [EmployeeController::class, 'employeeStore']);
