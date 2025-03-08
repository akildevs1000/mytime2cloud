<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;

Route::get('/log-view', function () {
    $path = storage_path('app/logs/whatsapp/22/2025-03-08/17:13.log');

    if (!File::exists($path)) {
        return response()->json(['message' => 'Log file not found'], 404);
    }
    $content = nl2br(File::get($path));

    return Response::make($content, 200, [
        'Content-Type' => 'text/plain',
    ]);
}); // Optional: Secure access