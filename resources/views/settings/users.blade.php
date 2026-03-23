{{-- resources/views/settings/users.blade.php --}}
@extends('layouts.app')
@section('title','Users')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Users & Access</h4>
        <p class="text-muted mb-0 small">Manage team members for this business</p>
    </div>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#inviteModal">
        <i class="fas fa-user-plus me-1"></i>Invite User
    </button>
</div>

<div class="card-dark">
    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0">
            <thead>
                <tr class="text-muted small">
                    <th>Name</th><th>Email</th><th>Mobile</th><th>Role</th><th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="d-flex align-items-center justify-content-center rounded-circle"
                                 style="width:32px;height:32px;background:linear-gradient(135deg,#4F46E5,#7C3AED);font-size:13px;color:#fff;font-weight:700;flex-shrink:0">
                                {{ strtoupper(substr($u->name, 0, 1)) }}
                            </div>
                            <div class="fw-semibold">{{ $u->name }}
                                @if($u->id === auth()->id())
                                    <span class="badge bg-secondary ms-1">You</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="text-muted small">{{ $u->email }}</td>
                    <td class="text-muted small">{{ $u->mobile ?? '-' }}</td>
                    <td>
                        @php $roleColors = ['admin'=>'danger','accountant'=>'warning','staff'=>'primary','viewer'=>'secondary']; @endphp
                        <span class="badge bg-{{ $roleColors[$u->role] ?? 'secondary' }}">{{ ucfirst($u->role) }}</span>
                    </td>
                    <td>
                        <span class="badge {{ $u->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $u->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="text-end">
                        @if($u->id !== auth()->id())
                        <button class="btn btn-outline-primary btn-sm" title="Edit role (coming soon)" disabled>
                            <i class="fas fa-edit"></i>
                        </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No users found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Role permissions reference --}}
<div class="card-dark mt-4">
    <h6 class="fw-semibold mb-3 text-muted small text-uppercase">Role Permissions</h6>
    <div class="row g-3">
        @foreach([
            'admin'      => ['All access', 'Settings', 'Users', 'Reports', 'Delete'],
            'accountant' => ['Invoices', 'Payments', 'Reports', 'Parties', 'Purchases'],
            'staff'      => ['Invoices (create)', 'Products', 'Parties'],
            'viewer'     => ['Reports (read-only)', 'Dashboard'],
        ] as $role => $perms)
        <div class="col-md-3">
            <div class="p-3 rounded" style="background:#0F172A;border:1px solid #334155">
                <div class="fw-semibold mb-2">{{ ucfirst($role) }}</div>
                @foreach($perms as $perm)
                    <div class="small text-muted"><i class="fas fa-check text-success me-1"></i>{{ $perm }}</div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- Invite Modal --}}
<div class="modal fade" id="inviteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:#1E293B;border:1px solid #334155">
            <div class="modal-header border-secondary">
                <h5 class="modal-title">Invite Team Member</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('settings.users') }}">
                @csrf
                <div class="modal-body">
                    <p class="text-muted small mb-3">They'll receive an email to set up their password.</p>
                    <div class="mb-3">
                        <label class="form-label text-muted small">Name</label>
                        <input type="text" name="name" class="form-control form-control-dark" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small">Email</label>
                        <input type="email" name="email" class="form-control form-control-dark" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small">Role</label>
                        <select name="role" class="form-select form-control-dark">
                            <option value="staff">Staff</option>
                            <option value="accountant">Accountant</option>
                            <option value="viewer">Viewer (read-only)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Send Invite</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
