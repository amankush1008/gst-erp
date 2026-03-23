{{-- resources/views/errors/404.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found – GST ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #0F172A; color: #F1F5F9; min-height: 100vh; display: flex; align-items: center; }
        .code { font-size: 120px; font-weight: 900; color: #4F46E5; line-height: 1; opacity: 0.15; }
        a { color: #818CF8; }
    </style>
</head>
<body>
<div class="container text-center">
    <div class="code">404</div>
    <h2 class="fw-bold mb-2">Page Not Found</h2>
    <p class="text-muted mb-4">The page you're looking for doesn't exist or was moved.</p>
    <a href="{{ url('/') }}" class="btn btn-primary px-4">Go to Dashboard</a>
</div>
</body>
</html>
