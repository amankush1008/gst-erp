{{-- resources/views/settings/warehouses.blade.php --}}
@extends('layouts.app')
@section('title','Warehouses')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Warehouses</h4>
</div>
<div class="row g-4">
    <div class="col-lg-5">
        <div class="card-dark">
            <h6 class="fw-semibold mb-3 text-muted small text-uppercase">Add Warehouse</h6>
            <form method="POST" action="{{ route('settings.warehouses.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label text-muted small">Name</label>
                    <input type="text" name="name" class="form-control form-control-dark" placeholder="e.g. Main Godown" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small">Address</label>
                    <textarea name="address" class="form-control form-control-dark" rows="2"></textarea>
                </div>
                <div class="mb-4 form-check">
                    <input class="form-check-input" type="checkbox" name="is_default" value="1" id="isDefault">
                    <label class="form-check-label text-muted small" for="isDefault">Set as default warehouse</label>
                </div>
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-plus me-2"></i>Add</button>
            </form>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card-dark">
            <h6 class="fw-semibold mb-3 text-muted small text-uppercase">Warehouses</h6>
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0">
                    <thead><tr class="text-muted small"><th>Name</th><th>Address</th><th>Default</th></tr></thead>
                    <tbody>
                        @forelse($warehouses as $w)
                        <tr>
                            <td class="fw-semibold">{{ $w->name }}</td>
                            <td class="text-muted small">{{ $w->address ?? '-' }}</td>
                            <td>@if($w->is_default)<span class="badge bg-success">Default</span>@endif</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">No warehouses added</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
