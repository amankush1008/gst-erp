@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="row g-3 mb-4">
    {{-- Stat Cards --}}
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#EFF6FF;color:#2563EB">
                <i class="bi bi-graph-up-arrow"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Sales This Month</div>
                <div class="stat-value text-currency">₹{{ number_format($salesThisMonth, 2) }}</div>
                <div class="stat-change text-success">
                    <i class="bi bi-arrow-up-short"></i> vs last month
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#F0FDF4;color:#16A34A">
                <i class="bi bi-cash-coin"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Net Profit</div>
                <div class="stat-value text-currency" style="color:{{ $profit >= 0 ? '#16A34A' : '#DC2626' }}">
                    ₹{{ number_format(abs($profit), 2) }}
                </div>
                <div class="stat-change {{ $profit >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ $profit >= 0 ? 'Profitable' : 'Loss' }} this month
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#FFF7ED;color:#EA580C">
                <i class="bi bi-clock-history"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Outstanding (Receive)</div>
                <div class="stat-value text-currency" style="color:#EA580C">₹{{ number_format($totalReceivable, 2) }}</div>
                <div class="stat-change text-warning">
                    {{ $overdueInvoices }} overdue invoices
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#FFF1F2;color:#E11D48">
                <i class="bi bi-arrow-up-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Outstanding (Pay)</div>
                <div class="stat-value text-currency" style="color:#E11D48">₹{{ number_format($totalPayable, 2) }}</div>
                @if($lowStockCount > 0)
                <div class="stat-change text-danger">
                    <i class="bi bi-exclamation-triangle"></i> {{ $lowStockCount }} low stock items
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    {{-- Sales Chart --}}
    <div class="col-xl-8">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center">
                <span>Sales vs Purchase (Last 12 Months)</span>
                <div class="ms-auto">
                    <small class="text-muted me-3"><span style="display:inline-block;width:12px;height:3px;background:#2563EB;border-radius:2px"></span> Sales</small>
                    <small class="text-muted"><span style="display:inline-block;width:12px;height:3px;background:#E2E8F0;border-radius:2px"></span> Purchase</small>
                </div>
            </div>
            <div class="card-body">
                <canvas id="salesChart" height="120"></canvas>
            </div>
        </div>
    </div>

    {{-- Top Receivables --}}
    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header">
                Top Outstanding Parties
                <a href="{{ route('reports.party-ledger') }}" class="btn btn-sm btn-link ms-auto p-0 text-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($topReceivables as $party)
                    <div class="list-group-item d-flex align-items-center px-4 py-3">
                        <div class="me-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold"
                                 style="width:36px;height:36px;background:#EFF6FF;color:#2563EB;font-size:14px">
                                {{ strtoupper(substr($party->name, 0, 1)) }}
                            </div>
                        </div>
                        <div class="flex-1">
                            <div class="fw-medium" style="font-size:13px">{{ $party->name }}</div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold text-currency" style="font-size:13px;color:#EA580C">
                                ₹{{ number_format($party->outstanding, 0) }}
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4 text-muted">No outstanding payments</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- Recent Invoices --}}
    <div class="col-xl-7">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                Recent Invoices
                <a href="{{ route('invoices.index') }}" class="btn btn-sm btn-outline-primary ms-auto">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Party</th>
                                <th>Date</th>
                                <th class="text-end">Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentInvoices as $invoice)
                            <tr>
                                <td>
                                    <a href="{{ route('invoices.show', $invoice) }}" class="text-primary fw-medium">
                                        {{ $invoice->invoice_number }}
                                    </a>
                                </td>
                                <td>{{ $invoice->party->name }}</td>
                                <td>{{ $invoice->invoice_date->format('d M Y') }}</td>
                                <td class="text-end text-currency fw-semibold">₹{{ number_format($invoice->total_amount, 2) }}</td>
                                <td>
                                    <span class="status-badge badge-{{ $invoice->payment_status }}">
                                        {{ ucfirst($invoice->payment_status) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No invoices yet. <a href="{{ route('invoices.create') }}">Create your first invoice</a></td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Top Products + Quick Links --}}
    <div class="col-xl-5">
        <div class="card mb-3">
            <div class="card-header">Top Products This Month</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topProducts as $product)
                            <tr>
                                <td style="font-size:13px">{{ $product->item_name }}</td>
                                <td class="text-end" style="font-size:13px">{{ number_format($product->total_qty, 2) }}</td>
                                <td class="text-end text-currency" style="font-size:13px">₹{{ number_format($product->total_amount, 0) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center text-muted py-3">No sales data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Quick Stats --}}
        <div class="row g-2">
            <div class="col-6">
                <div class="card p-3 text-center">
                    <div style="font-size:28px;font-weight:700;color:#2563EB">{{ $overdueInvoices }}</div>
                    <div style="font-size:12px;color:#64748B">Overdue Invoices</div>
                </div>
            </div>
            <div class="col-6">
                <div class="card p-3 text-center">
                    <div style="font-size:28px;font-weight:700;color:#DC2626">{{ $lowStockCount }}</div>
                    <div style="font-size:12px;color:#64748B">Low Stock Items</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const salesData = @json($salesChart);
const ctx = document.getElementById('salesChart').getContext('2d');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: salesData.map(d => d.month),
        datasets: [
            {
                label: 'Sales',
                data: salesData.map(d => d.sales),
                backgroundColor: 'rgba(37, 99, 235, 0.85)',
                borderRadius: 6,
                borderSkipped: false,
            },
            {
                label: 'Purchase',
                data: salesData.map(d => d.purchase),
                backgroundColor: 'rgba(226, 232, 240, 0.8)',
                borderRadius: 6,
                borderSkipped: false,
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => ' ₹' + new Intl.NumberFormat('en-IN').format(ctx.parsed.y)
                }
            }
        },
        scales: {
            x: { grid: { display: false }, border: { display: false }, ticks: { font: { size: 11 } } },
            y: {
                grid: { color: 'rgba(0,0,0,0.05)' },
                border: { display: false },
                ticks: {
                    font: { size: 11 },
                    callback: v => '₹' + new Intl.NumberFormat('en-IN', { notation: 'compact' }).format(v)
                }
            }
        }
    }
});
</script>
@endpush
