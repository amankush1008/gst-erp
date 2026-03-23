<?php
// =============================================================================
// routes/web.php
// =============================================================================

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    DashboardController,
    InvoiceController,
    ProductController,
    PartyController,
    PurchaseController,
    PaymentController,
    ExpenseController,
    ReportController,
    SettingsController,
    BusinessController,
};

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [App\Http\Controllers\Auth\AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [App\Http\Controllers\Auth\AuthController::class, 'login']);
    Route::get('/register', [App\Http\Controllers\Auth\AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [App\Http\Controllers\Auth\AuthController::class, 'register']);
});

Route::post('/logout', [App\Http\Controllers\Auth\AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Authenticated routes
Route::middleware(['auth', 'business.selected'])->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Business switcher
    Route::get('/business/switch/{business}', [BusinessController::class, 'switch'])->name('business.switch');
    Route::resource('business', BusinessController::class)->except(['show']);

    // Invoices
    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        Route::get('/create', [InvoiceController::class, 'create'])->name('create');
        Route::post('/', [InvoiceController::class, 'store'])->name('store');
        Route::get('/{invoice}', [InvoiceController::class, 'show'])->name('show');
        Route::get('/{invoice}/edit', [InvoiceController::class, 'edit'])->name('edit');
        Route::put('/{invoice}', [InvoiceController::class, 'update'])->name('update');
        Route::delete('/{invoice}', [InvoiceController::class, 'destroy'])->name('destroy');
        Route::get('/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('pdf');
        Route::post('/{invoice}/duplicate', [InvoiceController::class, 'duplicate'])->name('duplicate');
        Route::post('/{invoice}/payment', [InvoiceController::class, 'recordPayment'])->name('payment');
        Route::post('/{invoice}/eway-bill', [InvoiceController::class, 'generateEwayBill'])->name('eway-bill');
        Route::get('/export/excel', [InvoiceController::class, 'export'])->name('export');
    });

    // Credit/Debit Notes
    Route::resource('credit-notes', CreditNoteController::class)->only(['index', 'create', 'store', 'show']);
    Route::resource('debit-notes', DebitNoteController::class)->only(['index', 'create', 'store', 'show']);
    Route::resource('proforma-invoices', ProformaController::class);

    // Purchases
    Route::prefix('purchases')->name('purchases.')->group(function () {
        Route::get('/', [PurchaseController::class, 'index'])->name('index');
        Route::get('/create', [PurchaseController::class, 'create'])->name('create');
        Route::post('/', [PurchaseController::class, 'store'])->name('store');
        Route::get('/{purchase}', [PurchaseController::class, 'show'])->name('show');
        Route::get('/{purchase}/edit', [PurchaseController::class, 'edit'])->name('edit');
        Route::put('/{purchase}', [PurchaseController::class, 'update'])->name('update');
        Route::delete('/{purchase}', [PurchaseController::class, 'destroy'])->name('destroy');
        Route::post('/{purchase}/payment', [PurchaseController::class, 'recordPayment'])->name('payment');
    });

    // Parties (Customers & Suppliers)
    Route::prefix('parties')->name('parties.')->group(function () {
        Route::get('/', [PartyController::class, 'index'])->name('index');
        Route::get('/create', [PartyController::class, 'create'])->name('create');
        Route::post('/', [PartyController::class, 'store'])->name('store');
        Route::get('/{party}', [PartyController::class, 'show'])->name('show');
        Route::get('/{party}/edit', [PartyController::class, 'edit'])->name('edit');
        Route::put('/{party}', [PartyController::class, 'update'])->name('update');
        Route::delete('/{party}', [PartyController::class, 'destroy'])->name('destroy');
        Route::post('/verify-gstin', [PartyController::class, 'verifyGstin'])->name('verify-gstin');
        Route::get('/{party}/ledger', [PartyController::class, 'ledger'])->name('ledger');
        Route::get('/{party}/statement', [PartyController::class, 'statement'])->name('statement');
    });

    // Products & Inventory
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/create', [ProductController::class, 'create'])->name('create');
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::get('/{product}', [ProductController::class, 'show'])->name('show');
        Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
        Route::put('/{product}', [ProductController::class, 'update'])->name('update');
        Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
        Route::post('/{product}/adjust-stock', [ProductController::class, 'adjustStock'])->name('adjust-stock');
        Route::get('/{product}/barcode', [ProductController::class, 'generateBarcode'])->name('barcode');
        Route::get('/search/ajax', [ProductController::class, 'search'])->name('search');
        Route::get('/import/template', [ProductController::class, 'importTemplate'])->name('import-template');
        Route::post('/import', [ProductController::class, 'import'])->name('import');
    });

    // Payments
    Route::resource('payments', PaymentController::class);

    // Expenses
    Route::resource('expenses', ExpenseController::class);
    Route::resource('expense-categories', ExpenseCategoryController::class);

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/sales', [ReportController::class, 'salesReport'])->name('sales');
        Route::get('/purchases', [ReportController::class, 'purchaseReport'])->name('purchases');
        Route::get('/profit-loss', [ReportController::class, 'profitLoss'])->name('profit-loss');
        Route::get('/stock', [ReportController::class, 'stockReport'])->name('stock');
        Route::get('/party-ledger', [ReportController::class, 'partyLedger'])->name('party-ledger');
        Route::get('/gstr1', [ReportController::class, 'gstr1'])->name('gstr1');
        Route::get('/gstr3b', [ReportController::class, 'gstr3b'])->name('gstr3b');
        Route::get('/tax-summary', [ReportController::class, 'taxSummary'])->name('tax-summary');
        Route::get('/payments', [ReportController::class, 'paymentsReport'])->name('payments');
        Route::get('/expenses', [ReportController::class, 'expensesReport'])->name('expenses');
        Route::get('/stock-movement', [ReportController::class, 'stockMovement'])->name('stock-movement');
        Route::get('/export/{type}', [ReportController::class, 'export'])->name('export');
    });

    // Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::post('/business', [SettingsController::class, 'updateBusiness'])->name('business');
        Route::post('/invoice', [SettingsController::class, 'updateInvoice'])->name('invoice');
        Route::post('/tax', [SettingsController::class, 'updateTax'])->name('tax');
        Route::post('/numbering', [SettingsController::class, 'updateNumbering'])->name('numbering');
        Route::resource('custom-fields', CustomFieldController::class);
        Route::resource('warehouses', WarehouseController::class);
        Route::resource('units', UnitController::class);
        Route::resource('product-categories', ProductCategoryController::class);
        Route::get('/users', [UserManagementController::class, 'index'])->name('users');
        Route::post('/users', [UserManagementController::class, 'store']);
        Route::put('/users/{user}', [UserManagementController::class, 'update']);
        Route::delete('/users/{user}', [UserManagementController::class, 'destroy']);
        Route::get('/activity-log', [SettingsController::class, 'activityLog'])->name('activity-log');
        Route::get('/backup', [SettingsController::class, 'backup'])->name('backup');
        Route::post('/backup/download', [SettingsController::class, 'downloadBackup'])->name('backup.download');
    });
});


