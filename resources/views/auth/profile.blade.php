{{-- resources/views/auth/profile.blade.php --}}
@extends('layouts.app')
@section('title','My Profile')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">My Profile</h4>
        <p class="text-muted mb-0 small">Update your personal information</p>
    </div>
</div>

@if(session('success'))
    <div class="alert mb-4" style="background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.3);color:#6EE7B7;border-radius:8px">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    </div>
@endif

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card-dark">
            <div class="text-center mb-4">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                     style="width:72px;height:72px;background:linear-gradient(135deg,#4F46E5,#7C3AED);font-size:28px;color:#fff;font-weight:700">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <div class="fw-bold fs-5">{{ $user->name }}</div>
                    <span class="badge bg-primary">{{ ucfirst($user->role) }}</span>
                </div>
            </div>

            <form method="POST" action="{{ route('profile.update') }}">
                @csrf @method('PUT')

                <div class="mb-3">
                    <label class="form-label text-muted small">Full Name</label>
                    <input type="text" name="name" class="form-control form-control-dark"
                           value="{{ old('name', $user->name) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small">Email</label>
                    <input type="email" class="form-control form-control-dark"
                           value="{{ $user->email }}" readonly style="opacity:.5">
                    <small class="text-muted">Email cannot be changed</small>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small">Mobile</label>
                    <input type="text" name="mobile" class="form-control form-control-dark"
                           value="{{ old('mobile', $user->mobile) }}" maxlength="10">
                </div>

                <hr style="border-color:#334155;margin:1.5rem 0">
                <p class="text-muted small mb-3">Change Password <span class="text-secondary">(leave blank to keep current)</span></p>

                <div class="mb-3">
                    <label class="form-label text-muted small">New Password</label>
                    <input type="password" name="password" class="form-control form-control-dark"
                           placeholder="Minimum 8 characters" minlength="8">
                </div>
                <div class="mb-4">
                    <label class="form-label text-muted small">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control form-control-dark"
                           placeholder="Confirm new password">
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-save me-2"></i>Save Changes
                </button>
            </form>
        </div>

        {{-- Business Switcher --}}
        @php $businesses = auth()->user()->businesses; @endphp
        @if($businesses->count() > 1)
        <div class="card-dark mt-4">
            <h6 class="fw-semibold mb-3 text-muted small text-uppercase">Switch Business</h6>
            @foreach($businesses as $biz)
            <form method="POST" action="{{ route('business.switch') }}" class="d-flex align-items-center justify-content-between mb-2">
                @csrf
                <input type="hidden" name="business_id" value="{{ $biz->id }}">
                <div>
                    <div class="fw-semibold">{{ $biz->name }}</div>
                    <small class="text-muted">{{ $biz->gstin ?? 'No GSTIN' }}</small>
                </div>
                @if(currentBusiness()?->id === $biz->id)
                    <span class="badge bg-success">Active</span>
                @else
                    <button class="btn btn-outline-primary btn-sm">Switch</button>
                @endif
            </form>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection
