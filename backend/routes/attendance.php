<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceLogMissingController;
use Illuminate\Support\Facades\Route;

Route::post('seed_default_data', [AttendanceController::class, "seedDefaultDataManual"]);
Route::get('attendance_avg_clock', [AttendanceController::class, "attendance_avg_clock"]);
Route::get('company_stats', [AttendanceController::class, "companyStats"]);
Route::get('company_stats_hourly_trends', [AttendanceController::class, "companyStatsHourlyTrends"]);
Route::get('company_stats_department_breakdown', [AttendanceController::class, "companyStatsDepartmentBreakdown"]);
Route::get('company_stats_punctuality', [AttendanceController::class, "companyStatsPunctuality"]);
Route::get('get_attendance_tabs', [AttendanceController::class, "getAttendanceTabsDisplay"]);
Route::get('regenerate-attendance', [AttendanceController::class, "regenerateAttendance"]);

Route::get('attendance-logs-missing', [AttendanceLogMissingController::class, "GetMissingLogs"]);