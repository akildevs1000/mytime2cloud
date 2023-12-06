<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TanentController;

Route::apiResource('/tanent', TanentController::class);


Route::post('/tanent-update/{id}', [TanentController::class,"tanentUpdate"]);


