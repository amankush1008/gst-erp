@extends('layouts.app')
@section('title', isset($invoice) ? 'Edit Invoice' : 'Create Invoice')
@section('page-title', isset($invoice) ? 'Edit Invoice #' . $invoice->invoice_number : 'New Invoice')

@section('content')
<form action="{{ isset($invoice) ? route('invoices.update', $invoice) : route('invoices.store') }}" method="POST" id="invoiceForm">
    @csrf
    @if(isset($invoice)) @method('PUT') @endif

    <div class="row g-3">
        {{-- Left Column --}}
        <div class="col-xl-9">

            {{-- Invoice Header --}}
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Invoice Type <span class="text-danger">*</span></label>
                            <select name="invoice_type" class="form-select" id="invoiceType">
                                <option value="tax_invoice" {{ old('invoice_type', $invoice->invoice_type ?? '') == 'tax_invoice' ? 'selected' : '' }}>Tax Invoice</option>
                                <option value="retail_invoice" {{ old('invoice_type', $invoice->invoice_type ?? '') == 'retail_invoice' ? 'selected' : '' }}>Retail Invoice</option>
                                <option value="proforma_invoice" {{ old('invoice_type', $invoice->invoice_type ?? '') == 'proforma_invoice' ? 'selected' : '' }}>Proforma Invoice</option>
                                <option value="credit_note" {{ old('invoice_type', $invoice->invoice_type ?? '') == 'credit_note' ? 'selected' : '' }}>Credit Note</option>
                                <option value="debit_note" {{ old('invoice_type', $invoice->invoice_type ?? '') == 'debit_note' ? 'selected' : '' }}>Debit Note</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Invoice Number</label>
                            <input type="text" name="invoice_number" class="form-control" value="{{ $nextNumber ?? $invoice->invoice_number ?? '' }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Invoice Date <span class="text-danger">*</span></label>
                            <input type="text" name="invoice_date" class="form-control" data-date
                                   value="{{ old('invoice_date', isset($invoice) ? $invoice->invoice_date->format('Y-m-d') : date('Y-m-d')) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Due Date</label>
                            <input type="text" name="due_date" class="form-control" data-date
                                   value="{{ old('due_date', isset($invoice) ? $invoice->due_date?->format('Y-m-d') : '') }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Party Selection --}}
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Bill To (Customer) <span class="text-danger">*</span></label>
                            <div class="d-flex gap-2">
                                <select name="party_id" class="form-select select2" id="partySelect" required>
                                    <option value="">-- Select Customer --</option>
                                    @foreach($parties as $party)
                                    <option value="{{ $party->id }}"
                                        data-state="{{ $party->billing_state }}"
                                        data-gstin="{{ $party->gstin }}"
                                        data-address="{{ $party->billing_address_full }}"
                                        {{ old('party_id', $invoice->party_id ?? '') == $party->id ? 'selected' : '' }}>
                                        {{ $party->name }} {{ $party->gstin ? "({$party->gstin})" : '' }}
                                    </option>
                                    @endforeach
                                </select>
                                <a href="{{ route('parties.create') }}" class="btn btn-outline-secondary btn-sm" title="Add New Customer">
                                    <i class="bi bi-plus-lg"></i>
                                </a>
                            </div>
                            <div id="partyInfo" class="mt-2 p-2 rounded" style="background:#F8FAFC;font-size:12px;display:none">
                                <div id="partyAddress" class="text-muted"></div>
                                <div id="partyGstin" class="text-secondary"></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Place of Supply</label>
                            <input type="text" name="place_of_supply" class="form-control" id="placeOfSupply"
                                   value="{{ old('place_of_supply', $invoice->place_of_supply ?? '') }}"
                                   placeholder="State">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">PO Number</label>
                            <input type="text" name="po_number" class="form-control"
                                   value="{{ old('po_number', $invoice->po_number ?? '') }}"
                                   placeholder="Reference">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Line Items --}}
            <div class="card mb-3">
                <div class="card-header d-flex align-items-center">
                    <span>Items / Services</span>
                    <div class="ms-auto d-flex gap-2">
                        <label class="form-check-label small text-muted me-2">
                            <input type="checkbox" id="showHsnCol" class="form-check-input me-1" checked>
                            HSN
                        </label>
                        <label class="form-check-label small text-muted">
                            <input type="checkbox" id="showDiscountCol" class="form-check-input me-1">
                            Discount
                        </label>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0" id="itemsTable">
                            <thead>
                                <tr style="background:#F8FAFC">
                                    <th style="width:35px">#</th>
                                    <th style="min-width:220px">Item / Service</th>
                                    <th style="width:80px">HSN</th>
                                    <th style="width:70px">Qty</th>
                                    <th style="width:70px">Unit</th>
                                    <th style="width:100px">Rate (₹)</th>
                                    <th style="width:80px" class="disc-col" style="display:none">Disc%</th>
                                    <th style="width:80px">Tax%</th>
                                    <th style="width:110px" class="text-end">Amount (₹)</th>
                                    <th style="width:40px"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                {{-- Items added via JS --}}
                            </tbody>
                        </table>
                    </div>

                    <div class="p-3 border-top">
                        <button type="button" class="btn btn-outline-primary btn-sm" id="addItemBtn">
                            <i class="bi bi-plus-circle me-1"></i> Add Item
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm ms-2" id="addNoteBtn">
                            <i class="bi bi-chat-left-text me-1"></i> Add Note
                        </button>
                    </div>
                </div>
            </div>

            {{-- Transport Details --}}
            <div class="card mb-3" id="transportCard">
                <div class="card-header d-flex align-items-center">
                    <span>Transport / Shipping Details</span>
                    <div class="ms-auto">
                        <div class="form-check form-switch m-0">
                            <input class="form-check-input" type="checkbox" id="enableTransport" {{ isset($invoice) && $invoice->transport ? 'checked' : '' }}>
                            <label class="form-check-label small" for="enableTransport">Enable</label>
                        </div>
                    </div>
                </div>
                <div class="card-body" id="transportFields" style="{{ isset($invoice) && $invoice->transport ? '' : 'display:none' }}">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Transporter Name</label>
                            <input type="text" name="transport[transporter_name]" class="form-control"
                                   value="{{ $invoice->transport->transporter_name ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Transport ID / GSTIN</label>
                            <input type="text" name="transport[transporter_id]" class="form-control"
                                   value="{{ $invoice->transport->transporter_id ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Vehicle Number</label>
                            <input type="text" name="transport[vehicle_number]" class="form-control"
                                   placeholder="MH01AB1234"
                                   value="{{ $invoice->transport->vehicle_number ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">LR / RR Number</label>
                            <input type="text" name="transport[lr_number]" class="form-control"
                                   value="{{ $invoice->transport->lr_number ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Dispatch Date</label>
                            <input type="text" name="transport[dispatch_date]" class="form-control" data-date
                                   value="{{ isset($invoice->transport->dispatch_date) ? $invoice->transport->dispatch_date->format('Y-m-d') : '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Distance (KM)</label>
                            <input type="number" name="transport[distance_km]" class="form-control"
                                   value="{{ $invoice->transport->distance_km ?? '' }}">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Delivery Address</label>
                            <textarea name="transport[delivery_address]" class="form-control" rows="2">{{ $invoice->transport->delivery_address ?? '' }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Notes & Terms --}}
            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Notes (for customer)</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Thank you for your business!">{{ old('notes', $invoice->notes ?? '') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Terms & Conditions</label>
                            <textarea name="terms_conditions" class="form-control" rows="3"
                                      placeholder="Payment due within 30 days...">{{ old('terms_conditions', $invoice->terms_conditions ?? $business->terms_conditions) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column - Totals --}}
        <div class="col-xl-3">
            <div class="card sticky-top" style="top:80px">
                <div class="card-header">Invoice Summary</div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2" style="font-size:13px">
                        <span class="text-muted">Subtotal</span>
                        <span class="text-currency" id="subtotalDisplay">₹0.00</span>
                    </div>

                    <div class="mb-2">
                        <div class="d-flex justify-content-between mb-1" style="font-size:13px">
                            <span class="text-muted">Discount</span>
                        </div>
                        <div class="input-group input-group-sm">
                            <input type="number" name="discount_percent" id="discountPercent"
                                   class="form-control" placeholder="%" step="0.01" min="0" max="100"
                                   value="{{ old('discount_percent', $invoice->discount_percent ?? 0) }}">
                            <span class="input-group-text">%</span>
                        </div>
                        <div class="text-end mt-1" style="font-size:12px;color:#DC2626">
                            -₹<span id="discountAmountDisplay">0.00</span>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mb-2" style="font-size:13px">
                        <span class="text-muted">Taxable Amount</span>
                        <span class="text-currency" id="taxableDisplay">₹0.00</span>
                    </div>

                    <div id="taxBreakup" class="mb-2">
                        {{-- Tax rows injected by JS --}}
                    </div>

                    <div class="mb-2">
                        <div class="d-flex justify-content-between mb-1" style="font-size:13px">
                            <span class="text-muted">Other Charges</span>
                        </div>
                        <input type="number" name="other_charges" id="otherCharges"
                               class="form-control form-control-sm" step="0.01" min="0"
                               value="{{ old('other_charges', $invoice->other_charges ?? 0) }}">
                    </div>

                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="round_off_enabled" id="roundOffEnabled">
                        <label class="form-check-label" style="font-size:13px">Round Off</label>
                        <span class="ms-auto text-muted" style="font-size:12px" id="roundOffDisplay"></span>
                    </div>
                    <input type="hidden" name="round_off" id="roundOff" value="0">

                    <hr>

                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold">Total</span>
                        <span class="fw-bold text-currency" style="font-size:20px;color:#2563EB" id="totalDisplay">₹0.00</span>
                    </div>

                    <div class="d-flex justify-content-between mt-1" style="font-size:12px;color:#64748B">
                        <span>Amount in words:</span>
                    </div>
                    <div id="amountInWords" style="font-size:12px;color:#475569;font-style:italic;margin-top:4px"></div>

                    <div class="mt-3">
                        <label class="form-label" style="font-size:12px">Invoice Template</label>
                        <select name="template" class="form-select form-select-sm">
                            <option value="default">Default (Professional)</option>
                            <option value="classic">Classic (Tally Style)</option>
                            <option value="modern">Modern (Colorful)</option>
                            <option value="minimal">Minimal (Clean)</option>
                        </select>
                    </div>

                    <div class="mt-3">
                        <label class="form-label d-flex align-items-center" style="font-size:12px">
                            <input type="checkbox" name="reverse_charge" class="form-check-input me-2" value="1"
                                   {{ old('reverse_charge', $invoice->reverse_charge ?? false) ? 'checked' : '' }}>
                            Reverse Charge
                        </label>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" name="status" value="sent" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i> Save Invoice
                        </button>
                        <button type="submit" name="status" value="draft" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-file-earmark me-1"></i> Save as Draft
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- Item Row Template --}}
<template id="itemRowTemplate">
    <tr class="item-row">
        <td class="text-center text-muted item-number" style="padding-top:14px"></td>
        <td>
            <input type="text" name="items[INDEX][item_name]" class="form-control form-control-sm item-name"
                   placeholder="Search or type item..." required autocomplete="off">
            <input type="hidden" name="items[INDEX][product_id]" class="product-id">
            <div class="item-suggestions" style="position:relative;display:none">
                <div class="suggestions-dropdown" style="position:absolute;top:0;left:0;right:0;background:white;border:1px solid #E2E8F0;border-radius:8px;z-index:1000;max-height:200px;overflow-y:auto;box-shadow:0 4px 12px rgba(0,0,0,0.1)"></div>
            </div>
            <input type="text" name="items[INDEX][description]" class="form-control form-control-sm mt-1 item-desc"
                   placeholder="Description (optional)" style="font-size:11px">
        </td>
        <td class="hsn-col">
            <input type="text" name="items[INDEX][hsn_code]" class="form-control form-control-sm item-hsn" placeholder="HSN">
        </td>
        <td>
            <input type="number" name="items[INDEX][quantity]" class="form-control form-control-sm item-qty"
                   step="0.001" min="0.001" value="1" required>
        </td>
        <td>
            <input type="text" name="items[INDEX][unit]" class="form-control form-control-sm item-unit" placeholder="PCS">
        </td>
        <td>
            <input type="number" name="items[INDEX][rate]" class="form-control form-control-sm item-rate"
                   step="0.01" min="0" value="0" required>
        </td>
        <td class="disc-col" style="display:none">
            <input type="number" name="items[INDEX][discount_percent]" class="form-control form-control-sm item-discount"
                   step="0.01" min="0" max="100" value="0">
        </td>
        <td>
            <select name="items[INDEX][tax_rate]" class="form-select form-select-sm item-tax">
                <option value="0">0%</option>
                <option value="3">3%</option>
                <option value="5">5%</option>
                <option value="12">12%</option>
                <option value="18" selected>18%</option>
                <option value="28">28%</option>
            </select>
        </td>
        <td class="text-end">
            <div class="item-total fw-semibold text-currency" style="padding-top:8px;font-size:13px">0.00</div>
            <div class="item-tax-info text-muted" style="font-size:10px"></div>
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-link text-danger remove-item p-1" title="Remove">
                <i class="bi bi-trash3"></i>
            </button>
        </td>
    </tr>
