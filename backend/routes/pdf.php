<?php

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Reports\ReportController;
use App\Http\Controllers\Reports\WeeklyController;
use App\Http\Controllers\Reports\MonthlyController;

Route::get('report', [ReportController::class, 'index']);

//daily
Route::get('/daily', [Controller::class, 'daily']);
Route::get('/daily_download', [Controller::class, 'daily_download']);

// weekly
Route::get('/weekly_summary', [WeeklyController::class, 'weekly_summary']);
Route::get('/weekly_present', [WeeklyController::class, 'weekly_present']);
Route::get('/weekly_absent', [WeeklyController::class, 'weekly_absent']);
Route::get('/weekly_missing', [WeeklyController::class, 'weekly_missing']);
Route::get('/weekly_details', [WeeklyController::class, 'weekly_details']);


//monthly
Route::get('/monthly_details', [MonthlyController::class, 'monthly_details']);
Route::get('/monthly_summary', [MonthlyController::class, 'monthly_summary']);
Route::get('/monthly_present', [MonthlyController::class, 'monthly_present']);
Route::get('/monthly_absent', [MonthlyController::class, 'monthly_absent']);
Route::get('/monthly_late_in', [MonthlyController::class, 'monthly_late_in']);
Route::get('/monthly_early_out', [MonthlyController::class, 'monthly_early_out']);
Route::get('/monthly_performance', [MonthlyController::class, 'monthly_performance']);


//for testing static
Route::get('/daily_html', [Controller::class, 'daily_html']);
Route::get('/weekly_html', [WeeklyController::class, 'weekly_html']);
Route::get('/monthly_html', [MonthlyController::class, 'monthly_html']);
