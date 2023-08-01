<?php

use App\Http\Controllers\VisitorAttendanceController;
use Illuminate\Support\Facades\Route;

Route::apiResource('visitor_attendance', VisitorAttendanceController::class);
Route::get('visitor_monthly', [VisitorAttendanceController::class, "monthly_pdf"]);
Route::get('visitor_weekly', [VisitorAttendanceController::class, "monthly_pdf"]);
Route::get('visitor_monthly_download_pdf', [VisitorAttendanceController::class, "monthly_download_pdf"]);
Route::get('visitor_weekly_download_pdf', [VisitorAttendanceController::class, "monthly_download_pdf"]);
Route::get('visitor_weekly_download_csv', [VisitorAttendanceController::class, "monthly_download_csv"]);
Route::get('visitor_monthly_download_csv', [VisitorAttendanceController::class, "monthly_download_csv"]);
