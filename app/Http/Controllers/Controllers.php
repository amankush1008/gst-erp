<?php
// =============================================================================
// app/Http/Controllers/InvoiceController.php
// =============================================================================
namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Party;
use App\Models\Product;
use App\Services\InvoiceService;
use App\Services\GstService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    public function __construct(
        private InvoiceService $invoiceService,
        private GstService $gstService
    ) {}

    public function index(Request $request)
    {
        $business = currentBusiness();
        $query = Invoice::with(['party', 'items'])
            ->where('business_id', $business->id)
            ->whereIn('invoice_type', ['tax_invoice', 'retail_invoice', 'proforma_invoice']);

        if ($request->status) $query->where('status', $request->status);
        if ($request->party_id) $query->where('party_id', $request->party_id);
        if ($request->date_from) $query->whereDate('invoice_date', '>=', $request->date_from);
        if ($request->date_to) $query->whereDate('invoice_date', '<=', $request->date_to);
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('invoice_number', 'like', "%{$request->search}%")
                  ->orWhereHas('party', fn($p) => $p->where('name', 'like', "%{$request->search}%"));
            });
        }

        $invoices = $query->orderBy('invoice_date', 'desc')->paginate(25);
        $summary = $this->invoiceService->getSummary($business->id, $request->all());

        return view('invoices.index', compact('invoices', 'summary'));
    }

    public function create()
    {
        $business = currentBusiness();
        $parties = Party::where('business_id', $business->id)->customers()->active()->get();
        $products = Product::where('business_id', $business->id)->active()->get();
        $warehouses = $business->warehouses()->where('is_active', true)->get();
        $nextNumber = $business->generateNumber('invoice');

        return view('invoices.create', compact('business', 'parties', 'products', 'warehouses', 'nextNumber'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'party_id' => 'required|exists:parties,id',
            'invoice_date' => 'required|date',
            'invoice_type' => 'required|in:tax_invoice,retail_invoice,proforma_invoice,credit_note,debit_note',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.rate' => 'required|numeric|min:0',
        ]);

        $invoice = $this->invoiceService->createInvoice($request->all());
        logActivity('invoices', 'create', "Invoice {$invoice->invoice_number} created", 'Invoice', $invoice->id);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', "Invoice {$invoice->invoice_number} created successfully!");
    }

    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        $invoice->load(['business', 'party', 'items.product', 'transport', 'payments.payment', 'meta']);
        return view('invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        $this->authorize('update', $invoice);
        $business = currentBusiness();
        $parties = Party::where('business_id', $business->id)->customers()->active()->get();
        $products = Product::where('business_id', $business->id)->active()->get();
        $invoice->load(['items', 'transport', 'meta']);

        return view('invoices.edit', compact('invoice', 'parties', 'products', 'business'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);
        $this->invoiceService->updateInvoice($invoice, $request->all());
        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice updated!');
    }

    public function destroy(Invoice $invoice)
    {
        $this->authorize('delete', $invoice);
        $this->invoiceService->deleteInvoice($invoice);
        return redirect()->route('invoices.index')->with('success', 'Invoice deleted!');
    }

    public function pdf(Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        $invoice->load(['business', 'party', 'items.product', 'transport']);
        $template = $invoice->template ?? 'default';

        $pdf = Pdf::loadView("invoices.templates.{$template}", compact('invoice'))
            ->setPaper('a4')
            ->setOptions(['dpi' => 150, 'defaultFont' => 'sans-serif']);

        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }

    public function duplicate(Invoice $invoice)
    {
        $newInvoice = $this->invoiceService->duplicateInvoice($invoice);
        return redirect()->route('invoices.edit', $newInvoice)->with('success', 'Invoice duplicated!');
    }

    public function recordPayment(Request $request, Invoice $invoice)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $invoice->balance_amount,
            'payment_date' => 'required|date',
            'payment_mode' => 'required',
        ]);

        $this->invoiceService->recordPayment($invoice, $request->all());
        return back()->with('success', 'Payment recorded!');
    }

    public function generateEwayBill(Invoice $invoice)
    {
        $result = $this->gstService->generateEwayBill($invoice);
        if ($result['success']) {
            $invoice->update(['eway_bill_number' => $result['eway_bill_number'], 'eway_bill_data' => $result]);
            return back()->with('success', "E-Way Bill {$result['eway_bill_number']} generated!");
        }
        return back()->with('error', $result['message']);
    }

    public function export(Request $request)
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\InvoicesExport($request->all()),
            'invoices-' . now()->format('Y-m-d') . '.xlsx'
        );
    }
}


