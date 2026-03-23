{{-- resources/views/invoices/index.blade.php --}}
@extends('layouts.app')
@section('title','Invoices')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Invoices</h4>
        <p class="text-muted mb-0 small">Sales & billing management</p>
    </div>
    <div class="d-flex gap-2">
        <div class="dropdown">
            <button class="btn btn-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fas fa-plus me-1"></i>New Invoice
            </button>
            <ul class="dropdown-menu dropdown-menu-dark">
                <li><a class="dropdown-item" href="{{ route('invoices.create') }}?type=tax_invoice">
                    <i class="fas fa-file-invoice me-2 text-primary"></i>Tax Invoice</a></li>
                <li><a class="dropdown-item" href="{{ route('invoices.create') }}?type=retail_invoice">
                    <i class="fas fa-receipt me-2 text-info"></i>Retail Invoice</a></li>
                <li><a class="dropdown-item" href="{{ route('invoices.create') }}?type=proforma_invoice">
                    <i class="fas fa-file-alt me-2 text-warning"></i>Proforma Invoice</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="{{ route('invoices.create') }}?type=credit_note">
                    <i class="fas fa-file-minus me-2 text-danger"></i>Credit Note</a></li>
                <li><a class="dropdown-item" href="{{ route('invoices.create') }}?type=debit_note">
                    <i class="fas fa-file-plus me-2 text-success"></i>Debit Note</a></li>
            </ul>
        </div>
    </div>
</div>

{{-- Summary Cards --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(16,185,129,.15);color:#34D399">
                <i class="fas fa-rupee-sign"></i>
            </div>
            <div class="stat-value">₹{{ number_format($stats['total_sales'] ?? 0) }}</div>
            <div class="stat-label">Total Sales</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(239,68,68,.15);color:#FCA5A5">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-value">₹{{ number_format($stats['outstanding'] ?? 0) }}</div>
            <div class="stat-label">Outstanding</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(245,158,11,.15);color:#FCD34D">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-value">{{ $stats['overdue_count'] ?? 0 }}</div>
            <div class="stat-label">Overdue Invoices</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(99,102,241,.15);color:#A5B4FC">
                <i class="fas fa-check-double"></i>
            </div>
            <div class="stat-value">₹{{ number_format($stats['collected'] ?? 0) }}</div>
            <div class="stat-label">Collected</div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card-dark mb-3">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-3">
            <input type="text" name="search" class="form-control form-control-dark"
                   placeholder="Invoice #, party name..." value="{{ request('search') }}">
        </div>
        <div class="col-md-2">
            <select name="type" class="form-select form-control-dark">
                <option value="">All Types</option>
                <option value="tax_invoice"     @selected(request('type')=='tax_invoice')>Tax Invoice</option>
                <option value="retail_invoice"  @selected(request('type')=='retail_invoice')>Retail Invoice</option>
                <option value="proforma_invoice"@selected(request('type')=='proforma_invoice')>Proforma</option>
                <option value="credit_note"     @selected(request('type')=='credit_note')>Credit Note</option>
            </select>
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select form-control-dark">
                <option value="">All Status</option>
                <option value="unpaid"   @selected(request('status')=='unpaid')>Unpaid</option>
                <option value="partial"  @selected(request('status')=='partial')>Partial</option>
                <option value="paid"     @selected(request('status')=='paid')>Paid</option>
                <option value="overdue"  @selected(request('status')=='overdue')>Overdue</option>
            </select>
        </div>
        <div class="col-md-2">
            <input type="date" name="date_from" class="form-control form-control-dark"
                   value="{{ request('date_from') }}" placeholder="From">
        </div>
        <div class="col-md-1">
            <button class="btn btn-primary btn-sm w-100">Go</button>
        </div>
        <div class="col-md-1">
            <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary btn-sm w-100">×</a>
        </div>
    </form>
</div>

{{-- Table --}}
<div class="card-dark">
    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0">
            <thead>
                <tr class="text-muted small">
                    <th>Invoice #</th>
                    <th>Type</th>
                    <th>Party</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Balance</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $invoice)
                <tr>
                    <td class="fw-semibold font-monospace">{{ $invoice->invoice_number }}</td>
                    <td>
                        <span class="badge" style="background:#1E3A5F;color:#93C5FD;font-size:.7rem">
                            {{ str_replace('_', ' ', ucwords($invoice->invoice_type)) }}
                        </span>
                    </td>
                    <td>{{ $invoice->party->name ?? '-' }}</td>
                    <td class="text-muted small">{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}</td>
                    <td class="fw-semibold">₹{{ number_format($invoice->total_amount, 2) }}</td>
                    <td class="{{ $invoice->balance_amount > 0 ? 'text-warning' : 'text-muted' }}">
                        ₹{{ number_format($invoice->balance_amount, 2) }}
                    </td>
                    <td>
                        @php
                            $colors = ['paid'=>'success','partial'=>'warning','unpaid'=>'danger','draft'=>'secondary','cancelled'=>'dark'];
                            $c = $colors[$invoice->payment_status] ?? 'secondary';
                        @endphp
                        <span class="badge bg-{{ $c }}">{{ ucfirst($invoice->payment_status) }}</span>
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-outline-secondary" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('invoices.pdf', $invoice) }}" class="btn btn-outline-info" title="PDF" target="_blank">
                                <i class="fas fa-file-pdf"></i>
                            </a>
                            @if($invoice->payment_status !== 'paid')
                            <button type="button" class="btn btn-outline-success" title="Record Payment"
                                    onclick="paymentModal({{ $invoice->id }}, '{{ $invoice->invoice_number }}', {{ $invoice->balance_amount }})">
                                <i class="fas fa-money-bill-wave"></i>
                            </button>
                            @endif
                            <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-outline-primary" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('invoices.duplicate', $invoice) }}" class="d-inline">
                                @csrf
                                <button class="btn btn-outline-secondary" title="Duplicate">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('invoices.destroy', $invoice) }}" class="d-inline"
                                  onsubmit="return confirm('Delete invoice?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-outline-danger" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No invoices found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $invoices->links() }}</div>
</div>

{{-- Payment Modal --}}
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:#1E293B;border:1px solid #334155">
            <div class="modal-header border-secondary">
                <h5 class="modal-title">Record Payment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="paymentForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="text-muted small" id="paymentInvoiceInfo"></p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Amount (₹)</label>
                            <input type="number" name="amount" id="paymentAmount" class="form-control form-control-dark"
                                   min="0.01" step="0.01" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Payment Method</label>
                            <select name="payment_method" class="form-select form-control-dark">
                                <option value="cash">Cash</option>
                                <option value="bank">Bank Transfer</option>
                                <option value="upi">UPI</option>
                                <option value="cheque">Cheque</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted small">Date</label>
                            <input type="date" name="payment_date" class="form-control form-control-dark"
                                   value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted small">Reference / Remark</label>
                            <input type="text" name="reference" class="form-control form-control-dark" placeholder="UTR, Cheque No, etc.">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Record Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function paymentModal(id, number, balance) {
    document.getElementById('paymentInvoiceInfo').textContent = `Invoice: ${number} | Balance: ₹${parseFloat(balance).toFixed(2)}`;
    document.getElementById('paymentAmount').value = parseFloat(balance).toFixed(2);
    document.getElementById('paymentAmount').max = balance;
    document.getElementById('paymentForm').action = `/invoices/${id}/payment`;
    new bootstrap.Modal(document.getElementById('paymentModal')).show();
}
</script>
@endpush
