<?php

use App\Http\Controllers\EmployeeController;
use Illuminate\Support\Facades\Route;

Route::post('employee-store', [EmployeeController::class, 'employeeStore']);
Route::get('employee-single/{id}', [EmployeeController::class, 'employeeSingle']);
Route::post('employee-update/{id}', [EmployeeController::class, 'employeeUpdate']);
Route::post('employee-login-update/{id}', [EmployeeController::class, 'employeeLoginUpdate']);
Route::post('employee-single-column-update/{id}', [EmployeeController::class, 'employeeUpdateBySingleColumn']);
Route::post('employee-delete/{id}', [EmployeeController::class, 'employeeDelete']);
