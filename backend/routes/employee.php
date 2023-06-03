<?php

use App\Http\Controllers\EmployeeController;
use Illuminate\Support\Facades\Route;

Route::post('employee-store', [EmployeeController::class, 'employeeStore']);
Route::get('employee-single/{id}', [EmployeeController::class, 'employeeSingle']);
Route::post('employee-update/{id}', [EmployeeController::class, 'employeeUpdate']);
Route::post('employee-department-update/{id}', [EmployeeController::class, 'employeeDepartmentUpdate']);
Route::post('employee-designation-update/{id}', [EmployeeController::class, 'employeeDesignationUpdate']);
Route::post('employee-delete/{id}', [EmployeeController::class, 'employeeDelete']);
