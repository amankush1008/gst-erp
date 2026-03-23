{{-- resources/views/parties/ledger.blade.php --}}
@extends('layouts.app')
@section('title','Party Ledger')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">{{ $party->name }}</h4>
        <p class="text-muted mb-0 small">
            Party Ledger
            @if($party->gstin) • GSTIN: <span class="font-monospace">{{ $party->gstin }}</span> @endif
            @if($party->mobile) • {{ $party->mobile }} @endif
        </p>
    </div>
    <a href="{{ route('parties.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Back
    </a>
</div>

{{-- Balance Card --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-label">Total Invoiced</div>
            <div class="stat-value">₹{{ number_format($summary['total_invoiced'], 2) }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-label">Amount Received</div>
            <div class="stat-value text-success">₹{{ number_format($summary['total_paid'], 2) }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-label">Balance Outstanding</div>
            <div class="stat-value {{ $summary['balance'] > 0 ? 'text-danger' : 'text-success' }}">
                ₹{{ number_format(abs($summary['balance']), 2) }}
                <small class="fs-6 text-muted">{{ $summary['balance'] > 0 ? '(Receivable)' : '(Advance)' }}</small>
            </div>
        </div>
    </div>
</div>

{{-- Ledger Table --}}
<div class="card-dark">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-semibold mb-0 text-muted small text-uppercase">Transaction History</h6>
        <div class="d-flex gap-2">
            <input type="date" id="dateFrom" class="form-control form-control-dark form-control-sm" style="width:auto">
            <input type="date" id="dateTo"   class="form-control form-control-dark form-control-sm" style="width:auto">
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0">
            <thead>
                <tr class="text-muted small">
                    <th>Date</th><th>Type</th><th>Ref #</th>
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
                        <span class="badge" style="background:#1E3A5F;color:#93C5FD">
                            {{ ucfirst($entry['type']) }}
                        </span>
                    </td>
                    <td>
                        @if($entry['type'] === 'invoice')
                            <a href="{{ route('invoices.show', $entry['id']) }}" class="text-primary font-monospace small">
                                {{ $entry['reference'] }}
                            </a>
                        @else
                            <span class="font-monospace small">{{ $entry['reference'] }}</span>
                        @endif
                    </td>
                    <td class="text-end">
                        @if($entry['type'] === 'invoice')
                            <span class="text-danger">₹{{ number_format($entry['amount'], 2) }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="text-end">
                        @if($entry['type'] === 'payment')
                            <span class="text-success">₹{{ number_format($entry['amount'], 2) }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="text-end fw-semibold">₹{{ number_format($runningBalance, 2) }}</td>
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
            @if(count($ledger))
            <tfoot class="border-top border-secondary fw-bold">
                <tr>
                    <td colspan="3" class="text-end text-muted small">Closing Balance</td>
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
@endsection
