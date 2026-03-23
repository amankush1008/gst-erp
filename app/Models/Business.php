<?php
// =============================================================================
// app/Models/Business.php
// =============================================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Business extends Model
{
    protected $fillable = [
        'tenant_id', 'user_id', 'business_name', 'gstin', 'pan',
        'email', 'phone', 'mobile', 'website',
        'address_line1', 'address_line2', 'city', 'state', 'pincode', 'country',
        'logo', 'signature', 'bank_name', 'bank_account_no', 'bank_ifsc', 'bank_branch',
        'upi_id', 'currency', 'financial_year_start', 'terms_conditions', 'declaration',
        'is_default', 'invoice_settings',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'invoice_settings' => 'array',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function invoices(): HasMany { return $this->hasMany(Invoice::class); }
    public function purchases(): HasMany { return $this->hasMany(Purchase::class); }
    public function parties(): HasMany { return $this->hasMany(Party::class); }
    public function products(): HasMany { return $this->hasMany(Product::class); }
    public function payments(): HasMany { return $this->hasMany(Payment::class); }
    public function expenses(): HasMany { return $this->hasMany(Expense::class); }
    public function warehouses(): HasMany { return $this->hasMany(Warehouse::class); }

    public function getLogoUrlAttribute(): string
    {
        return $this->logo ? Storage::url($this->logo) : asset('images/default-logo.png');
    }

    public function getFullAddressAttribute(): string
    {
        return implode(', ', array_filter([
            $this->address_line1, $this->address_line2,
            $this->city, $this->state, $this->pincode
        ]));
    }

    public function generateNumber(string $type): string
    {
        $seq = NumberSequence::firstOrCreate(
            ['business_id' => $this->id, 'type' => $type, 'financial_year' => $this->currentFinancialYear()],
            ['prefix' => strtoupper(substr($type, 0, 3)), 'next_number' => 1, 'padding' => 4]
        );

        $number = $seq->prefix . str_pad($seq->next_number, $seq->padding, '0', STR_PAD_LEFT) . $seq->suffix;
        $seq->increment('next_number');
        return $number;
    }

    public function currentFinancialYear(): string
    {
        $now = now();
        $fyStart = explode('-', $this->financial_year_start);
        $startMonth = (int) $fyStart[0];
        if ($now->month >= $startMonth) {
            return $now->year . '-' . ($now->year + 1);
        }
        return ($now->year - 1) . '-' . $now->year;
    }
}
