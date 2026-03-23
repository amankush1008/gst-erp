<?php
// =============================================================================
// app/Models/Product.php
// =============================================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id', 'category_id', 'unit_id', 'name', 'sku', 'item_code',
        'barcode', 'upc', 'hsn_code', 'description', 'type',
        'sale_price', 'purchase_price', 'mrp', 'tax_rate', 'tax_type',
        'opening_stock', 'min_stock_alert', 'max_stock',
        'track_inventory', 'has_batch', 'has_expiry', 'image', 'is_active',
    ];

    protected $casts = [
        'sale_price' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'mrp' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'opening_stock' => 'decimal:3',
        'min_stock_alert' => 'decimal:3',
        'track_inventory' => 'boolean',
        'has_batch' => 'boolean',
        'has_expiry' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function business(): BelongsTo { return $this->belongsTo(Business::class); }
    public function category(): BelongsTo { return $this->belongsTo(ProductCategory::class); }
    public function unit(): BelongsTo { return $this->belongsTo(Unit::class); }
    public function meta(): HasMany { return $this->hasMany(ProductMeta::class); }
    public function stock(): HasMany { return $this->hasMany(Stock::class); }
    public function movements(): HasMany { return $this->hasMany(StockMovement::class); }
    public function batches(): HasMany { return $this->hasMany(Batch::class); }

    public function getTotalStockAttribute(): float
    {
        return $this->stock()->sum('quantity');
    }

    public function isLowStock(): bool
    {
        return $this->track_inventory && $this->total_stock <= $this->min_stock_alert;
    }

    public function getMetaValue(string $key): mixed
    {
        return $this->meta()->where('meta_key', $key)->value('meta_value');
    }

    public function scopeActive($query) { return $query->where('is_active', true); }
    public function scopeLowStock($query) {
        return $query->where('track_inventory', true)
            ->whereRaw('(SELECT COALESCE(SUM(quantity),0) FROM stock WHERE product_id = products.id) <= products.min_stock_alert');
    }
}


// =============================================================================
// app/Models/Party.php
// =============================================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Party extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id', 'type', 'name', 'gstin', 'gstin_verified', 'pan',
        'email', 'phone', 'mobile', 'contact_person',
        'billing_address', 'billing_city', 'billing_state', 'billing_pincode', 'billing_country',
        'same_as_billing', 'shipping_address', 'shipping_city', 'shipping_state', 'shipping_pincode',
        'credit_limit', 'payment_terms_days', 'opening_balance', 'opening_balance_type',
        'website', 'notes', 'is_active',
    ];

    protected $casts = [
        'gstin_verified' => 'boolean',
        'same_as_billing' => 'boolean',
        'credit_limit' => 'decimal:2',
        'opening_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function business() { return $this->belongsTo(Business::class); }
    public function invoices() { return $this->hasMany(Invoice::class); }
    public function purchases() { return $this->hasMany(Purchase::class); }
    public function payments() { return $this->hasMany(Payment::class); }

    public function getOutstandingAttribute(): float
    {
        $receivable = $this->invoices()->whereIn('payment_status', ['unpaid', 'partial'])->sum('balance_amount');
        $payable = $this->purchases()->whereIn('payment_status', ['unpaid', 'partial'])->sum('balance_amount');
        return $receivable - $payable;
    }

    public function getBillingAddressFullAttribute(): string
    {
        return implode(', ', array_filter([
            $this->billing_address, $this->billing_city,
            $this->billing_state, $this->billing_pincode
        ]));
    }

    public function scopeCustomers($query) { return $query->whereIn('type', ['customer', 'both']); }
    public function scopeSuppliers($query) { return $query->whereIn('type', ['supplier', 'both']); }
}


