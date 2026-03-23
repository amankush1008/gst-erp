<?php

use App\Models\Business;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

if (!function_exists('currentBusiness')) {
    function currentBusiness(): ?Business
    {
        static $business = null;

        if ($business) return $business;

        $businessId = Session::get('current_business_id');

        if ($businessId) {
            $business = Business::find($businessId);
        }

        if (!$business && Auth::check()) {
            $business = Auth::user()->businesses()->first();
            if ($business) {
                Session::put('current_business_id', $business->id);
            }
        }

        return $business;
    }
}

if (!function_exists('currentBusinessId')) {
    function currentBusinessId(): ?int
    {
        return currentBusiness()?->id;
    }
}

if (!function_exists('formatCurrency')) {
    function formatCurrency(float $amount, string $symbol = '₹'): string
    {
        return $symbol . number_format($amount, 2);
    }
}

if (!function_exists('formatDate')) {
    function formatDate(?string $date, string $format = 'd M Y'): string
    {
        if (!$date) return '-';
        return \Carbon\Carbon::parse($date)->format($format);
    }
}

if (!function_exists('gstRates')) {
    function gstRates(): array
    {
        return [0, 0.1, 0.25, 1, 1.5, 3, 5, 6, 7.5, 12, 18, 28];
    }
}

if (!function_exists('statusBadge')) {
    function statusBadge(string $status): string
    {
        $map = [
            'paid'        => 'success',
            'unpaid'      => 'danger',
            'partial'     => 'warning',
            'draft'       => 'secondary',
            'cancelled'   => 'dark',
            'active'      => 'success',
            'inactive'    => 'secondary',
            'pending'     => 'warning',
        ];
        $color = $map[strtolower($status)] ?? 'primary';
        return "<span class='badge bg-{$color}'>" . ucfirst($status) . "</span>";
    }
}

if (!function_exists('financialYear')) {
    function financialYear(): string
    {
        $month = now()->month;
        $year  = now()->year;
        if ($month >= 4) {
            return $year . '-' . ($year + 1);
        }
        return ($year - 1) . '-' . $year;
    }
}

if (!function_exists('canUser')) {
    function canUser(string $permission): bool
    {
        return Auth::check() && Auth::user()->can($permission);
    }
}
