{{-- resources/views/settings/custom-fields.blade.php --}}
@extends('layouts.app')
@section('title','Custom Fields')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Custom Fields</h4>
        <p class="text-muted mb-0 small">Add extra fields to products, invoices, and parties</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card-dark">
            <h6 class="fw-semibold mb-3 text-muted small text-uppercase">Add Custom Field</h6>
            <form method="POST" action="{{ route('settings.custom-fields.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label text-muted small">Module</label>
                    <select name="module" class="form-select form-control-dark" required>
                        <option value="product">Product</option>
                        <option value="invoice">Invoice</option>
                        <option value="party">Party</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small">Field Label</label>
                    <input type="text" name="label" class="form-control form-control-dark"
                           placeholder="e.g. Brand, Model No, Color" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small">Field Type</label>
                    <select name="field_type" class="form-select form-control-dark" id="fieldType">
                        <option value="text">Text</option>
                        <option value="number">Number</option>
                        <option value="date">Date</option>
                        <option value="select">Dropdown</option>
                        <option value="checkbox">Checkbox</option>
                    </select>
                </div>
                <div class="mb-3" id="optionsGroup" style="display:none">
                    <label class="form-label text-muted small">Options (comma-separated)</label>
                    <input type="text" name="options" class="form-control form-control-dark"
                           placeholder="Option 1, Option 2, Option 3">
                </div>
                <div class="mb-4 form-check">
                    <input class="form-check-input" type="checkbox" name="is_required" value="1" id="isRequired">
                    <label class="form-check-label text-muted small" for="isRequired">Required field</label>
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-plus me-2"></i>Add Field
                </button>
            </form>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card-dark">
            <h6 class="fw-semibold mb-3 text-muted small text-uppercase">Existing Custom Fields</h6>
            @if($fields->count())
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0">
                    <thead>
                        <tr class="text-muted small">
                            <th>Module</th><th>Label</th><th>Type</th><th>Required</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($fields as $field)
                        <tr>
                            <td><span class="badge bg-primary">{{ ucfirst($field->module) }}</span></td>
                            <td class="fw-semibold">{{ $field->label }}</td>
                            <td class="text-muted small">{{ ucfirst($field->field_type) }}</td>
                            <td>
                                @if($field->is_required)
                                    <i class="fas fa-check-circle text-success"></i>
                                @else
                                    <i class="fas fa-times text-muted"></i>
                                @endif
                            </td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('settings.custom-fields.delete', $field->id) }}"
                                      onsubmit="return confirm('Delete this field?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-outline-danger btn-sm"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-muted text-center py-3">No custom fields yet. Add your first one.</p>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('fieldType').addEventListener('change', function() {
    document.getElementById('optionsGroup').style.display = this.value === 'select' ? '' : 'none';
});
</script>
@endpush
