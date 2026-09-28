@extends('layouts.app')

@section('title', ($currentCategory ? $currentCategory->name . ' | ' : '') . 'Catálogo de Tecnología y Hardware | INEXUS Chile')

@section('content')

    <!-- Breadcrumbs -->
    <div style="background:#f1f5f9; padding: 14px 0; border-bottom: 1px solid var(--border-color);">
        <div class="container" style="display:flex; align-items:center; gap:8px; font-size:13px; color:var(--text-muted);">
            <a href="{{ route('home') }}" style="color:var(--navy-800);">Inicio</a>
            <span>/</span>
            <a href="{{ route('shop.index') }}" style="color:var(--navy-800);">Tienda</a>
            @if($currentCategory)
                <span>/</span>
                <span style="color:var(--primary); font-weight:600;">{{ $currentCategory->name }}</span>
            @endif
        </div>
    </div>

    <div class="container" style="padding: 40px 20px;">
        <div style="display:grid; grid-template-columns: 260px 1fr; gap: 32px; align-items: start;">
            
            <!-- Filters Sidebar -->
            <aside style="background:#ffffff; border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:24px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; padding-bottom:12px; border-bottom:1px solid var(--border-color);">
                    <h3 style="font-size:16px; margin:0;">Filtros de Búsqueda</h3>
                    <a href="{{ route('shop.index') }}" style="font-size:12px; color:var(--primary); font-weight:600;">Limpiar</a>
                </div>

                <form action="{{ route('shop.index') }}" method="GET" id="filter-form">
                    @if(request('q'))
                        <input type="hidden" name="q" value="{{ request('q') }}">
                    @endif

                    <!-- Category Filter -->
                    <div style="margin-bottom: 24px;">
                        <label class="form-label" style="font-size:13px; text-transform:uppercase; letter-spacing:0.5px; color:var(--text-muted);">Categorías</label>
                        <div style="display:flex; flex-direction:column; gap:8px; margin-top:8px;">
                            <a href="{{ route('shop.index', array_merge(request()->except(['categoria', 'page']))) }}" 
                               style="font-size:13.5px; display:flex; justify-content:space-between; color: {{ !request('categoria') ? 'var(--primary)' : 'var(--text-main)' }}; font-weight: {{ !request('categoria') ? '700' : '500' }};">
                                <span>Todas las categorías</span>
                                <span>({{ \App\Models\Product::where('is_active', true)->count() }})</span>
                            </a>
                            @foreach($categories as $cat)
                                <a href="{{ route('shop.index', array_merge(request()->except('page'), ['categoria' => $cat->slug])) }}" 
                                   style="font-size:13.5px; display:flex; justify-content:space-between; color: {{ request('categoria') === $cat->slug ? 'var(--primary)' : 'var(--text-main)' }}; font-weight: {{ request('categoria') === $cat->slug ? '700' : '500' }};">
                                    <span>{{ $cat->name }}</span>
                                    <span style="color:var(--text-light);">({{ $cat->products_count }})</span>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <!-- Brand Filter -->
                    <div style="margin-bottom: 24px;">
                        <label class="form-label" style="font-size:13px; text-transform:uppercase; letter-spacing:0.5px; color:var(--text-muted);">Marca</label>
                        <select name="marca" class="form-control" onchange="this.form.submit();" style="margin-top:8px; font-size:13px;">
                            <option value="">Todas las marcas</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand }}" {{ request('marca') === $brand ? 'selected' : '' }}>{{ $brand }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Price Range -->
                    <div style="margin-bottom: 24px;">
                        <label class="form-label" style="font-size:13px; text-transform:uppercase; letter-spacing:0.5px; color:var(--text-muted);">Rango de Precio (CLP)</label>
                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:8px; margin-top:8px;">
                            <input type="number" name="min_price" placeholder="Mínimo" value="{{ request('min_price') }}" class="form-control" style="font-size:12.5px;">
                            <input type="number" name="max_price" placeholder="Máximo" value="{{ request('max_price') }}" class="form-control" style="font-size:12.5px;">
                        </div>
                    </div>

                    <!-- In Stock Checkbox -->
                    <div style="margin-bottom: 24px;">
                        <label style="display:flex; align-items:center; gap:8px; font-size:13.5px; cursor:pointer;">
                            <input type="checkbox" name="en_stock" value="1" {{ request('en_stock') == '1' ? 'checked' : '' }} onchange="this.form.submit();">
                            <span>Sólo productos en stock</span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Aplicar Filtros</button>
                </form>
            </aside>

            <!-- Products Listing Main Area -->
            <div>
                <!-- Top Toolbar -->
                <div style="display:flex; justify-content:space-between; align-items:center; background:#ffffff; border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:14px 20px; margin-bottom:24px; flex-wrap:wrap; gap:16px;">
                    <div>
                        <span style="font-weight:700; color:var(--navy-900);">{{ $products->total() }}</span> productos encontrados
                        @if(request('q'))
                            para "<span style="color:var(--primary); font-weight:600;">{{ request('q') }}</span>"
                        @endif
                        @if($currentCategory)
                            en <span style="color:var(--primary); font-weight:600;">{{ $currentCategory->name }}</span>
                        @endif
                    </div>

                    <!-- Sort -->
                    <div style="display:flex; align-items:center; gap:10px;">
                        <label for="orden" style="font-size:13px; color:var(--text-muted);">Ordenar por:</label>
                        <select name="orden" id="orden" class="form-control" style="width:180px; height:38px; font-size:13px;" onchange="location = this.value;">
                            <option value="{{ request()->fullUrlWithQuery(['orden' => 'mas_reciente', 'page' => null]) }}" {{ $sort === 'mas_reciente' ? 'selected' : '' }}>Más recientes</option>
                            <option value="{{ request()->fullUrlWithQuery(['orden' => 'precio_menor', 'page' => null]) }}" {{ $sort === 'precio_menor' ? 'selected' : '' }}>Precio: Menor a Mayor</option>
                            <option value="{{ request()->fullUrlWithQuery(['orden' => 'precio_mayor', 'page' => null]) }}" {{ $sort === 'precio_mayor' ? 'selected' : '' }}>Precio: Mayor a Menor</option>
                            <option value="{{ request()->fullUrlWithQuery(['orden' => 'nombre_az', 'page' => null]) }}" {{ $sort === 'nombre_az' ? 'selected' : '' }}>Nombre: A - Z</option>
                        </select>
                    </div>
                </div>

                <!-- Products Grid -->
                @if($products->count() > 0)
                    <div class="products-grid" style="grid-template-columns: repeat(3, 1fr);">
                        @foreach($products as $product)
                            <div class="product-card">
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
                                        <div class="price-val">{{ $product->formatted_price }}</div>

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
                    </div>

                    <!-- Pagination -->
                    <div style="margin-top: 40px; display:flex; justify-content:center;">
                        {{ $products->links() }}
                    </div>
                @else
                    <div style="background:#ffffff; border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:60px 20px; text-align:center;">
                        <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" style="margin:0 auto 16px;">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <h3 style="font-size:20px; margin-bottom:8px;">No se encontraron productos con estos filtros</h3>
                        <p style="color:var(--text-muted); margin-bottom:20px;">Prueba ajustando los términos de búsqueda o eliminando los filtros seleccionados.</p>
                        <a href="{{ route('shop.index') }}" class="btn btn-primary">Ver Todos los Productos</a>
                    </div>
                @endif
            </div>

        </div>
    </div>

@endsection
