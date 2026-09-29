@foreach($products as $product)
    <div class="product-card" data-product-id="{{ $product->id }}">
        <div class="product-badge-wrap">
            @if($product->stock > 0)
                <span class="badge-stock">Stock: {{ $product->stock }}</span>
            @else
                <span class="badge-out-stock">Agotado</span>
            @endif
            @if($product->brand)
                <span class="badge-brand">{{ $product->brand }}</span>
            @endif
        </div>

        <a href="{{ route('product.show', $product->slug) }}" class="product-img-box">
            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy">
        </a>

        <div class="product-info">
            <div class="product-cat-tag">{{ $product->category?->name ?: 'Hardware' }}</div>
            <h3 class="product-title">
                <a href="{{ route('product.show', $product->slug) }}" title="{{ $product->name }}">
                    {{ $product->name }}
                </a>
            </h3>
            <div class="product-sku-code">SKU: {{ $product->sku }}</div>

            <div class="product-price-box">
                @if($product->regular_price > 0)
                    <div class="dual-pricing-card">
                        <span class="price-badge-pill">Transferencia {{ $product->transfer_discount_percentage }}% OFF</span>
                        <div class="price-transfer-val">{{ $product->formatted_transfer_price }}</div>
                        <div class="price-normal-wrap">
                            <span>Tarjeta:</span>
                            <span class="price-normal-val">{{ $product->formatted_normal_price }}</span>
                        </div>
                    </div>

                    <form action="{{ route('cart.add') }}" method="POST" class="ajax-add-to-cart">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="btn-quick-add" title="Agregar al Carrito">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                        </button>
                    </form>
                @else
                    <div class="quote-pricing-card">
                        <span class="badge-quote-pill">A Cotizar</span>
                        <div class="price-quote-title">A Consultar</div>
                        <div class="price-quote-sub">Venta corporativa</div>
                    </div>

                    @php
                        $waQuoteMsg = urlencode("Hola INEXUS, me interesa cotizar el producto:\n*{$product->name}*\nSKU: {$product->sku}");
                    @endphp
                    <a href="https://wa.me/56987654321?text={{ $waQuoteMsg }}" 
                       target="_blank" 
                       rel="noopener" 
                       class="btn-quick-quote" 
                       title="Cotizar por WhatsApp">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.699c.971.53 2.01.815 3.094.815 3.181 0 5.767-2.586 5.768-5.766 0-3.18-2.586-5.766-5.768-5.766zm9.969 5.766c0 5.518-4.482 10-10 10-1.748 0-3.385-.45-4.815-1.238l-7.185 1.886 1.92-7.009c-.846-1.47-1.32-3.179-1.32-4.999 0-5.518 4.482-10 10-10 5.518 0 10 4.482 10 10z"/>
                        </svg>
                    </a>
                @endif
            </div>
        </div>
    </div>
@endforeach
