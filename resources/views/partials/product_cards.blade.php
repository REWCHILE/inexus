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
            </div>
        </div>
    </div>
@endforeach
