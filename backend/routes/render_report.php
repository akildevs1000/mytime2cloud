<?php

use App\Http\Controllers\Shift\RenderController;
use Illuminate\Support\Facades\Route;

Route::get('render_multi_inout_report', [RenderController::class, 'renderMultiInOut']);
Route::get('render_general_report', [RenderController::class, 'renderGeneral']);

Route::get('render_off/{company_id}', [RenderController::class, 'renderOff']);
Route::get('render_absent/{company_id}', [RenderController::class, 'renderAbsent']);
Route::get('render_leaves/{company_id}', [RenderController::class, 'renderLeaves']);