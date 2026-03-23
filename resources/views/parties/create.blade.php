{{-- resources/views/parties/create.blade.php --}}
@extends('layouts.app')
@section('title', isset($party) ? 'Edit Party' : 'Add Party')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">{{ isset($party) ? 'Edit Party' : 'Add Party' }}</h4>
        <p class="text-muted mb-0 small">Customer or Supplier details</p>
    </div>
    <a href="{{ route('parties.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Back
    </a>
</div>

<form method="POST" action="{{ isset($party) ? route('parties.update', $party) : route('parties.store') }}">
    @csrf
    @if(isset($party)) @method('PUT') @endif

    <div class="row g-4">
        <div class="col-lg-8">
            {{-- Basic Info --}}
            <div class="card-dark mb-4">
                <h6 class="fw-semibold mb-3 text-muted small text-uppercase">Basic Information</h6>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label text-muted small">Party Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-dark"
                               value="{{ old('name', $party->name ?? '') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-select form-control-dark" required>
                            <option value="customer" @selected(old('type', $party->type ?? 'customer') === 'customer')>Customer</option>
                            <option value="supplier" @selected(old('type', $party->type ?? '') === 'supplier')>Supplier</option>
                            <option value="both"     @selected(old('type', $party->type ?? '') === 'both')>Both</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Mobile</label>
                        <input type="text" name="mobile" class="form-control form-control-dark"
                               value="{{ old('mobile', $party->mobile ?? '') }}" maxlength="10">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Email</label>
                        <input type="email" name="email" class="form-control form-control-dark"
                               value="{{ old('email', $party->email ?? '') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">PAN</label>
                        <input type="text" name="pan" class="form-control form-control-dark"
                               value="{{ old('pan', $party->pan ?? '') }}" maxlength="10" placeholder="ABCDE1234F">
                    </div>
                </div>
            </div>

            {{-- GST --}}
            <div class="card-dark mb-4">
                <h6 class="fw-semibold mb-3 text-muted small text-uppercase">GST Details</h6>
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label text-muted small">GSTIN</label>
                        <div class="input-group">
                            <input type="text" name="gstin" id="gstinInput" class="form-control form-control-dark"
                                   value="{{ old('gstin', $party->gstin ?? '') }}" maxlength="15"
                                   placeholder="22AAAAA0000A1Z5" style="text-transform:uppercase">
                            <button type="button" class="btn btn-outline-primary btn-sm" id="verifyGstinBtn">
                                <i class="fas fa-search me-1"></i>Verify
                            </button>
                        </div>
                        <div id="gstinResult" class="mt-2 small"></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small">GST Type</label>
                        <select name="gst_type" class="form-select form-control-dark">
                            <option value="regular"      @selected(old('gst_type', $party->gst_type ?? '') === 'regular')>Regular</option>
                            <option value="composition"  @selected(old('gst_type', $party->gst_type ?? '') === 'composition')>Composition</option>
                            <option value="unregistered" @selected(old('gst_type', $party->gst_type ?? '') === 'unregistered')>Unregistered</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Address --}}
            <div class="card-dark mb-4">
                <h6 class="fw-semibold mb-3 text-muted small text-uppercase">Billing Address</h6>
                <div class="row g-3">
                    <div class="col-12">
                        <textarea name="billing_address" class="form-control form-control-dark" rows="2"
                                  placeholder="Street address">{{ old('billing_address', $party->billing_address ?? '') }}</textarea>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="billing_city" class="form-control form-control-dark"
                               placeholder="City" value="{{ old('billing_city', $party->billing_city ?? '') }}">
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="billing_state" class="form-control form-control-dark"
                               placeholder="State" value="{{ old('billing_state', $party->billing_state ?? '') }}">
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="billing_pincode" class="form-control form-control-dark"
                               placeholder="Pincode" maxlength="6"
                               value="{{ old('billing_pincode', $party->billing_pincode ?? '') }}">
                    </div>
                </div>

                <div class="form-check mt-3">
                    <input class="form-check-input" type="checkbox" id="sameAsShipping" checked>
                    <label class="form-check-label text-muted small" for="sameAsShipping">
                        Shipping address same as billing
                    </label>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- Credit --}}
            <div class="card-dark mb-4">
                <h6 class="fw-semibold mb-3 text-muted small text-uppercase">Credit Terms</h6>
                <div class="mb-3">
                    <label class="form-label text-muted small">Credit Limit (₹)</label>
                    <input type="number" name="credit_limit" class="form-control form-control-dark"
                           value="{{ old('credit_limit', $party->credit_limit ?? 0) }}" min="0">
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small">Payment Terms (Days)</label>
                    <input type="number" name="payment_terms" class="form-control form-control-dark"
                           value="{{ old('payment_terms', $party->payment_terms ?? 30) }}" min="0">
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small">Opening Balance (₹)</label>
                    <input type="number" name="opening_balance" class="form-control form-control-dark"
                           value="{{ old('opening_balance', $party->opening_balance ?? 0) }}" step="0.01">
                    <small class="text-muted">Positive = you'll receive; Negative = you owe</small>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>{{ isset($party) ? 'Update' : 'Save Party' }}
                </button>
                <a href="{{ route('parties.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.getElementById('verifyGstinBtn').addEventListener('click', function () {
    const gstin = document.getElementById('gstinInput').value.trim().toUpperCase();
    if (gstin.length !== 15) {
        document.getElementById('gstinResult').innerHTML =
            '<span class="text-danger"><i class="fas fa-times me-1"></i>GSTIN must be 15 characters</span>';
        return;
    }

    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

    fetch(`/parties/verify-gstin/${gstin}`)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('gstinResult').innerHTML =
                    `<span class="text-success"><i class="fas fa-check-circle me-1"></i>${data.trade_name || data.legal_name}</span>
                     <small class="text-muted d-block">State: ${data.state || ''}</small>`;
            } else {
                document.getElementById('gstinResult').innerHTML =
                    `<span class="text-danger"><i class="fas fa-times me-1"></i>${data.message || 'Invalid GSTIN'}</span>`;
            }
        })
        .finally(() => {
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-search me-1"></i>Verify';
        });
});
</script>
@endpush
