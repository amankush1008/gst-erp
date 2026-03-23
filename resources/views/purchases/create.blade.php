{{-- resources/views/purchases/create.blade.php --}}
@extends('layouts.app')
@section('title','Add Purchase')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Add Purchase Bill</h4>
        <p class="text-muted mb-0 small">Record a purchase from supplier</p>
    </div>
    <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Back
    </a>
</div>

<form method="POST" action="{{ route('purchases.store') }}">
    @csrf

    <div class="row g-4">
        <div class="col-lg-8">
            {{-- Header --}}
            <div class="card-dark mb-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Supplier <span class="text-danger">*</span></label>
                        <select name="party_id" class="form-select form-control-dark select2" required>
                            <option value="">Select Supplier</option>
                            @foreach($suppliers as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Supplier Invoice # <span class="text-danger">*</span></label>
                        <input type="text" name="invoice_number" class="form-control form-control-dark" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Invoice Date <span class="text-danger">*</span></label>
                        <input type="date" name="invoice_date" class="form-control form-control-dark"
                               value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Due Date</label>
                        <input type="date" name="due_date" class="form-control form-control-dark">
                    </div>
                </div>
            </div>

            {{-- Items --}}
            <div class="card-dark mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-semibold mb-0 text-muted small text-uppercase">Items</h6>
                </div>

                <div class="table-responsive">
                    <table class="table table-dark mb-0" id="itemsTable">
                        <thead>
                            <tr class="text-muted small">
                                <th style="min-width:200px">Product</th>
                                <th style="width:80px">Qty</th>
                                <th style="width:110px">Rate (₹)</th>
                                <th style="width:90px">Discount</th>
                                <th style="width:80px">GST %</th>
                                <th style="width:100px">Amount</th>
                                <th style="width:40px"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            <tr class="item-row">
                                <td>
                                    <select name="items[0][product_id]" class="form-select form-control-dark product-select select2" required>
                                        <option value="">Select Product</option>
                                        @foreach($products as $p)
                                            <option value="{{ $p->id }}"
                                                    data-rate="{{ $p->purchase_price ?? $p->sale_price }}"
                                                    data-gst="{{ $p->gst_rate }}">
                                                {{ $p->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="number" name="items[0][qty]" class="form-control form-control-dark item-qty" min="0.01" step="0.01" value="1" required></td>
                                <td><input type="number" name="items[0][rate]" class="form-control form-control-dark item-rate" min="0" step="0.01" value="0" required></td>
                                <td><input type="number" name="items[0][discount]" class="form-control form-control-dark item-discount" min="0" step="0.01" value="0"></td>
                                <td>
                                    <select name="items[0][gst_rate]" class="form-select form-control-dark item-gst">
                                        @foreach(gstRates() as $r)
                                            <option value="{{ $r }}" @selected($r == 18)>{{ $r }}%</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="number" name="items[0][total]" class="form-control form-control-dark item-total" readonly></td>
                                <td>
                                    <button type="button" class="btn btn-outline-danger btn-sm remove-row">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="addItemRow">
                        <i class="fas fa-plus me-1"></i>Add Item
                    </button>
                </div>

                {{-- Totals --}}
                <div class="row justify-content-end mt-4">
                    <div class="col-md-5">
                        <table class="table table-dark table-sm mb-0">
                            <tr>
                                <td class="text-muted">Subtotal</td>
                                <td class="text-end" id="summarySubtotal">₹0.00</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Tax (GST)</td>
                                <td class="text-end text-warning" id="summaryTax">₹0.00</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Overall Discount</td>
                                <td class="text-end">
                                    <input type="number" name="overall_discount" id="overallDiscount"
                                           class="form-control form-control-dark form-control-sm text-end"
                                           style="width:100px;display:inline-block" min="0" step="0.01" value="0">
                                </td>
                            </tr>
                            <tr class="fw-bold border-top border-secondary">
                                <td>Total</td>
                                <td class="text-end text-success fs-5" id="summaryTotal">₹0.00</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label text-muted small">Notes</label>
                <textarea name="notes" class="form-control form-control-dark" rows="2" placeholder="Internal notes..."></textarea>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card-dark mb-4">
                <h6 class="fw-semibold mb-3 text-muted small text-uppercase">Payment</h6>
                <div class="mb-3">
                    <label class="form-label text-muted small">Payment Status</label>
                    <select name="payment_status" class="form-select form-control-dark">
                        <option value="unpaid">Unpaid</option>
                        <option value="paid">Paid</option>
                        <option value="partial">Partial</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small">Payment Method</label>
                    <select name="payment_method" class="form-select form-control-dark">
                        <option value="cash">Cash</option>
                        <option value="bank">Bank Transfer</option>
                        <option value="upi">UPI</option>
                        <option value="cheque">Cheque</option>
                    </select>
                </div>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Save Purchase
                </button>
                <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
let rowIndex = 1;

function calcRow(row) {
    const qty      = parseFloat(row.querySelector('.item-qty').value) || 0;
    const rate     = parseFloat(row.querySelector('.item-rate').value) || 0;
    const discount = parseFloat(row.querySelector('.item-discount').value) || 0;
    const gst      = parseFloat(row.querySelector('.item-gst').value) || 0;
    const taxable  = (qty * rate) - discount;
    const tax      = taxable * gst / 100;
    row.querySelector('.item-total').value = (taxable + tax).toFixed(2);
    calcTotals();
}

function calcTotals() {
    let subtotal = 0, tax = 0;
    document.querySelectorAll('.item-row').forEach(row => {
        const qty   = parseFloat(row.querySelector('.item-qty').value) || 0;
        const rate  = parseFloat(row.querySelector('.item-rate').value) || 0;
        const disc  = parseFloat(row.querySelector('.item-discount').value) || 0;
        const gstR  = parseFloat(row.querySelector('.item-gst').value) || 0;
        const taxable = (qty * rate) - disc;
        subtotal += taxable;
        tax      += taxable * gstR / 100;
    });
    const discount = parseFloat(document.getElementById('overallDiscount').value) || 0;
    const total    = subtotal + tax - discount;
    document.getElementById('summarySubtotal').textContent = '₹' + subtotal.toFixed(2);
    document.getElementById('summaryTax').textContent      = '₹' + tax.toFixed(2);
    document.getElementById('summaryTotal').textContent    = '₹' + total.toFixed(2);
}

document.getElementById('itemsBody').addEventListener('change', e => {
    const row = e.target.closest('.item-row');
    if (!row) return;
    if (e.target.classList.contains('product-select')) {
        const opt = e.target.selectedOptions[0];
        row.querySelector('.item-rate').value = opt.dataset.rate || 0;
        row.querySelector('.item-gst').value  = opt.dataset.gst  || 18;
    }
    calcRow(row);
});

document.getElementById('itemsBody').addEventListener('input', e => {
    const row = e.target.closest('.item-row');
    if (row) calcRow(row);
});

document.getElementById('overallDiscount').addEventListener('input', calcTotals);

document.getElementById('addItemRow').addEventListener('click', () => {
    const tbody = document.getElementById('itemsBody');
    const first = tbody.querySelector('.item-row');
    const clone = first.cloneNode(true);
    clone.querySelectorAll('input').forEach(i => { if (!i.readOnly) i.value = i.type === 'number' ? (i.classList.contains('item-qty') ? 1 : 0) : ''; });
    clone.querySelectorAll('select').forEach(s => s.selectedIndex = 0);
    // Update name indices
    clone.querySelectorAll('[name]').forEach(el => {
        el.name = el.name.replace(/\[\d+\]/, `[${rowIndex}]`);
    });
    tbody.appendChild(clone);
    rowIndex++;
});

document.getElementById('itemsBody').addEventListener('click', e => {
    if (e.target.closest('.remove-row')) {
        const rows = document.querySelectorAll('.item-row');
        if (rows.length > 1) { e.target.closest('.item-row').remove(); calcTotals(); }
    }
});

calcTotals();
</script>
@endpush
