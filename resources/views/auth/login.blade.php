<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - BarberGo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3a7bd5;
            --secondary-color: #00d2ff;
            --text-color: #333;
        }

        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .auth-card {
            background-color: white;
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            transition: all 0.3s;
            max-width: 400px;
            width: 100%;
        }

        .auth-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }

        .auth-header {
            background: linear-gradient(135deg, rgba(58, 123, 213, 0.1), rgba(0, 210, 255, 0.1));
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .brand-text {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-left: 10px;
        }

        .auth-body {
            padding: 25px;
        }

        .form-floating {
            margin-bottom: 15px;
        }

        .form-floating > .form-control {
            border-radius: 10px;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        .form-floating > .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(58, 123, 213, 0.25);
        }

        .btn-login {
            background: linear-gradient(135deg, var(--primary-color), #2d62aa);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 500;
            box-shadow: 0 4px 10px rgba(58, 123, 213, 0.3);
            transition: all 0.3s;
        }

        .btn-login:hover {
            box-shadow: 0 6px 15px rgba(58, 123, 213, 0.4);
            transform: translateY(-2px);
        }

        .btn-link {
            color: var(--primary-color);
            font-weight: 500;
        }

        .alert-danger {
            border-radius: 10px;
            border: none;
            background-color: rgba(255, 107, 107, 0.1);
            color: #ff6b6b;
            border-left: 4px solid #ff6b6b;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="auth-card">
                    <div class="auth-header">
                        <div class="logo-container">
                            @if(file_exists(public_path('assets/images/barbergo-logo.jpg')))
                                <img src="{{ asset('assets/images/barbergo-logo.jpg') }}" alt="BarberCall Logo" height="50px">
                            @else
                                <i class="fas fa-cut fa-2x" style="color: var(--primary-color);"></i>
                            @endif
                            <span class="brand-text">BarberGo</span>
                        </div>
                    </div>

                    <div class="auth-body">
                        <h4 class="text-center mb-4">Login Admin</h4>

                        @if($errors->any())
                            <div class="alert alert-danger mb-4">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('login') }}" method="POST">
                            @csrf
                            <div class="form-floating mb-3">
                                <input type="email" class="form-control" id="email" name="email" placeholder="Alamat Email" value="{{ old('email') }}" required>
                                <label for="email">
                                    <i class="fas fa-envelope me-1 text-primary"></i> Alamat Email
                                </label>
                            </div>

                            <div class="form-floating mb-3">
                                <input type="password" class="form-control" id="password" name="password" placeholder="Kata Sandi" required>
                                <label for="password">
                                    <i class="fas fa-lock me-1 text-primary"></i> Kata Sandi
                                </label>
                            </div>

                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">Ingat Saya</label>
                            </div>

                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-login">
                                    <i class="fas fa-sign-in-alt me-2"></i> Masuk
                                </button>
                            </div>

                            <div class="text-center">
                                <a href="{{ route('register') }}" class="btn-link text-decoration-none">
                                    Belum punya akun? Daftar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="text-center mt-3 text-white">
                    <small>&copy; {{ date('Y') }} BarberGo</small>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