// =============================================================================
// app/Http/Controllers/ProductController.php
// =============================================================================
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Unit;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(private InventoryService $inventoryService) {}

    public function index(Request $request)
    {
        $business = currentBusiness();
        $query = Product::with(['category', 'unit'])
            ->where('business_id', $business->id);

        if ($request->category_id) $query->where('category_id', $request->category_id);
        if ($request->type) $query->where('type', $request->type);
        if ($request->low_stock) $query->lowStock();
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('sku', 'like', "%{$request->search}%")
                  ->orWhere('barcode', 'like', "%{$request->search}%")
                  ->orWhere('hsn_code', 'like', "%{$request->search}%");
            });
        }

        $products = $query->withSum('stock', 'quantity')->orderBy('name')->paginate(25);
        $categories = ProductCategory::where('business_id', $business->id)->get();
        $lowStockCount = Product::where('business_id', $business->id)->lowStock()->count();

        return view('products.index', compact('products', 'categories', 'lowStockCount'));
    }

    public function create()
    {
        $business = currentBusiness();
        $categories = ProductCategory::where('business_id', $business->id)->get();
        $units = Unit::where('business_id', $business->id)->get();
        $customFields = \App\Models\CustomField::where('business_id', $business->id)
            ->where('module', 'product')->where('is_active', true)->get();
        $gstRates = [0, 3, 5, 12, 18, 28];

        return view('products.create', compact('business', 'categories', 'units', 'customFields', 'gstRates'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:product,service',
            'sale_price' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|in:0,3,5,12,18,28',
        ]);

        $product = $this->inventoryService->createProduct($request->all());
        return redirect()->route('products.show', $product)->with('success', 'Product created!');
    }

    public function show(Product $product)
    {
        $product->load(['category', 'unit', 'stock.warehouse', 'batches', 'movements' => fn($q) => $q->latest()->limit(20)]);
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $business = currentBusiness();
        $categories = ProductCategory::where('business_id', $business->id)->get();
        $units = Unit::where('business_id', $business->id)->get();
        $customFields = \App\Models\CustomField::where('business_id', $business->id)->where('module', 'product')->get();
        return view('products.edit', compact('product', 'categories', 'units', 'customFields'));
    }

    public function update(Request $request, Product $product)
    {
        $this->inventoryService->updateProduct($product, $request->all());
        return redirect()->route('products.show', $product)->with('success', 'Product updated!');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted!');
    }

    public function adjustStock(Request $request, Product $product)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|numeric',
            'type' => 'required|in:add,remove,set',
            'notes' => 'nullable|string',
        ]);
        $this->inventoryService->adjustStock($product, $request->all());
        return back()->with('success', 'Stock adjusted!');
    }

    public function generateBarcode(Product $product)
    {
        $barcodeValue = $product->barcode ?: $product->sku ?: $product->id;
        $generator = new \Milon\Barcode\DNS1D();
        $barcode = $generator->getBarcodeSVG($barcodeValue, 'C128');
        return response($barcode)->header('Content-Type', 'image/svg+xml');
    }

    public function search(Request $request)
    {
        $business = currentBusiness();
        $products = Product::where('business_id', $business->id)
            ->where('is_active', true)
            ->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->q}%")
                  ->orWhere('sku', 'like', "%{$request->q}%")
                  ->orWhere('barcode', $request->q);
            })
            ->with(['unit', 'stock'])
            ->limit(10)
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'sku' => $p->sku,
                'hsn_code' => $p->hsn_code,
                'sale_price' => $p->sale_price,
                'tax_rate' => $p->tax_rate,
                'unit' => $p->unit?->short_name,
                'stock' => $p->total_stock,
            ]);

        return response()->json($products);
    }
}


// =============================================================================
// app/Http/Controllers/PartyController.php
// =============================================================================
namespace App\Http\Controllers;

use App\Models\Party;
use App\Services\GstService;
use Illuminate\Http\Request;

class PartyController extends Controller
{
    public function __construct(private GstService $gstService) {}

