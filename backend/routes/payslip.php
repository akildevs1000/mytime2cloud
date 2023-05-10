<?php

use App\Http\Controllers\PayslipController;
use Illuminate\Support\Facades\Route;


// whatsapp
Route::get('/payslip/{id}', [PayslipController::class, 'show']);

