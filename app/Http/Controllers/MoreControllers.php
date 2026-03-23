<?php

namespace App\Http\Controllers;

use App\Models\Party;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// ─────────────────────────────────────────────
// PURCHASE CONTROLLER
// ─────────────────────────────────────────────
class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $query = Purchase::with('party')
            ->where('business_id', currentBusinessId())
            ->latest();

        if ($request->filled('search')) {
            $query->where('invoice_number', 'like', "%{$request->search}%");
        }
        if ($request->filled('party_id')) {
            $query->where('party_id', $request->party_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('invoice_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('invoice_date', '<=', $request->date_to);
        }

        $purchases = $query->paginate(20)->withQueryString();
        $parties   = Party::where('business_id', currentBusinessId())
                          ->where('type', 'supplier')->get();

        $stats = [
            'total_purchases' => $query->sum('total_amount'),
            'total_tax'       => $query->sum('tax_amount'),
            'pending'         => $query->where('payment_status', 'unpaid')->sum('balance_amount'),
        ];

        return view('purchases.index', compact('purchases', 'parties', 'stats'));
    }

    public function create()
    {
        $suppliers = Party::where('business_id', currentBusinessId())
                         ->where('type', 'supplier')->get();
        $products  = Product::where('business_id', currentBusinessId())->active()->get();
        return view('purchases.create', compact('suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'party_id'       => 'required|exists:parties,id',
            'invoice_date'   => 'required|date',
            'invoice_number' => 'required|string',
            'items'          => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty'        => 'required|numeric|min:0.01',
            'items.*.rate'       => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $items     = $request->input('items', []);
            $subtotal  = 0;
            $taxAmount = 0;

            foreach ($items as &$item) {
                $item['amount']     = $item['qty'] * $item['rate'];
                $item['discount']   = $item['discount'] ?? 0;
                $item['taxable']    = $item['amount'] - $item['discount'];
                $item['tax_amount'] = $item['taxable'] * ($item['gst_rate'] ?? 0) / 100;
                $item['total']      = $item['taxable'] + $item['tax_amount'];
                $subtotal  += $item['taxable'];
                $taxAmount += $item['tax_amount'];
            }

            $totalAmount = $subtotal + $taxAmount
                - ($request->overall_discount ?? 0)
                + ($request->round_off ?? 0);

            $purchase = Purchase::create([
                'business_id'    => currentBusinessId(),
                'party_id'       => $request->party_id,
                'invoice_number' => $request->invoice_number,
                'invoice_date'   => $request->invoice_date,
                'due_date'       => $request->due_date,
                'subtotal'       => $subtotal,
                'tax_amount'     => $taxAmount,
                'discount'       => $request->overall_discount ?? 0,
                'round_off'      => $request->round_off ?? 0,
                'total_amount'   => $totalAmount,
                'balance_amount' => $totalAmount,
                'payment_status' => 'unpaid',
                'notes'          => $request->notes,
            ]);

            foreach ($items as $item) {
                $purchase->items()->create($item);

                // Update stock
                $stock = DB::table('stock')
                    ->where('product_id', $item['product_id'])
                    ->where('business_id', currentBusinessId())
                    ->first();

                if ($stock) {
                    DB::table('stock')->where('id', $stock->id)
                        ->increment('quantity', $item['qty']);
                } else {
                    DB::table('stock')->insert([
                        'product_id'  => $item['product_id'],
                        'business_id' => currentBusinessId(),
                        'quantity'    => $item['qty'],
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                }

                DB::table('stock_movements')->insert([
                    'business_id'    => currentBusinessId(),
                    'product_id'     => $item['product_id'],
                    'reference_type' => 'purchase',
                    'reference_id'   => $purchase->id,
                    'type'           => 'in',
                    'quantity'       => $item['qty'],
                    'notes'          => "Purchase #{$purchase->invoice_number}",
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }
        });

        return redirect()->route('purchases.index')->with('success', 'Purchase recorded successfully.');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load('party', 'items.product');
        return view('purchases.show', compact('purchase'));
    }

    public function destroy(Purchase $purchase)
    {
        $purchase->items()->delete();
        $purchase->delete();
        return redirect()->route('purchases.index')->with('success', 'Purchase deleted.');
    }
}

// ─────────────────────────────────────────────
// PAYMENT CONTROLLER
// ─────────────────────────────────────────────
class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $payments = Payment::with('party', 'invoice')
            ->where('business_id', currentBusinessId())
            ->latest()
            ->paginate(20);

        $stats = [
            'received' => Payment::where('business_id', currentBusinessId())
                ->where('type', 'received')->sum('amount'),
            'paid'     => Payment::where('business_id', currentBusinessId())
                ->where('type', 'paid')->sum('amount'),
        ];

        return view('payments.index', compact('payments', 'stats'));
    }

    public function create()
    {
        $parties  = Party::where('business_id', currentBusinessId())->get();
        $invoices = Invoice::where('business_id', currentBusinessId())
                          ->whereIn('payment_status', ['unpaid', 'partial'])->get();
        return view('payments.create', compact('parties', 'invoices'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'party_id'       => 'required|exists:parties,id',
            'amount'         => 'required|numeric|min:0.01',
            'payment_date'   => 'required|date',
            'payment_method' => 'required|in:cash,bank,upi,cheque',
            'type'           => 'required|in:received,paid',
        ]);

        DB::transaction(function () use ($request) {
            $payment = Payment::create([
                'business_id'    => currentBusinessId(),
                'party_id'       => $request->party_id,
                'invoice_id'     => $request->invoice_id,
                'amount'         => $request->amount,
                'payment_date'   => $request->payment_date,
                'payment_method' => $request->payment_method,
                'type'           => $request->type,
                'reference'      => $request->reference,
                'notes'          => $request->notes,
            ]);

            // Update invoice balance
            if ($request->invoice_id) {
                $invoice = Invoice::find($request->invoice_id);
                if ($invoice) {
                    $newBalance = max(0, $invoice->balance_amount - $request->amount);
                    $status     = $newBalance == 0 ? 'paid' : 'partial';
                    $invoice->update([
                        'balance_amount' => $newBalance,
                        'payment_status' => $status,
                        'paid_amount'    => $invoice->paid_amount + $request->amount,
                    ]);
                }
            }
        });

        return redirect()->route('payments.index')->with('success', 'Payment recorded.');
    }

    public function getUnpaidInvoices(Request $request)
    {
        $invoices = Invoice::where('business_id', currentBusinessId())
            ->where('party_id', $request->party_id)
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->get(['id', 'invoice_number', 'total_amount', 'balance_amount']);

        return response()->json($invoices);
    }
}

// ─────────────────────────────────────────────
// EXPENSE CONTROLLER
// ─────────────────────────────────────────────
class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with('category')
            ->where('business_id', currentBusinessId())
            ->latest();

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('expense_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('expense_date', '<=', $request->date_to);
        }

        $expenses   = $query->paginate(20);
        $categories = DB::table('expense_categories')
            ->where('business_id', currentBusinessId())->get();

        $totalExpense = Expense::where('business_id', currentBusinessId())
            ->whereBetween('expense_date', [
                $request->date_from ?? now()->startOfMonth(),
                $request->date_to   ?? now()->endOfMonth(),
            ])->sum('amount');

        return view('expenses.index', compact('expenses', 'categories', 'totalExpense'));
    }

    public function create()
    {
        $categories = DB::table('expense_categories')
            ->where('business_id', currentBusinessId())->get();
        return view('expenses.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id'  => 'required',
            'amount'       => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'description'  => 'required|string|max:500',
        ]);

        $expense = Expense::create([
            'business_id'  => currentBusinessId(),
            'category_id'  => $request->category_id,
            'amount'       => $request->amount,
            'expense_date' => $request->expense_date,
            'description'  => $request->description,
            'payment_mode' => $request->payment_mode ?? 'cash',
            'reference'    => $request->reference,
        ]);

        if ($request->hasFile('receipt')) {
            $path = $request->file('receipt')->store('expenses', 'public');
            $expense->update(['receipt_path' => $path]);
        }

        return redirect()->route('expenses.index')->with('success', 'Expense added.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return back()->with('success', 'Expense deleted.');
    }
}

