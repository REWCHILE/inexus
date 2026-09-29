@extends('layouts.app')

@section('title', 'INEXUS | Equipamiento Destacado y Soluciones Tecnológicas en Chile')

@section('content')
    <!-- Dynamic Multi-Slide Hero Carousel (With Product Mini-Sliders) -->
    <section class="hero-section" id="hero-carousel-section">
        <div class="hero-slider" id="hero-slider">
            <div class="hero-slides-wrapper" id="hero-slides-wrapper">
                @foreach($heroSlides as $slideIndex => $slide)
                    <div class="hero-slide {{ $slideIndex === 0 ? 'active' : '' }}" data-slide-index="{{ $slideIndex }}">
                        <div class="container hero-grid">
                            <!-- Left Content Column -->
                            <div class="hero-content-col">
                                <span class="hero-tagline">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                                    </svg>
                                    {{ $slide['tagline'] }}
                                </span>
                                <h1 class="hero-title">{!! $slide['title'] !!}</h1>
                                <p class="hero-description">{{ $slide['description'] }}</p>
                                <div class="hero-ctas">
                                    <a href="{{ $slide['primary_btn_url'] }}" class="btn btn-primary btn-lg">
                                        <span>{{ $slide['primary_btn_text'] }}</span>
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <line x1="5" y1="12" x2="19" y2="12"></line>
                                            <polyline points="12 5 19 12 12 19"></polyline>
                                        </svg>
                                    </a>
                                    <a href="{{ $slide['secondary_btn_url'] }}" class="btn btn-secondary btn-lg">
                                        <span>{{ $slide['secondary_btn_text'] }}</span>
                                    </a>
                                </div>
                            </div>

                            <!-- Right Content: Slide Product Showcase Card with internal Product Carousel -->
                            <div class="hero-media-box">
                                <div class="hero-card-featured hero-product-carousel" data-slide-id="{{ $slideIndex }}">
                                    @foreach($slide['products'] as $pIdx => $prod)
                                        <div class="hero-prod-item {{ $pIdx === 0 ? 'active' : '' }}" data-prod-index="{{ $pIdx }}">
                                            <div class="hero-card-top-row">
                                                <span class="hero-card-badge">{{ $prod->brand ? $prod->brand . ' Oficial' : 'Oferta Destacada' }}</span>
                                                @if(count($slide['products']) > 1)
                                                    <div class="hero-card-counter">
                                                        <span>{{ $pIdx + 1 }}/{{ count($slide['products']) }}</span>
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="hero-prod-img-wrap">
                                                <img src="{{ $prod->image_url }}" alt="{{ $prod->name }}" loading="eager">
                                            </div>

                                            <div class="hero-prod-category">{{ $prod->category?->name ?? 'Hardware' }}</div>
                                            <h3 class="hero-prod-title" title="{{ $prod->name }}">
                                                <a href="{{ route('product.show', $prod->slug) }}">{{ $prod->name }}</a>
                                            </h3>

                                            <p class="hero-prod-specs">
                                                @if(!empty($prod->short_description))
                                                    {{ Str::limit($prod->short_description, 80) }}
                                                @elseif(!empty($prod->specifications) && is_array($prod->specifications))
                                                    {{ implode(' • ', array_slice(array_values($prod->specifications), 0, 3)) }}
                                                @else
                                                    Garantía Oficial • Despacho Express a Todo Chile
                                                @endif
                                            </p>

                                            <div class="hero-prod-bottom">
                                                <div>
                                                    <span class="hero-price-label">Precio Contado / Transferencia</span>
                                                    <span class="hero-price-val">{{ $prod->formatted_transfer_price }}</span>
                                                </div>
                                                <!-- Directly linking to this product's detail page -->
                                                <a href="{{ route('product.show', $prod->slug) }}" class="btn btn-primary hero-btn-offer">
                                                    Ver Oferta
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach

                                    <!-- Navigation arrows inside the product card -->
                                    @if(count($slide['products']) > 1)
                                        <div class="hero-prod-nav-bar">
                                            <button type="button" class="hero-prod-arrow hero-prod-prev" aria-label="Producto anterior">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"></polyline></svg>
                                            </button>
                                            <div class="hero-prod-dots">
                                                @foreach($slide['products'] as $pIdx => $prod)
                                                    <button type="button" class="hero-prod-dot {{ $pIdx === 0 ? 'active' : '' }}" data-goto="{{ $pIdx }}"></button>
                                                @endforeach
                                            </div>
                                            <button type="button" class="hero-prod-arrow hero-prod-next" aria-label="Siguiente producto">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Main Hero Carousel Prev / Next Arrow Controls -->
            <button type="button" class="hero-main-arrow hero-main-prev" id="hero-main-prev" aria-label="Slide anterior">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"></polyline></svg>
            </button>
            <button type="button" class="hero-main-arrow hero-main-next" id="hero-main-next" aria-label="Siguiente slide">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
            </button>

            <!-- Bottom Slide Indicators / Tabs -->
            <div class="container">
                <div class="hero-pagination-bar" id="hero-pagination-bar">
                    @foreach($heroSlides as $slideIndex => $slide)
                        <button type="button" class="hero-pagination-pill {{ $slideIndex === 0 ? 'active' : '' }}" data-slide="{{ $slideIndex }}">
                            <span class="pill-number">0{{ $slideIndex + 1 }}</span>
                            <span class="pill-title">{{ $slide['badge'] }}</span>
                            <span class="pill-progress"><span class="pill-progress-bar"></span></span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- Value Proposition Pillars -->
    <section class="value-pillars">
        <div class="container pillars-grid">
            <div class="pillar-card">
                <div class="pillar-icon-box">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="1" y="3" width="15" height="13"></rect>
                        <polygon points="16 8 20 8 23 11 23 16 16 16 8"></polygon>
                        <circle cx="5.5" cy="18.5" r="2.5"></circle>
                        <circle cx="18.5" cy="18.5" r="2.5"></circle>
                    </svg>
                </div>
                <div>
                    <h4 class="pillar-title">Despacho a Todo Chile</h4>
                    <p class="pillar-desc">Entrega en 24/48 hrs en RM y envíos diarios a todas las regiones vía couriers express.</p>
                </div>
            </div>

            <div class="pillar-card">
                <div class="pillar-icon-box">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                </div>
                <div>
                    <h4 class="pillar-title">Factura Inmediata SII</h4>
                    <p class="pillar-desc">Emisión automática de factura electrónica o boleta con tu RUT y Razón Social.</p>
                </div>
            </div>

            <div class="pillar-card">
                <div class="pillar-icon-box">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                </div>
                <div>
                    <h4 class="pillar-title">Garantía Oficial</h4>
                    <p class="pillar-desc">Todos los productos son 100% nuevos, sellados y con respaldo directo del fabricante.</p>
                </div>
            </div>

            <div class="pillar-card">
                <div class="pillar-icon-box">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                    </svg>
                </div>
                <div>
                    <h4 class="pillar-title">Asesoría Especializada</h4>
                    <p class="pillar-desc">Atención técnica personalizada por WhatsApp y teléfono para armar tu proyecto IT.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Showcase (Elegant Slow Horizontal Infinite Scroll) -->
    <section class="categories-showcase" id="categories-section">
        <div class="container">
            <div class="categories-header-row">
                <div>
                    <span class="section-subtitle">Familias de Hardware & Equipamiento</span>
                    <h2 class="section-title" style="margin-bottom: 0;">Explora por Categoría</h2>
                </div>
                <div class="categories-nav-controls">
                    <button type="button" class="cat-nav-btn" id="cat-prev-btn" aria-label="Desplazar hacia la izquierda" title="Ver categorías anteriores">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"></polyline></svg>
                    </button>
                    <button type="button" class="cat-nav-btn" id="cat-next-btn" aria-label="Desplazar hacia la derecha" title="Ver categorías siguientes">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </button>
                    <a href="{{ route('shop.index') }}" class="btn-view-all-cats">
                        <span>Ver catálogo completo ({{ $categories->count() }})</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </a>
                </div>
            </div>
        </div>

        <div class="categories-carousel-outer">
            <div class="categories-scroll-container" id="categories-scroll-container">
                <div class="categories-cards-track" id="categories-cards-track">
                    @foreach($categories->concat($categories)->concat($categories) as $cat)
                        <a href="{{ route('shop.index', ['categoria' => $cat->slug]) }}" class="category-card" title="{{ $cat->name }}">
                            <div class="category-icon-wrapper">
                                <img src="{{ $cat->icon_url }}" alt="{{ $cat->name }}" loading="lazy">
                            </div>
                            <h3 class="category-title">{{ $cat->name }}</h3>
                            <span class="category-count">{{ $cat->products_count }} productos</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Products Section (Matching Screenshot Headings) -->
    <section style="padding: 70px 0; background: #f8fafc;">
        <div class="container">
            <div class="section-header">
                <span class="section-subtitle">Equipamiento Destacado</span>
                <h2 class="section-title">Soluciones Tecnológicas</h2>
                <p class="section-desc">Explora nuestro catálogo de hardware de alta gama y potencia el rendimiento diario de tu empresa.</p>
            </div>

            <div class="products-grid">
                @foreach($featuredProducts as $product)
                    <div class="product-card">
                        <div class="product-badge-wrap">
                            @if($product->stock > 0)
                                <span class="badge-stock">En Stock ({{ $product->stock }})</span>
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
            </div>

            <div style="text-align: center; margin-top: 45px;">
                <a href="{{ route('shop.index') }}" class="btn btn-secondary btn-lg">
                    <span>Ver Todo el Catálogo de Hardware ({{ \App\Models\Product::where('is_active', true)->count() }} Productos)</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- Brands Carousel / Partners -->
    <section style="background: #ffffff; padding: 50px 0; border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color);">
        <div class="container">
            <div style="text-align: center; font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); margin-bottom: 30px;">
                Distribuidores Oficiales de las Marcas Líderes
            </div>
            <div style="display: flex; justify-content: space-around; align-items: center; flex-wrap: wrap; gap: 30px; opacity: 0.75;">
                <span style="font-family:'Plus Jakarta Sans'; font-weight:800; font-size:24px; color:#1e293b;">HP</span>
                <span style="font-family:'Plus Jakarta Sans'; font-weight:800; font-size:24px; color:#e2231a;">Lenovo</span>
                <span style="font-family:'Plus Jakarta Sans'; font-weight:800; font-size:24px; color:#0076ce;">DELL</span>
                <span style="font-family:'Plus Jakarta Sans'; font-weight:800; font-size:24px; color:#1e293b;">Kingston</span>
                <span style="font-family:'Plus Jakarta Sans'; font-weight:800; font-size:24px; color:#002244;">ASUS</span>
                <span style="font-family:'Plus Jakarta Sans'; font-weight:800; font-size:24px; color:#001489;">logitech</span>
                <span style="font-family:'Plus Jakarta Sans'; font-weight:800; font-size:24px; color:#003399;">EPSON</span>
                <span style="font-family:'Plus Jakarta Sans'; font-weight:800; font-size:24px; color:#d91424;">brother</span>
            </div>
        </div>
    </section>

    <!-- B2B Quotation CTA Banner -->
    <section style="background: linear-gradient(135deg, #0a192f 0%, #0369a1 100%); color:#ffffff; padding: 60px 0;">
        <div class="container" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 24px;">
            <div>
                <span style="background: rgba(255,255,255,0.15); padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: 700; text-transform: uppercase;">Soluciones Corporativas B2B</span>
                <h2 style="color:#ffffff; font-size: 32px; margin: 10px 0 6px;">¿Necesitas cotizar por volumen para tu empresa?</h2>
                <p style="color: #cbd5e1; font-size: 16px; max-width: 600px;">
                    Contamos con ejecutivos dedicados para licitaciones, compras corporativas y líneas de crédito 30 días para instituciones.
                </p>
            </div>
            <div style="display: flex; gap: 14px;">
                <a href="{{ route('page.contact') }}" class="btn btn-secondary btn-lg" style="background:#ffffff; color:#0a192f;">
                    Solicitar Cotización Formal
                </a>
            </div>
        </div>
    </section>

@endsection
