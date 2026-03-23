{{-- resources/views/settings/business.blade.php --}}
@extends('layouts.app')
@section('title','Business Settings')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Business Profile</h4>
        <p class="text-muted mb-0 small">Configure your business information</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible mb-4" style="background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.3);color:#6EE7B7;border-radius:8px">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form method="POST" action="{{ route('settings.business.update') }}" enctype="multipart/form-data">
    @csrf @method('PUT')

    <div class="row g-4">
        <div class="col-lg-8">
            {{-- Business Identity --}}
            <div class="card-dark mb-4">
                <h6 class="fw-semibold mb-3 text-muted small text-uppercase">Business Identity</h6>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label text-muted small">Business Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-dark"
                               value="{{ old('name', $business->name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">GSTIN</label>
                        <input type="text" name="gstin" class="form-control form-control-dark"
                               value="{{ old('gstin', $business->gstin) }}" maxlength="15" style="text-transform:uppercase">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">PAN</label>
                        <input type="text" name="pan" class="form-control form-control-dark"
                               value="{{ old('pan', $business->pan) }}" maxlength="10" style="text-transform:uppercase">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Mobile</label>
                        <input type="text" name="mobile" class="form-control form-control-dark"
                               value="{{ old('mobile', $business->mobile) }}" maxlength="10">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Email</label>
                        <input type="email" name="email" class="form-control form-control-dark"
                               value="{{ old('email', $business->email) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Website</label>
                        <input type="url" name="website" class="form-control form-control-dark"
                               value="{{ old('website', $business->website) }}" placeholder="https://...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Financial Year</label>
                        <select name="financial_year" class="form-select form-control-dark">
                            <option value="{{ date('Y') }}-{{ date('Y')+1 }}">Apr {{ date('Y') }} – Mar {{ date('Y')+1 }}</option>
                            <option value="{{ date('Y')-1 }}-{{ date('Y') }}">Apr {{ date('Y')-1 }} – Mar {{ date('Y') }}</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Address --}}
            <div class="card-dark mb-4">
                <h6 class="fw-semibold mb-3 text-muted small text-uppercase">Business Address</h6>
                <div class="row g-3">
                    <div class="col-12">
                        <textarea name="address" class="form-control form-control-dark" rows="2">{{ old('address', $business->address) }}</textarea>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="city" class="form-control form-control-dark"
                               placeholder="City" value="{{ old('city', $business->city) }}">
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="state" class="form-control form-control-dark"
                               placeholder="State" value="{{ old('state', $business->state) }}">
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="pincode" class="form-control form-control-dark"
                               placeholder="Pincode" maxlength="6" value="{{ old('pincode', $business->pincode) }}">
                    </div>
                </div>
            </div>

            {{-- Bank Details --}}
            <div class="card-dark mb-4">
                <h6 class="fw-semibold mb-3 text-muted small text-uppercase">Bank Details (shown on invoice)</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Bank Name</label>
                        <input type="text" name="bank_name" class="form-control form-control-dark"
                               value="{{ old('bank_name', $business->bank_name) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Account Number</label>
                        <input type="text" name="bank_account" class="form-control form-control-dark"
                               value="{{ old('bank_account', $business->bank_account) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">IFSC Code</label>
                        <input type="text" name="bank_ifsc" class="form-control form-control-dark"
                               value="{{ old('bank_ifsc', $business->bank_ifsc) }}" style="text-transform:uppercase">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Branch</label>
                        <input type="text" name="bank_branch" class="form-control form-control-dark"
                               value="{{ old('bank_branch', $business->bank_branch) }}">
                    </div>
                </div>
            </div>

            {{-- Invoice Settings --}}
            <div class="card-dark mb-4">
                <h6 class="fw-semibold mb-3 text-muted small text-uppercase">Invoice Settings</h6>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label text-muted small">Terms & Conditions</label>
                        <textarea name="terms" class="form-control form-control-dark" rows="3">{{ old('terms', $business->terms) }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted small">Declaration</label>
                        <textarea name="declaration" class="form-control form-control-dark" rows="2">{{ old('declaration', $business->declaration) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- Logo --}}
            <div class="card-dark mb-4">
                <h6 class="fw-semibold mb-3 text-muted small text-uppercase">Business Logo</h6>
                @if($business->logo)
                    <img src="{{ asset('storage/' . $business->logo) }}" class="img-fluid rounded mb-3" style="max-height:100px">
                @endif
                <input type="file" name="logo" class="form-control form-control-dark" accept="image/*">
                <small class="text-muted d-block mt-1">PNG or JPG, max 2MB. Shown on invoices.</small>
            </div>

            {{-- Signature --}}
            <div class="card-dark mb-4">
                <h6 class="fw-semibold mb-3 text-muted small text-uppercase">Authorized Signature</h6>
                @if($business->signature)
                    <img src="{{ asset('storage/' . $business->signature) }}" class="img-fluid rounded mb-3" style="max-height:80px">
                @endif
                <input type="file" name="signature" class="form-control form-control-dark" accept="image/*">
                <small class="text-muted d-block mt-1">PNG with transparent background preferred.</small>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Save Settings
                </button>
            </div>
        </div>
    </div>
</form>
@endsection
