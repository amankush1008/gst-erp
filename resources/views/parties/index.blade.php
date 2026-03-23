{{-- resources/views/parties/index.blade.php --}}
@extends('layouts.app')
@section('title','Parties')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Parties</h4>
        <p class="text-muted mb-0 small">Manage customers & suppliers</p>
    </div>
    <a href="{{ route('parties.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i>Add Party
    </a>
</div>

{{-- Filters --}}
<div class="card-dark mb-3">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control form-control-dark"
                   placeholder="Search name, GSTIN, mobile..." value="{{ request('search') }}">
        </div>
        <div class="col-md-2">
            <select name="type" class="form-select form-control-dark">
                <option value="">All Types</option>
                <option value="customer" @selected(request('type')=='customer')>Customers</option>
                <option value="supplier" @selected(request('type')=='supplier')>Suppliers</option>
                <option value="both" @selected(request('type')=='both')>Both</option>
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary btn-sm w-100">Filter</button>
        </div>
        <div class="col-md-2">
            <a href="{{ route('parties.index') }}" class="btn btn-outline-secondary btn-sm w-100">Reset</a>
        </div>
    </form>
</div>

<div class="card-dark">
    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0">
            <thead>
                <tr class="text-muted small">
                    <th>#</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>GSTIN</th>
                    <th>Mobile</th>
                    <th>Balance</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($parties as $party)
                <tr>
                    <td class="text-muted small">{{ $loop->iteration }}</td>
                    <td>
                        <div class="fw-semibold">{{ $party->name }}</div>
                        @if($party->email)
                            <small class="text-muted">{{ $party->email }}</small>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $party->type === 'customer' ? 'bg-primary' : ($party->type === 'supplier' ? 'bg-warning text-dark' : 'bg-info text-dark') }}">
                            {{ ucfirst($party->type) }}
                        </span>
                    </td>
                    <td class="font-monospace small">{{ $party->gstin ?? '-' }}</td>
                    <td>{{ $party->mobile ?? '-' }}</td>
                    <td>
                        @php $bal = $party->balance ?? 0; @endphp
                        <span class="{{ $bal > 0 ? 'text-success' : ($bal < 0 ? 'text-danger' : 'text-muted') }} fw-semibold">
                            ₹{{ number_format(abs($bal), 2) }}
                            @if($bal > 0) <small class="text-muted">(You'll receive)</small>
                            @elseif($bal < 0) <small class="text-muted">(You owe)</small>
                            @endif
                        </span>
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('parties.ledger', $party) }}" class="btn btn-outline-info" title="Ledger">
                                <i class="fas fa-book"></i>
                            </a>
                            <a href="{{ route('parties.edit', $party) }}" class="btn btn-outline-primary" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('parties.destroy', $party) }}" class="d-inline"
                                  onsubmit="return confirm('Delete this party?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-outline-danger" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No parties found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $parties->links() }}</div>
</div>
@endsection
