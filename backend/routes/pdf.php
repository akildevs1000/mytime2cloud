<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Controller;

Route::get('/monthly_details', [Controller::class, 'monthly_details']);
Route::get('/monthly_summary', [Controller::class, 'monthly_summary']);
Route::get('/monthly_present', [Controller::class, 'monthly_present']);

Route::get('/monthly_absent', [Controller::class, 'monthly_absent']);
Route::get('/monthly_late_in', [Controller::class, 'monthly_late_in']);
Route::get('/monthly_early_out', [Controller::class, 'monthly_early_out']);
Route::get('/monthly_performance', [Controller::class, 'monthly_performance']);
