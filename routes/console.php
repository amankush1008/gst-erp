<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Scheduled Tasks ───────────────────────────────────────────────────────────

// Daily: mark overdue invoices
Schedule::call(function () {
    \App\Models\Invoice::where('payment_status', 'unpaid')
        ->where('due_date', '<', now()->toDateString())
        ->update(['payment_status' => 'overdue']);
})->dailyAt('00:05')->name('mark-overdue-invoices');

// Weekly: clean up old activity logs (keep 90 days)
Schedule::call(function () {
    \DB::table('activity_logs')
        ->where('created_at', '<', now()->subDays(90))
        ->delete();
})->weekly()->name('clean-activity-logs');

// Monthly: auto-advance number sequences to new financial year if needed
Schedule::call(function () {
    $now = now();
    if ($now->month === 4 && $now->day === 1) {
        $newYear = $now->year . '-' . ($now->year + 1);
        \DB::table('number_sequences')
            ->update([
                'financial_year' => $newYear,
                'current_number' => 1,
                'updated_at'     => now(),
            ]);
        \Log::info("Number sequences reset for FY {$newYear}");
    }
})->dailyAt('00:01')->name('reset-fy-sequences');
