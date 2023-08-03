<?php

use App\Http\Controllers\VisitorAttendanceController;
use Illuminate\Support\Facades\Route;

Route::apiResource('visitor_attendance', VisitorAttendanceController::class);

Route::get('visitor_attendance_report', [VisitorAttendanceController::class, "report"]);
Route::get('visitor_log_list', [VisitorAttendanceController::class, 'visitor_log_list']);