// =============================================================================
// app/Models/Invoice.php
// =============================================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id', 'party_id', 'user_id', 'warehouse_id', 'invoice_type',
        'invoice_number', 'reference_invoice_id', 'invoice_date', 'due_date',
        'po_number', 'place_of_supply', 'reverse_charge', 'is_interstate',
        'subtotal', 'discount_amount', 'discount_percent', 'taxable_amount',
        'cgst_amount', 'sgst_amount', 'igst_amount', 'cess_amount',
        'other_charges', 'round_off', 'total_amount', 'paid_amount', 'balance_amount',
        'status', 'payment_status', 'notes', 'terms_conditions',
        'eway_bill_number', 'eway_bill_data', 'template',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'reverse_charge' => 'boolean',
        'is_interstate' => 'boolean',
        'eway_bill_data' => 'array',
        'subtotal' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
    ];

    public function business() { return $this->belongsTo(Business::class); }
    public function party() { return $this->belongsTo(Party::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function warehouse() { return $this->belongsTo(Warehouse::class); }
    public function items() { return $this->hasMany(InvoiceItem::class)->orderBy('sort_order'); }
    public function transport() { return $this->hasOne(TransportDetail::class); }
    public function payments() { return $this->hasMany(PaymentAllocation::class, 'reference_id')->where('reference_type', 'invoice'); }
    public function meta() { return $this->hasMany(InvoiceMeta::class); }

    public function getInvoiceTypeLabelAttribute(): string
    {
        return match($this->invoice_type) {
            'tax_invoice' => 'Tax Invoice',
            'retail_invoice' => 'Retail Invoice',
            'proforma_invoice' => 'Proforma Invoice',
            'credit_note' => 'Credit Note',
            'debit_note' => 'Debit Note',
            'delivery_challan' => 'Delivery Challan',
            default => 'Invoice',
        };
    }

    public function getAmountInWordsAttribute(): string
    {
        return \App\Helpers\NumberToWords::convert($this->total_amount);
    }

    public function getTaxSummaryAttribute(): array
    {
        $summary = [];
        foreach ($this->items as $item) {
            $key = $item->tax_rate;
            if (!isset($summary[$key])) {
                $summary[$key] = ['taxable' => 0, 'cgst' => 0, 'sgst' => 0, 'igst' => 0, 'total_tax' => 0];
            }
            $summary[$key]['taxable'] += $item->taxable_amount;
            $summary[$key]['cgst'] += $item->cgst_amount;
            $summary[$key]['sgst'] += $item->sgst_amount;
            $summary[$key]['igst'] += $item->igst_amount;
            $summary[$key]['total_tax'] += ($item->cgst_amount + $item->sgst_amount + $item->igst_amount);
        }
        return $summary;
    }

    public function scopeOverdue($query) {
        return $query->where('payment_status', '!=', 'paid')
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->toDateString());
    }
}


// =============================================================================
// app/Models/InvoiceItem.php
// =============================================================================
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id', 'product_id', 'item_name', 'description', 'hsn_code', 'unit',
        'quantity', 'rate', 'discount_percent', 'discount_amount', 'taxable_amount',
        'tax_rate', 'cgst_rate', 'sgst_rate', 'igst_rate',
        'cgst_amount', 'sgst_amount', 'igst_amount', 'total_amount', 'sort_order', 'meta',
    ];

    protected $casts = ['meta' => 'array', 'quantity' => 'decimal:3'];

    public function invoice() { return $this->belongsTo(Invoice::class); }
    public function product() { return $this->belongsTo(Product::class); }
}


// =============================================================================
// app/Models/Payment.php
// =============================================================================
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id', 'party_id', 'user_id', 'payment_number', 'type',
        'payment_date', 'amount', 'payment_mode', 'reference_number', 'bank_name', 'notes',
    ];

    protected $casts = ['payment_date' => 'date', 'amount' => 'decimal:2'];

    public function business() { return $this->belongsTo(Business::class); }
    public function party() { return $this->belongsTo(Party::class); }
    public function allocations() { return $this->hasMany(PaymentAllocation::class); }
}


// =============================================================================
// app/Models/Expense.php
// =============================================================================
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id', 'category_id', 'user_id', 'expense_number', 'title',
        'expense_date', 'amount', 'tax_amount', 'total_amount',
        'payment_mode', 'reference_number', 'bill_attachment', 'notes',
    ];

    protected $casts = ['expense_date' => 'date', 'amount' => 'decimal:2', 'total_amount' => 'decimal:2'];

    public function business() { return $this->belongsTo(Business::class); }
    public function category() { return $this->belongsTo(ExpenseCategory::class); }
}
