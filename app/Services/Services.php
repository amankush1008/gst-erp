<?php
// =============================================================================
// app/Services/InvoiceService.php
// =============================================================================
namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\TransportDetail;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function createInvoice(array $data): Invoice
    {
        return DB::transaction(function() use ($data) {
            $business = currentBusiness();
            $party = \App\Models\Party::findOrFail($data['party_id']);

            // Determine if interstate
            $isInterstate = $business->state !== $party->billing_state;

            // Calculate totals
            $calculated = $this->calculateTotals($data['items'], $data, $isInterstate);

            $invoice = Invoice::create([
                'business_id' => $business->id,
                'party_id' => $data['party_id'],
                'user_id' => auth()->id(),
                'warehouse_id' => $data['warehouse_id'] ?? null,
                'invoice_type' => $data['invoice_type'],
                'invoice_number' => $business->generateNumber('invoice'),
                'invoice_date' => $data['invoice_date'],
                'due_date' => $data['due_date'] ?? null,
                'po_number' => $data['po_number'] ?? null,
                'place_of_supply' => $data['place_of_supply'] ?? $party->billing_state,
                'reverse_charge' => $data['reverse_charge'] ?? false,
                'is_interstate' => $isInterstate,
                'other_charges' => $data['other_charges'] ?? 0,
                'round_off' => $data['round_off'] ?? 0,
                'notes' => $data['notes'] ?? null,
                'template' => $data['template'] ?? 'default',
                ...$calculated,
            ]);

            // Create line items
            foreach ($data['items'] as $i => $itemData) {
                $this->createInvoiceItem($invoice, $itemData, $i, $isInterstate);
            }

            // Transport details
            if (!empty($data['transport'])) {
                TransportDetail::create(['invoice_id' => $invoice->id, ...$data['transport']]);
            }

            // Custom meta fields
            if (!empty($data['meta'])) {
                foreach ($data['meta'] as $key => $value) {
                    $invoice->meta()->create(['meta_key' => $key, 'meta_value' => $value]);
                }
            }

            // Update stock
            if ($invoice->invoice_type !== 'proforma_invoice') {
                $this->updateStock($invoice, 'out');
            }

            // Update balance
            $invoice->update(['balance_amount' => $invoice->total_amount]);

            return $invoice->fresh();
        });
    }

    private function calculateTotals(array $items, array $data, bool $isInterstate): array
    {
        $subtotal = 0;
        $taxableAmount = 0;
        $cgstTotal = $sgstTotal = $igstTotal = 0;
        $overallDiscountPercent = $data['discount_percent'] ?? 0;

        foreach ($items as $item) {
            $qty = floatval($item['quantity']);
            $rate = floatval($item['rate']);
            $discountPercent = floatval($item['discount_percent'] ?? 0);
            $taxRate = floatval($item['tax_rate'] ?? 0);

            $lineTotal = $qty * $rate;
            $discountAmt = $lineTotal * ($discountPercent / 100);
            $taxable = $lineTotal - $discountAmt;

            $subtotal += $lineTotal;
            $taxableAmount += $taxable;

            if ($isInterstate) {
                $igstTotal += $taxable * ($taxRate / 100);
            } else {
                $cgstTotal += $taxable * ($taxRate / 200);
                $sgstTotal += $taxable * ($taxRate / 200);
            }
        }

        // Overall discount
        $overallDiscount = $taxableAmount * ($overallDiscountPercent / 100);
        $taxableAmount -= $overallDiscount;

        $total = $taxableAmount + $cgstTotal + $sgstTotal + $igstTotal
            + floatval($data['other_charges'] ?? 0)
            + floatval($data['round_off'] ?? 0);

        return [
            'subtotal' => round($subtotal, 2),
            'discount_amount' => round($overallDiscount, 2),
            'discount_percent' => $overallDiscountPercent,
            'taxable_amount' => round($taxableAmount, 2),
            'cgst_amount' => round($cgstTotal, 2),
            'sgst_amount' => round($sgstTotal, 2),
            'igst_amount' => round($igstTotal, 2),
            'total_amount' => round($total, 2),
        ];
    }

    private function createInvoiceItem(Invoice $invoice, array $data, int $index, bool $isInterstate): InvoiceItem
    {
        $qty = floatval($data['quantity']);
        $rate = floatval($data['rate']);
        $discountPercent = floatval($data['discount_percent'] ?? 0);
        $taxRate = floatval($data['tax_rate'] ?? 0);

        $lineTotal = $qty * $rate;
        $discountAmt = $lineTotal * ($discountPercent / 100);
        $taxable = $lineTotal - $discountAmt;

        $cgstRate = $sgstRate = $igstRate = 0;
        $cgstAmt = $sgstAmt = $igstAmt = 0;

        if ($isInterstate) {
            $igstRate = $taxRate;
            $igstAmt = $taxable * ($taxRate / 100);
        } else {
            $cgstRate = $sgstRate = $taxRate / 2;
            $cgstAmt = $sgstAmt = $taxable * ($taxRate / 200);
        }

        return $invoice->items()->create([
            'product_id' => $data['product_id'] ?? null,
            'item_name' => $data['item_name'],
            'description' => $data['description'] ?? null,
            'hsn_code' => $data['hsn_code'] ?? null,
            'unit' => $data['unit'] ?? null,
            'quantity' => $qty,
            'rate' => $rate,
            'discount_percent' => $discountPercent,
            'discount_amount' => round($discountAmt, 2),
            'taxable_amount' => round($taxable, 2),
            'tax_rate' => $taxRate,
            'cgst_rate' => $cgstRate,
            'sgst_rate' => $sgstRate,
            'igst_rate' => $igstRate,
            'cgst_amount' => round($cgstAmt, 2),
            'sgst_amount' => round($sgstAmt, 2),
            'igst_amount' => round($igstAmt, 2),
            'total_amount' => round($taxable + $cgstAmt + $sgstAmt + $igstAmt, 2),
            'sort_order' => $index,
        ]);
    }

    private function updateStock(Invoice $invoice, string $direction): void
    {
        $warehouseId = $invoice->warehouse_id ?? currentBusiness()->warehouses()->where('is_default', true)->value('id');
        if (!$warehouseId) return;

        foreach ($invoice->items as $item) {
            if (!$item->product_id) continue;
            $product = $item->product;
            if (!$product->track_inventory) continue;

            $stock = \App\Models\Stock::firstOrCreate(
                ['product_id' => $product->id, 'warehouse_id' => $warehouseId],
                ['quantity' => 0]
            );

            $newQty = $direction === 'out'
                ? $stock->quantity - $item->quantity
                : $stock->quantity + $item->quantity;

            $stock->update(['quantity' => $newQty]);

            StockMovement::create([
                'product_id' => $product->id,
                'warehouse_id' => $warehouseId,
                'business_id' => $invoice->business_id,
                'type' => $direction,
                'quantity' => $item->quantity,
                'balance' => $newQty,
                'rate' => $item->rate,
                'reference_type' => 'invoice',
                'reference_id' => $invoice->id,
                'user_id' => auth()->id(),
            ]);
        }
    }

    public function recordPayment(Invoice $invoice, array $data): void
    {
        DB::transaction(function() use ($invoice, $data) {
            $business = currentBusiness();

            $payment = Payment::create([
                'business_id' => $business->id,
                'party_id' => $invoice->party_id,
                'user_id' => auth()->id(),
                'payment_number' => $business->generateNumber('payment'),
                'type' => 'receipt',
                'payment_date' => $data['payment_date'],
                'amount' => $data['amount'],
                'payment_mode' => $data['payment_mode'],
                'reference_number' => $data['reference_number'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            PaymentAllocation::create([
                'payment_id' => $payment->id,
                'reference_type' => 'invoice',
                'reference_id' => $invoice->id,
                'allocated_amount' => $data['amount'],
            ]);

            $paid = $invoice->paid_amount + $data['amount'];
            $balance = $invoice->total_amount - $paid;

            $invoice->update([
                'paid_amount' => $paid,
                'balance_amount' => max(0, $balance),
                'payment_status' => $balance <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid'),
                'status' => $balance <= 0 ? 'paid' : ($paid > 0 ? 'partial' : $invoice->status),
            ]);
        });
    }

    public function getSummary(int $businessId, array $filters): array
    {
        $query = Invoice::where('business_id', $businessId);
        if (!empty($filters['date_from'])) $query->whereDate('invoice_date', '>=', $filters['date_from']);
        if (!empty($filters['date_to'])) $query->whereDate('invoice_date', '<=', $filters['date_to']);

        return [
            'total' => $query->count(),
            'total_amount' => $query->sum('total_amount'),
            'paid' => (clone $query)->sum('paid_amount'),
            'outstanding' => (clone $query)->sum('balance_amount'),
        ];
    }

    public function duplicateInvoice(Invoice $invoice): Invoice
    {
        $new = $invoice->replicate();
        $new->invoice_number = currentBusiness()->generateNumber('invoice');
        $new->invoice_date = now()->toDateString();
        $new->due_date = null;
        $new->status = 'draft';
        $new->payment_status = 'unpaid';
        $new->paid_amount = 0;
        $new->balance_amount = $invoice->total_amount;
        $new->save();

        foreach ($invoice->items as $item) {
            $newItem = $item->replicate();
            $newItem->invoice_id = $new->id;
            $newItem->save();
        }

        return $new;
    }

    public function deleteInvoice(Invoice $invoice): void
    {
        DB::transaction(function() use ($invoice) {
            // Reverse stock
            $this->updateStock($invoice, 'in');
            $invoice->items()->delete();
            $invoice->transport()->delete();
            $invoice->meta()->delete();
            $invoice->delete();
        });
    }

    public function updateInvoice(Invoice $invoice, array $data): Invoice
    {
        return DB::transaction(function() use ($invoice, $data) {
            // Reverse stock first
            $this->updateStock($invoice, 'in');
            $invoice->items()->delete();

            $party = \App\Models\Party::findOrFail($data['party_id']);
            $isInterstate = currentBusiness()->state !== $party->billing_state;
            $calculated = $this->calculateTotals($data['items'], $data, $isInterstate);

            $invoice->update([...$data, ...$calculated, 'is_interstate' => $isInterstate]);

            foreach ($data['items'] as $i => $itemData) {
                $this->createInvoiceItem($invoice, $itemData, $i, $isInterstate);
            }

            $this->updateStock($invoice, 'out');
            return $invoice->fresh();
        });
    }
}


// =============================================================================
// app/Services/GstService.php
// =============================================================================
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class GstService
{
    public function verify(string $gstin): array
    {
        if (!preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/', $gstin)) {
            return ['success' => false, 'status' => 'Invalid', 'message' => 'Invalid GSTIN format'];
        }

        return Cache::remember("gstin_{$gstin}", 3600, function() use ($gstin) {
            try {
                $response = Http::withHeaders(['X-API-KEY' => config('services.gst.api_key')])
                    ->get(config('services.gst.api_url') . '/taxpayerapi/v3.0/tp/' . $gstin);

                if ($response->successful()) {
                    $data = $response->json();
                    return [
                        'success' => true,
                        'status' => $data['taxpayerInfo']['sts'] ?? 'Unknown',
                        'name' => $data['taxpayerInfo']['tradeNam'] ?? '',
                        'legal_name' => $data['taxpayerInfo']['lgnm'] ?? '',
                        'address' => $data['taxpayerInfo']['pradr']['addr'] ?? '',
                        'state' => $data['taxpayerInfo']['pradr']['addr']['stcd'] ?? '',
                        'registration_type' => $data['taxpayerInfo']['dty'] ?? '',
                        'raw' => $data,
                    ];
                }

                return ['success' => false, 'status' => 'Error', 'message' => 'API verification failed'];
            } catch (\Exception $e) {
                \Log::error("GST verification failed: " . $e->getMessage());
                return ['success' => false, 'status' => 'Error', 'message' => $e->getMessage()];
            }
        });
    }

    public function generateEwayBill(Invoice $invoice): array
    {
        try {
            $payload = $this->buildEwayPayload($invoice);
            $response = Http::withBasicAuth(
                config('services.eway.username'),
                config('services.eway.password')
            )->post('https://einvapi.irisgst.com/eway/api/v1.03/ewb/generate', $payload);

            if ($response->successful()) {
                return ['success' => true, 'eway_bill_number' => $response->json('ewayBillNo'), ...$response->json()];
            }

            return ['success' => false, 'message' => $response->json('message') ?? 'E-Way Bill generation failed'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function buildEwayPayload(Invoice $invoice): array
    {
        $transport = $invoice->transport;
        return [
            'supplyType' => 'O',
            'subSupplyType' => '1',
            'docType' => 'INV',
            'docNo' => $invoice->invoice_number,
            'docDate' => $invoice->invoice_date->format('d/m/Y'),
            'fromGstin' => $invoice->business->gstin,
            'toGstin' => $invoice->party->gstin,
            'totInvVal' => $invoice->total_amount,
            'transDistance' => $transport?->distance_km ?? '0',
            'transporterName' => $transport?->transporter_name ?? '',
            'transId' => $transport?->transporter_id ?? '',
            'transMode' => '1', // Road
            'vehicleNo' => $transport?->vehicle_number ?? '',
            'itemList' => $invoice->items->map(fn($item) => [
                'itemNo' => $item->sort_order + 1,
                'productName' => $item->item_name,
                'hsnCode' => $item->hsn_code,
                'quantity' => $item->quantity,
                'qtyUnit' => 'NOS',
                'taxableAmount' => $item->taxable_amount,
                'sgstRate' => $item->sgst_rate,
                'cgstRate' => $item->cgst_rate,
                'igstRate' => $item->igst_rate,
            ])->toArray(),
        ];
    }
}


// =============================================================================
// app/Services/GstReportService.php
// =============================================================================
namespace App\Services;

use App\Models\Invoice;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;

class GstReportService
{
    public function generateGSTR1(int $businessId, string $month, int $year): array
    {
        $invoices = Invoice::with(['party', 'items'])
            ->where('business_id', $businessId)
            ->whereIn('invoice_type', ['tax_invoice', 'retail_invoice'])
            ->whereMonth('invoice_date', $month)
            ->whereYear('invoice_date', $year)
            ->get();

        $b2b = $invoices->where('is_interstate', false)->filter(fn($i) => !empty($i->party->gstin));
        $b2c = $invoices->filter(fn($i) => empty($i->party->gstin));
        $interstate = $invoices->where('is_interstate', true);

        return [
            'b2b' => $this->formatB2B($b2b),
            'b2cl' => $this->formatB2CL($b2c->where('is_interstate', true)),
            'b2cs' => $this->formatB2CS($b2c->where('is_interstate', false)),
            'exports' => [],
            'credit_notes' => $this->formatCreditNotes($businessId, $month, $year),
            'summary' => [
                'total_taxable' => $invoices->sum('taxable_amount'),
                'total_cgst' => $invoices->sum('cgst_amount'),
                'total_sgst' => $invoices->sum('sgst_amount'),
                'total_igst' => $invoices->sum('igst_amount'),
                'total_tax' => $invoices->sum('cgst_amount') + $invoices->sum('sgst_amount') + $invoices->sum('igst_amount'),
                'total_invoice_value' => $invoices->sum('total_amount'),
            ],
        ];
    }

    public function generateGSTR3B(int $businessId, string $month, int $year): array
    {
        $sales = Invoice::where('business_id', $businessId)
            ->whereMonth('invoice_date', $month)->whereYear('invoice_date', $year)->get();

        $purchases = Purchase::where('business_id', $businessId)
            ->whereMonth('purchase_date', $month)->whereYear('purchase_date', $year)->get();

        return [
            '3_1' => [ // Outward supplies
                'os_details' => ['taxval' => $sales->sum('taxable_amount'), 'igst' => $sales->sum('igst_amount'), 'cgst' => $sales->sum('cgst_amount'), 'sgst' => $sales->sum('sgst_amount'), 'cess' => 0],
            ],
            '4' => [ // ITC available
                'itc_avl' => ['igst' => $purchases->sum('igst_amount'), 'cgst' => $purchases->sum('cgst_amount'), 'sgst' => $purchases->sum('sgst_amount'), 'cess' => 0],
            ],
            'summary' => [
                'output_tax' => $sales->sum('cgst_amount') + $sales->sum('sgst_amount') + $sales->sum('igst_amount'),
                'input_tax_credit' => $purchases->sum('cgst_amount') + $purchases->sum('sgst_amount') + $purchases->sum('igst_amount'),
                'net_tax_payable' => max(0, ($sales->sum('cgst_amount') + $sales->sum('sgst_amount') + $sales->sum('igst_amount')) - ($purchases->sum('cgst_amount') + $purchases->sum('sgst_amount') + $purchases->sum('igst_amount'))),
            ],
        ];
    }

    private function formatB2B($invoices): array
    {
        return $invoices->groupBy('party.gstin')->map(fn($group, $gstin) => [
            'gstin' => $gstin,
            'party_name' => $group->first()->party->name,
            'invoices' => $group->map(fn($inv) => [
                'num' => $inv->invoice_number,
                'date' => $inv->invoice_date->format('d-m-Y'),
                'val' => $inv->total_amount,
                'pos' => $inv->place_of_supply,
                'rchrg' => $inv->reverse_charge ? 'Y' : 'N',
                'tax_amt' => $inv->cgst_amount + $inv->sgst_amount + $inv->igst_amount,
                'typ' => 'R',
            ])->values(),
        ])->values()->toArray();
    }

    private function formatB2CL($invoices): array { return []; }
    private function formatB2CS($invoices): array { return []; }
    private function formatCreditNotes(int $businessId, string $month, int $year): array { return []; }
}


// =============================================================================
// app/Services/InventoryService.php
// =============================================================================
namespace App\Services;

use App\Models\Product;
use App\Models\ProductMeta;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function createProduct(array $data): Product
    {
        return DB::transaction(function() use ($data) {
            $business = currentBusiness();
            $data['business_id'] = $business->id;

            if (empty($data['sku'])) {
                $data['sku'] = 'SKU-' . strtoupper(substr($data['name'], 0, 3)) . '-' . rand(1000, 9999);
            }

            $product = Product::create($data);

            // Handle image upload
            if (!empty($data['image_file'])) {
                $path = $data['image_file']->store("products/{$business->id}", 'public');
                $product->update(['image' => $path]);
            }

            // Opening stock
            if ($product->track_inventory && $product->opening_stock > 0) {
                $warehouseId = $business->warehouses()->where('is_default', true)->value('id');
                if ($warehouseId) {
                    Stock::create(['product_id' => $product->id, 'warehouse_id' => $warehouseId, 'quantity' => $product->opening_stock]);
                    StockMovement::create([
                        'product_id' => $product->id,
                        'warehouse_id' => $warehouseId,
                        'business_id' => $business->id,
                        'type' => 'in',
                        'quantity' => $product->opening_stock,
                        'balance' => $product->opening_stock,
                        'notes' => 'Opening stock',
                        'user_id' => auth()->id(),
                    ]);
                }
            }

            // Custom fields
            if (!empty($data['meta'])) {
                foreach ($data['meta'] as $key => $value) {
                    ProductMeta::create(['product_id' => $product->id, 'meta_key' => $key, 'meta_value' => $value]);
                }
            }

            return $product;
        });
    }

    public function updateProduct(Product $product, array $data): Product
    {
        $product->update($data);

        if (!empty($data['meta'])) {
            foreach ($data['meta'] as $key => $value) {
                ProductMeta::updateOrCreate(
                    ['product_id' => $product->id, 'meta_key' => $key],
                    ['meta_value' => $value]
                );
            }
        }

        return $product;
    }

    public function adjustStock(Product $product, array $data): void
    {
        DB::transaction(function() use ($product, $data) {
            $stock = Stock::firstOrCreate(
                ['product_id' => $product->id, 'warehouse_id' => $data['warehouse_id']],
                ['quantity' => 0]
            );

            $oldQty = $stock->quantity;
            $newQty = match($data['type']) {
                'add' => $oldQty + abs($data['quantity']),
                'remove' => max(0, $oldQty - abs($data['quantity'])),
                'set' => abs($data['quantity']),
            };

            $stock->update(['quantity' => $newQty]);

            StockMovement::create([
                'product_id' => $product->id,
                'warehouse_id' => $data['warehouse_id'],
                'business_id' => $product->business_id,
                'type' => 'adjustment',
                'quantity' => abs($newQty - $oldQty),
                'balance' => $newQty,
                'notes' => $data['notes'] ?? "Manual adjustment: {$data['type']}",
                'user_id' => auth()->id(),
            ]);
        });
    }
}
