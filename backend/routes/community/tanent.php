<?php

use App\Http\Controllers\Community\TanentController;
use Illuminate\Support\Facades\Route;

Route::apiResource('/tanent', TanentController::class);
Route::post('/tanent-update/{id}', [TanentController::class,"tanentUpdate"]);
Route::post('/tanent-validate', [TanentController::class,"validateTanent"]);
Route::post('/tanent-update-validate/{id}', [TanentController::class,"validateUpdateTanent"]);



