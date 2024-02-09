<?php

use App\Http\Controllers\Community\AccessControlController;
use Illuminate\Support\Facades\Route;


Route::get('access_control_report', [AccessControlController::class, 'index']);


// Route::get('/accessControlReport_print_pdf', [AccessControlController::class, 'accessControlReportPrint']);
// Route::get('/accessControlReport_download_pdf', [AccessControlController::class, 'accessControlReportDownload']);
