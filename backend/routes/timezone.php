<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TimezoneController;

Route::apiResource('timezone', TimezoneController::class);

Route::post('getTimezoneJson', [TimezoneController::class, 'getTimezoneJson']);
Route::post('storeTimezoneDefaultJson', [TimezoneController::class, 'storeTimezoneDefaultJson']);
