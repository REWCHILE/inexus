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

    <div class="container shop-container">
        <!-- Mobile Filter Button Trigger (Visible only on mobile/tablet) -->
        <button type="button" class="mobile-filter-trigger" id="mobile-filter-btn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
            </svg>
            <span>Filtrar por Categoría, Marca y Precio</span>
            <span class="filter-caret" id="filter-caret">▾</span>
        </button>

        <div class="shop-page-layout">
            
            <!-- Filters Sidebar -->
            <aside class="shop-filter-sidebar" id="shop-filter-sidebar">
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
            <div class="shop-products-main">
                <!-- Top Toolbar -->
                <div class="shop-toolbar">
                    <div class="shop-toolbar-info">
                        <span style="font-weight:700; color:var(--navy-900);">{{ $products->total() }}</span> productos encontrados
                        @if(request('q'))
                            para "<span style="color:var(--primary); font-weight:600;">{{ request('q') }}</span>"
                        @endif
                        @if($currentCategory)
                            en <span style="color:var(--primary); font-weight:600;">{{ $currentCategory->name }}</span>
                        @endif
                    </div>

                    <!-- Sort -->
                    <div class="shop-toolbar-sort">
                        <label for="orden" style="font-size:13px; color:var(--text-muted); white-space:nowrap;">Ordenar:</label>
                        <select name="orden" id="orden" class="form-control" style="width:170px; height:38px; font-size:13px;" onchange="location = this.value;">
                            <option value="{{ request()->fullUrlWithQuery(['orden' => 'mas_reciente', 'page' => null]) }}" {{ $sort === 'mas_reciente' ? 'selected' : '' }}>Más recientes</option>
                            <option value="{{ request()->fullUrlWithQuery(['orden' => 'precio_menor', 'page' => null]) }}" {{ $sort === 'precio_menor' ? 'selected' : '' }}>Precio: Menor a Mayor</option>
                            <option value="{{ request()->fullUrlWithQuery(['orden' => 'precio_mayor', 'page' => null]) }}" {{ $sort === 'precio_mayor' ? 'selected' : '' }}>Precio: Mayor a Menor</option>
                            <option value="{{ request()->fullUrlWithQuery(['orden' => 'nombre_az', 'page' => null]) }}" {{ $sort === 'nombre_az' ? 'selected' : '' }}>Nombre: A - Z</option>
                        </select>
                    </div>
                </div>

                <!-- Products Grid -->
                @if($products->count() > 0)
                    <div class="products-grid" id="products-catalog-grid">
                        @include('partials.product_cards', ['products' => $products])
                    </div>

                    <!-- Modern Infinite Scrolling Sentinel & Loader -->
                    <div id="infinite-scroll-container" style="margin-top: 40px; text-align: center;" 
                         data-next-page="{{ $products->nextPageUrl() }}" 
                         data-has-more="{{ $products->hasMorePages() ? '1' : '0' }}">
                        
                        <!-- Dynamic Loading Spinner -->
                        <div id="infinite-scroll-loading" style="display: none; padding: 24px 0; align-items: center; justify-content: center; gap: 12px; color: var(--navy-800); font-weight: 600; font-size: 14px;">
                            <span class="infinite-spinner"></span>
                            <span>Cargando más productos del catálogo INEXUS...</span>
                        </div>

                        <!-- Manual Trigger Button (as subtle fallback / trigger) -->
                        <div id="infinite-scroll-manual" style="padding: 10px 0; display: {{ $products->hasMorePages() ? 'block' : 'none' }};">
                            <button type="button" id="btn-load-more" class="btn btn-outline" style="border-radius: 30px; padding: 12px 32px; font-weight: 700; font-size: 13.5px; border-color: #cbd5e1; color: var(--navy-800); background: #ffffff; box-shadow: 0 4px 12px rgba(0,0,0,0.05); transition: all 0.25s ease;">
                                ↓ Cargar más productos
                            </button>
                        </div>

                        <!-- End of Catalog Reached Note -->
                        <div id="infinite-scroll-end" style="display: {{ !$products->hasMorePages() ? 'block' : 'none' }}; padding: 30px 0 10px; color: #94a3b8; font-size: 13px; font-weight: 500;">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 14px;">
                                <div style="height: 1px; width: 60px; background: #e2e8f0;"></div>
                                <span>Has llegado al final del catálogo ({{ $products->total() }} productos)</span>
                                <div style="height: 1px; width: 60px; background: #e2e8f0;"></div>
                            </div>
                        </div>
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