    public function index(Request $request)
    {
        $business = currentBusiness();
        $type = $request->type ?? 'customer';
        $query = Party::where('business_id', $business->id);

        if ($type === 'customer') $query->customers();
        elseif ($type === 'supplier') $query->suppliers();

        if ($request->search) {
            $query->where(fn($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('gstin', 'like', "%{$request->search}%")
                ->orWhere('phone', 'like', "%{$request->search}%"));
        }

        $parties = $query->withCount(['invoices', 'purchases'])->orderBy('name')->paginate(25);
        return view('parties.index', compact('parties', 'type'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:customer,supplier,both',
            'gstin' => 'nullable|string|size:15',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:15',
        ]);

        $data = $request->all();
        $data['business_id'] = currentBusiness()->id;

        if ($data['gstin']) {
            $gstInfo = $this->gstService->verify($data['gstin']);
            $data['gstin_verified'] = $gstInfo['status'] === 'Active';
        }

        $party = Party::create($data);
        return redirect()->route('parties.show', $party)->with('success', 'Party created!');
    }

    public function show(Party $party)
    {
        $party->load(['invoices' => fn($q) => $q->latest()->limit(10), 'purchases' => fn($q) => $q->latest()->limit(10)]);
        $ledger = $this->getLedger($party);
        return view('parties.show', compact('party', 'ledger'));
    }

    public function verifyGstin(Request $request)
    {
        $request->validate(['gstin' => 'required|string|size:15']);
        $result = $this->gstService->verify($request->gstin);
        return response()->json($result);
    }

    private function getLedger(Party $party): array
    {
        $entries = collect();
        $party->invoices->each(fn($inv) => $entries->push(['date' => $inv->invoice_date, 'type' => 'invoice', 'ref' => $inv->invoice_number, 'debit' => $inv->total_amount, 'credit' => 0]));
        $party->purchases->each(fn($pur) => $entries->push(['date' => $pur->purchase_date, 'type' => 'purchase', 'ref' => $pur->purchase_number, 'debit' => 0, 'credit' => $pur->total_amount]));
        return $entries->sortBy('date')->values()->toArray();
    }
}


// =============================================================================
// app/Http/Controllers/DashboardController.php
// =============================================================================
namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Purchase;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\Product;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $business = currentBusiness();
        $businessId = $business->id;
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfYear = Carbon::createFromFormat('m-d', $business->financial_year_start)->startOfDay();
        if ($startOfYear->isFuture()) $startOfYear->subYear();

        // Summary cards
        $salesThisMonth = Invoice::where('business_id', $businessId)
            ->whereDate('invoice_date', '>=', $startOfMonth)
            ->whereNotIn('invoice_type', ['credit_note', 'debit_note'])
            ->sum('total_amount');

        $purchaseThisMonth = Purchase::where('business_id', $businessId)
            ->whereDate('purchase_date', '>=', $startOfMonth)->sum('total_amount');

        $expenseThisMonth = Expense::where('business_id', $businessId)
            ->whereDate('expense_date', '>=', $startOfMonth)->sum('total_amount');

        $profit = $salesThisMonth - $purchaseThisMonth - $expenseThisMonth;

        $totalReceivable = Invoice::where('business_id', $businessId)
            ->whereIn('payment_status', ['unpaid', 'partial'])->sum('balance_amount');

        $totalPayable = Purchase::where('business_id', $businessId)
            ->whereIn('payment_status', ['unpaid', 'partial'])->sum('balance_amount');

        $overdueInvoices = Invoice::where('business_id', $businessId)->overdue()->count();
        $lowStockCount = Product::where('business_id', $businessId)->lowStock()->count();

        // Sales chart (last 12 months)
        $salesChart = collect(range(11, 0))->map(function($i) use ($businessId) {
            $month = Carbon::now()->subMonths($i);
            return [
                'month' => $month->format('M Y'),
                'sales' => Invoice::where('business_id', $businessId)
                    ->whereYear('invoice_date', $month->year)
                    ->whereMonth('invoice_date', $month->month)
                    ->sum('total_amount'),
                'purchase' => Purchase::where('business_id', $businessId)
                    ->whereYear('purchase_date', $month->year)
                    ->whereMonth('purchase_date', $month->month)
                    ->sum('total_amount'),
            ];
        });

        // Recent invoices
        $recentInvoices = Invoice::with('party')
            ->where('business_id', $businessId)->latest('invoice_date')->limit(5)->get();

        // Top products
        $topProducts = \DB::table('invoice_items')
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->where('invoices.business_id', $businessId)
            ->whereDate('invoices.invoice_date', '>=', $startOfMonth)
            ->groupBy('invoice_items.item_name')
            ->selectRaw('item_name, SUM(quantity) as total_qty, SUM(total_amount) as total_amount')
            ->orderByDesc('total_amount')->limit(5)->get();

        // Party-wise outstanding
        $topReceivables = \DB::table('invoices')
            ->join('parties', 'parties.id', '=', 'invoices.party_id')
            ->where('invoices.business_id', $businessId)
            ->whereIn('invoices.payment_status', ['unpaid', 'partial'])
            ->groupBy('invoices.party_id', 'parties.name')
            ->selectRaw('parties.name, SUM(invoices.balance_amount) as outstanding')
            ->orderByDesc('outstanding')->limit(5)->get();

