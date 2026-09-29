<!DOCTYPE html>
<html lang="es-CL">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel de Control') | INEXUS Chile Admin</title>
    
    <link rel="stylesheet" href="{{ asset('css/inexus.css') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/categories/1.png') }}">
    
    <style>
        .admin-sidebar-badge {
            background: rgba(2, 132, 199, 0.3);
            color: #38bdf8;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            margin-left: auto;
        }
    </style>
</head>
<body style="background:#f1f5f9;">

    <!-- Interactive Dynamic Magnetic Cursor Follower -->
    <div class="cursor-dot" id="cursor-dot"></div>
    <div class="cursor-circle" id="cursor-circle">
        <span class="cursor-text" id="cursor-text"></span>
    </div>

    <!-- Global Branded Preloader / Loader -->
    @include('partials.page_loader')

    <div class="admin-layout">
        
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="admin-sidebar-header">
                <a href="{{ route('admin.dashboard') }}" style="display:flex; align-items:center; gap:10px;">
                    <img src="{{ asset('images/logo.png') }}" alt="INEXUS" style="height:32px; filter:brightness(0) invert(1);">
                </a>
                <div style="font-size:11px; color:#64748b; margin-top:6px; letter-spacing:0.5px;">GESTIÓN EMPRESARIAL IT</div>
            </div>

            <ul class="admin-sidebar-menu">
                <li>
                    <a href="{{ route('admin.dashboard') }}" class="admin-sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                        <span>Dashboard</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('admin.products.index') }}" class="admin-sidebar-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                        </svg>
                        <span>Inventario Productos</span>
                        <span class="admin-sidebar-badge">{{ \App\Models\Product::count() }}</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('admin.categories.index') }}" class="admin-sidebar-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="8" y1="6" x2="21" y2="6"></line>
                            <line x1="8" y1="12" x2="21" y2="12"></line>
                            <line x1="8" y1="18" x2="21" y2="18"></line>
                            <line x1="3" y1="6" x2="3.01" y2="6"></line>
                            <line x1="3" y1="12" x2="3.01" y2="12"></line>
                            <line x1="3" y1="18" x2="3.01" y2="18"></line>
                        </svg>
                        <span>Categorías & Márgenes</span>
                    </a>
                </li>

                <li style="margin: 12px 16px 4px; font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700; letter-spacing: 0.5px;">
                    Integraciones de Sistema
                </li>

                <li>
                    <a href="{{ route('admin.ingram.index') }}" class="admin-sidebar-link {{ request()->routeIs('admin.ingram.*') ? 'active' : '' }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="2" y1="12" x2="22" y2="12"></line>
                            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                        </svg>
                        <span>Ingram Micro API</span>
                        <span class="badge" style="background:#0284c7; color:#fff; font-size:10px; margin-left:auto;">V6 Chile</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('admin.scraper.index') }}" class="admin-sidebar-link {{ request()->routeIs('admin.scraper.*') ? 'active' : '' }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <span>Scraper de Productos</span>
                        @php $pending = \App\Models\Product::where('scraper_status', 'pending')->orWhereNull('main_image')->count(); @endphp
                        @if($pending > 0)
                            <span class="badge" style="background:#f59e0b; color:#fff; font-size:10px; margin-left:auto;">{{ $pending }}</span>
                        @endif
                    </a>
                </li>

                <li style="margin: 12px 16px 4px; font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700; letter-spacing: 0.5px;">
                    Ventas & Facturación
                </li>

                <li>
                    <a href="{{ route('admin.orders.index') }}" class="admin-sidebar-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="9" cy="21" r="1"></circle>
                            <circle cx="20" cy="21" r="1"></circle>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg>
                        <span>Pedidos & Clientes</span>
                    </a>
                </li>

                <li style="margin-top:auto; padding-top:20px; border-top:1px solid rgba(255,255,255,0.08);">
                    <a href="{{ route('home') }}" target="_blank" class="admin-sidebar-link">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                            <polyline points="15 3 21 3 21 9"></polyline>
                            <line x1="10" y1="14" x2="21" y2="3"></line>
                        </svg>
                        <span>Ver Tienda Online</span>
                    </a>
                </li>

                <li>
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="admin-sidebar-link" style="width:100%; background:none; border:none; cursor:pointer; text-align:left;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                <polyline points="16 17 21 12 16 7"></polyline>
                                <line x1="21" y1="12" x2="9" y2="12"></line>
                            </svg>
                            <span style="color:#ef4444;">Cerrar Sesión</span>
                        </button>
                    </form>
                </li>
            </ul>
        </aside>

        <!-- Main Content Wrapper -->
        <div class="admin-main">
            <!-- Admin Topbar -->
            <header class="admin-topbar">
                <div style="display:flex; align-items:center; gap:12px;">
                    <button type="button" class="admin-menu-toggle" id="admin-menu-toggle" aria-label="Toggle Sidebar">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="3" y1="12" x2="21" y2="12"></line>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <line x1="3" y1="18" x2="21" y2="18"></line>
                        </svg>
                    </button>
                    <div style="font-weight:700; font-size:16px; color:var(--navy-900);">
                        @yield('header_title', 'Administración INEXUS Chile')
                    </div>
                </div>

                <div style="display:flex; align-items:center; gap:20px;">
                    <div style="font-size:13px; color:var(--text-muted);">
                        Ambiente Ingram: <span class="badge badge-info">{{ strtoupper(\App\Models\Setting::get('ingram_environment', 'sandbox')) }}</span>
                    </div>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div style="width:34px; height:34px; border-radius:50%; background:var(--primary); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:13px;">
                            {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                        </div>
                        <span style="font-weight:600; font-size:13.5px; color:var(--navy-900);">{{ auth()->user()->name ?? 'Administrador' }}</span>
                    </div>
                </div>
            </header>

            <!-- Content Body -->
            <div class="admin-content">
                @if(session('success'))
                    <div style="background:#f0fdf4; border:1px solid #bbf7d0; color:#15803d; padding:12px 18px; border-radius:8px; font-weight:600; margin-bottom:20px; display:flex; align-items:center; gap:10px;">
                        <span>✓</span> {{ session('success') }}
                    </div>
                @endif
                @if(session('warning'))
                    <div style="background:#fffbeb; border:1px solid #fde68a; color:#b45309; padding:12px 18px; border-radius:8px; font-weight:600; margin-bottom:20px; display:flex; align-items:center; gap:10px;">
                        <span>⚠</span> {{ session('warning') }}
                    </div>
                @endif
                @if(session('error'))
                    <div style="background:#fef2f2; border:1px solid #fecaca; color:#b91c1c; padding:12px 18px; border-radius:8px; font-weight:600; margin-bottom:20px; display:flex; align-items:center; gap:10px;">
                        <span>✕</span> {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </div>

    </div>

    <!-- Scripts -->
    <script src="{{ asset('js/inexus.js') }}"></script>
    @yield('scripts')
</body>
</html>
