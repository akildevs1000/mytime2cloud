<?php

use App\Http\Controllers\PayslipController;
use Illuminate\Support\Facades\Route;


// whatsapp
Route::get('/payslip/{id}', [PayslipController::class, 'show']);
Route::get('/payslip-by-department/{id}', [PayslipController::class, 'index']);
