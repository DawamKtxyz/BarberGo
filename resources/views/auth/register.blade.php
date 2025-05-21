<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Admin - BarberGp</title>
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

        .btn-register {
            background: linear-gradient(135deg, var(--primary-color), #2d62aa);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 500;
            box-shadow: 0 4px 10px rgba(58, 123, 213, 0.3);
            transition: all 0.3s;
        }

        .btn-register:hover {
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

        /* Password Strength Meter (simplified) */
        .password-strength {
            height: 5px;
            margin: -10px 0 15px 0;
            border-radius: 3px;
            overflow: hidden;
            background: #f1f1f1;
        }

        .password-strength-meter {
            height: 100%;
            width: 0%;
            transition: width 0.5s ease;
            background: var(--primary-color);
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
                        <h4 class="text-center mb-4">Registrasi Admin</h4>

                        @if($errors->any())
                            <div class="alert alert-danger mb-4">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('register') }}" method="POST" id="registerForm">
                            @csrf
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="nama" name="nama" placeholder="Nama Lengkap" value="{{ old('nama') }}" required>
                                <label for="nama">
                                    <i class="fas fa-user me-1 text-primary"></i> Nama Lengkap
                                </label>
                            </div>

                            <div class="form-floating mb-3">
                                <input type="email" class="form-control" id="email" name="email" placeholder="Alamat Email" value="{{ old('email') }}" required>
                                <label for="email">
                                    <i class="fas fa-envelope me-1 text-primary"></i> Alamat Email
                                </label>
                            </div>

                            <div class="form-floating mb-1">
                                <input type="password" class="form-control" id="password" name="password" placeholder="Kata Sandi" required>
                                <label for="password">
                                    <i class="fas fa-lock me-1 text-primary"></i> Kata Sandi
                                </label>
                            </div>

                            <div class="password-strength">
                                <div class="password-strength-meter" id="strengthMeter"></div>
                            </div>

                            <div class="form-floating mb-3">
                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Konfirmasi Kata Sandi" required>
                                <label for="password_confirmation">
                                    <i class="fas fa-lock me-1 text-primary"></i> Konfirmasi Kata Sandi
                                </label>
                            </div>

                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                                <label class="form-check-label" for="terms">
                                    Saya menyetujui syarat dan ketentuan
                                </label>
                            </div>

                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-register">
                                    <i class="fas fa-user-plus me-2"></i> Daftar
                                </button>
                            </div>

                            <div class="text-center">
                                <a href="{{ route('login') }}" class="btn-link text-decoration-none">
                                    Sudah punya akun? Masuk
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

    <script>
        // Simple password strength meter
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const meter = document.getElementById('strengthMeter');

            if (password.length === 0) {
                meter.style.width = '0%';
            } else if (password.length < 6) {
                meter.style.width = '25%';
                meter.style.backgroundColor = '#ff4d4d';
            } else if (password.length < 8) {
                meter.style.width = '50%';
                meter.style.backgroundColor = '#ffa64d';
            } else if (password.length < 10 || !/[A-Z]/.test(password) || !/[0-9]/.test(password)) {
                meter.style.width = '75%';
                meter.style.backgroundColor = '#77dd77';
            } else {
                meter.style.width = '100%';
                meter.style.backgroundColor = '#2ecc71';
            }
        });
    </script>
</body>
</html>