// =============================================================================
// routes/api.php
// =============================================================================

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', fn(Request $request) => $request->user());
    Route::get('/businesses', [App\Http\Controllers\Api\BusinessApiController::class, 'index']);

    // Products API
    Route::prefix('products')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\ProductApiController::class, 'index']);
        Route::get('/search', [App\Http\Controllers\Api\ProductApiController::class, 'search']);
        Route::get('/{id}', [App\Http\Controllers\Api\ProductApiController::class, 'show']);
        Route::post('/', [App\Http\Controllers\Api\ProductApiController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\Api\ProductApiController::class, 'update']);
    });

    // Parties API
    Route::prefix('parties')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\PartyApiController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Api\PartyApiController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\Api\PartyApiController::class, 'show']);
        Route::post('/verify-gstin', [App\Http\Controllers\Api\PartyApiController::class, 'verifyGstin']);
    });

    // Invoices API
    Route::prefix('invoices')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\InvoiceApiController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Api\InvoiceApiController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\Api\InvoiceApiController::class, 'show']);
        Route::put('/{id}', [App\Http\Controllers\Api\InvoiceApiController::class, 'update']);
        Route::get('/{id}/pdf', [App\Http\Controllers\Api\InvoiceApiController::class, 'pdf']);
    });

    // Dashboard stats
    Route::get('/dashboard/stats', [App\Http\Controllers\Api\DashboardApiController::class, 'stats']);
    Route::get('/dashboard/charts', [App\Http\Controllers\Api\DashboardApiController::class, 'charts']);

    // GST
    Route::post('/gst/verify', [App\Http\Controllers\Api\GstApiController::class, 'verify']);
    Route::post('/gst/eway-bill', [App\Http\Controllers\Api\GstApiController::class, 'ewayBill']);

    // Reports
    Route::get('/reports/sales', [App\Http\Controllers\Api\ReportApiController::class, 'sales']);
    Route::get('/reports/gstr1', [App\Http\Controllers\Api\ReportApiController::class, 'gstr1']);
    Route::get('/reports/gstr3b', [App\Http\Controllers\Api\ReportApiController::class, 'gstr3b']);
});

// Public token generation
Route::post('/tokens/create', function (Request $request) {
    $request->validate(['email' => 'required|email', 'password' => 'required', 'device_name' => 'required']);
    $user = \App\Models\User::where('email', $request->email)->first();
    if (!$user || !\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }
    return response()->json(['token' => $user->createToken($request->device_name)->plainTextToken]);
});
