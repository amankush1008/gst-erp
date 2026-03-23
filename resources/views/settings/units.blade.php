{{-- resources/views/settings/units.blade.php --}}
@extends('layouts.app')
@section('title','Units of Measurement')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Units of Measurement</h4>
</div>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card-dark">
            <h6 class="fw-semibold mb-3 text-muted small text-uppercase">Add Unit</h6>
            <form method="POST" action="{{ route('settings.units.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label text-muted small">Unit Name</label>
                    <input type="text" name="name" class="form-control form-control-dark" placeholder="e.g. Kilogram" required>
                </div>
                <div class="mb-4">
                    <label class="form-label text-muted small">Symbol</label>
                    <input type="text" name="symbol" class="form-control form-control-dark" placeholder="e.g. KG" required>
                </div>
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-plus me-2"></i>Add Unit</button>
            </form>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card-dark">
            <h6 class="fw-semibold mb-3 text-muted small text-uppercase">Units List</h6>
            <div class="row g-2">
                @forelse($units as $unit)
                <div class="col-md-4">
                    <div class="p-2 rounded d-flex justify-content-between align-items-center"
                         style="background:#0F172A;border:1px solid #334155">
                        <div>
                            <div class="fw-semibold">{{ $unit->name }}</div>
                            <small class="text-muted">{{ $unit->symbol }}</small>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12"><p class="text-muted text-center py-3">No units added yet</p></div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