</template>
@endsection

@push('scripts')
<script>
let itemIndex = 0;
const isInterstate = false; // Will be updated based on party state
const businessState = "{{ $business->state }}";
const gstRates = [0, 3, 5, 12, 18, 28];

// Pre-load existing items
const existingItems = @json(isset($invoice) ? $invoice->items->toArray() : []);

$(document).ready(function() {
    // Load existing items or start fresh
    if (existingItems.length > 0) {
        existingItems.forEach(item => addItemRow(item));
    } else {
        addItemRow();
    }

    // Party change handler
    $('#partySelect').on('change', function() {
        const option = $(this).find('option:selected');
        const partyState = option.data('state');
        const gstin = option.data('gstin');
        const address = option.data('address');

        if (option.val()) {
            $('#partyInfo').show();
            $('#partyAddress').text(address);
            $('#partyGstin').text(gstin ? 'GSTIN: ' + gstin : 'Unregistered');
            $('#placeOfSupply').val(partyState);
            window.isInterstate = partyState !== businessState;
            recalculate();
        } else {
            $('#partyInfo').hide();
        }
    }).trigger('change');

    // Transport toggle
    $('#enableTransport').on('change', function() {
        $('#transportFields').toggle(this.checked);
    });

    // Add item button
    $('#addItemBtn').on('click', () => addItemRow());

    // Discount / charges change
    $('#discountPercent, #otherCharges').on('input', recalculate);
    $('#roundOffEnabled').on('change', recalculate);

    // Column visibility toggles
    $('#showHsnCol').on('change', function() {
        $('.hsn-col').toggle(this.checked);
    });
    $('#showDiscountCol').on('change', function() {
        $('.disc-col').toggle(this.checked);
    });
});

