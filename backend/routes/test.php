<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::post('/do_spaces',function(Request $request){
    return $request->file("file")->storePublicly("upload","do") ? 1 : "0";
});

Route::post('/log_payload',function(Request $request){
    return $request->all();
});
