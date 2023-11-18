<?php

use App\Http\Controllers\DeviceController;
use App\Http\Controllers\DeviceStatusController;
use Illuminate\Support\Facades\Route;

// Device
Route::apiResource('device', DeviceController::class);
Route::get('device-list', [DeviceController::class, 'dropdownList']);

Route::get('device/search/{key}', [DeviceController::class, 'search']);
Route::get('device-by-user/{id}', [DeviceController::class, 'getDeviceByUserId']);
Route::post('device/details', [DeviceController::class, 'getDeviceCompany']);
Route::get('device/getLastRecordsByCount/{company_id}/{count}', [DeviceController::class, 'getLastRecordsByCount']);
Route::get('device/getLastRecordsHistory/{company_id}/{count}', [DeviceController::class, 'getLastRecordsHistory']);
//Route::get('device/getLastRecordsByCount', [DeviceController::class, 'getLastRecordsByCount']);
Route::post('device/delete/selected', [DeviceController::class, 'deleteSelected']);
Route::get('device_list', [DeviceController::class, 'getDeviceList']);
Route::get('devcie_count_Status/{company_id}', [DeviceController::class, 'devcieCountByStatus']);

Route::get('sync_device_date_time/{device_id}', [DeviceController::class, "sync_device_date_time"]);

//  Device Status
Route::apiResource('device_status', DeviceStatusController::class);
Route::get('device_status/search/{key}', [DeviceStatusController::class, 'search']);
Route::post('device_status/delete/selected', [DeviceStatusController::class, 'deleteSelected']);


Route::post('update_devices_active_settings/{key}', [DeviceController::class, 'updateActiveTimeSettings']);
Route::get('get_device_active_settings/{key}', [DeviceController::class, 'getActiveTimeSettings']);
