<?php

use App\Http\Controllers\ZoneController;
use Illuminate\Support\Facades\Route;

Route::get('zone_list', [ZoneController::class, "zone_list"]);

Route::apiResource('zone', ZoneController::class);
