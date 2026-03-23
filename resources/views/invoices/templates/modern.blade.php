{{-- resources/views/invoices/templates/modern.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #334155; }
  .page { padding: 0; }

  .top-bar { background: #4F46E5; color: #fff; padding: 25px 35px; }
  .top-bar table { width: 100%; }
  .biz-name { font-size: 20px; font-weight: 700; letter-spacing: -0.5px; }
  .biz-sub  { font-size: 9px; opacity: 0.8; margin-top: 4px; }
  .inv-label { font-size: 11px; opacity: 0.7; text-transform: uppercase; letter-spacing: 2px; text-align: right; }
  .inv-num   { font-size: 22px; font-weight: 700; text-align: right; }
  .inv-date  { font-size: 10px; opacity: 0.8; text-align: right; margin-top: 3px; }

  .body { padding: 25px 35px; }

  .party-row { margin-bottom: 20px; }
  .party-row table { width: 100%; }
  .section-label { font-size: 9px; text-transform: uppercase; letter-spacing: 1.5px; color: #94A3B8; font-weight: 700; margin-bottom: 6px; }
  .party-name { font-size: 13px; font-weight: 700; color: #0F172A; }
  .party-detail { color: #64748B; font-size: 10px; margin-top: 2px; }

  .items-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
  .items-table thead tr { background: #F1F5F9; }
  .items-table th { padding: 8px 10px; font-size: 9px; text-transform: uppercase; letter-spacing: 1px; color: #64748B; font-weight: 700; }
  .items-table td { padding: 8px 10px; border-bottom: 1px solid #F1F5F9; }
  .items-table tbody tr:hover td { background: #FAFBFF; }
  .text-right { text-align: right; }
  .text-center { text-align: center; }
  .item-name { font-weight: 600; color: #1E293B; }
  .item-desc { font-size: 9px; color: #94A3B8; }

  .totals-wrap { display: flex; justify-content: flex-end; margin-bottom: 20px; }
  .totals-box { background: #F8FAFC; border: 1px solid #E2E8F0; padding: 15px; width: 260px; }
  .totals-row { display: flex; justify-content: space-between; padding: 3px 0; font-size: 11px; }
  .totals-total { border-top: 2px solid #4F46E5; margin-top: 8px; padding-top: 8px; font-weight: 700; font-size: 14px; color: #4F46E5; }

  .footer { background: #F8FAFC; border-top: 1px solid #E2E8F0; padding: 20px 35px; margin-top: 10px; }
  .footer table { width: 100%; }
  .badge-paid { background: #D1FAE5; color: #065F46; padding: 3px 10px; border-radius: 20px; font-size: 10px; font-weight: 700; }
  .badge-unpaid { background: #FEE2E2; color: #991B1B; padding: 3px 10px; border-radius: 20px; font-size: 10px; font-weight: 700; }
</style>
</head>
<body>
<div class="page">
  {{-- Top bar --}}
  <div class="top-bar">
    <table><tr>
      <td width="55%">
        @if($business->logo)
          <img src="{{ public_path('storage/' . $business->logo) }}" height="35" style="margin-bottom:6px"><br>
        @endif
        <div class="biz-name">{{ $business->name }}</div>
        <div class="biz-sub">
          {{ $business->address }}@if($business->gstin) | GSTIN: {{ $business->gstin }}@endif
        </div>
      </td>
      <td width="45%">
        <div class="inv-label">{{ str_replace('_', ' ', $invoice->invoice_type) }}</div>
        <div class="inv-num">{{ $invoice->invoice_number }}</div>
        <div class="inv-date">
          {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}
          @if($invoice->due_date) • Due {{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}@endif
        </div>
      </td>
    </tr></table>
  </div>

  <div class="body">
    {{-- Party --}}
    <div class="party-row">
      <table><tr>
        <td width="45%">
          <div class="section-label">Billed To</div>
          <div class="party-name">{{ $invoice->party->name ?? '' }}</div>
          <div class="party-detail">{{ $invoice->party->billing_address ?? '' }}</div>
          @if($invoice->party->gstin)
          <div class="party-detail">GSTIN: {{ $invoice->party->gstin }}</div>
          @endif
        </td>
        <td width="30%"></td>
        <td width="25%">
          <div class="section-label">Payment Status</div>
          @if($invoice->payment_status === 'paid')
            <span class="badge-paid">PAID</span>
          @else
            <span class="badge-unpaid">{{ strtoupper($invoice->payment_status) }}</span>
          @endif
          @if($invoice->balance_amount > 0)
          <div class="party-detail" style="margin-top:5px">
            Balance: <strong>₹{{ number_format($invoice->balance_amount, 2) }}</strong>
          </div>
          @endif
        </td>
      </tr></table>
    </div>

    {{-- Items --}}
    <table class="items-table">
      <thead><tr>
        <th width="4%">#</th>
        <th width="32%">Item</th>
        <th width="8%" class="text-center">HSN</th>
        <th width="8%" class="text-center">Qty</th>
        <th width="12%" class="text-right">Rate (₹)</th>
        <th width="9%" class="text-right">GST</th>
        <th width="12%" class="text-right">Tax (₹)</th>
        <th width="13%" class="text-right">Total (₹)</th>
      </tr></thead>
      <tbody>
        @foreach($invoice->items as $i => $item)
        <tr>
          <td class="text-center" style="color:#94A3B8">{{ $i+1 }}</td>
          <td>
            <div class="item-name">{{ $item->product_name }}</div>
            @if($item->description)<div class="item-desc">{{ $item->description }}</div>@endif
          </td>
          <td class="text-center" style="color:#64748B">{{ $item->hsn_code ?? '-' }}</td>
          <td class="text-center">{{ $item->qty }}</td>
          <td class="text-right">{{ number_format($item->rate, 2) }}</td>
          <td class="text-center" style="color:#64748B">{{ $item->gst_rate }}%</td>
          <td class="text-right" style="color:#64748B">{{ number_format($item->tax_amount, 2) }}</td>
          <td class="text-right" style="font-weight:600;color:#1E293B">{{ number_format($item->total_amount, 2) }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>

    {{-- Totals --}}
    <table style="width:100%"><tr>
      <td width="55%" style="vertical-align:top;padding-right:20px">
        <div class="section-label" style="margin-bottom:4px">Amount in Words</div>
        <div style="font-style:italic;color:#475569;font-size:10px">{{ $invoice->amountInWords() }}</div>
        @if($business->bank_name)
        <div style="margin-top:12px">
          <div class="section-label" style="margin-bottom:4px">Bank Details</div>
          <div style="font-size:10px;color:#475569;line-height:1.6">
            {{ $business->bank_name }} &nbsp;|&nbsp; A/C: {{ $business->bank_account }}<br>
            IFSC: {{ $business->bank_ifsc }}@if($business->bank_branch) &nbsp;|&nbsp; {{ $business->bank_branch }}@endif
          </div>
        </div>
        @endif
      </td>
      <td width="45%" style="vertical-align:top">
        <div class="totals-box">
          <div class="totals-row"><span style="color:#64748B">Taxable Amount</span><span>₹{{ number_format($invoice->taxable_amount, 2) }}</span></div>
          @if($invoice->cgst_amount > 0)
          <div class="totals-row"><span style="color:#64748B">CGST</span><span>₹{{ number_format($invoice->cgst_amount, 2) }}</span></div>
          <div class="totals-row"><span style="color:#64748B">SGST</span><span>₹{{ number_format($invoice->sgst_amount, 2) }}</span></div>
          @endif
          @if($invoice->igst_amount > 0)
          <div class="totals-row"><span style="color:#64748B">IGST</span><span>₹{{ number_format($invoice->igst_amount, 2) }}</span></div>
          @endif
          @if($invoice->round_off != 0)
          <div class="totals-row"><span style="color:#64748B">Round Off</span><span>₹{{ number_format($invoice->round_off, 2) }}</span></div>
          @endif
          <div class="totals-row totals-total">
            <span>Total Amount</span><span>₹{{ number_format($invoice->total_amount, 2) }}</span>
          </div>
        </div>
      </td>
    </tr></table>
  </div>

  {{-- Footer --}}
  <div class="footer">
    <table><tr>
      <td width="60%">
        @if($business->terms)
        <div class="section-label" style="margin-bottom:4px">Terms & Conditions</div>
        <div style="font-size:9px;color:#64748B">{{ $business->terms }}</div>
        @endif
      </td>
      <td width="40%" style="text-align:right">
        <div style="height:45px"></div>
        <div style="border-top:1px solid #CBD5E1;padding-top:5px">
          <div style="font-weight:700;font-size:11px">{{ $business->name }}</div>
          <div class="section-label">Authorised Signatory</div>
        </div>
      </td>
    </tr></table>
  </div>
</div>
</body>
</html>
