{{-- resources/views/settings/number-formats.blade.php --}}
@extends('layouts.app')
@section('title','Number Formats')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Invoice Number Formats</h4>
        <p class="text-muted mb-0 small">Customize how invoice numbers are generated</p>
    </div>
</div>

@foreach([
    'invoice'         => 'Sales Invoice',
    'purchase'        => 'Purchase Bill',
    'credit_note'     => 'Credit Note',
    'debit_note'      => 'Debit Note',
    'proforma'        => 'Proforma Invoice',
    'payment_receipt' => 'Payment Receipt',
] as $module => $label)
@php $seq = $sequences->firstWhere('module', $module); @endphp
<div class="card-dark mb-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-semibold mb-0">{{ $label }}</h6>
        <small class="text-muted">Preview: <span class="font-monospace text-primary" id="preview_{{ $module }}">
            {{ ($seq->prefix ?? strtoupper(substr($module,0,3))) }}{{ date('Y') }}{{ str_pad(1, $seq->padding ?? 4, '0', STR_PAD_LEFT) }}{{ $seq->suffix ?? '' }}
        </span></small>
    </div>
    <form method="POST" action="{{ route('settings.number-formats.update') }}" class="row g-3 align-items-end">
        @csrf
        <input type="hidden" name="module" value="{{ $module }}">
        <div class="col-md-3">
            <label class="form-label text-muted small">Prefix</label>
            <input type="text" name="prefix" class="form-control form-control-dark"
                   value="{{ $seq->prefix ?? strtoupper(substr($module,0,3)) }}"
                   placeholder="e.g. INV-" maxlength="20">
        </div>
        <div class="col-md-3">
            <label class="form-label text-muted small">Starting Number</label>
            <input type="number" name="current_number" class="form-control form-control-dark"
                   value="{{ $seq->current_number ?? 1 }}" min="1">
        </div>
        <div class="col-md-2">
            <label class="form-label text-muted small">Padding (digits)</label>
            <input type="number" name="padding" class="form-control form-control-dark"
                   value="{{ $seq->padding ?? 4 }}" min="1" max="10">
        </div>
        <div class="col-md-2">
            <label class="form-label text-muted small">Suffix</label>
            <input type="text" name="suffix" class="form-control form-control-dark"
                   value="{{ $seq->suffix ?? '' }}" maxlength="20">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary btn-sm w-100">Save</button>
        </div>
    </form>
</div>
@endforeach
@endsection
