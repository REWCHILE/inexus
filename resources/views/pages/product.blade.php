@extends('layouts.app')

@section('title', $product->meta_title ?: $product->name . ' | INEXUS Chile')
@section('meta_description', $product->meta_description ?: Str::limit(strip_tags($product->short_description ?: $product->description), 155))
@section('og_image', $product->image_url)

@section('json_ld')
    <!-- Schema.org Product JSON-LD -->
    <script type="application/ld+json">
        {!! json_encode($productSchema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) !!}
    </script>

    @if($faqSchema)
        <!-- Schema.org FAQPage JSON-LD -->
        <script type="application/ld+json">
            {!! json_encode($faqSchema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) !!}
        </script>
    @endif

    <!-- Schema.org Breadcrumbs JSON-LD -->
    <script type="application/ld+json">
        {!! json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endsection

@section('content')

    <!-- Breadcrumb bar -->
    <div style="background:#f1f5f9; padding: 14px 0; border-bottom: 1px solid var(--border-color);">
        <div class="container" style="display:flex; align-items:center; gap:8px; font-size:13px; color:var(--text-muted);">
            <a href="{{ route('home') }}" style="color:var(--navy-800);">Inicio</a>
            <span>/</span>
            <a href="{{ route('shop.index') }}" style="color:var(--navy-800);">Tienda</a>
            @if($product->category)
                <span>/</span>
                <a href="{{ route('shop.index', ['categoria' => $product->category->slug]) }}" style="color:var(--navy-800);">{{ $product->category->name }}</a>
            @endif
            <span>/</span>
            <span style="color:var(--primary); font-weight:600;">{{ Str::limit($product->name, 45) }}</span>
        </div>
    </div>

    <div class="container">
        <!-- Main Product Section -->
        <div class="product-detail-grid">
            
            <!-- Gallery / Image Box -->
            <div>
                <div class="product-gallery-container" id="product-gallery-container">
                    @if(count($product->gallery_images) > 1)
                        <button type="button" class="gallery-arrow-btn gallery-prev" id="gallery-prev-btn" aria-label="Imagen anterior">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"></polyline></svg>
                        </button>
                    @endif

                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="product-main-view" id="main-product-img">

                    @if(count($product->gallery_images) > 1)
                        <button type="button" class="gallery-arrow-btn gallery-next" id="gallery-next-btn" aria-label="Imagen siguiente">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
                        </button>
                        
                        <div class="gallery-autoplay-badge" id="gallery-autoplay-badge" title="Pase de imágenes automático elegante">
                            <span class="gallery-indicator-dot"></span>
                            <span id="gallery-counter">1 / {{ count($product->gallery_images) }}</span>
                        </div>
                    @endif
                </div>

                @if(count($product->gallery_images) > 1)
                    <div style="display:flex; gap:10px; margin-top:14px; overflow-x:auto; padding-bottom:8px;" class="custom-scrollbar" id="gallery-thumbs-container">
                        @foreach($product->gallery_images as $idx => $gImg)
                            <div class="thumb-item gallery-thumb-item {{ $idx === 0 ? 'active' : '' }}" 
                                 data-index="{{ $idx }}"
                                 data-src="{{ $gImg }}"
                                 style="width:68px; height:68px; border:2px solid {{ $idx === 0 ? 'var(--primary)' : '#e2e8f0' }}; border-radius:8px; padding:4px; background:#fff; cursor:pointer; flex-shrink:0; transition:all 0.2s ease;">
                                <img src="{{ $gImg }}" alt="Foto producto {{ $idx+1 }}" style="width:100%; height:100%; object-fit:contain;">
                            </div>
                        @endforeach
                    </div>
                @endif

                @if(!$product->has_custom_image)
                    <div style="margin-top:12px; font-size:12px; color:var(--text-muted); text-align:center;">
                        ℹ Imagen de catálogo en proceso de indexación. Las especificaciones corresponden exactamente al SKU fabricante.
                    </div>
                @endif
            </div>

            <!-- Product Purchase Info -->
            <div>
                <div class="product-meta-header">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span class="product-brand-tag">{{ $product->brand ?: 'INEXUS Certificado' }}</span>
                        @if($product->stock > 0)
                            <span class="badge badge-success">En Stock ({{ $product->stock }} Unidades)</span>
                        @else
                            <span class="badge badge-danger">Agotado Temporalmente</span>
                        @endif
                    </div>
                    <h1 class="product-page-title">{{ $product->name }}</h1>

                    <div class="product-sku-row">
                        <span><strong>SKU:</strong> {{ $product->sku }}</span>
                        @if($product->vendor_part_number)
                            <span><strong>P/N:</strong> {{ $product->vendor_part_number }}</span>
                        @endif
                        @if($product->category)
                            <span><strong>Categoría:</strong> {{ $product->category->name }}</span>
                        @endif
                    </div>
                </div>

                @if($product->short_description)
                    <p style="font-size:15px; color:var(--text-muted); margin-bottom:20px; line-height:1.6;">
                        {{ $product->short_description }}
                    </p>
                @endif

                <!-- Dual Pricing Differentiated Box (Transferencia vs Tarjeta) -->
                <div class="dual-pricing-detail-card">
                    <div class="dual-pricing-grid-compare">
                        
                        <!-- 1. Transferencia / Efectivo (Highlight Card) -->
                        <div class="price-transfer-card">
                            <div class="price-card-header">
                                <span class="price-badge-discount">🔥 {{ $product->transfer_discount_percentage }}% DCTO</span>
                                <span class="price-label-primary">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M17 9V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"></path><rect x="9" y="9" width="13" height="12" rx="2"></rect><circle cx="15.5" cy="15" r="2"></circle></svg>
                                    Transferencia / Efectivo
                                </span>
                            </div>
                            <div class="pricing-current-transfer">{{ $product->formatted_transfer_price }}</div>
                            <div class="price-savings-note">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                <span>Ahorras <strong>{{ $product->formatted_transfer_savings }}</strong> pagando directo</span>
                            </div>
                        </div>

                        <!-- 2. Tarjeta / Webpay / Mercado Pago Card -->
                        <div class="price-normal-card">
                            <div class="price-card-header">
                                <span class="price-label-secondary">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                                    Tarjeta / Webpay / Otros
                                </span>
                            </div>
                            <div class="pricing-normal">{{ $product->formatted_normal_price }}</div>
                            <div class="price-card-note">
                                Hasta 12 cuotas con Mercado Pago
                            </div>
                        </div>

                    </div>

                    <div class="dual-pricing-footer-note">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        <span>Precios incluyen IVA (19%) | Factura Electrónica disponible para empresas con RUT y Giro</span>
                    </div>
                </div>

                <!-- Add to cart Form -->
                <form action="{{ route('cart.add') }}" method="POST" class="ajax-add-to-cart-form" style="margin-bottom: 30px;">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">

                    <div style="display:flex; align-items:center; gap:16px; margin-bottom:16px;">
                        <div style="display:flex; align-items:center; border:1px solid var(--border-color); border-radius:var(--radius-sm); overflow:hidden; background:#ffffff;">
                            <button type="button" onclick="const q = document.getElementById('qty-input'); if(q.value > 1) q.value--;" style="width:38px; height:44px; background:#f8fafc; border:none; cursor:pointer; font-weight:700; font-size:16px;">-</button>
                            <input type="number" id="qty-input" name="quantity" value="1" min="1" max="{{ max(1, $product->stock) }}" style="width:50px; height:44px; border:none; text-align:center; font-weight:700; font-size:15px;">
                            <button type="button" onclick="const q = document.getElementById('qty-input'); q.value++;" style="width:38px; height:44px; background:#f8fafc; border:none; cursor:pointer; font-weight:700; font-size:16px;">+</button>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg" style="flex:1;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="9" cy="21" r="1"></circle>
                                <circle cx="20" cy="21" r="1"></circle>
                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                            </svg>
                            <span>Agregar al Carrito</span>
                        </button>
                    </div>
                </form>

                <!-- Product WhatsApp Inquiry Button -->
                @php
                    $waProductText = urlencode("Hola INEXUS, tengo una consulta sobre el producto:\n*{$product->name}*\nSKU: {$product->sku}\nEnlace: " . url()->current());
                @endphp
                <div style="margin-bottom: 22px;">
                    <a href="https://wa.me/56987654321?text={{ $waProductText }}" 
                       target="_blank" 
                       rel="noopener" 
                       class="btn btn-whatsapp-product btn-block"
                       title="Consultar dudas sobre este producto en WhatsApp">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.699c.971.53 2.01.815 3.094.815 3.181 0 5.767-2.586 5.768-5.766 0-3.18-2.586-5.766-5.768-5.766zm9.969 5.766c0 5.518-4.482 10-10 10-1.748 0-3.385-.45-4.815-1.238l-7.185 1.886 1.92-7.009c-.846-1.47-1.32-3.179-1.32-4.999 0-5.518 4.482-10 10-10 5.518 0 10 4.482 10 10z"/>
                        </svg>
                        <span>Consultar dudas sobre este producto por WhatsApp</span>
                    </a>
                </div>

                <!-- Value Highlights -->
                <div class="product-value-highlights">
                    <div style="display:flex; align-items:center; gap:10px; font-size:13px; color:var(--navy-800);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2">
                            <rect x="1" y="3" width="15" height="13"></rect>
                            <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                        </svg>
                        <span>Despacho rápido a todo Chile</span>
                    </div>
                    <div style="display:flex; align-items:center; gap:10px; font-size:13px; color:var(--navy-800);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        </svg>
                        <span>Garantía oficial y legal 6 meses</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Interactive Tabs (Specs, Description, FAQs Accordion, Warranty) -->
        <div class="tabs-container">
            <div class="tabs-header">
                <button type="button" class="tab-btn active" data-target="tab-specs">Características Técnicas</button>
                <button type="button" class="tab-btn" data-target="tab-desc">Descripción Detallada</button>
                <button type="button" class="tab-btn" data-target="tab-faqs">Preguntas Frecuentes (FAQs)</button>
                <button type="button" class="tab-btn" data-target="tab-warranty">Garantía y Devoluciones</button>
            </div>

            <!-- Tab 1: Specifications Table -->
            <div class="tab-pane" id="tab-specs" style="display:block;">
                <div class="specs-table-container" style="background:#ffffff; border:1px solid var(--border-color); border-radius:var(--radius-lg); overflow-x:auto;">
                    @if(!empty($product->specifications) && count($product->specifications) > 0)
                        <table style="width:100%; border-collapse:collapse; font-size:14px;">
                            <tbody>
                                @foreach($product->specifications as $key => $val)
                                    <tr style="border-bottom:1px solid var(--border-color);">
                                        <td style="padding:14px 20px; font-weight:700; width:30%; background:#f8fafc; color:var(--navy-800);">
                                            {{ $key }}
                                        </td>
                                        <td style="padding:14px 20px; color:var(--text-main);">
                                            {{ $val }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div style="padding: 24px; color:var(--text-muted);">
                            <p>Especificaciones detalladas de fábrica provistas para el SKU <strong>{{ $product->sku }}</strong>.</p>
                            <ul style="margin: 12px 0 0 20px; line-height: 1.8;">
                                <li><strong>Marca:</strong> {{ $product->brand ?: 'Genérico Certificado' }}</li>
                                <li><strong>Modelo / Part Number:</strong> {{ $product->vendor_part_number ?: $product->sku }}</li>
                                <li><strong>Disponibilidad:</strong> {{ $product->stock > 0 ? 'En existencia para entrega inmediata' : 'Bajo pedido' }}</li>
                                <li><strong>Compatibilidad de Red Eléctrica:</strong> 220V 50Hz (Estándar Chile)</li>
                            </ul>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Tab 2: Detailed Description -->
            <div class="tab-pane" id="tab-desc" style="display:none;">
                <div style="background:#ffffff; border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:30px; font-size:15px; line-height:1.8;">
                    {!! $product->description ?: '<p>' . e($product->short_description) . '</p>' !!}
                </div>
            </div>

            <!-- Tab 3: Interactive FAQs Accordion (As Requested!) -->
            <div class="tab-pane" id="tab-faqs" style="display:none;">
                <div class="faqs-wrapper">
                    @php
                        $faqsList = $product->faqs;
                        if(empty($faqsList) || !is_array($faqsList)) {
                            $faqsList = [
                                ['question' => '¿Este producto es nuevo y sellado de fábrica?', 'answer' => 'Sí, todos nuestros equipos y partes provienen directamente de mayoristas oficiales autorizados como Ingram Micro Chile. Son 100% nuevos en caja sellada con sellos de garantía intactos.'],
                                ['question' => '¿Puedo solicitar Factura Electrónica a nombre de mi empresa?', 'answer' => 'Por supuesto. Al completar tu compra en el checkout, selecciona la opción "Factura" e ingresa el RUT, Razón Social, Giro comercial y Dirección tributaria de tu empresa. El documento tributario se emitirá automáticamente con validación ante el SII.'],
                                ['question' => '¿Cuáles son los plazos y formas de envío?', 'answer' => 'Enviamos a todas las comunas de Chile a través de couriers certificados con número de seguimiento. Los tiempos habituales son de 24 a 48 horas hábiles para la Región Metropolitana y de 2 a 4 días hábiles para otras regiones.'],
                                ['question' => '¿Qué cubre la garantía?', 'answer' => 'Cubre cualquier defecto de fabricación o falla de hardware durante el período de garantía legal de 6 meses establecido por la ley chilena (SERNAC), sumado a las extensiones otorgadas por marcas oficiales como Lenovo, HP, Dell o Kingston.'],
                            ];
                        }
                    @endphp

                    @foreach($faqsList as $faq)
                        <div class="faq-accordion-item">
                            <button type="button" class="faq-question-btn">
                                <span>{{ $faq['question'] }}</span>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </button>
                            <div class="faq-answer-content">
                                <p>{{ $faq['answer'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Tab 4: Warranty & Returns -->
            <div class="tab-pane" id="tab-warranty" style="display:none;">
                <div style="background:#ffffff; border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:30px; font-size:15px; line-height:1.7;">
                    <h4 style="margin-bottom:12px; color:var(--navy-900);">Garantía Legal Conforme a la Ley del Consumidor en Chile</h4>
                    <p style="margin-bottom:16px;">
                        En conformidad con la Ley Nº 19.496 sobre Protección de los Derechos de los Consumidores y la actualización legal Pro-Consumidor, este producto cuenta con un plazo de <strong>6 meses de garantía legal</strong> a partir de la recepción del producto.
                    </p>
                    <p style="margin-bottom:16px;">
                        Si el producto presenta fallas técnicas atribuibles a defectos de fabricación, el cliente tiene el derecho legal de optar libremente por:
                    </p>
                    <ul style="margin-left:24px; margin-bottom:16px;">
                        <li>La reparación gratuita del producto por servicio técnico autorizado.</li>
                        <li>La reposición por una unidad nueva idéntica o equivalente.</li>
                        <li>La devolución íntegra del dinero pagado.</li>
                    </ul>
                    <a href="{{ route('page.returns') }}" style="color:var(--primary); font-weight:700;">
                        Conoce los detalles de nuestra política completa de Cambios y Devoluciones →
                    </a>
                </div>
            </div>
        </div>

        <!-- Related Products Section -->
        @if($relatedProducts->count() > 0)
            <div style="margin: 60px 0;">
                <div class="section-header" style="text-align: left; margin-bottom: 24px;">
                    <span class="section-subtitle">Complementos & Alternativas</span>
                    <h3 style="font-size:24px;">Productos Relacionados en {{ $product->category?->name }}</h3>
                </div>

                <div class="products-grid">
                    @foreach($relatedProducts as $rel)
                        <div class="product-card">
                            <a href="{{ route('product.show', $rel->slug) }}" class="product-img-box">
                                <img src="{{ $rel->image_url }}" alt="{{ $rel->name }}">
                            </a>
                            <div class="product-info">
                                <h4 class="product-title" style="height:auto;">
                                    <a href="{{ route('product.show', $rel->slug) }}">{{ $rel->name }}</a>
                                </h4>
                                <div class="product-price-box" style="margin-top:auto;">
                                    <div class="dual-pricing-card">
                                        <span class="price-badge-pill">Transferencia {{ $rel->transfer_discount_percentage }}% OFF</span>
                                        <div class="price-transfer-val">{{ $rel->formatted_transfer_price }}</div>
                                        <div class="price-normal-wrap">
                                            <span>Tarjeta:</span>
                                            <span class="price-normal-val">{{ $rel->formatted_normal_price }}</span>
                                        </div>
                                    </div>
                                    <a href="{{ route('product.show', $rel->slug) }}" class="btn-quick-add" title="Ver producto">→</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>

@endsection
