{{-- resources/views/auth/login.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – GST ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        :root { --primary: #4F46E5; --primary-dark: #4338CA; }
        body { background: linear-gradient(135deg, #0F172A 0%, #1E1B4B 100%); min-height: 100vh; display: flex; align-items: center; }
        .auth-card { background: #1E293B; border: 1px solid #334155; border-radius: 16px; padding: 2.5rem; max-width: 420px; width: 100%; box-shadow: 0 25px 50px rgba(0,0,0,.5); }
        .brand-logo { font-size: 2rem; font-weight: 800; color: var(--primary); letter-spacing: -1px; }
        .form-control { background: #0F172A; border: 1px solid #334155; color: #F1F5F9; border-radius: 8px; padding: .75rem 1rem; }
        .form-control:focus { background: #0F172A; border-color: var(--primary); color: #F1F5F9; box-shadow: 0 0 0 3px rgba(79,70,229,.2); }
        .form-label { color: #94A3B8; font-size: .875rem; }
        .btn-primary { background: var(--primary); border: none; border-radius: 8px; padding: .75rem; font-weight: 600; }
        .btn-primary:hover { background: var(--primary-dark); }
        .divider { border-color: #334155; }
        a { color: var(--primary); }
        .alert-danger { background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.3); color: #FCA5A5; border-radius: 8px; }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="auth-card mx-auto">
                <div class="text-center mb-4">
                    <div class="brand-logo mb-1">⚡ GST ERP</div>
                    <p class="text-secondary mb-0">Sign in to your account</p>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger mb-3">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control"
                               value="{{ old('email') }}" placeholder="you@company.com" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control"
                               placeholder="••••••••" required>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember">
                            <label class="form-check-label text-secondary small" for="remember">Remember me</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-sign-in-alt me-2"></i>Sign In
                    </button>
                </form>

                <hr class="divider my-4">
                <p class="text-center text-secondary small mb-0">
                    Don't have an account?
                    <a href="{{ route('register') }}">Create one free</a>
                </p>
            </div>
        </div>
    </div>
</div>
</body>
</html>
