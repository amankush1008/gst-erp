<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9pt;
            color: #1a1a1a;
            background: white;
        }

        .page {
            padding: 15mm 12mm;
            min-height: 297mm;
            position: relative;
        }

        /* === HEADER === */
        .invoice-header {
            border: 2px solid #1a365d;
            border-bottom: none;
            padding: 10px 12px;
            display: flex;
            align-items: flex-start;
        }

        .business-info { flex: 1; }

        .business-name {
            font-size: 16pt;
            font-weight: bold;
            color: #1a365d;
            margin-bottom: 3px;
        }

        .business-address {
            font-size: 8pt;
            color: #555;
            line-height: 1.4;
        }

        .business-gstin {
            font-size: 9pt;
            font-weight: bold;
            color: #1a365d;
            margin-top: 4px;
        }

        .business-logo {
            width: 70px; height: 70px;
            object-fit: contain;
            margin-left: 12px;
        }

        /* === INVOICE TITLE === */
        .invoice-title-bar {
            background: #1a365d;
            color: white;
            text-align: center;
            padding: 6px;
            font-size: 12pt;
            font-weight: bold;
            letter-spacing: 1px;
            border: 2px solid #1a365d;
            border-bottom: none;
        }

        /* === PARTY & INVOICE INFO === */
        .invoice-info-section {
            border: 2px solid #1a365d;
            border-bottom: none;
            display: flex;
        }

        .party-section {
            flex: 1;
            padding: 8px 12px;
            border-right: 1px solid #1a365d;
        }

        .invoice-details-section {
            width: 200px;
            padding: 8px 12px;
        }

        .section-label {
            font-size: 7pt;
            font-weight: bold;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .party-name {
            font-size: 11pt;
            font-weight: bold;
            color: #1a365d;
            margin-bottom: 3px;
        }

        .detail-row {
            display: flex;
            margin-bottom: 3px;
            font-size: 8pt;
        }

        .detail-label {
            width: 80px;
            color: #666;
            font-weight: bold;
        }

        .detail-value { color: #1a1a1a; }

        /* === ITEMS TABLE === */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #1a365d;
            border-bottom: none;
        }

        .items-table thead tr {
            background: #1a365d;
            color: white;
        }

        .items-table thead th {
            padding: 6px 8px;
            font-size: 8pt;
            font-weight: bold;
            text-align: left;
            border-right: 1px solid #2d5080;
        }

        .items-table thead th.text-right { text-align: right; }
        .items-table thead th.text-center { text-align: center; }

        .items-table tbody tr {
            border-bottom: 1px solid #e2e8f0;
        }

        .items-table tbody tr:nth-child(even) {
            background: #F8FAFC;
        }

        .items-table tbody td {
            padding: 5px 8px;
            font-size: 8.5pt;
            border-right: 1px solid #e2e8f0;
            vertical-align: top;
        }

        .items-table tbody td.text-right { text-align: right; }
        .items-table tbody td.text-center { text-align: center; }

        .item-name-cell { font-weight: 500; }
        .item-desc { font-size: 7.5pt; color: #888; margin-top: 2px; }

        /* === SUMMARY SECTION === */
        .summary-section {
            border: 2px solid #1a365d;
            border-bottom: none;
            display: flex;
        }

        .tax-breakup {
            flex: 1;
            border-right: 1px solid #1a365d;
            padding: 8px 12px;
        }

        .tax-breakup-title {
            font-weight: bold;
            font-size: 8pt;
            color: #1a365d;
            margin-bottom: 5px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 3px;
        }

        .tax-breakup-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
        }

        .tax-breakup-table th {
            background: #F1F5F9;
            padding: 3px 6px;
            text-align: right;
            font-weight: bold;
            border: 1px solid #e2e8f0;
        }

        .tax-breakup-table th:first-child { text-align: left; }

        .tax-breakup-table td {
            padding: 3px 6px;
            text-align: right;
            border: 1px solid #e2e8f0;
        }

        .tax-breakup-table td:first-child { text-align: left; }

        .totals-section {
            width: 230px;
            padding: 8px 12px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 3px 0;
            font-size: 8.5pt;
            border-bottom: 1px solid #F1F5F9;
        }

        .total-row.grand-total {
            font-size: 11pt;
            font-weight: bold;
            color: #1a365d;
            border-top: 2px solid #1a365d;
            border-bottom: none;
            margin-top: 4px;
            padding-top: 6px;
        }

        /* === AMOUNT IN WORDS === */
        .amount-words-section {
            border: 2px solid #1a365d;
            border-bottom: none;
            padding: 6px 12px;
            background: #F8FAFC;
        }

        .amount-words-section strong { color: #1a365d; }

        /* === BANK & NOTES === */
        .bank-notes-section {
            border: 2px solid #1a365d;
            display: flex;
        }

        .bank-details {
            flex: 1;
            padding: 8px 12px;
            border-right: 1px solid #1a365d;
        }

        .notes-section {
            flex: 1;
            padding: 8px 12px;
        }

        .section-title {
            font-size: 8pt;
            font-weight: bold;
            color: #1a365d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 3px;
        }

        /* === DECLARATION & SIGNATURE === */
        .declaration-section {
            border: 2px solid #1a365d;
            border-top: none;
            display: flex;
        }

        .declaration {
            flex: 1;
            padding: 8px 12px;
            border-right: 1px solid #1a365d;
            font-size: 7.5pt;
            color: #555;
        }

        .signature-section {
            width: 200px;
            padding: 8px 12px;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #1a365d;
            margin-top: 40px;
            padding-top: 4px;
            font-size: 8pt;
            font-weight: bold;
            color: #1a365d;
        }

        /* === TRANSPORT === */
        .transport-section {
            border: 2px solid #1a365d;
            border-top: none;
            border-bottom: none;
            padding: 6px 12px;
        }

        /* Utilities */
        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }
        .fw-bold { font-weight: bold; }
        .mono { font-family: 'Courier New', monospace; }
    </style>
</head>
<body>
<div class="page">

    {{-- ========= HEADER ========= --}}
    <div class="invoice-header">
        <div class="business-info">
            <div class="business-name">{{ $invoice->business->business_name }}</div>
            <div class="business-address">
                {{ $invoice->business->address_line1 }}
                @if($invoice->business->address_line2), {{ $invoice->business->address_line2 }}@endif<br>
                {{ $invoice->business->city }}, {{ $invoice->business->state }} - {{ $invoice->business->pincode }}<br>
                @if($invoice->business->phone) Phone: {{ $invoice->business->phone }}@endif
                @if($invoice->business->email) | Email: {{ $invoice->business->email }}@endif
                @if($invoice->business->website) | {{ $invoice->business->website }}@endif
            </div>
            @if($invoice->business->gstin)
            <div class="business-gstin">GSTIN: {{ $invoice->business->gstin }}</div>
            @endif
            @if($invoice->business->pan)
            <div class="business-address">PAN: {{ $invoice->business->pan }}</div>
            @endif
        </div>
        @if($invoice->business->logo)
        <img src="{{ storage_path('app/public/' . $invoice->business->logo) }}" class="business-logo" alt="Logo">
        @endif
    </div>

    {{-- ========= INVOICE TITLE ========= --}}
    <div class="invoice-title-bar">{{ strtoupper($invoice->invoice_type_label) }}</div>

    {{-- ========= PARTY & INVOICE DETAILS ========= --}}
    <div class="invoice-info-section">
        <div class="party-section">
            <div class="section-label">Bill To</div>
            <div class="party-name">{{ $invoice->party->name }}</div>
            @if($invoice->party->billing_address)
            <div style="font-size:8pt;color:#555;line-height:1.5">
                {{ $invoice->party->billing_address }}<br>
                {{ $invoice->party->billing_city }}, {{ $invoice->party->billing_state }} - {{ $invoice->party->billing_pincode }}
            </div>
            @endif
            @if($invoice->party->gstin)
            <div style="font-size:8.5pt;font-weight:bold;margin-top:4px">GSTIN: {{ $invoice->party->gstin }}</div>
            @endif
            @if($invoice->party->pan)
            <div style="font-size:8pt">PAN: {{ $invoice->party->pan }}</div>
            @endif
        </div>
        <div class="invoice-details-section">
            <div class="section-label">Invoice Details</div>
            <div class="detail-row">
                <span class="detail-label">Invoice #:</span>
                <span class="detail-value fw-bold">{{ $invoice->invoice_number }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Date:</span>
                <span class="detail-value">{{ $invoice->invoice_date->format('d/m/Y') }}</span>
            </div>
            @if($invoice->due_date)
            <div class="detail-row">
                <span class="detail-label">Due Date:</span>
                <span class="detail-value">{{ $invoice->due_date->format('d/m/Y') }}</span>
            </div>
            @endif
            @if($invoice->po_number)
            <div class="detail-row">
                <span class="detail-label">PO Number:</span>
                <span class="detail-value">{{ $invoice->po_number }}</span>
            </div>
            @endif
            @if($invoice->place_of_supply)
            <div class="detail-row">
                <span class="detail-label">Place of Supply:</span>
                <span class="detail-value">{{ $invoice->place_of_supply }}</span>
            </div>
            @endif
            @if($invoice->reverse_charge)
            <div class="detail-row">
                <span class="detail-label">Rev. Charge:</span>
                <span class="detail-value fw-bold">Yes</span>
            </div>
            @endif
            @if($invoice->eway_bill_number)
            <div class="detail-row">
                <span class="detail-label">E-Way Bill:</span>
                <span class="detail-value">{{ $invoice->eway_bill_number }}</span>
            </div>
            @endif
        </div>
    </div>

    {{-- ========= TRANSPORT ========= --}}
    @if($invoice->transport)
    <div class="transport-section">
        <div style="font-size:8pt;font-weight:bold;color:#1a365d;margin-bottom:3px">Transport Details</div>
        <div style="display:flex;gap:20px;font-size:7.5pt;color:#555">
            @if($invoice->transport->transporter_name)
            <span><strong>Transporter:</strong> {{ $invoice->transport->transporter_name }}</span>
            @endif
            @if($invoice->transport->vehicle_number)
            <span><strong>Vehicle:</strong> {{ $invoice->transport->vehicle_number }}</span>
            @endif
            @if($invoice->transport->lr_number)
            <span><strong>LR No:</strong> {{ $invoice->transport->lr_number }}</span>
            @endif
            @if($invoice->transport->dispatch_date)
            <span><strong>Dispatch:</strong> {{ $invoice->transport->dispatch_date->format('d/m/Y') }}</span>
            @endif
        </div>
    </div>
    @endif

    {{-- ========= ITEMS TABLE ========= --}}
    <table class="items-table">
        <thead>
            <tr>
                <th style="width:28px" class="text-center">#</th>
                <th style="min-width:180px">Description of Goods / Services</th>
                <th style="width:60px" class="text-center">HSN/SAC</th>
                <th style="width:55px" class="text-center">Qty</th>
                <th style="width:40px" class="text-center">Unit</th>
                <th style="width:80px" class="text-right">Rate (₹)</th>
                <th style="width:50px" class="text-center">Disc %</th>
                @if(!$invoice->is_interstate)
                <th style="width:55px" class="text-center">CGST %</th>
                <th style="width:60px" class="text-right">CGST (₹)</th>
                <th style="width:55px" class="text-center">SGST %</th>
                <th style="width:60px" class="text-right">SGST (₹)</th>
                @else
                <th style="width:55px" class="text-center">IGST %</th>
                <th style="width:60px" class="text-right">IGST (₹)</th>
                @endif
                <th style="width:85px" class="text-right">Amount (₹)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $i => $item)
            <tr>
                <td class="text-center text-muted" style="color:#999">{{ $i + 1 }}</td>
                <td>
                    <div class="item-name-cell">{{ $item->item_name }}</div>
                    @if($item->description)
                    <div class="item-desc">{{ $item->description }}</div>
                    @endif
                </td>
                <td class="text-center">{{ $item->hsn_code }}</td>
                <td class="text-center mono">{{ number_format($item->quantity, 2) }}</td>
                <td class="text-center">{{ $item->unit }}</td>
                <td class="text-right mono">{{ number_format($item->rate, 2) }}</td>
                <td class="text-center">{{ $item->discount_percent ? $item->discount_percent . '%' : '-' }}</td>
                @if(!$invoice->is_interstate)
                <td class="text-center">{{ $item->cgst_rate }}%</td>
                <td class="text-right mono">{{ number_format($item->cgst_amount, 2) }}</td>
                <td class="text-center">{{ $item->sgst_rate }}%</td>
                <td class="text-right mono">{{ number_format($item->sgst_amount, 2) }}</td>
                @else
                <td class="text-center">{{ $item->igst_rate }}%</td>
                <td class="text-right mono">{{ number_format($item->igst_amount, 2) }}</td>
                @endif
                <td class="text-right mono fw-bold">{{ number_format($item->total_amount, 2) }}</td>
            </tr>
            @endforeach

            {{-- Empty rows to maintain look --}}
            @for($e = $invoice->items->count(); $e < 8; $e++)
            <tr><td colspan="13" style="height:18px">&nbsp;</td></tr>
            @endfor
        </tbody>
    </table>

    {{-- ========= SUMMARY SECTION ========= --}}
    <div class="summary-section">
        <div class="tax-breakup">
            <div class="tax-breakup-title">Tax Summary</div>
            <table class="tax-breakup-table">
                <thead>
                    <tr>
                        <th>GST Rate</th>
                        <th>Taxable Amt</th>
                        @if(!$invoice->is_interstate)
                        <th>CGST Amt</th>
                        <th>SGST Amt</th>
                        @else
                        <th>IGST Amt</th>
                        @endif
                        <th>Total Tax</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->tax_summary as $rate => $tax)
                    <tr>
                        <td>{{ $rate }}%</td>
                        <td>{{ number_format($tax['taxable'], 2) }}</td>
                        @if(!$invoice->is_interstate)
                        <td>{{ number_format($tax['cgst'], 2) }}</td>
                        <td>{{ number_format($tax['sgst'], 2) }}</td>
                        @else
                        <td>{{ number_format($tax['igst'], 2) }}</td>
                        @endif
                        <td>{{ number_format($tax['total_tax'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="totals-section">
            <div class="total-row">
                <span>Subtotal</span>
                <span class="mono">₹{{ number_format($invoice->subtotal, 2) }}</span>
            </div>
            @if($invoice->discount_amount > 0)
            <div class="total-row" style="color:#DC2626">
                <span>Discount ({{ $invoice->discount_percent }}%)</span>
                <span class="mono">-₹{{ number_format($invoice->discount_amount, 2) }}</span>
            </div>
            @endif
            <div class="total-row">
                <span>Taxable Amount</span>
                <span class="mono">₹{{ number_format($invoice->taxable_amount, 2) }}</span>
            </div>
            @if($invoice->cgst_amount > 0)
            <div class="total-row">
                <span>CGST</span>
                <span class="mono">₹{{ number_format($invoice->cgst_amount, 2) }}</span>
            </div>
            <div class="total-row">
                <span>SGST</span>
                <span class="mono">₹{{ number_format($invoice->sgst_amount, 2) }}</span>
            </div>
            @endif
            @if($invoice->igst_amount > 0)
            <div class="total-row">
                <span>IGST</span>
                <span class="mono">₹{{ number_format($invoice->igst_amount, 2) }}</span>
            </div>
            @endif
            @if($invoice->other_charges != 0)
            <div class="total-row">
                <span>Other Charges</span>
                <span class="mono">₹{{ number_format($invoice->other_charges, 2) }}</span>
            </div>
            @endif
            @if($invoice->round_off != 0)
            <div class="total-row">
                <span>Round Off</span>
                <span class="mono">₹{{ number_format($invoice->round_off, 2) }}</span>
            </div>
            @endif
            <div class="total-row grand-total">
                <span>TOTAL</span>
                <span class="mono">₹{{ number_format($invoice->total_amount, 2) }}</span>
            </div>
        </div>
    </div>

    {{-- ========= AMOUNT IN WORDS ========= --}}
    <div class="amount-words-section">
        <strong>Amount in Words: </strong>
        <span style="font-size:8pt">{{ $invoice->amount_in_words }}</span>
    </div>

    {{-- ========= BANK & NOTES ========= --}}
    <div class="bank-notes-section">
        <div class="bank-details">
            @if($invoice->business->bank_account_no)
            <div class="section-title">Bank Details</div>
            <div class="detail-row">
                <span class="detail-label">Bank Name:</span>
                <span class="detail-value">{{ $invoice->business->bank_name }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Account No:</span>
                <span class="detail-value mono fw-bold">{{ $invoice->business->bank_account_no }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">IFSC Code:</span>
                <span class="detail-value mono">{{ $invoice->business->bank_ifsc }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Branch:</span>
                <span class="detail-value">{{ $invoice->business->bank_branch }}</span>
            </div>
            @if($invoice->business->upi_id)
            <div class="detail-row">
                <span class="detail-label">UPI ID:</span>
                <span class="detail-value">{{ $invoice->business->upi_id }}</span>
            </div>
            @endif
            @endif
        </div>
        <div class="notes-section">
            @if($invoice->notes)
            <div class="section-title">Notes</div>
            <div style="font-size:8pt;color:#555;line-height:1.5">{{ $invoice->notes }}</div>
            @endif
            @if($invoice->terms_conditions)
            <div class="section-title" style="margin-top:{{ $invoice->notes ? '6px' : '0' }}">Terms & Conditions</div>
            <div style="font-size:7.5pt;color:#666;line-height:1.5">{{ $invoice->terms_conditions }}</div>
            @endif
        </div>
    </div>

    {{-- ========= DECLARATION & SIGNATURE ========= --}}
    <div class="declaration-section">
        <div class="declaration">
            <div class="section-title">Declaration</div>
            {{ $invoice->business->declaration ?? 'We declare that this invoice shows the actual price of the goods described and that all particulars are true and correct.' }}
        </div>
        <div class="signature-section">
            <div style="font-size:8pt;color:#1a365d;font-weight:bold;margin-bottom:4px">
                For {{ $invoice->business->business_name }}
            </div>
            @if($invoice->business->signature)
            <img src="{{ storage_path('app/public/' . $invoice->business->signature) }}" style="max-height:50px;max-width:150px;margin-top:5px">
            @endif
            <div class="signature-line">Authorised Signatory</div>
        </div>
    </div>

    {{-- ========= FOOTER ========= --}}
    <div style="text-align:center;margin-top:8px;font-size:7pt;color:#aaa">
        This is a computer generated invoice. — Generated by GST ERP
    </div>

</div>
</body>
</html>
