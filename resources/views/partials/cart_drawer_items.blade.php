@if(!empty($cart) && count($cart) > 0)
    <div class="drawer-items-list">
        @foreach($cart as $id => $item)
            <div class="drawer-item" id="drawer-item-{{ $id }}">
                <div class="drawer-item-img-box">
                    <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}">
                </div>
                <div class="drawer-item-info">
                    <a href="{{ route('product.show', $item['slug']) }}" class="drawer-item-title">
                        {{ $item['name'] }}
                    </a>
                    <span class="drawer-item-sku">SKU: {{ $item['sku'] }}</span>
                    <div class="drawer-item-price-row">
                        <span class="drawer-item-price">${{ number_format($item['price'], 0, ',', '.') }} CLP</span>
                        
                        <!-- AJAX Quantity Controls -->
                        <div class="drawer-qty-control">
                            <button type="button" class="drawer-qty-btn btn-qty-minus" data-id="{{ $id }}" aria-label="Disminuir">-</button>
                            <span class="drawer-qty-val" id="drawer-qty-{{ $id }}">{{ $item['quantity'] }}</span>
                            <button type="button" class="drawer-qty-btn btn-qty-plus" data-id="{{ $id }}" aria-label="Aumentar">+</button>
                        </div>
                    </div>
                </div>
                <button type="button" class="drawer-item-remove btn-remove-item" data-id="{{ $id }}" title="Eliminar del carrito" aria-label="Eliminar">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    </svg>
                </button>
            </div>
        @endforeach
    </div>
@else
    <div class="drawer-empty-state">
        <div class="drawer-empty-icon">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5">
                <circle cx="9" cy="21" r="1"></circle>
                <circle cx="20" cy="21" r="1"></circle>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
            </svg>
        </div>
        <h4>Tu carrito está vacío</h4>
        <p>No tienes productos agregados aún. Explora nuestro catálogo de hardware corporativo y componentes.</p>
        <a href="{{ route('shop.index') }}" class="btn btn-primary" onclick="if(window.closeCartDrawer) window.closeCartDrawer();">
            Explorar Tienda Online
        </a>
    </div>
@endif
