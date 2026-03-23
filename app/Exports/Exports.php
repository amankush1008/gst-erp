<?php

namespace App\Exports;

use App\Models\Invoice;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

// ─────────────────────────────────────────────────────────────────────────────
// SALES EXPORT
// ─────────────────────────────────────────────────────────────────────────────
class SalesExport implements FromCollection, WithHeadings, WithStyles, WithTitle, ShouldAutoSize
{
    public function __construct(
        private int $businessId,
        private ?string $dateFrom = null,
        private ?string $dateTo   = null,
    ) {}

    public function title(): string { return 'Sales Report'; }

    public function headings(): array
    {
        return [
            'Invoice #', 'Date', 'Due Date', 'Party', 'GSTIN', 'Type',
            'Taxable Amount', 'CGST', 'SGST', 'IGST', 'Total Amount',
            'Paid Amount', 'Balance', 'Status',
        ];
    }

    public function collection()
    {
        return Invoice::with('party')
            ->where('business_id', $this->businessId)
            ->when($this->dateFrom, fn($q) => $q->whereDate('invoice_date', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn($q) => $q->whereDate('invoice_date', '<=', $this->dateTo))
            ->get()
            ->map(fn($inv) => [
                $inv->invoice_number,
                $inv->invoice_date,
                $inv->due_date,
                $inv->party->name ?? '',
                $inv->party->gstin ?? '',
                str_replace('_', ' ', ucwords($inv->invoice_type)),
                $inv->taxable_amount,
                $inv->cgst_amount,
                $inv->sgst_amount,
                $inv->igst_amount,
                $inv->total_amount,
                $inv->paid_amount,
                $inv->balance_amount,
                ucfirst($inv->payment_status),
            ]);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => [
                'fillType'   => 'solid',
                'startColor' => ['rgb' => '1E3A5F'],
            ]],
        ];
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// GSTR-1 EXPORT
// ─────────────────────────────────────────────────────────────────────────────
class Gstr1Export implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
{
    public function __construct(
        private array $b2bData,
        private array $b2cData,
    ) {}

    public function title(): string { return 'GSTR-1'; }

    public function headings(): array
    {
        return [
            'GSTIN', 'Party Name', 'Invoice #', 'Invoice Date',
            'Invoice Value', 'Place of Supply', 'Reverse Charge',
            'Invoice Type', 'Taxable Value', 'Tax Rate',
            'IGST', 'CGST', 'SGST', 'Cess',
        ];
    }

    public function collection()
    {
        $rows = collect();

        foreach ($this->b2bData as $row) {
            $rows->push([
                $row['gstin'],
                $row['party'],
                $row['invoice_number'],
                $row['date'],
                $row['total'],
                $row['pos'] ?? '',
                'N',
                'Regular',
                $row['taxable'],
                $row['tax_rate'],
                $row['igst'] ?? 0,
                $row['cgst'] ?? 0,
                $row['sgst'] ?? 0,
                0,
            ]);
        }

        return $rows;
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// STOCK EXPORT
// ─────────────────────────────────────────────────────────────────────────────
class StockExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
{
    public function __construct(private int $businessId) {}

    public function title(): string { return 'Stock Report'; }

    public function headings(): array
    {
        return [
            'Product Name', 'SKU', 'HSN Code', 'Category', 'Unit',
            'Opening Stock', 'Stock In', 'Stock Out',
            'Current Stock', 'Min Stock', 'Purchase Price', 'Stock Value',
        ];
    }

    public function collection()
    {
        return \DB::table('products')
            ->leftJoin('product_categories', 'product_categories.id', '=', 'products.category_id')
            ->leftJoin('units', 'units.id', '=', 'products.unit_id')
            ->leftJoin('stock', function ($join) {
                $join->on('stock.product_id', '=', 'products.id')
                     ->where('stock.business_id', $this->businessId);
            })
            ->where('products.business_id', $this->businessId)
            ->select([
                'products.name', 'products.sku', 'products.hsn_code',
                'product_categories.name as category',
                'units.symbol as unit',
                'products.opening_stock',
                \DB::raw('COALESCE(stock.quantity, 0) as current_stock'),
                'products.min_stock',
                'products.purchase_price',
                \DB::raw('COALESCE(stock.quantity, 0) * COALESCE(products.purchase_price, 0) as stock_value'),
            ])
            ->get()
            ->map(fn($r) => [
                $r->name, $r->sku, $r->hsn_code, $r->category, $r->unit,
                $r->opening_stock ?? 0, 0, 0,
                $r->current_stock, $r->min_stock ?? 0,
                $r->purchase_price ?? 0, $r->stock_value,
            ]);
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// PARTY LEDGER EXPORT
// ─────────────────────────────────────────────────────────────────────────────
class PartyLedgerExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
{
    public function __construct(
        private int $partyId,
        private array $ledger,
    ) {}

    public function title(): string { return 'Party Ledger'; }

    public function headings(): array
    {
        return ['Date', 'Type', 'Reference', 'Debit (Dr)', 'Credit (Cr)', 'Balance', 'Status'];
    }

    public function collection()
    {
        $rows    = collect();
        $balance = 0;

        foreach ($this->ledger as $entry) {
            if ($entry['type'] === 'invoice') $balance += $entry['amount'];
            else                               $balance -= $entry['amount'];

            $rows->push([
                $entry['date'],
                ucfirst($entry['type']),
                $entry['reference'],
                $entry['type'] === 'invoice' ? $entry['amount'] : 0,
                $entry['type'] === 'payment' ? $entry['amount'] : 0,
                $balance,
                $entry['status'] ?? '',
            ]);
        }

        return $rows;
    }
}
