<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RecordController;
use App\Http\Controllers\SDKController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

Route::get('/get_devices', [RecordController::class, 'get_devices']);
Route::get('/get_logs_from_sdk', [RecordController::class, 'get_logs_from_sdk']);

Route::post('/{id}/WriteTimeGroup', [SDKController::class, 'processTimeGroup']);
Route::post('/Person/AddRange', [SDKController::class, 'PersonAddRange']);
