<!DOCTYPE html>
<html lang="es-CL">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    
    <title>@yield('title', 'INEXUS Chile | Tecnología, Hardware Corporativo y Soluciones IT')</title>
    <meta name="description" content="@yield('meta_description', 'Tienda en línea de tecnología en Chile. Distribuidores de hardware corporativo, notebooks, servidores, componentes, pantallas y periféricos con despacho a todo Chile.')">
    <link rel="canonical" href="@yield('canonical', url()->current())">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('title', 'INEXUS Chile - Hardware y Tecnología')">
    <meta property="og:description" content="@yield('meta_description', 'Equipamiento tecnológico y soluciones de hardware de alta gama para empresas en Chile.')">
    <meta property="og:image" content="@yield('og_image', asset('images/logo.png'))">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/inexus.css') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/categories/1.png') }}">

    <!-- Schema.org JSON-LD Structured Data -->
    @yield('json_ld')
</head>
<body>

    <!-- Interactive Dynamic Magnetic Cursor Follower -->
    <div class="cursor-dot" id="cursor-dot"></div>
    <div class="cursor-circle" id="cursor-circle">
        <span class="cursor-text" id="cursor-text"></span>
    </div>

    <!-- Global Branded Preloader / Loader -->
    @include('partials.page_loader')

    <!-- Top Announcement Bar -->
    <div class="topbar">
        <div class="container topbar-content">
            <div class="topbar-left">
                <span class="topbar-badge">Hardware Oficial</span>
                <span class="topbar-text-desktop">Despacho express a todo Chile | Facturación electrónica para empresas</span>
                <span class="topbar-text-mobile">🇨🇱 Despacho a todo Chile | Factura SII</span>
            </div>
            <div class="topbar-right">
                <a href="{{ route('page.faqs') }}" class="topbar-link">Ayuda & FAQs</a>
                @auth
                    @if(auth()->user()->is_admin)
                        <a href="{{ route('admin.dashboard') }}" class="topbar-link" style="color:#38bdf8; font-weight:700;">
                            ⚙️ Mi Portal Admin
                        </a>
                    @endif
                    <a href="{{ route('customer.account') }}" class="topbar-link" style="font-weight:600;">
                        Hola, {{ Str::words(auth()->user()->name, 1, '') }}
                    </a>
                    <form action="{{ route('logout') }}" method="POST" style="display:inline; margin:0;">
                        @csrf
                        <button type="submit" class="topbar-link" style="background:none; border:none; color:#94a3b8; cursor:pointer; padding:0; font-size:12px;">(Salir)</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="topbar-link">Iniciar Sesión</a>
                    <span style="color: #475569;">|</span>
                    <a href="{{ route('register') }}" class="topbar-link">Regístrate</a>
                @endauth
                <span style="color: #475569;">|</span>
                <a href="tel:+56229876543" class="topbar-link">+56 2 2987 6543</a>
            </div>
        </div>
    </div>

    <!-- Sticky Main Header -->
    <header class="site-header">
        <div class="container header-wrapper">
            <!-- Brand Logo -->
            <a href="{{ route('home') }}" class="header-brand" title="INEXUS Chile">
                <img src="{{ asset('images/logo.png') }}" alt="INEXUS Chile" class="header-logo">
            </a>

            <!-- Search Bar (Desktop) -->
            <form action="{{ route('shop.index') }}" method="GET" class="header-search">
                <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" name="q" placeholder="Buscar notebook, SSD, SKU o marca..." value="{{ request('q') }}">
            </form>

            <!-- Navigation Links -->
            <nav>
                <ul class="nav-menu">
                    <li class="nav-item {{ request()->routeIs('home') ? 'active' : '' }}"><a href="{{ route('home') }}">Inicio</a></li>
                    <li class="nav-item {{ request()->routeIs('page.about') ? 'active' : '' }}"><a href="{{ route('page.about') }}">Nosotros</a></li>
                    <li class="nav-item {{ request()->routeIs('shop.*') ? 'active' : '' }}"><a href="{{ route('shop.index') }}">Tienda</a></li>
                    <li class="nav-item {{ request()->routeIs('page.faqs') ? 'active' : '' }}"><a href="{{ route('page.faqs') }}">FAQs</a></li>
                    <li class="nav-item {{ request()->routeIs('page.contact') ? 'active' : '' }}"><a href="{{ route('page.contact') }}">Contáctanos</a></li>
                </ul>
            </nav>

            <!-- Header Actions -->
            <div class="header-actions">
                @auth
                    @if(auth()->user()->is_admin)
                        <a href="{{ route('admin.dashboard') }}" class="btn-portal-header" title="Acceso al Panel de Administración">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="7" height="7"></rect>
                                <rect x="14" y="3" width="7" height="7"></rect>
                                <rect x="14" y="14" width="7" height="7"></rect>
                                <rect x="3" y="14" width="7" height="7"></rect>
                            </svg>
                            <span>Mi Portal</span>
                        </a>
                    @else
                        <a href="{{ route('customer.account') }}" class="header-action-btn" title="Mi Cuenta" style="display:flex; align-items:center; gap:6px; padding:0 10px; width:auto; text-decoration:none;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            <span style="font-size:13px; font-weight:700; color:var(--navy-800);">Mi Cuenta</span>
                        </a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="header-action-btn" title="Iniciar Sesión" style="display:flex; align-items:center; gap:6px; padding:0 10px; width:auto; text-decoration:none;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <span style="font-size:13px; font-weight:700; color:var(--navy-800);">Ingresar</span>
                    </a>
                @endauth

                <a href="{{ route('cart.index') }}" class="header-action-btn" id="header-cart-btn" title="Ver Carrito de Compras">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                    @php $cartTotalQty = array_sum(array_column(session('cart', []), 'quantity')); @endphp
                    <span class="badge-count" id="header-cart-badge">{{ $cartTotalQty }}</span>
                </a>

                <!-- Mobile Hamburger Toggle -->
                <button type="button" class="hamburger-btn" id="hamburger-toggle" aria-label="Abrir menú">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Search Row (Always accessible on smartphone view) -->
        <div class="mobile-search-row">
            <div class="container">
                <form action="{{ route('shop.index') }}" method="GET" class="mobile-search-form">
                    <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" name="q" placeholder="Buscar notebook, SSD, SKU o marca..." value="{{ request('q') }}">
                </form>
            </div>
        </div>
    </header>

    <!-- Slide-Over Shopping Cart Drawer (Right to Left) -->
    @include('partials.cart_drawer')

    <!-- Mobile Drawer Overlay -->
    <div class="mobile-drawer-overlay" id="drawer-overlay"></div>

    <!-- Sliding Mobile Menu Drawer -->
    <div class="mobile-drawer" id="mobile-drawer">
        <div class="mobile-drawer-header">
            <img src="{{ asset('images/logo.png') }}" alt="INEXUS" style="height: 32px;">
            <button type="button" id="drawer-close" style="background:none; border:none; cursor:pointer; font-size:22px; color:var(--navy-800); width:36px; height:36px; display:flex; align-items:center; justify-content:center; border-radius:50%;">✕</button>
        </div>
        <ul class="mobile-nav-links">
            <li><a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Inicio</a></li>
            <li><a href="{{ route('shop.index') }}" class="{{ request()->routeIs('shop.*') ? 'active' : '' }}">Catálogo de Hardware</a></li>
            <li><a href="{{ route('page.about') }}">Nosotros</a></li>
            <li><a href="{{ route('page.faqs') }}">Preguntas Frecuentes (FAQs)</a></li>
            <li><a href="{{ route('page.contact') }}">Cotización para Empresas</a></li>
            <li><a href="{{ route('page.returns') }}">Garantía & Devoluciones (6 meses)</a></li>
            <li><a href="{{ route('page.terms') }}">Términos y Condiciones</a></li>
            <li style="margin-top: 16px; border-top: 1px solid #e2e8f0; padding-top: 14px;">
                @auth
                    @if(auth()->user()->is_admin)
                        <a href="{{ route('admin.dashboard') }}" style="color:#0284c7; font-weight:800;">⚙️ Mi Portal Administración</a>
                    @endif
                    <a href="{{ route('customer.account') }}" style="font-weight:700; color:var(--navy-900);">👤 Mi Cuenta ({{ auth()->user()->name }})</a>
                    <form action="{{ route('logout') }}" method="POST" style="margin-top:8px;">
                        @csrf
                        <button type="submit" style="background:none; border:none; color:#ef4444; font-weight:700; font-size:14px; padding:0; cursor:pointer;">Cerrar Sesión</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" style="color:var(--primary); font-weight:700;">🔑 Iniciar Sesión</a>
                    <a href="{{ route('register') }}" style="color:var(--navy-800); font-weight:600; margin-top:6px;">📝 Crear Cuenta / Registrarse</a>
                @endauth
            </li>
            <li style="padding-top: 8px;">
                <a href="tel:+56229876543" style="color:#64748b; font-size:13.5px;">📞 +56 2 2987 6543</a>
            </li>
        </ul>
    </div>

    <!-- Flash Notifications -->
    @if(session('success'))
        <div class="container" style="margin-top: 16px;">
            <div style="background:#f0fdf4; border:1px solid #bbf7d0; color:#15803d; padding:12px 18px; border-radius:8px; font-weight:600; display:flex; align-items:center; gap:10px;">
                <span>✓</span> {{ session('success') }}
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="container" style="margin-top: 16px;">
            <div style="background:#fef2f2; border:1px solid #fecaca; color:#b91c1c; padding:12px 18px; border-radius:8px; font-weight:600; display:flex; align-items:center; gap:10px;">
                <span>✕</span> {{ session('error') }}
            </div>
        </div>
    @endif
    @if(session('warning'))
        <div class="container" style="margin-top: 16px;">
            <div style="background:#fffbeb; border:1px solid #fde68a; color:#b45309; padding:12px 18px; border-radius:8px; font-weight:600; display:flex; align-items:center; gap:10px;">
                <span>⚠</span> {{ session('warning') }}
            </div>
        </div>
    @endif

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Floating WhatsApp Button (Compact Circular on Bottom-Right) -->
    <a href="https://wa.me/56987654321?text=Hola%20INEXUS,%20necesito%20asesor%C3%ADa%20sobre%20productos%20y%20hardware" 
       target="_blank" 
       rel="noopener" 
       class="floating-whatsapp"
       title="Escríbenos por WhatsApp"
       aria-label="Contactar por WhatsApp">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.699c.971.53 2.01.815 3.094.815 3.181 0 5.767-2.586 5.768-5.766 0-3.18-2.586-5.766-5.768-5.766zm9.969 5.766c0 5.518-4.482 10-10 10-1.748 0-3.385-.45-4.815-1.238l-7.185 1.886 1.92-7.009c-.846-1.47-1.32-3.179-1.32-4.999 0-5.518 4.482-10 10-10 5.518 0 10 4.482 10 10z"/>
        </svg>
    </a>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="container footer-grid">
            <div>
                <img src="{{ asset('images/logo.png') }}" alt="INEXUS" style="height: 38px; filter: brightness(0) invert(1); margin-bottom: 18px;">
                <p style="line-height: 1.6; margin-bottom: 16px;">
                    Distribución de hardware, equipamiento informático, servidores y soluciones tecnológicas de alta gama en Chile. Proveedor corporativo con respaldo oficial de marca.
                </p>
                <div style="font-size: 13px; color: #94a3b8;">
                    <strong>Dirección:</strong> Av. Providencia 1208, Of. 601, Santiago, Chile<br>
                    <strong>Contacto:</strong> contacto@inexus.cl | +56 2 2987 6543
                </div>
            </div>

            <div>
                <h4 class="footer-col-title">Navegación</h4>
                <ul class="footer-links">
                    <li><a href="{{ route('home') }}">Inicio</a></li>
                    <li><a href="{{ route('shop.index') }}">Catálogo de Hardware</a></li>
                    <li><a href="{{ route('page.about') }}">Nosotros</a></li>
                    <li><a href="{{ route('page.contact') }}">Cotizaciones Empresas</a></li>
                    <li><a href="{{ route('page.sitemap') }}">Mapa del Sitio (XML)</a></li>
                </ul>
            </div>

            <div>
                <h4 class="footer-col-title">Políticas y Respaldo</h4>
                <ul class="footer-links">
                    <li><a href="{{ route('page.terms') }}">Términos y Condiciones</a></li>
                    <li><a href="{{ route('page.privacy') }}">Políticas de Privacidad</a></li>
                    <li><a href="{{ route('page.returns') }}">Garantía Legal 6 Meses</a></li>
                    <li><a href="{{ route('page.returns') }}">Cambios y Devoluciones</a></li>
                    <li><a href="{{ route('page.faqs') }}">Preguntas Frecuentes</a></li>
                </ul>
            </div>

            <div>
                <h4 class="footer-col-title">Medios de Pago Seguros</h4>
                <p style="font-size: 13px; margin-bottom: 14px;">
                    Transacciones 100% encriptadas y protegidas en Pesos Chilenos (CLP).
                </p>
                <div style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:16px;">
                    <span style="background:#ffffff; color:#0f172a; padding:6px 12px; border-radius:6px; font-weight:700; font-size:12px;">Mercado Pago CLP</span>
                    <span style="background:#ffffff; color:#0f172a; padding:6px 12px; border-radius:6px; font-weight:700; font-size:12px;">Webpay Plus</span>
                    <span style="background:#ffffff; color:#0f172a; padding:6px 12px; border-radius:6px; font-weight:700; font-size:12px;">Transferencia SII</span>
                </div>
                <div style="font-size: 12.5px; color:#64748b;">
                    Emisión automática de Factura Electrónica y Boleta SII.
                </div>
            </div>
        </div>

        <div class="container footer-bottom">
            <div>
                © {{ date('Y') }} INEXUS Chile. Todos los derechos reservados. Desarrollado por <a href="https://www.rew.cl" target="_blank" rel="noopener" style="color: #38bdf8; font-weight: 700; text-decoration: underline;">www.rew.cl</a>
            </div>
            <div>
                Hardware y Tecnología Corporativa para Todo Chile
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="{{ asset('js/inexus.js') }}"></script>
    @yield('scripts')
</body>
</html>
