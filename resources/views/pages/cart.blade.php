@extends('layouts.app')

@section('title', 'Carrito de Compras | INEXUS Chile')

@section('content')

    <!-- Breadcrumb bar -->
    <div style="background:#f1f5f9; padding: 14px 0; border-bottom: 1px solid var(--border-color);">
        <div class="container" style="display:flex; align-items:center; gap:8px; font-size:13px; color:var(--text-muted);">
            <a href="{{ route('home') }}" style="color:var(--navy-800);">Inicio</a>
            <span>/</span>
            <span style="color:var(--primary); font-weight:600;">Carrito de Compras</span>
        </div>
    </div>

    <div class="container cart-container">
        <h1 style="font-size:28px; margin-bottom:24px;">Carrito de Compras</h1>

        @if(!empty($cart) && count($cart) > 0)
            <div class="cart-layout-grid">
                
                <!-- Cart Items Table Card -->
                <div style="background:#ffffff; border:1px solid var(--border-color); border-radius:var(--radius-lg); overflow:hidden;">
                    <div class="cart-table-responsive">
                    <table style="width:100%; min-width: 600px; border-collapse:collapse; font-size:14px;">
                        <thead>
                            <tr style="background:#f8fafc; border-bottom:1px solid var(--border-color);">
                                <th style="padding:14px 20px; text-align:left; color:var(--navy-800);">Producto</th>
                                <th style="padding:14px 16px; text-align:right; color:var(--navy-800);">Precio Unitario</th>
                                <th style="padding:14px 16px; text-align:center; color:var(--navy-800);">Cantidad</th>
                                <th style="padding:14px 20px; text-align:right; color:var(--navy-800);">Subtotal</th>
                                <th style="padding:14px 12px; text-align:center;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cart as $id => $item)
                                <tr style="border-bottom:1px solid var(--border-color);" id="cart-row-{{ $id }}">
                                    <!-- Product Info -->
                                    <td style="padding:16px 20px; vertical-align:middle;">
                                        <div style="display:flex; align-items:center; gap:16px;">
                                            <div style="width:60px; height:60px; border:1px solid var(--border-color); border-radius:8px; padding:4px; display:flex; align-items:center; justify-content:center; background:#ffffff; flex-shrink:0;">
                                                <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" style="max-height:50px; max-width:50px; object-fit:contain;">
                                            </div>
                                            <div>
                                                <h4 style="font-size:14px; font-weight:700; margin-bottom:4px;">
                                                    <a href="{{ route('product.show', $item['slug']) }}">{{ $item['name'] }}</a>
                                                </h4>
                                                <span style="font-size:12px; color:var(--text-light); font-family:monospace;">SKU: {{ $item['sku'] }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Unit Price -->
                                    <td style="padding:16px; text-align:right; font-weight:600; color:var(--navy-900); vertical-align:middle;">
                                        ${{ number_format($item['price'], 0, ',', '.') }} CLP
                                    </td>

                                    <!-- Quantity Input -->
                                    <td style="padding:16px; text-align:center; vertical-align:middle;">
                                        <form action="{{ route('cart.update') }}" method="POST" style="display:inline-flex; align-items:center; border:1px solid var(--border-color); border-radius:6px; overflow:hidden;">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $id }}">
                                            <button type="button" onclick="const inp=this.nextElementSibling; if(inp.value>1){ inp.value--; this.form.submit(); }" style="width:30px; height:34px; background:#f8fafc; border:none; cursor:pointer; font-weight:700;">-</button>
                                            <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="1" max="{{ max(1, $item['stock']) }}" style="width:40px; height:34px; border:none; text-align:center; font-weight:700; font-size:13px;" onchange="this.form.submit();">
                                            <button type="button" onclick="const inp=this.previousElementSibling; inp.value++; this.form.submit();" style="width:30px; height:34px; background:#f8fafc; border:none; cursor:pointer; font-weight:700;">+</button>
                                        </form>
                                    </td>

                                    <!-- Subtotal -->
                                    <td style="padding:16px 20px; text-align:right; font-weight:800; font-size:15px; color:var(--navy-900); font-family:'Plus Jakarta Sans'; vertical-align:middle;">
                                        ${{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }} CLP
                                    </td>

                                    <!-- Delete Button -->
                                    <td style="padding:16px 12px; text-align:center; vertical-align:middle;">
                                        <form action="{{ route('cart.remove') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $id }}">
                                            <button type="submit" style="background:none; border:none; color:#ef4444; cursor:pointer; padding:6px;" title="Eliminar del Carrito">
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <polyline points="3 6 5 6 21 6"></polyline>
                                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>

                    <div style="padding:16px 20px; display:flex; justify-content:space-between; align-items:center; background:#f8fafc; border-top:1px solid var(--border-color);">
                        <a href="{{ route('shop.index') }}" class="btn btn-secondary" style="font-size:13px;">
                            ← Continuar Comprando
                        </a>
                        <span style="font-size:13px; color:var(--text-muted);">
                            Precios expresados en Pesos Chilenos (CLP) con IVA incluido.
                        </span>
                    </div>
                </div>

                <!-- Order Summary Card -->
                <div style="background:#ffffff; border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:26px;">
                    <h3 style="font-size:18px; margin-bottom:20px; padding-bottom:12px; border-bottom:1px solid var(--border-color);">
                        Resumen del Pedido
                    </h3>

                    <div style="display:flex; justify-content:space-between; margin-bottom:12px; font-size:14.5px;">
                        <span style="color:var(--text-muted);">Subtotal Productos:</span>
                        <span style="font-weight:700; color:var(--navy-900);">${{ number_format($subtotal, 0, ',', '.') }} CLP</span>
                    </div>

                    <div style="display:flex; justify-content:space-between; margin-bottom:12px; font-size:14.5px;">
                        <span style="color:var(--text-muted);">Despacho Express:</span>
                        <span style="font-weight:700; color:{{ $shipping === 0 ? '#166534' : 'var(--navy-900)' }};">
                            {{ $shipping === 0 ? 'GRATIS (Compras > $150.000)' : '$' . number_format($shipping, 0, ',', '.') . ' CLP' }}
                        </span>
                    </div>

                    <div style="display:flex; justify-content:space-between; margin-bottom:18px; font-size:13px; color:#64748b; padding-bottom:14px; border-bottom:1px solid var(--border-color);">
                        <span>IVA 19% (Incluido en el total):</span>
                        <span>${{ number_format($tax, 0, ',', '.') }} CLP</span>
                    </div>

                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
                        <span style="font-size:17px; font-weight:800; color:var(--navy-900);">Total a Pagar:</span>
                        <span style="font-family:'Plus Jakarta Sans'; font-size:24px; font-weight:800; color:var(--primary);">${{ number_format($total, 0, ',', '.') }} CLP</span>
                    </div>

                    <a href="{{ route('checkout.index') }}" class="btn btn-primary btn-block btn-lg" style="margin-bottom:16px;">
                        <span>Ir al Checkout Seguro</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </a>

                    <!-- Trust indicators -->
                    <div style="background:#f8fafc; border-radius:8px; padding:14px; text-align:center; font-size:12.5px; color:var(--text-muted); line-height:1.5;">
                        🔒 Transacción 100% Protegida con Encriptación SSL y pasarela certificada Mercado Pago en Chile.
                    </div>
                </div>

            </div>
        @else
            <!-- Empty Cart State -->
            <div style="background:#ffffff; border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:70px 20px; text-align:center; max-width:600px; margin:0 auto;">
                <svg width="70" height="70" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" style="margin:0 auto 18px;">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
                <h2 style="font-size:22px; margin-bottom:8px;">Tu carrito de compras está vacío</h2>
                <p style="color:var(--text-muted); margin-bottom:24px;">Explora nuestro catálogo de soluciones tecnológicas y encuentra el hardware que necesitas.</p>
                <a href="{{ route('shop.index') }}" class="btn btn-primary btn-lg">Explorar Tienda Online</a>
            </div>
        @endif

    </div>

@endsection
