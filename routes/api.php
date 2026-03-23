<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiProductController;
use App\Http\Controllers\Api\ApiPartyController;
use App\Http\Controllers\Api\ApiInvoiceController;
use App\Http\Controllers\Api\ApiDashboardController;
use App\Http\Controllers\Api\ApiGstController;
use App\Http\Controllers\Api\ApiTokenController;

// ── Token Auth ────────────────────────────────────────────────────────────────
Route::post('/auth/token',  [ApiTokenController::class, 'generate'])->name('api.token');
Route::post('/auth/revoke', [ApiTokenController::class, 'revoke'])->middleware('auth:sanctum')->name('api.revoke');

// ── Protected API Routes ──────────────────────────────────────────────────────
Route::middleware(['auth:sanctum'])->group(function () {

    // Products
    Route::get('/products',             [ApiProductController::class, 'index']);
    Route::get('/products/{id}/stock',  [ApiProductController::class, 'stock']);

    // Parties
    Route::get('/parties',              [ApiPartyController::class, 'index']);
    Route::get('/parties/{id}/ledger',  [ApiPartyController::class, 'ledger']);

    // Invoices
    Route::get('/invoices',             [ApiInvoiceController::class, 'index']);
    Route::get('/invoices/{id}',        [ApiInvoiceController::class, 'show']);

    // Dashboard
    Route::get('/dashboard/stats',      [ApiDashboardController::class, 'stats']);
    Route::get('/dashboard/chart',      [ApiDashboardController::class, 'chart']);

    // GST
    Route::get('/gst/verify/{gstin}',   [ApiGstController::class, 'verifyGstin']);
    Route::post('/gst/eway-bill',        [ApiGstController::class, 'generateEwayBill']);

    // Reports
    Route::get('/reports/gstr1',         [App\Http\Controllers\ReportController::class, 'gstr1Api']);
    Route::get('/reports/gstr3b',        [App\Http\Controllers\ReportController::class, 'gstr3bApi']);
});
