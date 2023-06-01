<?php

use App\Http\Controllers\EmployeeController;
use Illuminate\Support\Facades\Route;

Route::post('employee-store', [EmployeeController::class, 'employeeStore']);
Route::post('employee-update/{id}', [EmployeeController::class, 'employeeUpdate']);
Route::post('employee-delete/{id}', [EmployeeController::class, 'employeeDelete']);
