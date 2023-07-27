<?php

use App\Http\Controllers\Dashboards\VisitorDashboard;
use App\Http\Controllers\VisitorController;
use App\Http\Controllers\VisitorMappingController;
use Illuminate\Support\Facades\Route;

Route::get('visitor-count', VisitorDashboard::class);
Route::post('visitor/{id}', [VisitorController::class, 'update']);

Route::post('/visitor_test', [VisitorController::class, "store_test"]);

Route::apiResource('visitor', VisitorController::class);
Route::get('/get_visitors_with_timezonename', [VisitorMappingController::class, 'get_visitors_with_timezonename']);
Route::post('/visitor_timezone_mapping', [VisitorMappingController::class, "store"]);



// use App\Http\Controllers\EmployeeTimezoneMappingController;

Route::apiResource('/employee_timezone_mapping', EmployeeTimezoneMappingController::class);
// Route::get('/getemployees_timezoneids', [EmployeeTimezoneMappingController::class, 'get_employees_timezoneids']);
// Route::post('/deletetimezone', [EmployeeTimezoneMappingController::class, 'deleteTimezone']);
// Route::get('/gettimezonesinfo', [EmployeeTimezoneMappingController::class, 'gettimezonesinfo']);
// Route::get('/gettimezonesinfo/search/{key}', [EmployeeTimezoneMappingController::class, 'gettimezonesinfo_search']);
// Route::get('/get_employeeswith_timezonename_id/{id}', [EmployeeTimezoneMappingController::class, 'get_employeeswith_timezonename_id']);