// ─────────────────────────────────────────────
// SETTINGS CONTROLLER
// ─────────────────────────────────────────────
class SettingsController extends Controller
{
    public function business()
    {
        $business = currentBusiness();
        return view('settings.business', compact('business'));
    }

    public function updateBusiness(Request $request)
    {
        $business = currentBusiness();
        $request->validate([
            'name'    => 'required|string|max:150',
            'gstin'   => 'nullable|string|size:15',
            'mobile'  => 'nullable|string|max:15',
            'email'   => 'nullable|email',
            'address' => 'nullable|string|max:500',
            'state'   => 'nullable|string|max:50',
            'pincode' => 'nullable|string|max:10',
        ]);

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('logos', 'public');
            $business->update(['logo' => $path]);
        }

        $business->update($request->except('logo', '_token', '_method'));
        return back()->with('success', 'Business profile updated.');
    }

    public function customFields()
    {
        $fields = DB::table('custom_fields')
            ->where('business_id', currentBusinessId())
            ->get();
        return view('settings.custom-fields', compact('fields'));
    }

    public function storeCustomField(Request $request)
    {
        $request->validate([
            'module'     => 'required|in:product,invoice,party',
            'label'      => 'required|string|max:100',
            'field_type' => 'required|in:text,number,date,select,checkbox',
        ]);

        DB::table('custom_fields')->insert([
            'business_id' => currentBusinessId(),
            'module'      => $request->module,
            'label'       => $request->label,
            'field_name'  => \Str::slug($request->label, '_'),
            'field_type'  => $request->field_type,
            'options'     => $request->options ? json_encode(explode(',', $request->options)) : null,
            'is_required' => $request->boolean('is_required'),
            'sort_order'  => $request->sort_order ?? 0,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return back()->with('success', 'Custom field added.');
    }

    public function deleteCustomField(int $id)
    {
        DB::table('custom_fields')
            ->where('id', $id)
            ->where('business_id', currentBusinessId())
            ->delete();
        return back()->with('success', 'Custom field deleted.');
    }

    public function warehouses()
    {
        $warehouses = DB::table('warehouses')
            ->where('business_id', currentBusinessId())->get();
        return view('settings.warehouses', compact('warehouses'));
    }

    public function storeWarehouse(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100']);
        DB::table('warehouses')->insert([
            'business_id' => currentBusinessId(),
            'name'        => $request->name,
            'address'     => $request->address,
            'is_default'  => $request->boolean('is_default'),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
        return back()->with('success', 'Warehouse added.');
    }

    public function units()
    {
        $units = DB::table('units')->where('business_id', currentBusinessId())->get();
        return view('settings.units', compact('units'));
    }

    public function storeUnit(Request $request)
    {
        $request->validate(['name' => 'required', 'symbol' => 'required']);
        DB::table('units')->insert([
            'business_id' => currentBusinessId(),
            'name'        => $request->name,
            'symbol'      => $request->symbol,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
        return back()->with('success', 'Unit added.');
    }

    public function activityLog()
    {
        $logs = DB::table('activity_logs')
            ->join('users', 'users.id', '=', 'activity_logs.user_id')
            ->where('activity_logs.business_id', currentBusinessId())
            ->select('activity_logs.*', 'users.name as user_name')
            ->latest('activity_logs.created_at')
            ->paginate(50);

        return view('settings.activity-log', compact('logs'));
    }

    public function numberFormats()
    {
        $sequences = DB::table('number_sequences')
            ->where('business_id', currentBusinessId())->get();
        return view('settings.number-formats', compact('sequences'));
    }

    public function updateNumberFormat(Request $request)
    {
        $data = $request->validate([
            'module'  => 'required|string',
            'prefix'  => 'nullable|string|max:20',
            'suffix'  => 'nullable|string|max:20',
            'padding' => 'required|integer|min:1|max:10',
        ]);

        DB::table('number_sequences')->updateOrInsert(
            ['business_id' => currentBusinessId(), 'module' => $data['module']],
            array_merge($data, ['business_id' => currentBusinessId(), 'updated_at' => now()])
        );

        return back()->with('success', 'Number format updated.');
    }

    public function users()
    {
        $users = \App\Models\User::where('business_id', currentBusinessId())->get();
        return view('settings.users', compact('users'));
    }

    public function backup()
    {
        return view('settings.backup');
    }
}
