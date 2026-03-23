{{-- resources/views/reports/purchases.blade.php --}}
@extends('layouts.app')
@section('title','Purchases Report')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Purchases Report</h4>
        <p class="text-muted mb-0 small">Purchase analysis & ITC summary</p>
    </div>
    <a href="{{ route('reports.export', 'purchases') }}?{{ http_build_query(request()->all()) }}"
       class="btn btn-outline-success btn-sm">
        <i class="fas fa-file-excel me-1"></i>Export
    </a>
</div>

<div class="card-dark mb-4">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-2">
            <label class="form-label text-muted small">From</label>
            <input type="date" name="date_from" class="form-control form-control-dark"
                   value="{{ request('date_from', now()->startOfMonth()->format('Y-m-d')) }}">
        </div>
        <div class="col-md-2">
            <label class="form-label text-muted small">To</label>
            <input type="date" name="date_to" class="form-control form-control-dark"
                   value="{{ request('date_to', now()->format('Y-m-d')) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label text-muted small">Supplier</label>
            <select name="party_id" class="form-select form-control-dark">
                <option value="">All Suppliers</option>
                @foreach($parties ?? [] as $party)
                    <option value="{{ $party->id }}" @selected(request('party_id') == $party->id)>{{ $party->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">&nbsp;</label>
            <button class="btn btn-primary btn-sm d-block w-100">Generate</button>
        </div>
    </form>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-label">Total Purchases</div>
            <div class="stat-value text-danger">₹{{ number_format($summary['total_purchases'] ?? 0, 2) }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-label">Input Tax Credit (ITC)</div>
            <div class="stat-value text-warning">₹{{ number_format($summary['total_tax'] ?? 0, 2) }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-label">Net Taxable Amount</div>
            <div class="stat-value">₹{{ number_format($summary['taxable'] ?? 0, 2) }}</div>
        </div>
    </div>
</div>

<div class="card-dark">
    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0">
            <thead>
                <tr class="text-muted small">
                    <th>Date</th><th>Invoice #</th><th>Supplier</th>
                    <th>Taxable</th><th>CGST</th><th>SGST</th><th>IGST</th>
                    <th>Total</th><th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchases ?? [] as $purchase)
                <tr>
                    <td class="small text-muted">{{ \Carbon\Carbon::parse($purchase->invoice_date)->format('d M Y') }}</td>
                    <td class="font-monospace fw-semibold">{{ $purchase->invoice_number }}</td>
                    <td>{{ $purchase->party->name ?? '-' }}</td>
                    <td>₹{{ number_format($purchase->taxable_amount ?? 0, 2) }}</td>
                    <td>₹{{ number_format($purchase->cgst_amount ?? 0, 2) }}</td>
                    <td>₹{{ number_format($purchase->sgst_amount ?? 0, 2) }}</td>
                    <td>₹{{ number_format($purchase->igst_amount ?? 0, 2) }}</td>
                    <td class="fw-semibold">₹{{ number_format($purchase->total_amount, 2) }}</td>
                    <td>
                        @php $c = ['paid'=>'success','partial'=>'warning','unpaid'=>'danger'][$purchase->payment_status] ?? 'secondary'; @endphp
                        <span class="badge bg-{{ $c }}">{{ ucfirst($purchase->payment_status) }}</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-muted py-4">No data for selected period</td></tr>
                @endforelse
            </tbody>
            @if(!empty($purchases) && count($purchases))
            <tfoot class="border-top border-secondary fw-semibold text-muted small">
                <tr>
                    <td colspan="3" class="text-end">Totals:</td>
                    <td>₹{{ number_format($summary['taxable'] ?? 0, 2) }}</td>
                    <td>₹{{ number_format($summary['cgst'] ?? 0, 2) }}</td>
                    <td>₹{{ number_format($summary['sgst'] ?? 0, 2) }}</td>
                    <td>₹{{ number_format($summary['igst'] ?? 0, 2) }}</td>
                    <td>₹{{ number_format($summary['total_purchases'] ?? 0, 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
    @if(isset($purchases))
    <div class="p-3">{{ $purchases->links() }}</div>
    @endif
</div>
@endsection
