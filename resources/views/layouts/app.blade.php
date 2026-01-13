<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Marketplace Barang Bekas')</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div id="app" class="@if(request()->routeIs('home')) homepage-theme @endif">
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary-blue shadow-sm sticky-top">
            <div class="container">
                <a class="navbar-brand fw-bold" href="{{ route('home') }}">
                    <i class="fas fa-shopping-bag me-2"></i>BarangBekas.id
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('/') && !Request::is('marketplace') ? 'active' : '' }}"
                                href="{{ route('home') }}">Beranda</a>
                        </li>
                        @auth
                            <li class="nav-item">
                                <a class="nav-link {{ Request::is('marketplace') || Request::is('product/*') ? 'active' : '' }}"
                                    href="{{ route('marketplace') }}">Produk</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ Request::is('jual-barang*') || Request::is('produk/*/edit') ? 'active' : '' }}"
                                    href="{{ route('sell.create') }}">Jual Produk</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ Request::is('cart*') ? 'active' : '' }}"
                                    href="{{ route('cart.index') }}">Keranjang</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ Request::is('orders*') ? 'active' : '' }}"
                                    href="{{ route('orders.index') }}">Riwayat</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ Request::is('messages*') ? 'active' : '' }}"
                                    href="{{ route('messages.index') }}">
                                    Pesan
                                    @php
                                        $unreadCount = Auth::check()
                                            ? \App\Models\Message::where('receiver_id', Auth::id())
                                                ->where('is_read', false)
                                                ->count()
                                            : 0;
                                    @endphp
                                    @if ($unreadCount > 0)
                                        <span class="badge bg-danger rounded-pill">{{ $unreadCount }}</span>
                                    @endif
                                </a>
                            </li>
                        @endauth
                    </ul>

                    <ul class="navbar-nav ms-auto">
                        @guest
                            <li class="nav-item">
                                <a class="btn {{ Request::is('login') ? 'btn-light' : 'btn-outline-light' }} rounded-pill px-4 mx-1"
                                    href="{{ route('login') }}">Login</a>
                            </li>
                            <li class="nav-item">
                                <a class="btn {{ Request::is('register') ? 'btn-light' : 'btn-outline-light' }} rounded-pill px-4 mx-1"
                                    href="{{ route('register') }}">Register</a>
                            </li>
                        @else
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-user-circle"></i> {{ Auth::user()->name }}
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('profile.index') }}">Profil</a></li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('logout') }}"
                                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                            Logout
                                        </a>
                                        <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                            class="d-none">
                                            @csrf
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Banner Section - only show on home page -->
        @if(request()->routeIs('home'))
        <div class="container" style="margin-top: 4rem;">
            <div class="banner-section rounded-3 overflow-hidden shadow-sm">
                <img src="{{ asset('logo.png') }}" alt="Banner" class="img-fluid w-100" style="max-height: 300px;">
            </div>
        </div>
        @endif

        <!-- Main Content -->
        <main class="py-4">
            <!-- Flash Messages -->
            @if (session('success'))
                <div class="container">
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="container">
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="container">
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            @endif

            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-primary-blue text-white py-3">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <h5>BarangBekas.id</h5>
                        <p>Platform jual beli barang bekas terpercaya untuk anak kos dan masyarakat umum.</p>
                    </div>
                    <div class="col-md-6 text-end">
                        <p>&copy; 2024 BarangBekas.id. All rights reserved.</p>
                    </div>
                </div>
            </div>
        </footer>

        </main>
    </div> <!-- end #app -->

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script src="{{ asset('js/app.js') }}"></script>
</body>

</html>
