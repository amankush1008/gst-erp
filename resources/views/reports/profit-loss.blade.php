{{-- resources/views/reports/profit-loss.blade.php --}}
@extends('layouts.app')
@section('title','Profit & Loss')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Profit & Loss Statement</h4>
        <p class="text-muted mb-0 small">Business performance overview</p>
    </div>
    <a href="{{ route('reports.export', 'profit-loss') }}?{{ http_build_query(request()->all()) }}"
       class="btn btn-outline-success btn-sm">
        <i class="fas fa-file-excel me-1"></i>Export
    </a>
</div>

<div class="card-dark mb-4">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-2">
            <label class="form-label text-muted small">From</label>
            <input type="date" name="date_from" class="form-control form-control-dark"
                   value="{{ request('date_from', now()->startOfYear()->format('Y-m-d')) }}">
        </div>
        <div class="col-md-2">
            <label class="form-label text-muted small">To</label>
            <input type="date" name="date_to" class="form-control form-control-dark"
                   value="{{ request('date_to', now()->format('Y-m-d')) }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">&nbsp;</label>
            <button class="btn btn-primary btn-sm d-block w-100">Generate</button>
        </div>
    </form>
</div>

<div class="row g-4">
    {{-- Income --}}
    <div class="col-md-6">
        <div class="card-dark h-100">
            <h6 class="fw-semibold mb-4 text-success">
                <i class="fas fa-arrow-trend-up me-2"></i>Income
            </h6>
            <table class="table table-dark table-sm mb-0">
                <tbody>
                    <tr>
                        <td class="text-muted">Sales Revenue</td>
                        <td class="text-end fw-semibold">₹{{ number_format($report['sales'] ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Other Income</td>
                        <td class="text-end fw-semibold">₹{{ number_format($report['other_income'] ?? 0, 2) }}</td>
                    </tr>
                    <tr class="border-top border-secondary fw-bold">
                        <td class="text-success">Total Income</td>
                        <td class="text-end text-success fs-5">₹{{ number_format($report['total_income'] ?? 0, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Expenses --}}
    <div class="col-md-6">
        <div class="card-dark h-100">
            <h6 class="fw-semibold mb-4 text-danger">
                <i class="fas fa-arrow-trend-down me-2"></i>Expenses
            </h6>
            <table class="table table-dark table-sm mb-0">
                <tbody>
                    <tr>
                        <td class="text-muted">Cost of Goods Sold</td>
                        <td class="text-end fw-semibold">₹{{ number_format($report['cogs'] ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Purchase Cost</td>
                        <td class="text-end fw-semibold">₹{{ number_format($report['purchases'] ?? 0, 2) }}</td>
                    </tr>
                    @foreach($report['expense_categories'] ?? [] as $cat)
                    <tr>
                        <td class="text-muted ps-3 small">{{ $cat['name'] }}</td>
                        <td class="text-end small">₹{{ number_format($cat['amount'], 2) }}</td>
                    </tr>
                    @endforeach
                    <tr>
                        <td class="text-muted">Total Operating Expenses</td>
                        <td class="text-end fw-semibold">₹{{ number_format($report['total_expenses'] ?? 0, 2) }}</td>
                    </tr>
                    <tr class="border-top border-secondary fw-bold">
                        <td class="text-danger">Total Outflow</td>
                        <td class="text-end text-danger fs-5">₹{{ number_format($report['total_outflow'] ?? 0, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Bottom Line --}}
<div class="card-dark mt-4">
    @php
        $profit = ($report['total_income'] ?? 0) - ($report['total_outflow'] ?? 0);
        $margin = ($report['total_income'] ?? 0) > 0
            ? round($profit / ($report['total_income']) * 100, 2) : 0;
    @endphp
    <div class="row text-center">
        <div class="col-md-4">
            <div class="p-3 rounded" style="background:#0F172A">
                <div class="text-muted small">Gross Profit</div>
                <div class="fw-bold fs-4 {{ ($report['gross_profit'] ?? 0) > 0 ? 'text-success' : 'text-danger' }}">
                    ₹{{ number_format($report['gross_profit'] ?? 0, 2) }}
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 rounded" style="background:#0F172A">
                <div class="text-muted small">Net Profit / (Loss)</div>
                <div class="fw-bold fs-3 {{ $profit > 0 ? 'text-success' : 'text-danger' }}">
                    ₹{{ number_format(abs($profit), 2) }}
                    <small class="fs-6">{{ $profit >= 0 ? 'Profit' : 'Loss' }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 rounded" style="background:#0F172A">
                <div class="text-muted small">Net Profit Margin</div>
                <div class="fw-bold fs-4 {{ $margin > 0 ? 'text-success' : 'text-danger' }}">
                    {{ $margin }}%
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
