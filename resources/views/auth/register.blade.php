{{-- resources/views/auth/register.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register – GST ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        :root { --primary: #4F46E5; }
        body { background: linear-gradient(135deg, #0F172A 0%, #1E1B4B 100%); min-height: 100vh; display: flex; align-items: center; padding: 2rem 0; }
        .auth-card { background: #1E293B; border: 1px solid #334155; border-radius: 16px; padding: 2.5rem; max-width: 500px; width: 100%; box-shadow: 0 25px 50px rgba(0,0,0,.5); }
        .brand-logo { font-size: 1.8rem; font-weight: 800; color: var(--primary); }
        .form-control { background: #0F172A; border: 1px solid #334155; color: #F1F5F9; border-radius: 8px; }
        .form-control:focus { background: #0F172A; border-color: var(--primary); color: #F1F5F9; box-shadow: 0 0 0 3px rgba(79,70,229,.2); }
        .form-label { color: #94A3B8; font-size: .875rem; }
        .btn-primary { background: var(--primary); border: none; border-radius: 8px; padding: .75rem; font-weight: 600; }
        a { color: var(--primary); }
        .divider { border-color: #334155; }
        .section-title { color: #64748B; font-size: .75rem; text-transform: uppercase; letter-spacing: .1em; font-weight: 600; }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="auth-card mx-auto">
                <div class="text-center mb-4">
                    <div class="brand-logo mb-1">⚡ GST ERP</div>
                    <p class="text-secondary mb-0">Create your free account</p>
                </div>

                @if ($errors->any())
                    <div class="alert" style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#FCA5A5;border-radius:8px;" class="mb-3">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <p class="section-title mb-3">Your Details</p>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mobile</label>
                            <input type="text" name="mobile" class="form-control" value="{{ old('mobile') }}" maxlength="10" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>

                    <hr class="divider">
                    <p class="section-title mb-3">Business Details</p>
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <label class="form-label">Business Name</label>
                            <input type="text" name="business_name" class="form-control" value="{{ old('business_name') }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">GSTIN <span class="text-secondary">(optional)</span></label>
                            <input type="text" name="gstin" class="form-control" value="{{ old('gstin') }}"
                                   maxlength="15" placeholder="22AAAAA0000A1Z5">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-rocket me-2"></i>Create Account & Start Free
                    </button>
                </form>

                <hr class="divider my-4">
                <p class="text-center text-secondary small mb-0">
                    Already have an account? <a href="{{ route('login') }}">Sign in</a>
                </p>
            </div>
        </div>
    </div>
</div>
</body>
</html>
