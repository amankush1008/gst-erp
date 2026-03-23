<?php
// Additional methods for ReportController and PartyController ledger
// These extend the Controllers.php classes already created

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Party;
use App\Services\GstReportService;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesExport;
use App\Exports\StockExport;
use App\Exports\Gstr1Export;

// Supplement to PartyController – adds ledger() method implementation detail
class PartyLedgerHelper
{
    public static function buildLedger(Party $party, int $businessId): array
    {
        $invoices = Invoice::where('business_id', $businessId)
            ->where('party_id', $party->id)
            ->orderBy('invoice_date')
            ->get(['id', 'invoice_number', 'invoice_date', 'total_amount', 'payment_status']);

        $payments = DB::table('payments')
            ->where('business_id', $businessId)
            ->where('party_id', $party->id)
            ->orderBy('payment_date')
            ->get(['id', 'payment_date', 'amount', 'payment_method', 'reference', 'type']);

        $ledger = [];

        foreach ($invoices as $inv) {
            $ledger[] = [
                'date'      => $inv->invoice_date,
                'type'      => 'invoice',
                'id'        => $inv->id,
                'reference' => $inv->invoice_number,
                'amount'    => $inv->total_amount,
                'status'    => $inv->payment_status,
            ];
        }

        foreach ($payments as $pmt) {
            $ledger[] = [
                'date'      => $pmt->payment_date,
                'type'      => 'payment',
                'id'        => $pmt->id,
                'reference' => $pmt->reference ?? ('PMT-' . $pmt->id),
                'amount'    => $pmt->amount,
                'status'    => null,
            ];
        }

        // Sort all entries by date
        usort($ledger, fn($a, $b) => strcmp($a['date'], $b['date']));

        $totalInvoiced = $invoices->sum('total_amount');
        $totalPaid     = $payments->sum('amount');

        return [
            'ledger'  => $ledger,
            'summary' => [
                'total_invoiced' => $totalInvoiced,
                'total_paid'     => $totalPaid,
                'balance'        => $totalInvoiced - $totalPaid,
            ],
        ];
    }
}

// Supplement to ReportController – purchases report
class ReportControllerSupplement
{
    public static function purchasesReport(Request $request, int $businessId): array
    {
        $query = DB::table('purchases')
            ->join('parties', 'parties.id', '=', 'purchases.party_id')
            ->where('purchases.business_id', $businessId)
            ->when($request->date_from, fn($q) => $q->whereDate('purchases.invoice_date', '>=', $request->date_from))
            ->when($request->date_to,   fn($q) => $q->whereDate('purchases.invoice_date', '<=', $request->date_to))
            ->when($request->party_id,  fn($q) => $q->where('purchases.party_id', $request->party_id))
            ->select(
                'purchases.*',
                'parties.name as party_name',
                'parties.gstin as party_gstin'
            );

        $purchases = $query->paginate(20)->withQueryString();

        $summary = [
            'total_purchases' => $query->sum('purchases.total_amount'),
            'total_tax'       => $query->sum('purchases.tax_amount'),
            'taxable'         => $query->sum('purchases.taxable_amount'),
            'cgst'            => $query->sum('purchases.cgst_amount'),
            'sgst'            => $query->sum('purchases.sgst_amount'),
            'igst'            => $query->sum('purchases.igst_amount'),
        ];

        return compact('purchases', 'summary');
    }
}
