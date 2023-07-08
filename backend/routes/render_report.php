<?php

use App\Http\Controllers\Shift\FiloShiftController;
use App\Http\Controllers\Shift\MultiInOutShiftController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Shift\RenderController;
use App\Http\Controllers\Shift\SingleShiftController;
use Illuminate\Http\Request;

Route::get('render_multi_inout_report', [RenderController::class, 'renderMultiInOut']);


Route::get('/processByManual', function (Request $request) {

    return [
        "MultiInOut" => (new MultiInOutShiftController)->processByManual($request),
        // "Single" => (new SingleShiftController)->processByManual($request),
        // "FILO" => (new FiloShiftController)->processByManual($request),

    ];
});
