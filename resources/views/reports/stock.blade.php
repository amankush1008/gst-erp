{{-- resources/views/reports/stock.blade.php --}}
@extends('layouts.app')
@section('title','Stock Report')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Stock Report</h4>
        <p class="text-muted mb-0 small">Current inventory levels</p>
    </div>
    <a href="{{ route('reports.export', 'stock') }}" class="btn btn-outline-success btn-sm">
        <i class="fas fa-file-excel me-1"></i>Export
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-label">Total Products</div>
            <div class="stat-value">{{ $summary['total_products'] ?? 0 }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-label">Stock Value (Purchase)</div>
            <div class="stat-value">₹{{ number_format($summary['total_value'] ?? 0) }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-label">Low Stock Items</div>
            <div class="stat-value text-warning">{{ $summary['low_stock'] ?? 0 }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-label">Out of Stock</div>
            <div class="stat-value text-danger">{{ $summary['out_of_stock'] ?? 0 }}</div>
        </div>
    </div>
</div>

<div class="card-dark mb-3">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-3">
            <input type="text" name="search" class="form-control form-control-dark"
                   placeholder="Search product..." value="{{ request('search') }}">
        </div>
        <div class="col-md-2">
            <select name="category_id" class="form-select form-control-dark">
                <option value="">All Categories</option>
                @foreach($categories ?? [] as $cat)
                    <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="stock_filter" class="form-select form-control-dark">
                <option value="">All</option>
                <option value="low" @selected(request('stock_filter')=='low')>Low Stock</option>
                <option value="out" @selected(request('stock_filter')=='out')>Out of Stock</option>
            </select>
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
                    <th>Product</th><th>SKU</th><th>Category</th><th>Unit</th>
                    <th class="text-end">In</th><th class="text-end">Out</th>
                    <th class="text-end">Current Stock</th>
                    <th class="text-end">Value (₹)</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stocks ?? [] as $item)
                @php
                    $current = $item->current_stock ?? 0;
                    $min     = $item->min_stock ?? 0;
                    $status  = $current <= 0 ? 'danger' : ($current <= $min ? 'warning' : 'success');
                    $label   = $current <= 0 ? 'Out' : ($current <= $min ? 'Low' : 'OK');
                @endphp
                <tr>
                    <td class="fw-semibold">{{ $item->name }}</td>
                    <td class="small font-monospace text-muted">{{ $item->sku ?? '-' }}</td>
                    <td class="small text-muted">{{ $item->category_name ?? '-' }}</td>
                    <td class="small text-muted">{{ $item->unit_symbol ?? '-' }}</td>
                    <td class="text-end text-success">{{ number_format($item->total_in ?? 0, 2) }}</td>
                    <td class="text-end text-danger">{{ number_format($item->total_out ?? 0, 2) }}</td>
                    <td class="text-end fw-bold text-{{ $status }}">{{ number_format($current, 2) }}</td>
                    <td class="text-end">₹{{ number_format($current * ($item->purchase_price ?? 0), 2) }}</td>
                    <td><span class="badge bg-{{ $status }}">{{ $label }}</span></td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-muted py-4">No stock data</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