function addItemRow(data = {}) {
    const template = document.getElementById('itemRowTemplate').content.cloneNode(true);
    const row = template.querySelector('tr');
    const html = row.outerHTML.replace(/INDEX/g, itemIndex);
    const $row = $(html);

    if (data.item_name) {
        $row.find('.item-name').val(data.item_name);
        $row.find('.product-id').val(data.product_id || '');
        $row.find('.item-hsn').val(data.hsn_code || '');
        $row.find('.item-qty').val(data.quantity || 1);
        $row.find('.item-rate').val(data.rate || 0);
        $row.find('.item-unit').val(data.unit || '');
        $row.find('.item-discount').val(data.discount_percent || 0);
        $row.find('.item-tax').val(data.tax_rate || 18);
        $row.find('.item-desc').val(data.description || '');
    }

    $('#itemsBody').append($row);
    initItemRow($row);
    itemIndex++;
    updateItemNumbers();
    recalculate();
}

function initItemRow($row) {
    // Live search for product
    let searchTimeout;
    $row.find('.item-name').on('input', function() {
        const query = $(this).val();
        clearTimeout(searchTimeout);
        if (query.length < 2) { $row.find('.item-suggestions').hide(); return; }

        searchTimeout = setTimeout(() => {
            $.get('{{ route('products.search') }}', { q: query }, function(products) {
                const $dropdown = $row.find('.suggestions-dropdown').empty();
                if (products.length === 0) { $row.find('.item-suggestions').hide(); return; }

                products.forEach(p => {
                    $dropdown.append(`
                        <div class="suggestion-item px-3 py-2" style="cursor:pointer;font-size:13px;border-bottom:1px solid #F1F5F9"
                             data-product='${JSON.stringify(p)}'>
                            <div class="fw-medium">${p.name}</div>
                            <div class="text-muted" style="font-size:11px">
                                ${p.sku ? 'SKU: ' + p.sku + ' | ' : ''}Rate: ₹${p.sale_price} | GST: ${p.tax_rate}% | Stock: ${p.stock}
                            </div>
                        </div>
                    `);
                });

                $row.find('.item-suggestions').show();
            });
        }, 250);
    });

    // Select product from dropdown
    $row.on('click', '.suggestion-item', function() {
        const p = $(this).data('product');
        $row.find('.item-name').val(p.name);
        $row.find('.product-id').val(p.id);
        $row.find('.item-hsn').val(p.hsn_code || '');
        $row.find('.item-unit').val(p.unit || '');
        $row.find('.item-rate').val(p.sale_price);
        $row.find('.item-tax').val(p.tax_rate);
        $row.find('.item-suggestions').hide();
        recalculate();
    });

    // Hide suggestions on outside click
    $(document).on('click', function(e) {
        if (!$row.find('.item-name').is(e.target)) $row.find('.item-suggestions').hide();
    });

    // Row change events
    $row.find('.item-qty, .item-rate, .item-discount, .item-tax').on('input change', recalculate);

    // Remove row
    $row.find('.remove-item').on('click', function() {
        if ($('#itemsBody tr').length <= 1) return toastr.warning('At least one item is required');
        $row.remove();
        updateItemNumbers();
        recalculate();
    });
}

