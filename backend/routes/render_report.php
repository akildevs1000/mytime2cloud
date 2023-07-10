<?php

use App\Http\Controllers\Shift\FiloShiftController;
use App\Http\Controllers\Shift\MultiInOutShiftController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Shift\RenderController;
use App\Http\Controllers\Shift\SingleShiftController;
use Illuminate\Http\Request;

Route::get('render_multi_inout_report', [RenderController::class, 'renderMultiInOut']);
Route::get('render_general_report', [RenderController::class, 'renderGeneral']);

Route::get('render_off/{company_id}', [RenderController::class, 'renderOff']);
