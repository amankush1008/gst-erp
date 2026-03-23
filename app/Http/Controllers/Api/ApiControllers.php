<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Party;
use App\Models\Product;
use App\Services\GstService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $products = Product::where('business_id', currentBusinessId())
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('sku', 'like', "%{$request->search}%"))
            ->active()
            ->with('unit')
            ->limit(20)
            ->get(['id', 'name', 'sku', 'hsn_code', 'gst_rate', 'sale_price', 'unit_id']);

        return response()->json(['data' => $products]);
    }

    public function stock(int $productId): JsonResponse
    {
        $stock = DB::table('stock')
            ->where('product_id', $productId)
            ->where('business_id', currentBusinessId())
            ->sum('quantity');

        return response()->json(['stock' => $stock]);
    }
}

class ApiPartyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $parties = Party::where('business_id', currentBusinessId())
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->limit(20)
            ->get(['id', 'name', 'gstin', 'billing_state', 'mobile', 'balance']);

        return response()->json(['data' => $parties]);
    }

    public function ledger(int $partyId): JsonResponse
    {
        $party    = Party::findOrFail($partyId);
        $invoices = Invoice::where('party_id', $partyId)
            ->where('business_id', currentBusinessId())
            ->latest()
            ->get(['id', 'invoice_number', 'invoice_date', 'total_amount', 'balance_amount', 'payment_status']);

        return response()->json([
            'party'    => $party,
            'invoices' => $invoices,
            'balance'  => $party->balance,
        ]);
    }
}

class ApiInvoiceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $invoices = Invoice::with('party')
            ->where('business_id', currentBusinessId())
            ->latest()
            ->limit(50)
            ->get();

        return response()->json(['data' => $invoices]);
    }

    public function show(int $id): JsonResponse
    {
        $invoice = Invoice::with(['party', 'items.product', 'payments'])
            ->where('business_id', currentBusinessId())
            ->findOrFail($id);

        return response()->json(['data' => $invoice]);
    }
}

class ApiDashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        $bid = currentBusinessId();

        $sales = Invoice::where('business_id', $bid)
            ->whereMonth('invoice_date', now()->month)
            ->sum('total_amount');

        $purchases = DB::table('purchases')
            ->where('business_id', $bid)
            ->whereMonth('invoice_date', now()->month)
            ->sum('total_amount');

        $receivable = Invoice::where('business_id', $bid)
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->sum('balance_amount');

        $payable = DB::table('purchases')
            ->where('business_id', $bid)
            ->where('payment_status', 'unpaid')
            ->sum('balance_amount');

        return response()->json(compact('sales', 'purchases', 'receivable', 'payable'));
    }

    public function chart(): JsonResponse
    {
        $bid = currentBusinessId();

        $months = collect(range(11, 0))->map(function ($i) use ($bid) {
            $date = now()->subMonths($i);
            return [
                'month'     => $date->format('M Y'),
                'sales'     => Invoice::where('business_id', $bid)
                    ->whereYear('invoice_date', $date->year)
                    ->whereMonth('invoice_date', $date->month)
                    ->sum('total_amount'),
                'purchases' => DB::table('purchases')
                    ->where('business_id', $bid)
                    ->whereYear('invoice_date', $date->year)
                    ->whereMonth('invoice_date', $date->month)
                    ->sum('total_amount'),
            ];
        });

        return response()->json(['data' => $months]);
    }
}

class ApiGstController extends Controller
{
    public function verifyGstin(string $gstin, GstService $gstService): JsonResponse
    {
        $result = $gstService->verifyGstin($gstin);
        return response()->json($result);
    }

    public function generateEwayBill(Request $request, GstService $gstService): JsonResponse
    {
        $invoice = Invoice::where('business_id', currentBusinessId())
            ->findOrFail($request->invoice_id);

        $result = $gstService->generateEwayBill($invoice, $request->transport ?? []);

        if ($result['success']) {
            $invoice->update([
                'eway_bill_number' => $result['ewbNo'],
                'eway_bill_status' => 'generated',
                'eway_bill_date'   => now(),
            ]);
        }

        return response()->json($result);
    }
}

class ApiTokenController extends Controller
{
    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (!\Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $user  = \Auth::user();
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token'      => $token,
            'token_type' => 'Bearer',
            'user'       => $user->only('id', 'name', 'email'),
        ]);
    }

    public function revoke(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Token revoked.']);
    }
}
