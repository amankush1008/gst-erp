{{-- resources/views/products/index.blade.php --}}
@extends('layouts.app')
@section('title','Products')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Products</h4>
        <p class="text-muted mb-0 small">Manage your inventory items</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i>Add Product
        </a>
    </div>
</div>

{{-- Stat cards --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(79,70,229,.15);color:#818CF8">
                <i class="fas fa-boxes"></i>
            </div>
            <div class="stat-value">{{ $stats['total'] ?? 0 }}</div>
            <div class="stat-label">Total Products</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(16,185,129,.15);color:#34D399">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-value">{{ $stats['active'] ?? 0 }}</div>
            <div class="stat-label">Active</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(245,158,11,.15);color:#FCD34D">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-value">{{ $stats['low_stock'] ?? 0 }}</div>
            <div class="stat-label">Low Stock</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(99,102,241,.15);color:#A5B4FC">
                <i class="fas fa-rupee-sign"></i>
            </div>
            <div class="stat-value">₹{{ number_format($stats['stock_value'] ?? 0) }}</div>
            <div class="stat-label">Stock Value</div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card-dark mb-3">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control form-control-dark"
                   placeholder="Search by name, SKU, HSN..." value="{{ request('search') }}">
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
            <select name="low_stock" class="form-select form-control-dark">
                <option value="">All Stock</option>
                <option value="1" @selected(request('low_stock'))>Low Stock Only</option>
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary btn-sm w-100">Filter</button>
        </div>
        <div class="col-md-2">
            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-sm w-100">Reset</a>
        </div>
    </form>
</div>

{{-- Table --}}
<div class="card-dark">
    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0">
            <thead>
                <tr class="text-muted small">
                    <th>#</th>
                    <th>Product</th>
                    <th>SKU / HSN</th>
                    <th>GST %</th>
                    <th>Sale Price</th>
                    <th>Purchase Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr>
                    <td class="text-muted small">{{ $loop->iteration }}</td>
                    <td>
                        <div class="fw-semibold">{{ $product->name }}</div>
                        @if($product->unit)
                            <small class="text-muted">{{ $product->unit->symbol ?? '' }}</small>
                        @endif
                    </td>
                    <td>
                        <div class="small">{{ $product->sku ?? '-' }}</div>
                        <div class="small text-muted">HSN: {{ $product->hsn_code ?? '-' }}</div>
                    </td>
                    <td>
                        <span class="badge" style="background:#1E3A5F;color:#93C5FD">{{ $product->gst_rate }}%</span>
                    </td>
                    <td class="fw-semibold">₹{{ number_format($product->sale_price, 2) }}</td>
                    <td class="text-muted">₹{{ number_format($product->purchase_price ?? 0, 2) }}</td>
                    <td>
                        @php $stock = $product->currentStock(); @endphp
                        <span class="{{ $stock <= ($product->min_stock ?? 0) && $stock > 0 ? 'text-warning' : ($stock == 0 ? 'text-danger' : 'text-success') }} fw-semibold">
                            {{ $stock }}
                        </span>
                        @if($stock <= ($product->min_stock ?? 0))
                            <i class="fas fa-exclamation-circle text-warning ms-1" title="Low Stock"></i>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $product->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $product->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="{{ route('products.barcode', $product) }}" class="btn btn-outline-secondary" target="_blank">
                                <i class="fas fa-barcode"></i>
                            </a>
                            <button type="button" class="btn btn-outline-warning"
                                    onclick="adjustStockModal({{ $product->id }}, '{{ $product->name }}')">
                                <i class="fas fa-layer-group"></i>
                            </button>
                            <form method="POST" action="{{ route('products.destroy', $product) }}" class="d-inline"
                                  onsubmit="return confirm('Delete this product?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-muted py-4">No products found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">
        {{ $products->links() }}
    </div>
</div>

{{-- Adjust Stock Modal --}}
<div class="modal fade" id="stockModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:#1E293B;border:1px solid #334155">
            <div class="modal-header border-secondary">
                <h5 class="modal-title">Adjust Stock</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="stockForm">
                @csrf
                <div class="modal-body">
                    <p class="text-muted" id="stockProductName"></p>
                    <div class="mb-3">
                        <label class="form-label text-muted small">Type</label>
                        <select name="type" class="form-select form-control-dark">
                            <option value="add">Add Stock (In)</option>
                            <option value="remove">Remove Stock (Out)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small">Quantity</label>
                        <input type="number" name="quantity" class="form-control form-control-dark" min="0.01" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small">Reason</label>
                        <input type="text" name="reason" class="form-control form-control-dark" placeholder="e.g. Physical count, damage...">
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Adjust</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function adjustStockModal(id, name) {
    document.getElementById('stockProductName').textContent = name;
    document.getElementById('stockForm').action = `/products/${id}/adjust-stock`;
    new bootstrap.Modal(document.getElementById('stockModal')).show();
}
</script>
@endpush
