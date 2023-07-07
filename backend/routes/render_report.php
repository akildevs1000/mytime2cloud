<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Shift\RenderController;

Route::get('render_multi_inout_report', [RenderController::class, 'renderMultiInoutReport']);
