{{-- resources/views/invoices/templates/classic.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1a1a1a; background: #fff; }
  .page { padding: 30px; }

  /* Header */
  .header { border-bottom: 3px solid #1a1a1a; padding-bottom: 15px; margin-bottom: 20px; }
  .header table { width: 100%; }
  .biz-name { font-size: 22px; font-weight: 700; letter-spacing: -0.5px; }
  .biz-sub  { font-size: 10px; color: #555; margin-top: 3px; }
  .inv-title { font-size: 18px; font-weight: 700; text-align: right; text-transform: uppercase; letter-spacing: 2px; }
  .inv-number { font-size: 13px; font-weight: 700; text-align: right; margin-top: 5px; }

  /* Party details */
  .party-section { margin-bottom: 18px; }
  .party-section table { width: 100%; }
  .party-box { padding: 12px; border: 1px solid #ddd; }
  .party-label { font-size: 9px; text-transform: uppercase; letter-spacing: 1px; color: #888; font-weight: 700; margin-bottom: 5px; }
  .party-name { font-size: 13px; font-weight: 700; }

  /* Items */
  .items-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
  .items-table th { background: #1a1a1a; color: #fff; padding: 7px 8px; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
  .items-table td { padding: 7px 8px; border-bottom: 1px solid #eee; vertical-align: top; }
  .items-table tr:nth-child(even) td { background: #fafafa; }
  .text-right { text-align: right; }
  .text-center { text-align: center; }

  /* Totals */
  .totals-section { margin-bottom: 15px; }
  .totals-table { width: 250px; margin-left: auto; border-collapse: collapse; }
  .totals-table td { padding: 4px 8px; }
  .totals-table .total-row { border-top: 2px solid #1a1a1a; font-weight: 700; font-size: 13px; }
  .totals-table .label { color: #555; }

  /* Footer */
  .footer-section { margin-top: 20px; border-top: 1px solid #ddd; padding-top: 15px; }
  .footer-section table { width: 100%; }
  .sign-box { text-align: right; }
  .sign-label { font-size: 9px; color: #888; text-transform: uppercase; letter-spacing: 1px; }
  .amount-words { font-style: italic; font-size: 10px; color: #444; margin-bottom: 10px; }
</style>
</head>
<body>
<div class="page">
  {{-- Header --}}
  <div class="header">
    <table><tr>
      <td width="60%">
        <div class="biz-name">{{ $business->name }}</div>
        <div class="biz-sub">
          {{ $business->address }}@if($business->city), {{ $business->city }}@endif
          @if($business->gstin)<br>GSTIN: {{ $business->gstin }}@endif
          @if($business->mobile) | {{ $business->mobile }}@endif
        </div>
      </td>
      <td width="40%">
        <div class="inv-title">{{ str_replace('_', ' ', $invoice->invoice_type) }}</div>
        <div class="inv-number"># {{ $invoice->invoice_number }}</div>
        <div style="text-align:right;color:#555;margin-top:4px;font-size:10px">
          Date: {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d-M-Y') }}
          @if($invoice->due_date) | Due: {{ \Carbon\Carbon::parse($invoice->due_date)->format('d-M-Y') }} @endif
        </div>
      </td>
    </tr></table>
  </div>

  {{-- Party --}}
  <div class="party-section">
    <table><tr>
      <td width="50%" style="padding-right:10px">
        <div class="party-box">
          <div class="party-label">Bill To</div>
          <div class="party-name">{{ $invoice->party->name ?? '' }}</div>
          @if($invoice->party->billing_address)
            <div style="color:#555;margin-top:3px;font-size:10px">{{ $invoice->party->billing_address }}</div>
          @endif
          @if($invoice->party->gstin)
            <div style="font-size:10px;margin-top:3px">GSTIN: {{ $invoice->party->gstin }}</div>
          @endif
        </div>
      </td>
      <td width="50%">
        <table style="width:100%;font-size:10px">
          <tr><td style="color:#888">Invoice No.</td><td style="text-align:right;font-weight:700">{{ $invoice->invoice_number }}</td></tr>
          <tr><td style="color:#888">Invoice Date</td><td style="text-align:right">{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}</td></tr>
          @if($invoice->due_date)
          <tr><td style="color:#888">Due Date</td><td style="text-align:right">{{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}</td></tr>
          @endif
          @if($invoice->eway_bill_number)
          <tr><td style="color:#888">E-Way Bill</td><td style="text-align:right;font-weight:700">{{ $invoice->eway_bill_number }}</td></tr>
          @endif
        </table>
      </td>
    </tr></table>
  </div>

  {{-- Items --}}
  <table class="items-table">
    <thead>
      <tr>
        <th width="4%" class="text-center">#</th>
        <th width="28%">Description</th>
        <th width="8%" class="text-center">HSN</th>
        <th width="7%" class="text-center">Qty</th>
        <th width="10%" class="text-right">Rate</th>
        @if($invoice->discount_amount > 0)
        <th width="9%" class="text-right">Disc.</th>
        @endif
        <th width="9%" class="text-right">Taxable</th>
        <th width="8%" class="text-right">GST%</th>
        <th width="10%" class="text-right">Tax Amt</th>
        <th width="10%" class="text-right">Total</th>
      </tr>
    </thead>
    <tbody>
      @foreach($invoice->items as $i => $item)
      <tr>
        <td class="text-center">{{ $i+1 }}</td>
        <td>
          <strong>{{ $item->product_name }}</strong>
          @if($item->description)<br><span style="color:#666;font-size:9px">{{ $item->description }}</span>@endif
        </td>
        <td class="text-center">{{ $item->hsn_code ?? '' }}</td>
        <td class="text-center">{{ $item->qty }} {{ $item->unit }}</td>
        <td class="text-right">{{ number_format($item->rate, 2) }}</td>
        @if($invoice->discount_amount > 0)
        <td class="text-right">{{ number_format($item->discount, 2) }}</td>
        @endif
        <td class="text-right">{{ number_format($item->taxable_amount, 2) }}</td>
        <td class="text-center">{{ $item->gst_rate }}%</td>
        <td class="text-right">{{ number_format($item->tax_amount, 2) }}</td>
        <td class="text-right"><strong>{{ number_format($item->total_amount, 2) }}</strong></td>
      </tr>
      @endforeach
    </tbody>
  </table>

  {{-- Totals --}}
  <div class="totals-section">
    <table class="totals-table">
      <tr><td class="label">Subtotal</td><td class="text-right">{{ number_format($invoice->taxable_amount, 2) }}</td></tr>
      @if($invoice->cgst_amount > 0)
      <tr><td class="label">CGST</td><td class="text-right">{{ number_format($invoice->cgst_amount, 2) }}</td></tr>
      <tr><td class="label">SGST</td><td class="text-right">{{ number_format($invoice->sgst_amount, 2) }}</td></tr>
      @endif
      @if($invoice->igst_amount > 0)
      <tr><td class="label">IGST</td><td class="text-right">{{ number_format($invoice->igst_amount, 2) }}</td></tr>
      @endif
      @if($invoice->round_off != 0)
      <tr><td class="label">Round Off</td><td class="text-right">{{ number_format($invoice->round_off, 2) }}</td></tr>
      @endif
      <tr class="total-row">
        <td>TOTAL</td>
        <td class="text-right">₹ {{ number_format($invoice->total_amount, 2) }}</td>
      </tr>
    </table>
  </div>

  <div class="amount-words">Amount in Words: {{ $invoice->amountInWords() }}</div>

  {{-- Footer --}}
  <div class="footer-section">
    <table><tr>
      <td width="60%">
        @if($business->bank_name)
        <div style="font-size:10px">
          <strong>Bank Details:</strong><br>
          Bank: {{ $business->bank_name }}<br>
          A/C: {{ $business->bank_account }}<br>
          IFSC: {{ $business->bank_ifsc }}
        </div>
        @endif
        @if($business->terms)
        <div style="margin-top:8px;font-size:9px;color:#555">
          <strong>Terms:</strong> {{ $business->terms }}
        </div>
        @endif
      </td>
      <td width="40%" class="sign-box">
        <div style="height:50px"></div>
        <div style="border-top:1px solid #aaa;padding-top:5px;font-size:10px;font-weight:700">
          {{ $business->name }}
        </div>
        <div class="sign-label">Authorised Signatory</div>
      </td>
    </tr></table>
  </div>
</div>
</body>
</html>
