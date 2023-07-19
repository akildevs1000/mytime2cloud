<?php

use App\Http\Controllers\Shift\RenderController;
use Illuminate\Support\Facades\Route;

Route::get('render_multi_inout_report', [RenderController::class, 'renderMultiInOut']);
Route::get('render_general_report', [RenderController::class, 'renderGeneral']);

Route::get('render_off', [RenderController::class, 'renderOff']);
Route::get('render_absent', [RenderController::class, 'renderAbsent']);
Route::get('render_leaves/{company_id}', [RenderController::class, 'renderLeaves']);