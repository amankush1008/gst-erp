{{-- resources/views/payments/create.blade.php --}}
@extends('layouts.app')
@section('title','Record Payment')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Record Payment</h4>
        <p class="text-muted mb-0 small">Cash in / Cash out entry</p>
    </div>
    <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Back
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card-dark">
            <form method="POST" action="{{ route('payments.store') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label text-muted small">Transaction Type <span class="text-danger">*</span></label>
                    <div class="d-flex gap-2">
                        <div class="flex-fill">
                            <input type="radio" class="btn-check" name="type" id="typeReceived" value="received" checked>
                            <label class="btn btn-outline-success w-100" for="typeReceived">
                                <i class="fas fa-arrow-down me-1"></i>Payment Received (In)
                            </label>
                        </div>
                        <div class="flex-fill">
                            <input type="radio" class="btn-check" name="type" id="typePaid" value="paid">
                            <label class="btn btn-outline-danger w-100" for="typePaid">
                                <i class="fas fa-arrow-up me-1"></i>Payment Made (Out)
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted small">Party <span class="text-danger">*</span></label>
                    <select name="party_id" id="partySelect" class="form-select form-control-dark select2" required>
                        <option value="">Select Party</option>
                        @foreach($parties as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted small">Against Invoice <span class="text-muted">(optional)</span></label>
                    <select name="invoice_id" id="invoiceSelect" class="form-select form-control-dark">
                        <option value="">Select Invoice (optional)</option>
                        @foreach($invoices as $inv)
                            <option value="{{ $inv->id }}" data-balance="{{ $inv->balance_amount }}">
                                {{ $inv->invoice_number }} – Balance ₹{{ number_format($inv->balance_amount, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Amount (₹) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" id="amountInput" class="form-control form-control-dark"
                               min="0.01" step="0.01" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Date <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" class="form-control form-control-dark"
                               value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted small">Payment Method <span class="text-danger">*</span></label>
                    <div class="row g-2">
                        @foreach(['cash'=>'Cash','bank'=>'Bank','upi'=>'UPI','cheque'=>'Cheque'] as $val => $label)
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="payment_method" id="pm_{{ $val }}" value="{{ $val }}" @checked($val === 'cash')>
                            <label class="btn btn-outline-secondary w-100" for="pm_{{ $val }}">{{ $label }}</label>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted small">Reference / UTR / Cheque No</label>
                    <input type="text" name="reference" class="form-control form-control-dark"
                           placeholder="Optional reference number">
                </div>

                <div class="mb-4">
                    <label class="form-label text-muted small">Notes</label>
                    <textarea name="notes" class="form-control form-control-dark" rows="2"></textarea>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-save me-2"></i>Record Payment
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('invoiceSelect').addEventListener('change', function () {
    const opt = this.selectedOptions[0];
    if (opt && opt.dataset.balance) {
        document.getElementById('amountInput').value = parseFloat(opt.dataset.balance).toFixed(2);
    }
});

// Dynamic invoice loading by party
document.getElementById('partySelect').addEventListener('change', function () {
    const partyId = this.value;
    if (!partyId) return;
    fetch(`/payments/unpaid-invoices?party_id=${partyId}`)
        .then(r => r.json())
        .then(data => {
            const sel = document.getElementById('invoiceSelect');
            sel.innerHTML = '<option value="">Select Invoice (optional)</option>';
            data.forEach(inv => {
                sel.innerHTML += `<option value="${inv.id}" data-balance="${inv.balance_amount}">
                    ${inv.invoice_number} – Balance ₹${parseFloat(inv.balance_amount).toFixed(2)}
                </option>`;
            });
        });
});
</script>
@endpush
