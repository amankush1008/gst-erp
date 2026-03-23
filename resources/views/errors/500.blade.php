{{-- resources/views/errors/500.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Error – GST ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #0F172A; color: #F1F5F9; min-height: 100vh; display: flex; align-items: center; }
        .code { font-size: 120px; font-weight: 900; color: #EF4444; line-height: 1; opacity: 0.15; }
        a { color: #818CF8; }
    </style>
</head>
<body>
<div class="container text-center">
    <div class="code">500</div>
    <h2 class="fw-bold mb-2">Something went wrong</h2>
    <p class="text-muted mb-4">We encountered an internal server error. Please try again or contact support.</p>
    <a href="{{ url('/') }}" class="btn btn-primary px-4">Go to Dashboard</a>
</div>
</body>
</html>
