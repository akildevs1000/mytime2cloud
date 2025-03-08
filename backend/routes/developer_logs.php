<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;

Route::get('/log-view', function () {

    $app_key = request("app_key");  // e.g., 22

    if ($app_key != env("APP_KEY")) {
        return response()->json(['message' => 'Invalid Key'], 400);
    }

    $folder = request("folder");  // e.g., 22
    $date = request("date");      // e.g., 2025-03-08
    $time = request("time");      // e.g., 17:13

    // Validate parameters
    if (!$folder || !$date || !$time) {
        return response()->json(['message' => 'Missing required parameters'], 400);
    }

    // Build the log file path dynamically
    $path = storage_path("app/logs/whatsapp/{$folder}/{$date}/{$time}.log");

    if (!File::exists($path)) {
        return response()->json(['message' => 'Log file not found'], 404);
    }
    $content = nl2br(File::get($path));

    return Response::make($content, 200);
});
