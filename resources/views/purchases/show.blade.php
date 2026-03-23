{{-- resources/views/purchases/show.blade.php --}}
@extends('layouts.app')
@section('title', 'Purchase #' . $purchase->invoice_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Purchase Bill</h4>
        <p class="text-muted mb-0 small"># {{ $purchase->invoice_number }} &bull;
            {{ \Carbon\Carbon::parse($purchase->invoice_date)->format('d M Y') }}</p>
    </div>
    <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Back
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card-dark mb-4">
            <div class="row mb-4">
                <div class="col-md-6">
                    <p class="text-muted small text-uppercase fw-semibold mb-1">Supplier</p>
                    <p class="fw-bold mb-1">{{ $purchase->party->name ?? '-' }}</p>
                    @if($purchase->party->gstin)
                        <p class="text-muted small font-monospace mb-0">GSTIN: {{ $purchase->party->gstin }}</p>
                    @endif
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-muted small mb-1">Invoice Date: <strong>{{ \Carbon\Carbon::parse($purchase->invoice_date)->format('d M Y') }}</strong></p>
                    @if($purchase->due_date)
                    <p class="text-muted small mb-1">Due Date: <strong>{{ \Carbon\Carbon::parse($purchase->due_date)->format('d M Y') }}</strong></p>
                    @endif
                    @php $c = ['paid'=>'success','partial'=>'warning','unpaid'=>'danger'][$purchase->payment_status] ?? 'secondary'; @endphp
                    <span class="badge bg-{{ $c }}">{{ ucfirst($purchase->payment_status) }}</span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-dark table-sm mb-0">
                    <thead>
                        <tr class="text-muted small border-secondary">
                            <th>#</th><th>Item</th><th>HSN</th>
                            <th class="text-end">Qty</th>
                            <th class="text-end">Rate</th>
                            <th class="text-end">Taxable</th>
                            <th class="text-end">Tax</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchase->items as $i => $item)
                        <tr>
                            <td class="text-muted">{{ $i + 1 }}</td>
                            <td class="fw-semibold">{{ $item->product->name ?? $item->product_name }}</td>
                            <td class="small text-muted">{{ $item->hsn_code ?? '-' }}</td>
                            <td class="text-end">{{ $item->qty }}</td>
                            <td class="text-end">₹{{ number_format($item->rate, 2) }}</td>
                            <td class="text-end">₹{{ number_format($item->taxable_amount, 2) }}</td>
                            <td class="text-end text-muted small">{{ $item->gst_rate }}%<br>₹{{ number_format($item->tax_amount, 2) }}</td>
                            <td class="text-end fw-semibold">₹{{ number_format($item->total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-top border-secondary">
                        <tr class="fw-bold">
                            <td colspan="7" class="text-end">Total</td>
                            <td class="text-end text-success fs-5">₹{{ number_format($purchase->total_amount, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card-dark">
            <h6 class="fw-semibold mb-3 text-muted small text-uppercase">Summary</h6>
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Subtotal</span>
                <span>₹{{ number_format($purchase->subtotal, 2) }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Tax (ITC)</span>
                <span class="text-warning">₹{{ number_format($purchase->tax_amount, 2) }}</span>
            </div>
            <div class="d-flex justify-content-between mb-3 border-top border-secondary pt-2 mt-2 fw-bold">
                <span>Total</span>
                <span class="text-success fs-5">₹{{ number_format($purchase->total_amount, 2) }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Paid</span>
                <span class="text-success">₹{{ number_format($purchase->paid_amount, 2) }}</span>
            </div>
            <div class="d-flex justify-content-between fw-bold">
                <span>Balance Due</span>
                <span class="{{ $purchase->balance_amount > 0 ? 'text-danger' : 'text-success' }}">
                    ₹{{ number_format($purchase->balance_amount, 2) }}
                </span>
            </div>
        </div>
    </div>
</div>
@endsection
