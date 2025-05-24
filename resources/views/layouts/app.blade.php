<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Admin Panel Aplikasi Tukang Cukur Panggilan">
    <title>@yield('title', 'BarberGo Admin')</title>
    <!-- CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom Styles -->
    <style>
        :root {
            --primary-color: #3a7bd5;
            --primary-dark: #2d62aa;
            --secondary-color: #00d2ff;
            --text-color: #333;
            --text-light: #6c757d;
            --bg-light: #f8f9fa;
            --accent-color: #ff6b6b;
            --transition-speed: 0.3s;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7fa;
            color: var(--text-color);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Navbar Styling */
        .navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            padding: 0.8rem 1.5rem;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: white !important;
            display: flex;
            align-items: center;
        }

        .navbar-brand i {
            margin-right: 10px;
            font-size: 1.8rem;
        }

        .navbar .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            padding: 0.7rem 1rem;
            transition: all var(--transition-speed);
        }

        .navbar .nav-link:hover,
        .navbar .nav-link:focus {
            color: white !important;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 5px;
        }

        .navbar .dropdown-menu {
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .navbar .dropdown-item {
            padding: 10px 20px;
            font-weight: 500;
            transition: all var(--transition-speed);
        }

        .navbar .dropdown-item:hover,
        .navbar .dropdown-item:focus {
            background-color: rgba(58, 123, 213, 0.1);
            color: var(--primary-color);
        }

        /* Sidebar Styling */
        .sidebar {
            height: calc(100vh - 70px);
            background-color: white;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
            padding: 2rem 0;
            position: sticky;
            top: 70px;
            overflow-y: auto;
            transition: all var(--transition-speed);
            z-index: 1000;
            border-radius: 0 15px 15px 0;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: var(--text-light);
            font-weight: 500;
            transition: all var(--transition-speed);
            margin: 5px 15px;
            border-radius: 8px;
            position: relative;
            text-decoration: none;
        }

        .sidebar-link i {
            margin-right: 12px;
            font-size: 1.2rem;
            min-width: 25px;
            text-align: center;
        }

        .sidebar-link:hover,
        .sidebar-link:focus {
            color: var(--primary-color);
            background-color: rgba(58, 123, 213, 0.08);
            transform: translateX(5px);
        }

        .sidebar-link.active {
            color: var(--primary-color);
            background-color: rgba(58, 123, 213, 0.1);
            font-weight: 600;
        }

        .sidebar-link.active::before {
            content: '';
            position: absolute;
            left: -15px;
            top: 0;
            height: 100%;
            width: 5px;
            background: linear-gradient(to bottom, var(--primary-color), var(--secondary-color));
            border-radius: 0 5px 5px 0;
        }

        .sidebar-heading {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-light);
            margin: 1.5rem 0 0.5rem 25px;
            font-weight: 600;
        }

        /* Main Content Area */
        .content {
            padding: 2rem;
            min-height: calc(100vh - 70px);
        }

        .content-header {
            margin-bottom: 1.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding-bottom: 1rem;
        }

        .content-title {
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 0.25rem;
        }

        .content-subtitle {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        /* Cards Styling */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
            transition: transform var(--transition-speed), box-shadow var(--transition-speed);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background-color: transparent;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.25rem 1.5rem;
            font-weight: 600;
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Utilities */
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            border: none;
            box-shadow: 0 3px 8px rgba(58, 123, 213, 0.3);
            font-weight: 500;
            padding: 0.5rem 1.5rem;
            border-radius: 8px;
            transition: all var(--transition-speed);
        }

        .btn-primary:hover,
        .btn-primary:focus {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary-color));
            box-shadow: 0 5px 12px rgba(58, 123, 213, 0.5);
            transform: translateY(-1px);
        }

        /* Animation for Page Load */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.5s ease-out forwards;
        }

        /* Responsive Sidebar */
        @media (max-width: 992px) {
            .sidebar {
                position: fixed;
                left: -250px;
                top: 70px;
                height: calc(100vh - 70px);
                width: 250px;
                z-index: 1000;
                transition: left var(--transition-speed);
            }

            .sidebar.show {
                left: 0;
            }

            .sidebar-backdrop {
                position: fixed;
                top: 70px;
                left: 0;
                width: 100vw;
                height: calc(100vh - 70px);
                background: rgba(0, 0, 0, 0.5);
                z-index: 999;
                display: none;
            }

            .sidebar-backdrop.show {
                display: block;
            }
        }

        /* Notification Badge */
        .notification-badge {
            position: relative;
        }

        .notification-badge::after {
            content: '';
            position: absolute;
            top: 6px;
            right: 3px;
            width: 8px;
            height: 8px;
            background-color: var(--accent-color);
            border-radius: 50%;
            display: block;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg-light);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-dark);
        }
    </style>
    @yield('styles')