function updateItemNumbers() {
    $('#itemsBody tr.item-row').each((i, row) => $(row).find('.item-number').text(i + 1));
}

function recalculate() {
    let subtotal = 0, totalTax = 0, cgstTotal = 0, sgstTotal = 0, igstTotal = 0;
    const taxGroups = {};
    const isInter = window.isInterstate || false;

    $('#itemsBody tr.item-row').each(function() {
        const qty = parseFloat($(this).find('.item-qty').val()) || 0;
        const rate = parseFloat($(this).find('.item-rate').val()) || 0;
        const discPct = parseFloat($(this).find('.item-discount').val()) || 0;
        const taxRate = parseFloat($(this).find('.item-tax').val()) || 0;

        const lineTotal = qty * rate;
        const discAmt = lineTotal * (discPct / 100);
        const taxable = lineTotal - discAmt;
        const taxAmt = taxable * (taxRate / 100);

        const cgst = isInter ? 0 : taxAmt / 2;
        const sgst = isInter ? 0 : taxAmt / 2;
        const igst = isInter ? taxAmt : 0;
        const lineGrandTotal = taxable + taxAmt;

        subtotal += lineTotal;
        totalTax += taxAmt;
        cgstTotal += cgst;
        sgstTotal += sgst;
        igstTotal += igst;

        $(this).find('.item-total').text('₹' + formatNum(lineGrandTotal));

        // Tax info per row
        let taxInfo = '';
        if (!isInter && taxRate > 0) {
            taxInfo = `CGST ${taxRate/2}%: ₹${formatNum(cgst)} | SGST ${taxRate/2}%: ₹${formatNum(sgst)}`;
        } else if (isInter && taxRate > 0) {
            taxInfo = `IGST ${taxRate}%: ₹${formatNum(igst)}`;
        }
        $(this).find('.item-tax-info').text(taxInfo);

        // Tax group for summary
        if (taxRate > 0) {
            if (!taxGroups[taxRate]) taxGroups[taxRate] = { taxable: 0, cgst: 0, sgst: 0, igst: 0 };
            taxGroups[taxRate].taxable += taxable;
            taxGroups[taxRate].cgst += cgst;
            taxGroups[taxRate].sgst += sgst;
            taxGroups[taxRate].igst += igst;
        }
    });

    // Overall discount
    const overallDiscPct = parseFloat($('#discountPercent').val()) || 0;
    const taxableAmount = subtotal - (subtotal * overallDiscPct / 100);
    const overallDiscAmt = subtotal * overallDiscPct / 100;

    // Adjust tax for overall discount
    const taxAfterDisc = taxableAmount * (totalTax / Math.max(subtotal - overallDiscAmt, 0.01)) || totalTax;

    const otherCharges = parseFloat($('#otherCharges').val()) || 0;
    let total = taxableAmount + totalTax + otherCharges;

    // Round off
    let roundOff = 0;
    if ($('#roundOffEnabled').is(':checked')) {
        roundOff = Math.round(total) - total;
        total = Math.round(total);
        $('#roundOffDisplay').text((roundOff >= 0 ? '+' : '') + formatNum(roundOff));
    }
    $('#roundOff').val(roundOff.toFixed(2));

    // Update displays
    $('#subtotalDisplay').text('₹' + formatNum(subtotal));
    $('#discountAmountDisplay').text(formatNum(overallDiscAmt));
    $('#taxableDisplay').text('₹' + formatNum(subtotal - overallDiscAmt));
    $('#totalDisplay').text('₹' + formatNum(total));
    $('#amountInWords').text(numberToWords(total) + ' Only');

    // Tax breakup
    const $taxBreakup = $('#taxBreakup').empty();
    if (!isInter && cgstTotal > 0) {
        Object.entries(taxGroups).forEach(([rate, data]) => {
            $taxBreakup.append(`
                <div class="d-flex justify-content-between" style="font-size:12px;color:#64748B;margin-bottom:3px">
                    <span>CGST @${rate/2}%</span><span class="text-currency">₹${formatNum(data.cgst)}</span>
                </div>
                <div class="d-flex justify-content-between" style="font-size:12px;color:#64748B;margin-bottom:3px">
                    <span>SGST @${rate/2}%</span><span class="text-currency">₹${formatNum(data.sgst)}</span>
                </div>
            `);
        });
    } else if (isInter && igstTotal > 0) {
        Object.entries(taxGroups).forEach(([rate, data]) => {
            $taxBreakup.append(`
                <div class="d-flex justify-content-between" style="font-size:12px;color:#64748B;margin-bottom:3px">
                    <span>IGST @${rate}%</span><span class="text-currency">₹${formatNum(data.igst)}</span>
                </div>
            `);
        });
    }

    if (totalTax > 0) {
        $taxBreakup.append(`
            <div class="d-flex justify-content-between mb-2" style="font-size:13px;font-weight:600">
                <span>Total Tax</span><span class="text-currency">₹${formatNum(totalTax)}</span>
            </div>
        `);
    }
}