        return view('dashboard.index', compact(
            'business', 'salesThisMonth', 'purchaseThisMonth', 'expenseThisMonth', 'profit',
            'totalReceivable', 'totalPayable', 'overdueInvoices', 'lowStockCount',
            'salesChart', 'recentInvoices', 'topProducts', 'topReceivables'
        ));
    }
}


// =============================================================================
// app/Http/Controllers/ReportController.php
// =============================================================================
namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Purchase;
use App\Models\Payment;
use App\Models\Expense;
use App\Services\GstReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(private GstReportService $gstReportService) {}

    public function salesReport(Request $request)
    {
        $business = currentBusiness();
        $dateFrom = $request->date_from ?? now()->startOfMonth()->toDateString();
        $dateTo = $request->date_to ?? now()->toDateString();

        $invoices = Invoice::with(['party', 'items'])
            ->where('business_id', $business->id)
            ->whereNotIn('invoice_type', ['proforma_invoice'])
            ->whereDate('invoice_date', '>=', $dateFrom)
            ->whereDate('invoice_date', '<=', $dateTo)
            ->orderBy('invoice_date')->get();

        $summary = [
            'total_invoices' => $invoices->count(),
            'taxable_amount' => $invoices->sum('taxable_amount'),
            'cgst' => $invoices->sum('cgst_amount'),
            'sgst' => $invoices->sum('sgst_amount'),
            'igst' => $invoices->sum('igst_amount'),
            'total_amount' => $invoices->sum('total_amount'),
            'paid' => $invoices->sum('paid_amount'),
            'outstanding' => $invoices->sum('balance_amount'),
        ];

        return view('reports.sales', compact('invoices', 'summary', 'dateFrom', 'dateTo'));
    }

    public function gstr1(Request $request)
    {
        $business = currentBusiness();
        $month = $request->month ?? now()->format('m');
        $year = $request->year ?? now()->year;

        $data = $this->gstReportService->generateGSTR1($business->id, $month, $year);
        return view('reports.gstr1', compact('data', 'month', 'year', 'business'));
    }

    public function gstr3b(Request $request)
    {
        $business = currentBusiness();
        $month = $request->month ?? now()->format('m');
        $year = $request->year ?? now()->year;

        $data = $this->gstReportService->generateGSTR3B($business->id, $month, $year);
        return view('reports.gstr3b', compact('data', 'month', 'year', 'business'));
    }

    public function stockReport(Request $request)
    {
        $business = currentBusiness();
        $products = \App\Models\Product::with(['stock.warehouse', 'category', 'unit'])
            ->where('business_id', $business->id)
            ->where('track_inventory', true)
            ->withSum('stock', 'quantity')
            ->get();

        return view('reports.stock', compact('products'));
    }

    public function partyLedger(Request $request)
    {
        $business = currentBusiness();
        $party = \App\Models\Party::findOrFail($request->party_id);
        $dateFrom = $request->date_from ?? now()->startOfYear()->toDateString();
        $dateTo = $request->date_to ?? now()->toDateString();

        // Get all transactions
        $invoices = Invoice::where('business_id', $business->id)
            ->where('party_id', $party->id)
            ->whereDate('invoice_date', '>=', $dateFrom)
            ->whereDate('invoice_date', '<=', $dateTo)
            ->get();

        $payments = Payment::where('business_id', $business->id)
            ->where('party_id', $party->id)
            ->whereDate('payment_date', '>=', $dateFrom)
            ->whereDate('payment_date', '<=', $dateTo)
            ->get();

        return view('reports.party-ledger', compact('party', 'invoices', 'payments', 'dateFrom', 'dateTo'));
    }

    public function profitLoss(Request $request)
    {
        $business = currentBusiness();
        $dateFrom = $request->date_from ?? now()->startOfYear()->toDateString();
        $dateTo = $request->date_to ?? now()->toDateString();

        $sales = Invoice::where('business_id', $business->id)
            ->whereDate('invoice_date', '>=', $dateFrom)
            ->whereDate('invoice_date', '<=', $dateTo)
            ->whereNotIn('invoice_type', ['proforma_invoice'])->sum('total_amount');

        $purchases = Purchase::where('business_id', $business->id)
            ->whereDate('purchase_date', '>=', $dateFrom)
            ->whereDate('purchase_date', '<=', $dateTo)->sum('total_amount');

        $expenses = Expense::where('business_id', $business->id)
            ->whereDate('expense_date', '>=', $dateFrom)
            ->whereDate('expense_date', '<=', $dateTo)->sum('total_amount');

        $grossProfit = $sales - $purchases;
        $netProfit = $grossProfit - $expenses;

        return view('reports.profit-loss', compact('sales', 'purchases', 'expenses', 'grossProfit', 'netProfit', 'dateFrom', 'dateTo'));
    }
}
