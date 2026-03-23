{{-- resources/views/invoices/show.blade.php --}}
@extends('layouts.app')
@section('title','Invoice #' . $invoice->invoice_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">{{ $invoice->invoice_number }}</h4>
        <p class="text-muted mb-0 small">
            {{ str_replace('_',' ', ucwords($invoice->invoice_type)) }} •
            {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('invoices.pdf', $invoice) }}" class="btn btn-outline-info btn-sm" target="_blank">
            <i class="fas fa-file-pdf me-1"></i>PDF
        </a>
        @if($invoice->payment_status !== 'paid')
        <button class="btn btn-success btn-sm"
                onclick="paymentModal({{ $invoice->id }}, '{{ $invoice->invoice_number }}', {{ $invoice->balance_amount }})">
            <i class="fas fa-money-bill-wave me-1"></i>Record Payment
        </button>
        @endif
        <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-edit me-1"></i>Edit
        </a>
        <form method="POST" action="{{ route('invoices.duplicate', $invoice) }}" class="d-inline">
            @csrf
            <button class="btn btn-outline-secondary btn-sm"><i class="fas fa-copy me-1"></i>Duplicate</button>
        </form>
        <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        {{-- Invoice Preview --}}
        <div class="card-dark mb-4">
            {{-- Header --}}
            <div class="row mb-4">
                <div class="col-md-6">
                    <h5 class="fw-bold mb-1">{{ currentBusiness()->name }}</h5>
                    <p class="text-muted small mb-1">{{ currentBusiness()->address }}</p>
                    <p class="text-muted small mb-1">GSTIN: {{ currentBusiness()->gstin ?? 'N/A' }}</p>
                    <p class="text-muted small">{{ currentBusiness()->mobile }}</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="fw-bold fs-5 text-primary mb-1">{{ strtoupper(str_replace('_',' ',$invoice->invoice_type)) }}</div>
                    <div class="font-monospace fw-semibold"># {{ $invoice->invoice_number }}</div>
                    <div class="text-muted small mt-1">Date: {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}</div>
                    @if($invoice->due_date)
                    <div class="text-muted small">Due: {{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}</div>
                    @endif
                </div>
            </div>

            {{-- Bill To --}}
            <div class="row mb-4">
                <div class="col-md-6">
                    <p class="text-muted small text-uppercase fw-semibold mb-1">Bill To</p>
                    <p class="fw-bold mb-1">{{ $invoice->party->name ?? '-' }}</p>
                    <p class="text-muted small mb-1">{{ $invoice->party->billing_address ?? '' }}</p>
                    @if($invoice->party->gstin)
                    <p class="text-muted small font-monospace mb-0">GSTIN: {{ $invoice->party->gstin }}</p>
                    @endif
                </div>
                @if($invoice->eway_bill_number)
                <div class="col-md-6 text-md-end">
                    <p class="text-muted small text-uppercase fw-semibold mb-1">E-Way Bill</p>
                    <p class="fw-semibold font-monospace mb-0">{{ $invoice->eway_bill_number }}</p>
                </div>
                @endif
            </div>

            {{-- Items Table --}}
            <div class="table-responsive mb-4">
                <table class="table table-dark table-sm mb-0">
                    <thead>
                        <tr class="text-muted small border-secondary">
                            <th>#</th><th>Item</th><th>HSN</th>
                            <th class="text-end">Qty</th>
                            <th class="text-end">Rate</th>
                            <th class="text-end">Taxable</th>
                            <th class="text-end">GST</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $i => $item)
                        <tr>
                            <td class="text-muted">{{ $i+1 }}</td>
                            <td>
                                <div class="fw-semibold">{{ $item->product->name ?? $item->product_name }}</div>
                                @if($item->description)
                                    <small class="text-muted">{{ $item->description }}</small>
                                @endif
                            </td>
                            <td class="small text-muted">{{ $item->hsn_code ?? $item->product->hsn_code ?? '-' }}</td>
                            <td class="text-end">{{ $item->qty }}</td>
                            <td class="text-end">₹{{ number_format($item->rate, 2) }}</td>
                            <td class="text-end">₹{{ number_format($item->taxable_amount, 2) }}</td>
                            <td class="text-end text-muted small">{{ $item->gst_rate }}%<br>₹{{ number_format($item->tax_amount, 2) }}</td>
                            <td class="text-end fw-semibold">₹{{ number_format($item->total_amount, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-top border-secondary">
                        <tr>
                            <td colspan="5" class="text-end text-muted small">Subtotal</td>
                            <td class="text-end">₹{{ number_format($invoice->taxable_amount, 2) }}</td>
                            <td class="text-end text-warning">₹{{ number_format($invoice->tax_amount, 2) }}</td>
                            <td class="text-end">₹{{ number_format($invoice->subtotal, 2) }}</td>
                        </tr>
                        @if($invoice->round_off != 0)
                        <tr>
                            <td colspan="7" class="text-end text-muted small">Round Off</td>
                            <td class="text-end">{{ $invoice->round_off > 0 ? '+' : '' }}₹{{ number_format($invoice->round_off, 2) }}</td>
                        </tr>
                        @endif
                        <tr class="fw-bold">
                            <td colspan="7" class="text-end">Total</td>
                            <td class="text-end text-success fs-5">₹{{ number_format($invoice->total_amount, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Amount in Words --}}
            <p class="text-muted small mb-0">
                <strong>Amount in words:</strong> {{ $invoice->amountInWords() }}
            </p>
        </div>

        {{-- Payment History --}}
        @if($invoice->payments->count())
        <div class="card-dark">
            <h6 class="fw-semibold mb-3 text-muted small text-uppercase">Payment History</h6>
            <div class="table-responsive">
                <table class="table table-dark table-sm mb-0">
                    <thead>
                        <tr class="text-muted small">
                            <th>Date</th><th>Amount</th><th>Method</th><th>Reference</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->payments as $payment)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</td>
                            <td class="fw-semibold text-success">₹{{ number_format($payment->amount, 2) }}</td>
                            <td>{{ ucfirst($payment->payment_method) }}</td>
                            <td class="text-muted small">{{ $payment->reference ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    {{-- Right Sidebar --}}
    <div class="col-lg-4">
        <div class="card-dark mb-4">
            <h6 class="fw-semibold mb-3 text-muted small text-uppercase">Summary</h6>
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Invoice Total</span>
                <span class="fw-bold">₹{{ number_format($invoice->total_amount, 2) }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Paid</span>
                <span class="text-success fw-bold">₹{{ number_format($invoice->paid_amount, 2) }}</span>
            </div>
            <div class="d-flex justify-content-between mb-3 border-top border-secondary pt-2 mt-2">
                <span class="text-muted fw-semibold">Balance Due</span>
                <span class="fw-bold {{ $invoice->balance_amount > 0 ? 'text-danger' : 'text-success' }} fs-5">
                    ₹{{ number_format($invoice->balance_amount, 2) }}
                </span>
            </div>
            @php $c = ['paid'=>'success','partial'=>'warning','unpaid'=>'danger','draft'=>'secondary'][$invoice->payment_status] ?? 'secondary'; @endphp
            <span class="badge bg-{{ $c }} w-100 py-2">{{ ucfirst($invoice->payment_status) }}</span>
        </div>

        <div class="card-dark mb-4">
            <h6 class="fw-semibold mb-3 text-muted small text-uppercase">Tax Breakdown</h6>
            @php $taxSummary = $invoice->taxSummary(); @endphp
            @foreach($taxSummary as $row)
            <div class="d-flex justify-content-between mb-2 small">
                <span class="text-muted">{{ $row['rate'] }}% GST</span>
                <span>CGST: ₹{{ number_format($row['cgst'],2) }} | SGST: ₹{{ number_format($row['sgst'],2) }}</span>
            </div>
            @endforeach
            @if($invoice->igst_amount > 0)
            <div class="d-flex justify-content-between small border-top border-secondary pt-2 mt-2">
                <span class="text-muted">IGST</span>
                <span>₹{{ number_format($invoice->igst_amount, 2) }}</span>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Payment Modal --}}
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:#1E293B;border:1px solid #334155">
            <div class="modal-header border-secondary">
                <h5 class="modal-title">Record Payment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="paymentForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="text-muted small" id="paymentInfo"></p>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label text-muted small">Amount (₹)</label>
                            <input type="number" name="amount" id="payAmt" class="form-control form-control-dark" min="0.01" step="0.01" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted small">Date</label>
                            <input type="date" name="payment_date" class="form-control form-control-dark" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted small">Method</label>
                            <select name="payment_method" class="form-select form-control-dark">
                                <option value="cash">Cash</option>
                                <option value="bank">Bank</option>
                                <option value="upi">UPI</option>
                                <option value="cheque">Cheque</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted small">Reference</label>
                            <input type="text" name="reference" class="form-control form-control-dark">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Record</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function paymentModal(id, number, balance) {
    document.getElementById('paymentInfo').textContent = `Invoice: ${number} | Balance: ₹${parseFloat(balance).toFixed(2)}`;
    document.getElementById('payAmt').value = parseFloat(balance).toFixed(2);
    document.getElementById('paymentForm').action = `/invoices/${id}/payment`;
    new bootstrap.Modal(document.getElementById('paymentModal')).show();
}
</script>
@endpush
