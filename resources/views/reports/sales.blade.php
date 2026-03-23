{{-- resources/views/reports/sales.blade.php --}}
@extends('layouts.app')
@section('title','Sales Report')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Sales Report</h4>
        <p class="text-muted mb-0 small">Detailed sales analysis</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.export', ['type' => 'sales']) }}?{{ http_build_query(request()->all()) }}"
           class="btn btn-outline-success btn-sm">
            <i class="fas fa-file-excel me-1"></i>Export Excel
        </a>
    </div>
</div>

{{-- Filters --}}
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
        <div class="col-md-2">
            <label class="form-label text-muted small">Party</label>
            <select name="party_id" class="form-select form-control-dark">
                <option value="">All Parties</option>
                @foreach($parties ?? [] as $party)
                    <option value="{{ $party->id }}" @selected(request('party_id') == $party->id)>{{ $party->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label text-muted small">Group By</label>
            <select name="group_by" class="form-select form-control-dark">
                <option value="invoice" @selected(request('group_by','invoice')=='invoice')>Invoice</option>
                <option value="party"   @selected(request('group_by')=='party')>Party</option>
                <option value="product" @selected(request('group_by')=='product')>Product</option>
                <option value="month"   @selected(request('group_by')=='month')>Month</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label text-muted small">&nbsp;</label>
            <button class="btn btn-primary btn-sm d-block w-100">Generate</button>
        </div>
    </form>
</div>

{{-- Summary --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-label">Total Sales</div>
            <div class="stat-value text-success">₹{{ number_format($summary['total_sales'] ?? 0, 2) }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-label">Tax Collected</div>
            <div class="stat-value">₹{{ number_format($summary['total_tax'] ?? 0, 2) }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-label">Collected</div>
            <div class="stat-value text-success">₹{{ number_format($summary['collected'] ?? 0, 2) }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-label">Outstanding</div>
            <div class="stat-value text-danger">₹{{ number_format($summary['outstanding'] ?? 0, 2) }}</div>
        </div>
    </div>
</div>

{{-- Data Table --}}
<div class="card-dark">
    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0">
            <thead>
                <tr class="text-muted small">
                    <th>Date</th>
                    <th>Invoice #</th>
                    <th>Party</th>
                    <th>Taxable</th>
                    <th>CGST</th>
                    <th>SGST</th>
                    <th>IGST</th>
                    <th>Total</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices ?? [] as $invoice)
                <tr>
                    <td class="small text-muted">{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}</td>
                    <td class="font-monospace fw-semibold">{{ $invoice->invoice_number }}</td>
                    <td>{{ $invoice->party->name ?? '-' }}</td>
                    <td>₹{{ number_format($invoice->taxable_amount ?? 0, 2) }}</td>
                    <td>₹{{ number_format($invoice->cgst_amount ?? 0, 2) }}</td>
                    <td>₹{{ number_format($invoice->sgst_amount ?? 0, 2) }}</td>
                    <td>₹{{ number_format($invoice->igst_amount ?? 0, 2) }}</td>
                    <td class="fw-semibold">₹{{ number_format($invoice->total_amount, 2) }}</td>
                    <td>
                        @php $colors = ['paid'=>'success','partial'=>'warning','unpaid'=>'danger']; @endphp
                        <span class="badge bg-{{ $colors[$invoice->payment_status] ?? 'secondary' }}">
                            {{ ucfirst($invoice->payment_status) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-muted py-4">No data for the selected period</td></tr>
                @endforelse
            </tbody>
            @if(isset($invoices) && $invoices->count())
            <tfoot class="text-muted small fw-semibold border-top border-secondary">
                <tr>
                    <td colspan="3" class="text-end">Totals:</td>
                    <td>₹{{ number_format($summary['taxable'] ?? 0, 2) }}</td>
                    <td>₹{{ number_format($summary['cgst'] ?? 0, 2) }}</td>
                    <td>₹{{ number_format($summary['sgst'] ?? 0, 2) }}</td>
                    <td>₹{{ number_format($summary['igst'] ?? 0, 2) }}</td>
                    <td>₹{{ number_format($summary['total_sales'] ?? 0, 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
    @if(isset($invoices))
    <div class="p-3">{{ $invoices->links() }}</div>
    @endif
</div>
@endsection