</head>
<body>
    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container-fluid">
            <!-- Toggle Button for Mobile Sidebar -->
            <button id="sidebarToggle" class="btn d-lg-none me-2" type="button">
                <i class="fas fa-bars text-white"></i>
            </button>

            <!-- Brand Logo -->
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <i class="fas fa-cut"></i> BarberGo
            </a>

            <!-- Navbar Toggler for Mobile -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navbar Items -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- Search Form -->
                <form class="d-flex ms-auto me-3">
                    <div class="input-group">
                        <input class="form-control" type="search" placeholder="Cari..." aria-label="Search" style="border-radius: 20px 0 0 20px;">
                        <button class="btn btn-light" type="submit" style="border-radius: 0 20px 20px 0;">
                            <i class="fas fa-search text-primary"></i>
                        </button>
                    </div>
                </form>

                <!-- Right Menu Items -->
                <ul class="navbar-nav">
                    <!-- Notifications -->
                    <li class="nav-item dropdown">
                        <a class="nav-link notification-badge" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-bell"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end p-0" aria-labelledby="notificationsDropdown" style="width: 320px; max-height: 400px; overflow-y: auto;">
                            <div class="p-3 border-bottom">
                                <h6 class="m-0">Notifikasi</h6>
                            </div>
                            <div class="list-group list-group-flush">
                                <a href="#" class="list-group-item list-group-item-action p-3">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">Pesanan Baru</h6>
                                        <small class="text-muted">3 jam yang lalu</small>
                                    </div>
                                    <p class="mb-1">Ada pesanan baru dari Ahmad Fajar.</p>
                                </a>
                                <a href="#" class="list-group-item list-group-item-action p-3">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">Pembayaran Berhasil</h6>
                                        <small class="text-muted">5 jam yang lalu</small>
                                    </div>
                                    <p class="mb-1">Pembayaran dari Rudi Hartono telah berhasil.</p>
                                </a>
                                <a href="#" class="list-group-item list-group-item-action p-3">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">Tukang Cukur Baru</h6>
                                        <small class="text-muted">Kemarin</small>
                                    </div>
                                    <p class="mb-1">Deni Saputra telah mendaftar sebagai tukang cukur.</p>
                                </a>
                            </div>
                            <div class="p-2 border-top text-center">
                                <a href="#" class="text-decoration-none small fw-medium">Lihat Semua Notifikasi</a>
                            </div>
                        </div>
                    </li>

                    <!-- User Profile -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="https://ui-avatars.com/api/?name={{ Auth::user()->nama }}&background=random" class="rounded-circle me-1" width="32" height="32" alt="Profile">
                            <span>{{ Auth::user()->nama }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li>
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-user me-2 text-primary"></i> Profil Saya
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-cog me-2 text-primary"></i> Pengaturan
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt me-2 text-danger"></i> Keluar
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Sidebar Backdrop for Mobile -->
    <div class="sidebar-backdrop"></div>

    <!-- Main Container -->
    <div class="container-fluid" style="margin-top: 70px;">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-2 px-0 sidebar">
                <div class="sidebar-heading">Menu Utama</div>
                <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard*') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>

                <div class="sidebar-heading">Manajemen</div>
                <a href="{{ route('pelanggan.index') }}" class="sidebar-link {{ request()->routeIs('pelanggan*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i> Pelanggan
                </a>
                <a href="{{ route('tukang_cukur.index') }}" class="sidebar-link {{ request()->routeIs('tukang_cukur*') ? 'active' : '' }}">
                    <i class="fas fa-cut"></i> Tukang Cukur
                </a>
                <a href="{{ route('admin.index') }}" class="sidebar-link {{ request()->routeIs('admin*') ? 'active' : '' }}">
                    <i class="fas fa-user-shield"></i> Admin
                </a>
                <a href="{{ route('pesanan.index') }}" class="sidebar-link {{ request()->routeIs('pesanan*') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-list"></i> Pesanan
                </a>

                <div class="sidebar-heading">Keuangan</div>
                <a href="{{ route('pendapatan') }}" class="sidebar-link {{ request()->routeIs('pendapatan*') ? 'active' : '' }}">
                    <i class="fas fa-money-bill-wave"></i> Pendapatan
                </a>
                <a href="{{ route('penggajian.index') }}" class="sidebar-link {{ request()->routeIs('penggajian*') ? 'active' : '' }}">
                      <i class="fas fa-money-bill-wave"></i> Penggajian
                </a>
                <a href="{{ route('laporan_penggajian.index') }}" class="sidebar-link {{ request()->routeIs('laporan_penggajian*') ? 'active' : '' }}">
                    <i class="fas fa-chart-bar"></i> Laporan Penggajian
                </a>

                <div class="sidebar-heading">Pengaturan</div>
                <a href="#" class="sidebar-link {{ request()->routeIs('pengaturan*') ? 'active' : '' }}">
                    <i class="fas fa-cog"></i> Pengaturan Sistem
                </a>
            </div>

            <!-- Main Content -->
            <div class="col-lg-10 content fade-in-up">
                <!-- Content Header - Only show if the blade defines it -->
                @hasSection('header')
                <div class="content-header">
                    <h1 class="content-title">@yield('header')</h1>
                    <p class="content-subtitle">@yield('subheader')</p>
                </div>
                @endif

                <!-- Main Content Area -->
                @yield('content')
            </div>
        </div>
    </div>

    <!-- Core JS Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize sidebar toggle for mobile
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.querySelector('.sidebar');
            const backdrop = document.querySelector('.sidebar-backdrop');

            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                    backdrop.classList.toggle('show');
                });
            }

            if (backdrop) {
                backdrop.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    backdrop.classList.remove('show');
                });
            }

            // Add animation to cards
            const cards = document.querySelectorAll('.card');
            cards.forEach((card, index) => {
                card.style.animationDelay = index * 0.1 + 's';
                card.classList.add('fade-in-up');
            });
        });
    </script>
    @yield('scripts')
</body>
</html>
