<?php

use App\Http\Controllers\Reports\AutoReportController;
use App\Http\Controllers\Reports\ManualReportController;
use Illuminate\Support\Facades\Route;


Route::get('custom_report', [ReportController::class, 'custom_report']);

Route::get('manual_report', [ManualReportController::class, 'custom_report']);
Route::post('manual_report', [ManualReportController::class, 'store']);
Route::get('auto_report', [AutoReportController::class, 'custom_report']);
Route::post('auto_report', [AutoReportController::class, 'store']);
Route::get('SyncDefaultAttendance', [AutoReportController::class, 'SyncDefaultAttendance']);

Route::get('no_report', [ReportController::class, 'no_report']);
Route::get('overnight_report', [ReportController::class, 'overnight_report']);
Route::get('odd_even_report', [ReportController::class, 'odd_even_report']);