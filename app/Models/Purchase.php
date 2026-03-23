<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id', 'party_id', 'invoice_number', 'invoice_date', 'due_date',
        'subtotal', 'taxable_amount', 'discount', 'cgst_amount', 'sgst_amount',
        'igst_amount', 'tax_amount', 'round_off', 'total_amount',
        'paid_amount', 'balance_amount', 'payment_status', 'payment_method', 'notes',
        'created_by',
    ];

    protected $casts = [
        'invoice_date'   => 'date',
        'due_date'       => 'date',
        'total_amount'   => 'decimal:2',
        'balance_amount' => 'decimal:2',
        'paid_amount'    => 'decimal:2',
        'tax_amount'     => 'decimal:2',
    ];

    public function party()
    {
        return $this->belongsTo(Party::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}

class PurchaseItem extends Model
{
    protected $table = 'purchase_items';

    protected $fillable = [
        'purchase_id', 'product_id', 'product_name', 'hsn_code',
        'qty', 'unit', 'rate', 'discount', 'taxable_amount',
        'gst_rate', 'tax_amount', 'total',
    ];

    protected $casts = [
        'qty'            => 'decimal:3',
        'rate'           => 'decimal:2',
        'total'          => 'decimal:2',
        'taxable_amount' => 'decimal:2',
        'tax_amount'     => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }
}
