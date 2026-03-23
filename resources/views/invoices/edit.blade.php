{{-- resources/views/invoices/edit.blade.php --}}
{{-- Reuses the create form with pre-populated data --}}
@extends('layouts.app')
@section('title', 'Edit Invoice #' . $invoice->invoice_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Edit Invoice</h4>
        <p class="text-muted mb-0 small"># {{ $invoice->invoice_number }}</p>
    </div>
    <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Back
    </a>
</div>

<form method="POST" action="{{ route('invoices.update', $invoice) }}" id="invoiceForm">
    @csrf @method('PUT')

    {{-- Type selector (read-only on edit) --}}
    <input type="hidden" name="invoice_type" value="{{ $invoice->invoice_type }}">

    <div class="row g-4">
        <div class="col-lg-8">
            {{-- Header --}}
            <div class="card-dark mb-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Invoice Type</label>
                        <input type="text" class="form-control form-control-dark"
                               value="{{ str_replace('_',' ', ucwords($invoice->invoice_type)) }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Party <span class="text-danger">*</span></label>
                        <select name="party_id" class="form-select form-control-dark select2" required>
                            <option value="">Select Party</option>
                            @foreach($parties as $party)
                                <option value="{{ $party->id }}"
                                        data-state="{{ $party->billing_state }}"
                                        data-gstin="{{ $party->gstin }}"
                                        @selected($invoice->party_id == $party->id)>
                                    {{ $party->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Invoice Date <span class="text-danger">*</span></label>
                        <input type="date" name="invoice_date" class="form-control form-control-dark"
                               value="{{ $invoice->invoice_date }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Due Date</label>
                        <input type="date" name="due_date" class="form-control form-control-dark"
                               value="{{ $invoice->due_date }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Template</label>
                        <select name="template" class="form-select form-control-dark">
                            <option value="default" @selected($invoice->template === 'default')>Default</option>
                            <option value="classic" @selected($invoice->template === 'classic')>Classic</option>
                            <option value="modern"  @selected($invoice->template === 'modern')>Modern</option>
                            <option value="minimal" @selected($invoice->template === 'minimal')>Minimal</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Items table --}}
            <div class="card-dark mb-4">
                <h6 class="fw-semibold mb-3 text-muted small text-uppercase">Items</h6>
                <div class="table-responsive">
                    <table class="table table-dark mb-0" id="itemsTable">
                        <thead>
                            <tr class="text-muted small">
                                <th style="min-width:200px">Product / Service</th>
                                <th style="width:70px">Qty</th>
                                <th style="width:110px">Rate (₹)</th>
                                <th style="width:90px">Discount (₹)</th>
                                <th style="width:80px">GST %</th>
                                <th style="width:100px">Amount</th>
                                <th style="width:40px"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            @foreach($invoice->items as $i => $item)
                            <tr class="item-row">
                                <td>
                                    <input type="hidden" name="items[{{ $i }}][product_id]" value="{{ $item->product_id }}" class="item-product-id">
                                    <input type="text" name="items[{{ $i }}][product_name]" class="form-control form-control-dark"
                                           value="{{ $item->product_name }}" required>
                                    <input type="text" name="items[{{ $i }}][hsn_code]" class="form-control form-control-dark mt-1"
                                           value="{{ $item->hsn_code }}" placeholder="HSN">
                                </td>
                                <td><input type="number" name="items[{{ $i }}][qty]" class="form-control form-control-dark item-qty"
                                           value="{{ $item->qty }}" min="0.01" step="0.01" required></td>
                                <td><input type="number" name="items[{{ $i }}][rate]" class="form-control form-control-dark item-rate"
                                           value="{{ $item->rate }}" min="0" step="0.01" required></td>
                                <td><input type="number" name="items[{{ $i }}][discount]" class="form-control form-control-dark item-discount"
                                           value="{{ $item->discount }}" min="0" step="0.01"></td>
                                <td>
                                    <select name="items[{{ $i }}][gst_rate]" class="form-select form-control-dark item-gst">
                                        @foreach(gstRates() as $r)
                                            <option value="{{ $r }}" @selected($item->gst_rate == $r)>{{ $r }}%</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="number" name="items[{{ $i }}][total]" class="form-control form-control-dark item-total"
                                           value="{{ $item->total_amount }}" readonly></td>
                                <td>
                                    <button type="button" class="btn btn-outline-danger btn-sm remove-row">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="addItem">
                        <i class="fas fa-plus me-1"></i>Add Item
                    </button>
                </div>

                {{-- Totals --}}
                <div class="row justify-content-end mt-4">
                    <div class="col-md-5">
                        <table class="table table-dark table-sm mb-0">
                            <tr><td class="text-muted">Subtotal</td>
                                <td class="text-end" id="summarySubtotal">₹{{ number_format($invoice->taxable_amount, 2) }}</td></tr>
                            <tr><td class="text-muted">GST</td>
                                <td class="text-end text-warning" id="summaryTax">₹{{ number_format($invoice->tax_amount, 2) }}</td></tr>
                            <tr><td class="text-muted">Discount</td>
                                <td class="text-end">
                                    <input type="number" name="overall_discount" id="overallDiscount"
                                           class="form-control form-control-dark form-control-sm text-end"
                                           style="width:100px;display:inline-block" value="{{ $invoice->discount_amount }}"
                                           min="0" step="0.01">
                                </td>
                            </tr>
                            <tr><td class="text-muted">Round Off</td>
                                <td class="text-end">
                                    <input type="number" name="round_off" id="roundOff"
                                           class="form-control form-control-dark form-control-sm text-end"
                                           style="width:100px;display:inline-block" value="{{ $invoice->round_off }}"
                                           step="0.01">
                                </td>
                            </tr>
                            <tr class="fw-bold border-top border-secondary">
                                <td>Total</td>
                                <td class="text-end text-success fs-5" id="summaryTotal">
                                    ₹{{ number_format($invoice->total_amount, 2) }}
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label text-muted small">Notes</label>
                <textarea name="notes" class="form-control form-control-dark" rows="2">{{ $invoice->notes }}</textarea>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Update Invoice
                </button>
                <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
let rowIdx = {{ $invoice->items->count() }};

function calcRow(row) {
    const qty  = parseFloat(row.querySelector('.item-qty').value) || 0;
    const rate = parseFloat(row.querySelector('.item-rate').value) || 0;
    const disc = parseFloat(row.querySelector('.item-discount')?.value) || 0;
    const gst  = parseFloat(row.querySelector('.item-gst').value) || 0;
    const taxable = (qty * rate) - disc;
    const total   = taxable + (taxable * gst / 100);
    const el = row.querySelector('.item-total');
    if (el) el.value = total.toFixed(2);
    calcTotals();
}

function calcTotals() {
    let sub = 0, tax = 0;
    document.querySelectorAll('.item-row').forEach(row => {
        const qty  = parseFloat(row.querySelector('.item-qty').value) || 0;
        const rate = parseFloat(row.querySelector('.item-rate').value) || 0;
        const disc = parseFloat(row.querySelector('.item-discount')?.value) || 0;
        const gst  = parseFloat(row.querySelector('.item-gst').value) || 0;
        const t = (qty * rate) - disc;
        sub += t;
        tax += t * gst / 100;
    });
    const disc  = parseFloat(document.getElementById('overallDiscount').value) || 0;
    const ro    = parseFloat(document.getElementById('roundOff').value) || 0;
    const total = sub + tax - disc + ro;
    document.getElementById('summarySubtotal').textContent = '₹' + sub.toFixed(2);
    document.getElementById('summaryTax').textContent      = '₹' + tax.toFixed(2);
    document.getElementById('summaryTotal').textContent    = '₹' + total.toFixed(2);
}

document.getElementById('itemsBody').addEventListener('input', e => {
    const row = e.target.closest('.item-row');
    if (row) calcRow(row);
});

document.getElementById('overallDiscount').addEventListener('input', calcTotals);
document.getElementById('roundOff').addEventListener('input', calcTotals);

document.getElementById('addItem').addEventListener('click', () => {
    const row = `<tr class="item-row">
        <td>
            <input type="hidden" name="items[${rowIdx}][product_id]" class="item-product-id">
            <input type="text" name="items[${rowIdx}][product_name]" class="form-control form-control-dark" placeholder="Product name" required>
            <input type="text" name="items[${rowIdx}][hsn_code]" class="form-control form-control-dark mt-1" placeholder="HSN">
        </td>
        <td><input type="number" name="items[${rowIdx}][qty]" class="form-control form-control-dark item-qty" value="1" min="0.01" step="0.01" required></td>
        <td><input type="number" name="items[${rowIdx}][rate]" class="form-control form-control-dark item-rate" value="0" min="0" step="0.01" required></td>
        <td><input type="number" name="items[${rowIdx}][discount]" class="form-control form-control-dark item-discount" value="0" min="0" step="0.01"></td>
        <td><select name="items[${rowIdx}][gst_rate]" class="form-select form-control-dark item-gst">
            <option value="0">0%</option><option value="5">5%</option><option value="12">12%</option>
            <option value="18" selected>18%</option><option value="28">28%</option>
        </select></td>
        <td><input type="number" name="items[${rowIdx}][total]" class="form-control form-control-dark item-total" readonly></td>
        <td><button type="button" class="btn btn-outline-danger btn-sm remove-row"><i class="fas fa-times"></i></button></td>
    </tr>`;
    document.getElementById('itemsBody').insertAdjacentHTML('beforeend', row);
    rowIdx++;
});

document.getElementById('itemsBody').addEventListener('click', e => {
    if (e.target.closest('.remove-row')) {
        if (document.querySelectorAll('.item-row').length > 1) {
            e.target.closest('.item-row').remove();
            calcTotals();
        }
    }
});

calcTotals();
</script>
@endpush
