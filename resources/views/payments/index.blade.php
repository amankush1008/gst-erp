{{-- resources/views/payments/index.blade.php --}}
@extends('layouts.app')
@section('title','Payments')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Payments</h4>
        <p class="text-muted mb-0 small">Cash & bank transactions</p>
    </div>
    <a href="{{ route('payments.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i>Record Payment
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(16,185,129,.15);color:#34D399"><i class="fas fa-arrow-down"></i></div>
            <div class="stat-value text-success">₹{{ number_format($stats['received'] ?? 0, 2) }}</div>
            <div class="stat-label">Total Received</div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(239,68,68,.15);color:#FCA5A5"><i class="fas fa-arrow-up"></i></div>
            <div class="stat-value text-danger">₹{{ number_format($stats['paid'] ?? 0, 2) }}</div>
            <div class="stat-label">Total Paid Out</div>
        </div>
    </div>
</div>

<div class="card-dark">
    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0">
            <thead>
                <tr class="text-muted small">
                    <th>Date</th><th>Party</th><th>Invoice</th>
                    <th>Amount</th><th>Method</th><th>Type</th><th>Reference</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                <tr>
                    <td class="text-muted small">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</td>
                    <td>{{ $payment->party->name ?? '-' }}</td>
                    <td class="font-monospace small">{{ $payment->invoice->invoice_number ?? '-' }}</td>
                    <td class="fw-semibold {{ $payment->type === 'received' ? 'text-success' : 'text-danger' }}">
                        {{ $payment->type === 'received' ? '+' : '-' }}₹{{ number_format($payment->amount, 2) }}
                    </td>
                    <td>
                        @php $icons = ['cash'=>'money-bill-wave','bank'=>'university','upi'=>'mobile-alt','cheque'=>'file-alt']; @endphp
                        <i class="fas fa-{{ $icons[$payment->payment_method] ?? 'circle' }} me-1 text-muted"></i>
                        {{ ucfirst($payment->payment_method) }}
                    </td>
                    <td>
                        <span class="badge {{ $payment->type === 'received' ? 'bg-success' : 'bg-danger' }}">
                            {{ ucfirst($payment->type) }}
                        </span>
                    </td>
                    <td class="text-muted small">{{ $payment->reference ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No payments recorded</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $payments->links() }}</div>
</div>
@endsection
