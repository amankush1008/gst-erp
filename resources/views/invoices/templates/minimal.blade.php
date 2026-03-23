{{-- resources/views/invoices/templates/minimal.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 10.5px; color: #333; }
  .page { padding: 28px 32px; }
  hr { border: none; border-top: 1px solid #e2e2e2; margin: 14px 0; }
  hr.thick { border-top: 2px solid #333; }

  .header { margin-bottom: 18px; }
  .header table { width: 100%; }
  .biz-name  { font-size: 18px; font-weight: 700; }
  .biz-gstin { font-size: 9.5px; color: #777; margin-top: 2px; }
  .inv-type  { font-size: 10px; color: #777; text-align: right; text-transform: uppercase; letter-spacing: 1.5px; }
  .inv-num   { font-size: 16px; font-weight: 700; text-align: right; }
  .inv-meta  { font-size: 10px; color: #777; text-align: right; margin-top: 3px; }

  .party-section { margin-bottom: 16px; }
  .party-section table { width: 100%; }
  .label { font-size: 9px; text-transform: uppercase; letter-spacing: 1px; color: #aaa; margin-bottom: 3px; }
  .party-name { font-size: 12px; font-weight: 700; }
  .party-sub  { font-size: 10px; color: #666; margin-top: 2px; }

  .items-table { width: 100%; border-collapse: collapse; }
  .items-table th { font-size: 9px; text-transform: uppercase; letter-spacing: 0.8px; color: #888; padding: 5px 4px; border-bottom: 1px solid #e2e2e2; font-weight: 600; }
  .items-table td { padding: 6px 4px; border-bottom: 1px solid #f0f0f0; vertical-align: top; }
  .item-name { font-weight: 600; }
  .item-hsn  { font-size: 9px; color: #aaa; }
  .tr  { text-align: right; }
  .tc  { text-align: center; }

  .totals-section { margin-top: 12px; }
  .totals-section table { margin-left: auto; width: 210px; }
  .totals-section td { padding: 3px 4px; font-size: 10.5px; }
  .totals-section .total-final { font-size: 13px; font-weight: 700; border-top: 2px solid #333; padding-top: 5px; }

  .footer { margin-top: 20px; font-size: 9.5px; color: #777; }
  .footer table { width: 100%; }
  .sign-area { text-align: right; }
  .sign-line { display: inline-block; width: 130px; border-top: 1px solid #bbb; margin-top: 35px; padding-top: 4px; font-size: 9.5px; }
</style>
</head>
<body>
<div class="page">

  {{-- Header --}}
  <div class="header">
    <table><tr>
      <td width="55%">
        @if($business->logo)
          <img src="{{ public_path('storage/' . $business->logo) }}" height="28" style="margin-bottom:4px"><br>
        @endif
        <div class="biz-name">{{ $business->name }}</div>
        <div class="biz-gstin">
          @if($business->address){{ $business->address }}@endif
          @if($business->gstin) &nbsp;·&nbsp; GSTIN: {{ $business->gstin }}@endif
          @if($business->mobile) &nbsp;·&nbsp; {{ $business->mobile }}@endif
        </div>
      </td>
      <td width="45%">
        <div class="inv-type">{{ str_replace('_', ' ', $invoice->invoice_type) }}</div>
        <div class="inv-num">{{ $invoice->invoice_number }}</div>
        <div class="inv-meta">
          {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}
          @if($invoice->due_date) &nbsp;·&nbsp; Due {{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}@endif
        </div>
      </td>
    </tr></table>
  </div>
  <hr class="thick">

  {{-- Party --}}
  <div class="party-section">
    <table><tr>
      <td width="50%">
        <div class="label">Bill To</div>
        <div class="party-name">{{ $invoice->party->name ?? '' }}</div>
        @if($invoice->party->billing_address)
          <div class="party-sub">{{ $invoice->party->billing_address }}</div>
        @endif
        @if($invoice->party->gstin)
          <div class="party-sub">GSTIN: {{ $invoice->party->gstin }}</div>
        @endif
      </td>
      @if($invoice->eway_bill_number)
      <td width="50%" style="text-align:right">
        <div class="label" style="text-align:right">E-Way Bill No.</div>
        <div style="font-weight:700">{{ $invoice->eway_bill_number }}</div>
      </td>
      @endif
    </tr></table>
  </div>
  <hr>

  {{-- Items --}}
  <table class="items-table">
    <thead><tr>
      <th width="4%" class="tc">#</th>
      <th width="30%">Item / Description</th>
      <th width="9%" class="tc">HSN</th>
      <th width="7%" class="tc">Qty</th>
      <th width="12%" class="tr">Rate (₹)</th>
      <th width="9%" class="tc">GST%</th>
      <th width="12%" class="tr">GST (₹)</th>
      <th width="13%" class="tr">Amount (₹)</th>
    </tr></thead>
    <tbody>
      @foreach($invoice->items as $i => $item)
      <tr>
        <td class="tc" style="color:#bbb">{{ $i+1 }}</td>
        <td>
          <div class="item-name">{{ $item->product_name }}</div>
          @if($item->description)
            <div style="font-size:9px;color:#999">{{ $item->description }}</div>
          @endif
        </td>
        <td class="tc item-hsn">{{ $item->hsn_code ?? '' }}</td>
        <td class="tc">{{ $item->qty }}{{ $item->unit ? ' '.$item->unit : '' }}</td>
        <td class="tr">{{ number_format($item->rate, 2) }}</td>
        <td class="tc" style="color:#888">{{ $item->gst_rate }}%</td>
        <td class="tr" style="color:#888">{{ number_format($item->tax_amount, 2) }}</td>
        <td class="tr" style="font-weight:600">{{ number_format($item->total_amount, 2) }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>

  {{-- Totals --}}
  <div class="totals-section">
    <table>
      <tr><td style="color:#888">Taxable Amount</td><td class="tr">{{ number_format($invoice->taxable_amount, 2) }}</td></tr>
      @if($invoice->cgst_amount > 0)
      <tr><td style="color:#888">CGST</td><td class="tr">{{ number_format($invoice->cgst_amount, 2) }}</td></tr>
      <tr><td style="color:#888">SGST</td><td class="tr">{{ number_format($invoice->sgst_amount, 2) }}</td></tr>
      @endif
      @if($invoice->igst_amount > 0)
      <tr><td style="color:#888">IGST</td><td class="tr">{{ number_format($invoice->igst_amount, 2) }}</td></tr>
      @endif
      @if($invoice->discount_amount > 0)
      <tr><td style="color:#888">Discount</td><td class="tr">- {{ number_format($invoice->discount_amount, 2) }}</td></tr>
      @endif
      @if($invoice->round_off != 0)
      <tr><td style="color:#888">Round Off</td><td class="tr">{{ number_format($invoice->round_off, 2) }}</td></tr>
      @endif
      <tr class="total-final">
        <td><strong>Total Amount</strong></td>
        <td class="tr"><strong>₹ {{ number_format($invoice->total_amount, 2) }}</strong></td>
      </tr>
    </table>
  </div>

  <hr style="margin-top:14px">

  <div style="font-size:9.5px;color:#666;margin-bottom:14px;font-style:italic">
    Amount in Words: {{ $invoice->amountInWords() }}
  </div>

  {{-- Footer --}}
  <div class="footer">
    <table><tr>
      <td width="58%" style="vertical-align:top">
        @if($business->bank_name)
          <div class="label" style="margin-bottom:3px">Bank Details</div>
          <div>{{ $business->bank_name }} &nbsp;·&nbsp; A/C: {{ $business->bank_account }}</div>
          <div>IFSC: {{ $business->bank_ifsc }}@if($business->bank_branch) &nbsp;·&nbsp; {{ $business->bank_branch }}@endif</div>
        @endif
        @if($business->terms)
          <div style="margin-top:8px">
            <div class="label" style="margin-bottom:3px">Terms & Conditions</div>
            <div>{{ $business->terms }}</div>
          </div>
        @endif
      </td>
      <td width="42%" class="sign-area" style="vertical-align:bottom">
        <div class="sign-line">
          {{ $business->name }}<br>
          <span style="font-size:9px;color:#aaa">Authorised Signatory</span>
        </div>
      </td>
    </tr></table>
  </div>

</div>
</body>
</html>
