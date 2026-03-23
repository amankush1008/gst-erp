{{-- resources/views/purchases/index.blade.php --}}
@extends('layouts.app')
@section('title','Purchases')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Purchases</h4>
        <p class="text-muted mb-0 small">Manage purchase bills & expenses</p>
    </div>
    <a href="{{ route('purchases.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i>Add Purchase
    </a>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(239,68,68,.15);color:#FCA5A5"><i class="fas fa-shopping-cart"></i></div>
            <div class="stat-value">₹{{ number_format($stats['total_purchases'] ?? 0) }}</div>
            <div class="stat-label">Total Purchases</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(245,158,11,.15);color:#FCD34D"><i class="fas fa-percent"></i></div>
            <div class="stat-value">₹{{ number_format($stats['total_tax'] ?? 0) }}</div>
            <div class="stat-label">Input Tax (ITC)</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(239,68,68,.15);color:#FCA5A5"><i class="fas fa-clock"></i></div>
            <div class="stat-value">₹{{ number_format($stats['pending'] ?? 0) }}</div>
            <div class="stat-label">Pending Payable</div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card-dark mb-3">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-3">
            <input type="text" name="search" class="form-control form-control-dark"
                   placeholder="Search invoice #..." value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
            <select name="party_id" class="form-select form-control-dark">
                <option value="">All Suppliers</option>
                @foreach($parties as $p)
                    <option value="{{ $p->id }}" @selected(request('party_id') == $p->id)>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <input type="date" name="date_from" class="form-control form-control-dark" value="{{ request('date_from') }}">
        </div>
        <div class="col-md-2">
            <input type="date" name="date_to" class="form-control form-control-dark" value="{{ request('date_to') }}">
        </div>
        <div class="col-md-1">
            <button class="btn btn-primary btn-sm w-100">Go</button>
        </div>
        <div class="col-md-1">
            <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary btn-sm w-100">×</a>
        </div>
    </form>
</div>

<div class="card-dark">
    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0">
            <thead>
                <tr class="text-muted small">
                    <th>Invoice #</th><th>Supplier</th><th>Date</th>
                    <th>Total</th><th>Tax</th><th>Balance</th><th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchases as $purchase)
                <tr>
                    <td class="fw-semibold font-monospace">{{ $purchase->invoice_number }}</td>
                    <td>{{ $purchase->party->name ?? '-' }}</td>
                    <td class="text-muted small">{{ \Carbon\Carbon::parse($purchase->invoice_date)->format('d M Y') }}</td>
                    <td class="fw-semibold">₹{{ number_format($purchase->total_amount, 2) }}</td>
                    <td class="text-muted">₹{{ number_format($purchase->tax_amount, 2) }}</td>
                    <td class="{{ $purchase->balance_amount > 0 ? 'text-warning' : 'text-muted' }}">
                        ₹{{ number_format($purchase->balance_amount, 2) }}
                    </td>
                    <td>
                        @php $c = ['paid'=>'success','partial'=>'warning','unpaid'=>'danger'][$purchase->payment_status] ?? 'secondary'; @endphp
                        <span class="badge bg-{{ $c }}">{{ ucfirst($purchase->payment_status) }}</span>
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('purchases.show', $purchase) }}" class="btn btn-outline-secondary"><i class="fas fa-eye"></i></a>
                            <form method="POST" action="{{ route('purchases.destroy', $purchase) }}" class="d-inline"
                                  onsubmit="return confirm('Delete this purchase?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No purchases found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $purchases->links() }}</div>
</div>
@endsection
