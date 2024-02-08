<?php

use App\Http\Controllers\Community\AccessControlController;
use Illuminate\Support\Facades\Route;


Route::get('access_control_report', [AccessControlController::class, 'index']);