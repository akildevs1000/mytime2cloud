<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;

Route::get('/log-view', function () {

    $app_key = request("app_key");

    if ($app_key != env("APP_KEY")) {
        return response()->json(['message' => 'Invalid Key'], 400);
    }

    $url = request("url"); 

    $path = storage_path($url);

    if (!File::exists($path)) {
        return response()->json(['message' => 'Log file not found'], 404);
    }

    return Response::make(File::get($path), 200);
});
