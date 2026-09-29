<!-- Cart Drawer Overlay -->
<div class="cart-drawer-overlay" id="cart-drawer-overlay"></div>

<!-- Slide-Over Cart Drawer (Opens from Right to Left) -->
<aside class="cart-drawer" id="cart-drawer" aria-label="Carrito de compras deslizante">
    <!-- Header -->
    <div class="cart-drawer-header">
        <div class="cart-drawer-title-wrap">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="9" cy="21" r="1"></circle>
                <circle cx="20" cy="21" r="1"></circle>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
            </svg>
            <h3 class="cart-drawer-title">Carrito de Compras</h3>
            @php 
                $drawerCart = session('cart', []);
                $drawerCount = array_sum(array_column($drawerCart, 'quantity'));
                $drawerSubtotal = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $drawerCart));
                $drawerShipping = $drawerSubtotal > 150000 || empty($drawerCart) ? 0 : 4990;
                $drawerTotal = $drawerSubtotal + $drawerShipping;
                $drawerThreshold = 150000;
                $drawerDiff = max(0, $drawerThreshold - $drawerSubtotal);
            @endphp
            <span class="cart-drawer-badge" id="cart-drawer-badge">{{ $drawerCount }}</span>
        </div>
        <button type="button" class="cart-drawer-close" id="cart-drawer-close" aria-label="Cerrar carrito">✕</button>
    </div>

    <!-- Free Shipping Notice -->
    <div class="cart-drawer-shipping-notice" id="cart-drawer-shipping-notice">
        @if($drawerSubtotal >= $drawerThreshold && count($drawerCart) > 0)
            <span class="shipping-unlocked">🎉 ¡Felicidades! Tienes <strong>Despacho GRATIS</strong></span>
        @elseif(count($drawerCart) > 0)
            <span>Agrega <strong id="drawer-shipping-diff">${{ number_format($drawerDiff, 0, ',', '.') }} CLP</strong> más para <strong>Despacho GRATIS</strong></span>
        @else
            <span>🇨🇱 Despacho express a todo Chile | Factura SII</span>
        @endif
    </div>

    <!-- Items Container -->
    <div class="cart-drawer-body" id="cart-drawer-items">
        @include('partials.cart_drawer_items', ['cart' => $drawerCart])
    </div>

    <!-- Footer Summary & Checkout Actions -->
    <div class="cart-drawer-footer" id="cart-drawer-footer" style="{{ empty($drawerCart) ? 'display:none;' : '' }}">
        <div class="cart-drawer-totals">
            <div class="drawer-subtotal-row">
                <span>Subtotal</span>
                <span id="drawer-subtotal-val">${{ number_format($drawerSubtotal, 0, ',', '.') }} CLP</span>
            </div>
            <div class="drawer-shipping-row">
                <span>Despacho</span>
                <span id="drawer-shipping-val" style="color:{{ $drawerShipping === 0 ? '#16a34a' : 'inherit' }}; font-weight:{{ $drawerShipping === 0 ? '700' : '500' }};">
                    {{ $drawerShipping === 0 ? 'GRATIS' : '$' . number_format($drawerShipping, 0, ',', '.') . ' CLP' }}
                </span>
            </div>
            <div class="drawer-total-row">
                <span>Total a Pagar</span>
                <span id="drawer-total-val" class="drawer-total-amount">
                    ${{ number_format($drawerTotal, 0, ',', '.') }} CLP
                </span>
            </div>
        </div>

        <div class="cart-drawer-actions">
            <a href="{{ route('checkout.index') }}" class="btn btn-primary btn-block btn-lg" id="btn-drawer-checkout">
                <span>Iniciar Compra Segura</span>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </a>
            <a href="{{ route('cart.index') }}" class="cart-drawer-view-all">
                Ver detalle completo del carrito →
            </a>
        </div>
    </div>
</aside>
