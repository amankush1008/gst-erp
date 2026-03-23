{{-- resources/views/products/create.blade.php --}}
@extends('layouts.app')
@section('title', isset($product) ? 'Edit Product' : 'Add Product')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">{{ isset($product) ? 'Edit Product' : 'Add New Product' }}</h4>
        <p class="text-muted mb-0 small">Fill in the product details below</p>
    </div>
    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Back
    </a>
</div>

<form method="POST" action="{{ isset($product) ? route('products.update', $product) : route('products.store') }}"
      enctype="multipart/form-data">
    @csrf
    @if(isset($product)) @method('PUT') @endif

    <div class="row g-4">
        {{-- Left Column --}}
        <div class="col-lg-8">
            {{-- Basic Info --}}
            <div class="card-dark mb-4">
                <h6 class="fw-semibold mb-3 text-muted small text-uppercase">Basic Information</h6>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label text-muted small">Product Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-dark @error('name') is-invalid @enderror"
                               value="{{ old('name', $product->name ?? '') }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">SKU / Item Code</label>
                        <input type="text" name="sku" class="form-control form-control-dark"
                               value="{{ old('sku', $product->sku ?? '') }}" placeholder="Auto-generated if empty">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">HSN Code</label>
                        <input type="text" name="hsn_code" class="form-control form-control-dark"
                               value="{{ old('hsn_code', $product->hsn_code ?? '') }}" placeholder="e.g. 8471">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Barcode / UPC</label>
                        <input type="text" name="barcode" class="form-control form-control-dark"
                               value="{{ old('barcode', $product->barcode ?? '') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Category</label>
                        <select name="category_id" class="form-select form-control-dark">
                            <option value="">Select Category</option>
                            @foreach($categories ?? [] as $cat)
                                <option value="{{ $cat->id }}" @selected(old('category_id', $product->category_id ?? '') == $cat->id)>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted small">Description</label>
                        <textarea name="description" class="form-control form-control-dark" rows="2"
                                  placeholder="Optional product description">{{ old('description', $product->description ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Pricing & Tax --}}
            <div class="card-dark mb-4">
                <h6 class="fw-semibold mb-3 text-muted small text-uppercase">Pricing & Tax</h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Sale Price (₹) <span class="text-danger">*</span></label>
                        <input type="number" name="sale_price" class="form-control form-control-dark"
                               value="{{ old('sale_price', $product->sale_price ?? '') }}"
                               min="0" step="0.01" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Purchase Price (₹)</label>
                        <input type="number" name="purchase_price" class="form-control form-control-dark"
                               value="{{ old('purchase_price', $product->purchase_price ?? '') }}"
                               min="0" step="0.01">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small">MRP (₹)</label>
                        <input type="number" name="mrp" class="form-control form-control-dark"
                               value="{{ old('mrp', $product->mrp ?? '') }}" min="0" step="0.01">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small">GST Rate (%) <span class="text-danger">*</span></label>
                        <select name="gst_rate" class="form-select form-control-dark" required>
                            @foreach(gstRates() as $rate)
                                <option value="{{ $rate }}" @selected(old('gst_rate', $product->gst_rate ?? 18) == $rate)>
                                    {{ $rate }}%
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Price Inclusive of Tax?</label>
                        <select name="price_includes_tax" class="form-select form-control-dark">
                            <option value="0" @selected(!($product->price_includes_tax ?? false))>Exclusive (+ GST)</option>
                            <option value="1" @selected($product->price_includes_tax ?? false)>Inclusive (incl. GST)</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Cess (%)</label>
                        <input type="number" name="cess_rate" class="form-control form-control-dark"
                               value="{{ old('cess_rate', $product->cess_rate ?? 0) }}" min="0" step="0.01">
                    </div>
                </div>
            </div>

            {{-- Stock --}}
            <div class="card-dark mb-4">
                <h6 class="fw-semibold mb-3 text-muted small text-uppercase">Stock & Unit</h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Unit <span class="text-danger">*</span></label>
                        <select name="unit_id" class="form-select form-control-dark" required>
                            <option value="">Select Unit</option>
                            @foreach($units ?? [] as $unit)
                                <option value="{{ $unit->id }}" @selected(old('unit_id', $product->unit_id ?? '') == $unit->id)>
                                    {{ $unit->name }} ({{ $unit->symbol }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @if(!isset($product))
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Opening Stock</label>
                        <input type="number" name="opening_stock" class="form-control form-control-dark"
                               value="{{ old('opening_stock', 0) }}" min="0" step="0.01">
                    </div>
                    @endif
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Min Stock (Alert Threshold)</label>
                        <input type="number" name="min_stock" class="form-control form-control-dark"
                               value="{{ old('min_stock', $product->min_stock ?? 0) }}" min="0">
                    </div>
                </div>
            </div>

            {{-- Custom Fields --}}
            @if(isset($customFields) && $customFields->count())
            <div class="card-dark mb-4">
                <h6 class="fw-semibold mb-3 text-muted small text-uppercase">Custom Fields</h6>
                <div class="row g-3">
                    @foreach($customFields as $field)
                    <div class="col-md-6">
                        <label class="form-label text-muted small">
                            {{ $field->label }}
                            @if($field->is_required) <span class="text-danger">*</span> @endif
                        </label>
                        @if($field->field_type === 'select')
                            <select name="custom_fields[{{ $field->field_name }}]" class="form-select form-control-dark"
                                    @if($field->is_required) required @endif>
                                <option value="">Select...</option>
                                @foreach(json_decode($field->options ?? '[]') as $opt)
                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                @endforeach
                            </select>
                        @elseif($field->field_type === 'checkbox')
                            <div class="form-check mt-2">
                                <input type="checkbox" name="custom_fields[{{ $field->field_name }}]" class="form-check-input" value="1">
                            </div>
                        @else
                            <input type="{{ $field->field_type === 'number' ? 'number' : ($field->field_type === 'date' ? 'date' : 'text') }}"
                                   name="custom_fields[{{ $field->field_name }}]"
                                   class="form-control form-control-dark"
                                   @if($field->is_required) required @endif>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Right Column --}}
        <div class="col-lg-4">
            {{-- Status --}}
            <div class="card-dark mb-4">
                <h6 class="fw-semibold mb-3 text-muted small text-uppercase">Status</h6>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive"
                           @checked(old('is_active', $product->is_active ?? true))>
                    <label class="form-check-label text-muted" for="isActive">Product is Active</label>
                </div>
            </div>

            {{-- Image --}}
            <div class="card-dark mb-4">
                <h6 class="fw-semibold mb-3 text-muted small text-uppercase">Product Image</h6>
                @if(isset($product) && $product->image)
                    <img src="{{ asset('storage/' . $product->image) }}" class="img-fluid rounded mb-3" alt="">
                @endif
                <input type="file" name="image" class="form-control form-control-dark" accept="image/*">
                <small class="text-muted">JPG, PNG up to 2MB</small>
            </div>

            {{-- Actions --}}
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>{{ isset($product) ? 'Update Product' : 'Save Product' }}
                </button>
                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </div>
    </div>
</form>
@endsection
