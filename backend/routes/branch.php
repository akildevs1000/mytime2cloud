<?php

use App\Http\Controllers\CompanyBranchController;
use Illuminate\Support\Facades\Route;

Route::apiResource('/branch', CompanyBranchController::class);