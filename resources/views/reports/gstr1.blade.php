{{-- resources/views/reports/gstr1.blade.php --}}
@extends('layouts.app')
@section('title','GSTR-1 Report')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">GSTR-1 Report</h4>
        <p class="text-muted mb-0 small">Outward supplies summary for GST filing</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.export', 'gstr1') }}?{{ http_build_query(request()->all()) }}"
           class="btn btn-outline-success btn-sm">
            <i class="fas fa-file-excel me-1"></i>Export
        </a>
    </div>
</div>

<div class="card-dark mb-4">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label text-muted small">Financial Year</label>
            <select name="year" class="form-select form-control-dark">
                <option value="{{ date('Y') }}-{{ date('Y')+1 }}">{{ date('Y') }}-{{ date('Y')+1 }}</option>
                <option value="{{ date('Y')-1 }}-{{ date('Y') }}">{{ date('Y')-1 }}-{{ date('Y') }}</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label text-muted small">Return Period</label>
            <select name="month" class="form-select form-control-dark">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" @selected(request('month', now()->month) == $m)>
                        {{ date('F', mktime(0,0,0,$m,1)) }}
                    </option>
                @endfor
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">&nbsp;</label>
            <button class="btn btn-primary btn-sm d-block w-100">Generate</button>
        </div>
    </form>
</div>

{{-- B2B Section --}}
@if(!empty($report['b2b']))
<div class="card-dark mb-4">
    <h6 class="fw-semibold mb-3">4A - B2B Supplies (Registered)</h6>
    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0 small">
            <thead>
                <tr class="text-muted">
                    <th>GSTIN</th><th>Party</th><th>Invoice #</th><th>Date</th>
                    <th>Taxable</th><th>Tax Rate</th><th>IGST</th><th>CGST</th><th>SGST</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report['b2b'] as $row)
                <tr>
                    <td class="font-monospace">{{ $row['gstin'] }}</td>
                    <td>{{ $row['party'] }}</td>
                    <td>{{ $row['invoice_number'] }}</td>
                    <td>{{ $row['date'] }}</td>
                    <td>₹{{ number_format($row['taxable'], 2) }}</td>
                    <td>{{ $row['tax_rate'] }}%</td>
                    <td>₹{{ number_format($row['igst'] ?? 0, 2) }}</td>
                    <td>₹{{ number_format($row['cgst'] ?? 0, 2) }}</td>
                    <td>₹{{ number_format($row['sgst'] ?? 0, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- B2C Large --}}
@if(!empty($report['b2cl']))
<div class="card-dark mb-4">
    <h6 class="fw-semibold mb-3">5A - B2C Large (>₹2.5L, Unregistered)</h6>
    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0 small">
            <thead>
                <tr class="text-muted">
                    <th>Invoice #</th><th>Date</th><th>State</th>
                    <th>Taxable</th><th>Tax Rate</th><th>IGST</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report['b2cl'] as $row)
                <tr>
                    <td>{{ $row['invoice_number'] }}</td>
                    <td>{{ $row['date'] }}</td>
                    <td>{{ $row['state'] }}</td>
                    <td>₹{{ number_format($row['taxable'], 2) }}</td>
                    <td>{{ $row['tax_rate'] }}%</td>
                    <td>₹{{ number_format($row['igst'] ?? 0, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Summary --}}
<div class="card-dark">
    <h6 class="fw-semibold mb-3">Tax Summary</h6>
    <div class="row g-3">
        <div class="col-md-4">
            <div class="p-3 rounded" style="background:#0F172A;border:1px solid #334155">
                <div class="text-muted small">Total Taxable Amount</div>
                <div class="fw-bold fs-5 mt-1">₹{{ number_format($report['summary']['total_taxable'] ?? 0, 2) }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 rounded" style="background:#0F172A;border:1px solid #334155">
                <div class="text-muted small">Total Tax (GST)</div>
                <div class="fw-bold fs-5 mt-1 text-warning">₹{{ number_format($report['summary']['total_tax'] ?? 0, 2) }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 rounded" style="background:#0F172A;border:1px solid #334155">
                <div class="text-muted small">Total Invoice Value</div>
                <div class="fw-bold fs-5 mt-1 text-success">₹{{ number_format($report['summary']['total_value'] ?? 0, 2) }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
