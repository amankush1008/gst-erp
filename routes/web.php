<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PartyController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;

// ── Auth Routes ──────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',   [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',[AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ── Authenticated Routes ─────────────────────────────────────────────────────
Route::middleware(['auth', App\Http\Middleware\BusinessSelected::class])->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/chart-data', [DashboardController::class, 'chartData'])->name('dashboard.chart');

    // Profile & Business Switcher
    Route::get('/profile',         [AuthController::class, 'profile'])->name('profile');
    Route::put('/profile',         [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::post('/switch-business',[AuthController::class, 'switchBusiness'])->name('business.switch');

    // ── Invoices ──────────────────────────────────────────────────────────────
    Route::resource('invoices', InvoiceController::class);
    Route::get('/invoices/{invoice}/pdf',        [InvoiceController::class, 'pdf'])->name('invoices.pdf');
    Route::post('/invoices/{invoice}/duplicate', [InvoiceController::class, 'duplicate'])->name('invoices.duplicate');
    Route::post('/invoices/{invoice}/payment',   [InvoiceController::class, 'recordPayment'])->name('invoices.payment');
    Route::post('/invoices/{invoice}/eway-bill', [InvoiceController::class, 'generateEwayBill'])->name('invoices.eway');

    // ── Products ──────────────────────────────────────────────────────────────
    Route::resource('products', ProductController::class);
    Route::post('/products/{product}/adjust-stock', [ProductController::class, 'adjustStock'])->name('products.adjust-stock');
    Route::get('/products/{product}/barcode',        [ProductController::class, 'generateBarcode'])->name('products.barcode');
    Route::get('/products/search',                   [ProductController::class, 'search'])->name('products.search');

    // ── Parties ──────────────────────────────────────────────────────────────
    Route::resource('parties', PartyController::class);
    Route::get('/parties/{party}/ledger',            [PartyController::class, 'ledger'])->name('parties.ledger');
    Route::get('/parties/verify-gstin/{gstin}',      [PartyController::class, 'verifyGstin'])->name('parties.verify-gstin');

    // ── Purchases ────────────────────────────────────────────────────────────
    Route::resource('purchases', PurchaseController::class);

    // ── Payments ─────────────────────────────────────────────────────────────
    Route::resource('payments', PaymentController::class)->only(['index','create','store']);
    Route::get('/payments/unpaid-invoices', [PaymentController::class, 'getUnpaidInvoices'])->name('payments.unpaid-invoices');

    // ── Expenses ─────────────────────────────────────────────────────────────
    Route::resource('expenses', ExpenseController::class)->only(['index','create','store','destroy']);

    // ── Reports ───────────────────────────────────────────────────────────────
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/sales',          [ReportController::class, 'sales'])->name('sales');
        Route::get('/purchases',      [ReportController::class, 'purchases'])->name('purchases');
        Route::get('/profit-loss',    [ReportController::class, 'profitLoss'])->name('profit-loss');
        Route::get('/gstr1',          [ReportController::class, 'gstr1'])->name('gstr1');
        Route::get('/gstr3b',         [ReportController::class, 'gstr3b'])->name('gstr3b');
        Route::get('/stock',          [ReportController::class, 'stock'])->name('stock');
        Route::get('/party-ledger',   [ReportController::class, 'partyLedger'])->name('party-ledger');
        Route::get('/export/{type}',  [ReportController::class, 'export'])->name('export');
    });

    // ── Settings ──────────────────────────────────────────────────────────────
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/business',          [SettingsController::class, 'business'])->name('business');
        Route::put('/business',          [SettingsController::class, 'updateBusiness'])->name('business.update');

        Route::get('/custom-fields',     [SettingsController::class, 'customFields'])->name('custom-fields');
        Route::post('/custom-fields',    [SettingsController::class, 'storeCustomField'])->name('custom-fields.store');
        Route::delete('/custom-fields/{id}', [SettingsController::class, 'deleteCustomField'])->name('custom-fields.delete');

        Route::get('/warehouses',        [SettingsController::class, 'warehouses'])->name('warehouses');
        Route::post('/warehouses',       [SettingsController::class, 'storeWarehouse'])->name('warehouses.store');

        Route::get('/units',             [SettingsController::class, 'units'])->name('units');
        Route::post('/units',            [SettingsController::class, 'storeUnit'])->name('units.store');

        Route::get('/users',             [SettingsController::class, 'users'])->name('users');
        Route::get('/number-formats',    [SettingsController::class, 'numberFormats'])->name('number-formats');
        Route::post('/number-formats',   [SettingsController::class, 'updateNumberFormat'])->name('number-formats.update');

        Route::get('/activity-log',      [SettingsController::class, 'activityLog'])->name('activity-log');
        Route::get('/backup',            [SettingsController::class, 'backup'])->name('backup');
    });
});
