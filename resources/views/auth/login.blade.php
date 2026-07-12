<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — MyTerai</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            font-family: "DM Sans", system-ui, sans-serif;
            background: #f7f7fb;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            width: 100%;
            max-width: 380px;
            border: 1px solid #eee;
            border-radius: 1rem;
            padding: 2rem;
            background: #fff;
        }
        .brand-dot {
            width: 12px; height: 12px; border-radius: 50%;
            background: #6f42c1; display: inline-block;
        }
        .btn-primary {
            background: #6f42c1;
            border-color: #6f42c1;
        }
        .btn-primary:hover {
            background: #5e36a6;
            border-color: #5e36a6;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="brand-dot"></span>
            <span class="fw-bold">MyTerai</span>
        </div>
        <p class="text-muted small mb-4">Masuk ke Dashboard Admin untuk mengelola data deteksi materai.</p>

        @if ($errors->any())
            <div class="alert alert-danger py-2 small">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.attempt') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label small fw-semibold">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus placeholder="admin@example.com">
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold">Password</label>
                <input type="password" name="password" class="form-control" required placeholder="••••••••">
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label small" for="remember">Ingat saya</label>
            </div>

            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
            </button>
        </form>
    </div>
</body>
</html>
