<?php

use App\Http\Controllers\GroupLoginController;
use Illuminate\Support\Facades\Route;

Route::apiResource('group-login', GroupLoginController::class);