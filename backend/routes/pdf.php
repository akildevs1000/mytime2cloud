<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Controller;

//daily
Route::get('/daily_summary', [Controller::class, 'daily_summary']);
Route::get('/daily_present', [Controller::class, 'daily_present']);
Route::get('/daily_absent', [Controller::class, 'daily_absent']);
Route::get('/daily_missing', [Controller::class, 'daily_missing']);
Route::get('/daily_details', [Controller::class, 'daily_details']);

// weekly
Route::get('/weekly_summary', [Controller::class, 'weekly_summary']);
Route::get('/weekly_present', [Controller::class, 'weekly_present']);
Route::get('/weekly_absent', [Controller::class, 'weekly_absent']);
Route::get('/weekly_missing', [Controller::class, 'weekly_missing']);
Route::get('/weekly_details', [Controller::class, 'weekly_details']);


//monthly
Route::get('/monthly_details', [Controller::class, 'monthly_details']);
Route::get('/monthly_summary', [Controller::class, 'monthly_summary']);
Route::get('/monthly_present', [Controller::class, 'monthly_present']);

Route::get('/monthly_absent', [Controller::class, 'monthly_absent']);
Route::get('/monthly_late_in', [Controller::class, 'monthly_late_in']);
Route::get('/monthly_early_out', [Controller::class, 'monthly_early_out']);
Route::get('/monthly_performance', [Controller::class, 'monthly_performance']);