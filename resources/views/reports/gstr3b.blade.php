{{-- resources/views/reports/gstr3b.blade.php --}}
@extends('layouts.app')
@section('title','GSTR-3B')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">GSTR-3B Summary</h4>
        <p class="text-muted mb-0 small">Monthly tax liability & input tax credit summary</p>
    </div>
</div>

<div class="card-dark mb-4">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label text-muted small">Month</label>
            <select name="month" class="form-select form-control-dark">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" @selected(request('month', now()->month) == $m)>
                        {{ date('F', mktime(0,0,0,$m,1)) }}
                    </option>
                @endfor
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label text-muted small">Year</label>
            <select name="year" class="form-select form-control-dark">
                @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                    <option value="{{ $y }}" @selected(request('year', date('Y')) == $y)>{{ $y }}</option>
                @endfor
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">&nbsp;</label>
            <button class="btn btn-primary btn-sm d-block w-100">Generate</button>
        </div>
    </form>
</div>

@if(isset($report))
{{-- 3.1 Outward Supplies --}}
<div class="card-dark mb-4">
    <h6 class="fw-semibold mb-3">3.1 – Details of Outward Supplies</h6>
    <div class="table-responsive">
        <table class="table table-dark table-sm mb-0">
            <thead>
                <tr class="text-muted small">
                    <th>Nature of Supplies</th>
                    <th class="text-end">Total Taxable Value</th>
                    <th class="text-end">Integrated Tax</th>
                    <th class="text-end">Central Tax</th>
                    <th class="text-end">State/UT Tax</th>
                    <th class="text-end">Cess</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report['outward'] as $row)
                <tr>
                    <td>{{ $row['label'] }}</td>
                    <td class="text-end">₹{{ number_format($row['taxable'], 2) }}</td>
                    <td class="text-end">₹{{ number_format($row['igst'], 2) }}</td>
                    <td class="text-end">₹{{ number_format($row['cgst'], 2) }}</td>
                    <td class="text-end">₹{{ number_format($row['sgst'], 2) }}</td>
                    <td class="text-end">₹{{ number_format($row['cess'] ?? 0, 2) }}</td>
                </tr>
                @endforeach
                <tr class="fw-bold border-top border-secondary">
                    <td>Total (A)</td>
                    <td class="text-end">₹{{ number_format($report['outward_total']['taxable'], 2) }}</td>
                    <td class="text-end text-warning">₹{{ number_format($report['outward_total']['igst'], 2) }}</td>
                    <td class="text-end text-warning">₹{{ number_format($report['outward_total']['cgst'], 2) }}</td>
                    <td class="text-end text-warning">₹{{ number_format($report['outward_total']['sgst'], 2) }}</td>
                    <td class="text-end">₹0.00</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

{{-- 4. ITC --}}
<div class="card-dark mb-4">
    <h6 class="fw-semibold mb-3">4 – Eligible ITC (Input Tax Credit)</h6>
    <div class="table-responsive">
        <table class="table table-dark table-sm mb-0">
            <thead>
                <tr class="text-muted small">
                    <th>Details</th>
                    <th class="text-end">Integrated Tax</th>
                    <th class="text-end">Central Tax</th>
                    <th class="text-end">State/UT Tax</th>
                    <th class="text-end">Cess</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>ITC Available (Purchases)</td>
                    <td class="text-end text-success">₹{{ number_format($report['itc']['igst'], 2) }}</td>
                    <td class="text-end text-success">₹{{ number_format($report['itc']['cgst'], 2) }}</td>
                    <td class="text-end text-success">₹{{ number_format($report['itc']['sgst'], 2) }}</td>
                    <td class="text-end">₹0.00</td>
                </tr>
                <tr class="fw-bold border-top border-secondary">
                    <td>Net ITC (B)</td>
                    <td class="text-end text-success">₹{{ number_format($report['itc']['igst'], 2) }}</td>
                    <td class="text-end text-success">₹{{ number_format($report['itc']['cgst'], 2) }}</td>
                    <td class="text-end text-success">₹{{ number_format($report['itc']['sgst'], 2) }}</td>
                    <td class="text-end">₹0.00</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

{{-- Tax Payable --}}
<div class="card-dark">
    <h6 class="fw-semibold mb-3">5 – Net Tax Payable (A – B)</h6>
    <div class="row g-3">
        <div class="col-md-4">
            <div class="p-3 rounded" style="background:#0F172A;border:1px solid #334155">
                <div class="text-muted small">IGST Payable</div>
                <div class="fw-bold fs-5 mt-1 text-warning">
                    ₹{{ number_format(max(0, $report['outward_total']['igst'] - $report['itc']['igst']), 2) }}
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 rounded" style="background:#0F172A;border:1px solid #334155">
                <div class="text-muted small">CGST Payable</div>
                <div class="fw-bold fs-5 mt-1 text-warning">
                    ₹{{ number_format(max(0, $report['outward_total']['cgst'] - $report['itc']['cgst']), 2) }}
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 rounded" style="background:#0F172A;border:1px solid #334155">
                <div class="text-muted small">SGST Payable</div>
                <div class="fw-bold fs-5 mt-1 text-warning">
                    ₹{{ number_format(max(0, $report['outward_total']['sgst'] - $report['itc']['sgst']), 2) }}
                </div>
            </div>
        </div>
    </div>
    @php
        $totalPayable = max(0, $report['outward_total']['igst'] - $report['itc']['igst'])
                      + max(0, $report['outward_total']['cgst'] - $report['itc']['cgst'])
                      + max(0, $report['outward_total']['sgst'] - $report['itc']['sgst']);
    @endphp
    <div class="mt-3 p-3 rounded" style="background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.3)">
        <div class="d-flex justify-content-between align-items-center">
            <span class="text-warning fw-semibold">Total Tax Payable for {{ date('F', mktime(0,0,0, request('month', now()->month), 1)) }}</span>
            <span class="fw-bold fs-4 text-warning">₹{{ number_format($totalPayable, 2) }}</span>
        </div>
    </div>
</div>
@endif
@endsection