function formatNum(n) {
    return parseFloat(n || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

// Simple number to words
function numberToWords(amount) {
    const ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
                  'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen',
                  'Seventeen', 'Eighteen', 'Nineteen'];
    const tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

    function convert(n) {
        if (n === 0) return '';
        if (n < 20) return ones[n] + ' ';
        if (n < 100) return tens[Math.floor(n / 10)] + ' ' + ones[n % 10] + ' ';
        if (n < 1000) return ones[Math.floor(n / 100)] + ' Hundred ' + convert(n % 100);
        if (n < 100000) return convert(Math.floor(n / 1000)) + 'Thousand ' + convert(n % 1000);
        if (n < 10000000) return convert(Math.floor(n / 100000)) + 'Lakh ' + convert(n % 100000);
        return convert(Math.floor(n / 10000000)) + 'Crore ' + convert(n % 10000000);
    }

    const rupees = Math.floor(amount);
    const paise = Math.round((amount - rupees) * 100);
    let words = 'Rupees ' + convert(rupees).trim();
    if (paise > 0) words += ' and ' + convert(paise).trim() + ' Paise';
    return words;
}

// Form validation
$('#invoiceForm').on('submit', function(e) {
    if ($('#itemsBody tr.item-row').length === 0) {
        e.preventDefault();
        toastr.error('Please add at least one item');
    }
});
</script>
@endpush
