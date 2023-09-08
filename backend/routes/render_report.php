<?php

use App\Http\Controllers\Shift\FiloShiftController;
use App\Http\Controllers\Shift\RenderController;
use App\Http\Controllers\Shift\SingleShiftController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('render_logs', [RenderController::class, 'renderLogs']);

Route::get('render_logs', [FiloShiftController::class, 'renderData']);


Route::get('/render_logs', function (Request $request) {

    $filo = (new FiloShiftController)->renderData($request);
    $single = (new SingleShiftController)->renderData($request);


    return  ["Filo Shift" => $filo, "Single Shift" => $single];
});



Route::get('render_multi_inout_report', [RenderController::class, 'renderMultiInOut']);
Route::get('render_general_report', [RenderController::class, 'renderGeneral']);

Route::get('render_off', [RenderController::class, 'renderOff']);
Route::get('render_absent', [RenderController::class, 'renderAbsent']);
Route::get('render_leaves/{company_id}', [RenderController::class, 'renderLeaves']);
Route::get('render_holidays/{company_id}', [RenderController::class, 'renderHolidays']);
Route::get('renderLeavesCron/{company_id}', [RenderController::class, 'renderLeavesCron']);
Route::get('renderHolidaysCron/{company_id}', [RenderController::class, 'renderHolidaysCron']);

Route::post('render_employee_report', [RenderController::class, 'renderEmployeeReport']);
