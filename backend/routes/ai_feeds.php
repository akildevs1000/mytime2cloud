<?php

use App\Http\Controllers\AIFeedsController;
use Illuminate\Support\Facades\Route;

Route::get('ai-feeds', [AIFeedsController::class, 'index']);
