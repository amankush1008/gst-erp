{{-- resources/views/reports/party-ledger.blade.php --}}
@extends('layouts.app')
@section('title','Party Ledger Report')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Party Ledger</h4>
        <p class="text-muted mb-0 small">Receivables & payables by party</p>
    </div>
    <a href="{{ route('reports.export', 'party-ledger') }}?{{ http_build_query(request()->all()) }}"
       class="btn btn-outline-success btn-sm">
        <i class="fas fa-file-excel me-1"></i>Export
    </a>
</div>

<div class="card-dark mb-4">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label text-muted small">Party</label>
            <select name="party_id" class="form-select form-control-dark select2" required>
                <option value="">Select Party</option>
                @foreach($parties as $party)
                    <option value="{{ $party->id }}" @selected(request('party_id') == $party->id)>
                        {{ $party->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label text-muted small">From</label>
            <input type="date" name="date_from" class="form-control form-control-dark"
                   value="{{ request('date_from') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label text-muted small">To</label>
            <input type="date" name="date_to" class="form-control form-control-dark"
                   value="{{ request('date_to') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">&nbsp;</label>
            <button class="btn btn-primary btn-sm d-block w-100">Generate</button>
        </div>
    </form>
</div>

@if(isset($party))
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-label">Total Invoiced (Dr)</div>
            <div class="stat-value text-danger">₹{{ number_format($summary['total_invoiced'], 2) }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-label">Total Received (Cr)</div>
            <div class="stat-value text-success">₹{{ number_format($summary['total_paid'], 2) }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-label">Net Balance</div>
            <div class="stat-value {{ $summary['balance'] > 0 ? 'text-danger' : 'text-success' }}">
                ₹{{ number_format(abs($summary['balance']), 2) }}
                <small class="fs-6 fw-normal text-muted">
                    {{ $summary['balance'] > 0 ? 'Receivable' : 'Advance' }}
                </small>
            </div>
        </div>
    </div>
</div>

<div class="card-dark">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h6 class="fw-semibold mb-0">{{ $party->name }}</h6>
            @if($party->gstin)
                <small class="text-muted font-monospace">GSTIN: {{ $party->gstin }}</small>
            @endif
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0">
            <thead>
                <tr class="text-muted small">
                    <th>Date</th>
                    <th>Type</th>
                    <th>Reference</th>
                    <th class="text-end">Debit (Dr)</th>
                    <th class="text-end">Credit (Cr)</th>
                    <th class="text-end">Balance</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @php $runningBalance = 0; @endphp
                @forelse($ledger as $entry)
                @php
                    if ($entry['type'] === 'invoice') $runningBalance += $entry['amount'];
                    else $runningBalance -= $entry['amount'];
                @endphp
                <tr>
                    <td class="text-muted small">{{ \Carbon\Carbon::parse($entry['date'])->format('d M Y') }}</td>
                    <td>
                        <span class="badge {{ $entry['type'] === 'invoice' ? 'bg-primary' : 'bg-success' }}">
                            {{ ucfirst($entry['type']) }}
                        </span>
                    </td>
                    <td>
                        @if($entry['type'] === 'invoice')
                            <a href="{{ route('invoices.show', $entry['id']) }}"
                               class="text-primary font-monospace small">
                                {{ $entry['reference'] }}
                            </a>
                        @else
                            <span class="font-monospace small text-muted">{{ $entry['reference'] }}</span>
                        @endif
                    </td>
                    <td class="text-end">
                        @if($entry['type'] === 'invoice')
                            <span class="text-danger fw-semibold">₹{{ number_format($entry['amount'], 2) }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-end">
                        @if($entry['type'] === 'payment')
                            <span class="text-success fw-semibold">₹{{ number_format($entry['amount'], 2) }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-end fw-semibold {{ $runningBalance > 0 ? 'text-warning' : 'text-muted' }}">
                        ₹{{ number_format($runningBalance, 2) }}
                    </td>
                    <td>
                        @if(isset($entry['status']))
                            @php $c = ['paid'=>'success','partial'=>'warning','unpaid'=>'danger'][$entry['status']] ?? 'secondary'; @endphp
                            <span class="badge bg-{{ $c }}">{{ ucfirst($entry['status']) }}</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No transactions found</td></tr>
                @endforelse
            </tbody>
            @if(count($ledger ?? []))
            <tfoot class="border-top border-secondary fw-bold small">
                <tr>
                    <td colspan="3" class="text-end text-muted">Closing Balance</td>
                    <td class="text-end text-danger">₹{{ number_format($summary['total_invoiced'], 2) }}</td>
                    <td class="text-end text-success">₹{{ number_format($summary['total_paid'], 2) }}</td>
                    <td class="text-end {{ $summary['balance'] > 0 ? 'text-danger' : 'text-success' }}">
                        ₹{{ number_format(abs($summary['balance']), 2) }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@else
<div class="card-dark text-center py-5 text-muted">
    <i class="fas fa-book-open fa-3x mb-3 opacity-25"></i>
    <p>Select a party above to view their ledger</p>
</div>
@endif
@endsection
