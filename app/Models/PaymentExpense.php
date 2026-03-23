<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    protected $fillable = [
        'business_id', 'party_id', 'invoice_id', 'amount',
        'payment_date', 'payment_method', 'type',
        'reference', 'notes', 'bank_reference', 'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount'       => 'decimal:2',
    ];

    public function party()
    {
        return $this->belongsTo(Party::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}

class Expense extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id', 'category_id', 'amount', 'expense_date',
        'description', 'payment_mode', 'reference', 'receipt_path', 'created_by',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount'       => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}

class ExpenseCategory extends Model
{
    protected $table = 'expense_categories';

    protected $fillable = ['business_id', 'name', 'description'];
}
