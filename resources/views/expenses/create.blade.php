{{-- resources/views/expenses/create.blade.php --}}
@extends('layouts.app')
@section('title','Add Expense')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Add Expense</h4>
        <p class="text-muted mb-0 small">Record a business expense</p>
    </div>
    <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Back
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card-dark">
            <form method="POST" action="{{ route('expenses.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label text-muted small">Category <span class="text-danger">*</span></label>
                    <div class="d-flex gap-2">
                        <select name="category_id" class="form-select form-control-dark" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted small">Description <span class="text-danger">*</span></label>
                    <textarea name="description" class="form-control form-control-dark" rows="2"
                              placeholder="What was this expense for?" required>{{ old('description') }}</textarea>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Amount (₹) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control form-control-dark"
                               min="0.01" step="0.01" value="{{ old('amount') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Date <span class="text-danger">*</span></label>
                        <input type="date" name="expense_date" class="form-control form-control-dark"
                               value="{{ old('expense_date', date('Y-m-d')) }}" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted small">Payment Mode</label>
                    <div class="row g-2">
                        @foreach(['cash'=>'Cash','bank'=>'Bank','upi'=>'UPI','cheque'=>'Cheque'] as $val => $label)
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="payment_mode" id="mode_{{ $val }}"
                                   value="{{ $val }}" @checked($val === 'cash' || old('payment_mode') === $val)>
                            <label class="btn btn-outline-secondary w-100" for="mode_{{ $val }}">{{ $label }}</label>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted small">Reference</label>
                    <input type="text" name="reference" class="form-control form-control-dark"
                           placeholder="Bill no, cheque no, UTR..." value="{{ old('reference') }}">
                </div>

                <div class="mb-4">
                    <label class="form-label text-muted small">Receipt / Bill Image</label>
                    <input type="file" name="receipt" class="form-control form-control-dark"
                           accept="image/*,.pdf">
                    <small class="text-muted">JPG, PNG or PDF, max 5MB</small>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-save me-2"></i>Save Expense
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
