{{-- resources/views/settings/backup.blade.php --}}
@extends('layouts.app')
@section('title','Backup & Data')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Backup & Data</h4>
        <p class="text-muted mb-0 small">Export, import or reset business data</p>
    </div>
</div>

<div class="row g-4">
    {{-- Export --}}
    <div class="col-md-6">
        <div class="card-dark h-100">
            <div class="mb-3">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="d-flex align-items-center justify-content-center rounded"
                         style="width:48px;height:48px;background:rgba(16,185,129,.15)">
                        <i class="fas fa-file-export text-success fs-5"></i>
                    </div>
                    <div>
                        <h6 class="fw-semibold mb-0">Export Data</h6>
                        <small class="text-muted">Download your business data as Excel/CSV</small>
                    </div>
                </div>
            </div>
            <div class="d-grid gap-2">
                <a href="{{ route('reports.export', 'sales') }}" class="btn btn-outline-success btn-sm">
                    <i class="fas fa-file-excel me-2"></i>Export All Invoices
                </a>
                <a href="{{ route('reports.export', 'purchases') }}" class="btn btn-outline-success btn-sm">
                    <i class="fas fa-file-excel me-2"></i>Export Purchases
                </a>
                <a href="{{ route('reports.export', 'stock') }}" class="btn btn-outline-success btn-sm">
                    <i class="fas fa-file-excel me-2"></i>Export Stock Report
                </a>
                <a href="{{ route('reports.export', 'gstr1') }}" class="btn btn-outline-success btn-sm">
                    <i class="fas fa-file-excel me-2"></i>Export GSTR-1
                </a>
            </div>
        </div>
    </div>

    {{-- Data Management --}}
    <div class="col-md-6">
        <div class="card-dark h-100">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="d-flex align-items-center justify-content-center rounded"
                     style="width:48px;height:48px;background:rgba(239,68,68,.15)">
                    <i class="fas fa-database text-danger fs-5"></i>
                </div>
                <div>
                    <h6 class="fw-semibold mb-0">Data Management</h6>
                    <small class="text-muted">Manage your business data</small>
                </div>
            </div>

            <div class="p-3 rounded mb-3" style="background:#0F172A;border:1px solid rgba(239,68,68,.3)">
                <p class="text-warning small mb-2"><i class="fas fa-exclamation-triangle me-1"></i>
                    <strong>Danger Zone</strong>
                </p>
                <p class="text-muted small mb-3">
                    These actions are irreversible. Proceed with caution.
                </p>
                <button type="button" class="btn btn-outline-danger btn-sm w-100"
                        onclick="if(confirm('This will delete ALL invoices for this business. Are you absolutely sure? Type DELETE to confirm.') && prompt('Type DELETE to confirm') === 'DELETE') { alert('Feature coming soon — contact support to reset data.') }">
                    <i class="fas fa-trash me-2"></i>Reset All Invoices
                </button>
            </div>

            <div class="p-3 rounded" style="background:#0F172A;border:1px solid #334155">
                <p class="text-muted small mb-2"><i class="fas fa-info-circle me-1"></i>Database Info</p>
                <div class="small text-muted">
                    <div class="d-flex justify-content-between">
                        <span>Connection</span>
                        <span class="text-light">MySQL</span>
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        <span>Timezone</span>
                        <span class="text-light">Asia/Kolkata</span>
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        <span>Financial Year</span>
                        <span class="text-light">{{ currentBusiness()?->financial_year ?? '—' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
