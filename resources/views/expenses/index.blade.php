{{-- resources/views/expenses/index.blade.php --}}
@extends('layouts.app')
@section('title','Expenses')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Expenses</h4>
        <p class="text-muted mb-0 small">Track business expenses</p>
    </div>
    <a href="{{ route('expenses.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i>Add Expense
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(239,68,68,.15);color:#FCA5A5"><i class="fas fa-receipt"></i></div>
            <div class="stat-value text-danger">₹{{ number_format($totalExpense, 2) }}</div>
            <div class="stat-label">This Month's Expenses</div>
        </div>
    </div>
</div>

<div class="card-dark mb-3">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-3">
            <select name="category_id" class="form-select form-control-dark">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <input type="date" name="date_from" class="form-control form-control-dark" value="{{ request('date_from') }}">
        </div>
        <div class="col-md-2">
            <input type="date" name="date_to" class="form-control form-control-dark" value="{{ request('date_to') }}">
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary btn-sm w-100">Filter</button>
        </div>
    </form>
</div>

<div class="card-dark">
    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0">
            <thead>
                <tr class="text-muted small">
                    <th>Date</th><th>Category</th><th>Description</th>
                    <th>Amount</th><th>Mode</th><th>Receipt</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $expense)
                <tr>
                    <td class="text-muted small">{{ \Carbon\Carbon::parse($expense->expense_date)->format('d M Y') }}</td>
                    <td>
                        <span class="badge" style="background:#1E3A5F;color:#93C5FD">
                            {{ $expense->category->name ?? '-' }}
                        </span>
                    </td>
                    <td>{{ $expense->description }}</td>
                    <td class="fw-semibold text-danger">₹{{ number_format($expense->amount, 2) }}</td>
                    <td class="text-muted small">{{ ucfirst($expense->payment_mode ?? 'cash') }}</td>
                    <td>
                        @if($expense->receipt_path)
                            <a href="{{ asset('storage/'.$expense->receipt_path) }}" target="_blank" class="text-primary small">
                                <i class="fas fa-file me-1"></i>View
                            </a>
                        @else
                            <span class="text-muted small">-</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <form method="POST" action="{{ route('expenses.destroy', $expense) }}" class="d-inline"
                              onsubmit="return confirm('Delete this expense?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-outline-danger btn-sm"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No expenses recorded</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $expenses->links() }}</div>
</div>
@endsection
