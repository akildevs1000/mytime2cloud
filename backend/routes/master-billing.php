<?php

use App\Http\Controllers\Master\InvoiceController;
use App\Http\Controllers\Master\PaymentController;
use Illuminate\Support\Facades\Route;

// TODO(auth): wrap this group in ['middleware' => 'auth:sanctum'] (plus a
// master-admin gate / is_master check) once master auth is standardized across
// routes/admin.php. The master-app frontend already sends a Bearer token.
Route::prefix('master')->group(function () {
    Route::get ('payments',                [PaymentController::class, 'index']);
    Route::get ('payments/{id}',           [PaymentController::class, 'show']);
    Route::post('payments',                [PaymentController::class, 'store']);
    Route::get ('companies/{id}/payments', [PaymentController::class, 'companyHistory']);

    Route::get ('invoices',                [InvoiceController::class, 'index']);
    Route::get ('invoices/{id}',           [InvoiceController::class, 'show']);
    Route::get ('invoices/{id}/pdf',       [InvoiceController::class, 'pdf']);
    Route::post('invoices/{id}/email',     [InvoiceController::class, 'email']);
});
