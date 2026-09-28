@extends('layouts.app')

@section('title', 'INEXUS | Equipamiento Destacado y Soluciones Tecnológicas en Chile')

@section('content')

    <!-- Top Categories Showcase (Exactly matching screenshot styling) -->
    <section class="categories-showcase">
        <div class="container">
            <div class="category-cards-grid">
                @foreach($categories as $cat)
                    <a href="{{ route('shop.index', ['categoria' => $cat->slug]) }}" class="category-card">
                        <div class="category-icon-wrapper">
                            <img src="{{ $cat->icon_url }}" alt="{{ $cat->name }}">
                        </div>
                        <h3 class="category-title">{{ $cat->name }}</h3>
                        <span class="category-count">{{ $cat->products_count }} productos</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Hero Sales Funnel Section -->
    <section class="hero-section">
        <div class="container hero-grid">
            <div>
                <span class="hero-tagline">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                    </svg>
                    Mayorista & Retail IT Chile
                </span>
                <h1 class="hero-title">
                    Hardware de alta gama y <span>soluciones tecnológicas</span> integrales.
                </h1>
                <p class="hero-description">
                    Abastecemos a empresas, instituciones y profesionales con el mejor equipamiento informático, servidores, notebooks de resistencia corporativa y componentes de última generación.
                </p>
                <div class="hero-ctas">
                    <a href="{{ route('shop.index') }}" class="btn btn-primary btn-lg">
                        <span>Explorar Catálogo</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </a>
                    <a href="{{ route('page.contact') }}" class="btn btn-secondary btn-lg">
                        <span>Cotización para Empresas</span>
                    </a>
                </div>
            </div>

            <div class="hero-media-box">
                <div class="hero-card-featured">
                    <span class="hero-card-badge">Oferta Destacada</span>
                    <div style="height: 240px; display:flex; align-items:center; justify-content:center; margin-bottom:16px;">
                        <img src="https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?w=600&auto=format&fit=crop&q=80" alt="Lenovo ThinkPad E14" style="max-height: 220px; object-fit: contain;">
                    </div>
                    <div style="font-size:12px; color:var(--primary); font-weight:700; text-transform:uppercase;">Notebook Corporativo</div>
                    <h3 style="font-size:17px; margin:4px 0 8px;">Lenovo ThinkPad E14 Gen 5 Core i7</h3>
                    <p style="font-size:13px; color:var(--text-muted); margin-bottom:14px;">16GB RAM | 512GB SSD NVMe | Windows 11 Pro | Garantía 3 Años</p>
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <span style="font-size:11.5px; color:#64748b; display:block;">Precio Contado / Transferencia</span>
                            <span style="font-size:22px; font-weight:800; color:var(--navy-900); font-family:'Plus Jakarta Sans';">$ 949.990 CLP</span>
                        </div>
                        <a href="{{ route('shop.index') }}" class="btn btn-primary" style="padding:8px 16px;">Ver Oferta</a>
                    </div>
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
                        <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
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
