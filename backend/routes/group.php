<?php

use App\Http\Controllers\BranchGroupLoginController;
use App\Http\Controllers\GroupLoginController;
use Illuminate\Support\Facades\Route;

Route::apiResource('branch-group-login', BranchGroupLoginController::class);
Route::apiResource('group-login', GroupLoginController::class);