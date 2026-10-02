@extends('layouts.app')

@section('title', 'Finalizar Compra Seguro | INEXUS Chile')

@section('content')

@php
    $regions = $regions ?? app(\App\Services\BlueExpressService::class)->getRegions();
    $initialRegion = $initialRegion ?? 'CL-RM';
    $communes = $communes ?? app(\App\Services\BlueExpressService::class)->getCommunesByRegion($initialRegion);
    $initialCommune = $initialCommune ?? 'Santiago';
    $shippingServiceName = $shippingServiceName ?? 'Blue Express Express (Terrestre)';
    $shippingPromise = $shippingPromise ?? 'Hasta 2 a 3 días hábiles';
    $initialShippingType = $initialShippingType ?? 'domicilio';
    $isFreeShipping = $isFreeShipping ?? false;
@endphp

<style>
    /* Prevent floating elements from obstructing checkout inputs on mobile */
    .floating-whatsapp {
        display: none !important;
    }

    /* Checkout Layout Grid */
    .checkout-wrapper {
        padding: 30px 16px 60px;
        max-width: 1200px;
        margin: 0 auto;
    }
    .checkout-grid {
        display: grid;
        grid-template-columns: 1.35fr 0.85fr;
        gap: 32px;
        align-items: start;
    }
    .checkout-card {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 24px;
        margin-bottom: 22px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }
    .checkout-card-title {
        font-size: 17.5px;
        font-weight: 700;
        margin-bottom: 18px;
        display: flex;
        align-items: center;
        gap: 10px;
        color: var(--navy-900);
    }
    .step-number {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: var(--primary);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 800;
        flex-shrink: 0;
    }

    /* Form Fields */
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-bottom: 14px;
    }
    .form-group {
        margin-bottom: 14px;
    }
    .form-label {
        display: block;
        font-weight: 600;
        font-size: 13px;
        margin-bottom: 6px;
        color: var(--navy-800);
    }
    .form-control {
        width: 100%;
        height: 46px;
        padding: 10px 14px;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-sm);
        font-size: 14.5px;
        background: #ffffff;
        transition: var(--transition);
        box-sizing: border-box;
    }
    .form-control:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
    }

    /* Shipping Method Selector (Domicilio vs Punto Pick Up) */
    .shipping-method-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
        margin-bottom: 18px;
    }
    .shipping-option-card {
        border: 2px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 16px 14px;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        background: #ffffff;
        user-select: none;
    }
    .shipping-option-card:hover {
        border-color: #93c5fd;
        background: #f8fafc;
    }
    .shipping-option-card.active {
        border-color: #0033a1;
        background: #eff6ff;
        box-shadow: 0 2px 8px rgba(0, 51, 161, 0.08);
    }
    .shipping-option-icon {
        font-size: 22px;
        line-height: 1;
        margin-top: 2px;
    }
    .shipping-option-title {
        font-weight: 700;
        font-size: 14.5px;
        color: var(--navy-900);
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }
    .shipping-option-desc {
        font-size: 12px;
        color: var(--text-muted);
        margin-top: 3px;
        line-height: 1.35;
    }
    .shipping-option-rate {
        margin-top: 6px;
        font-weight: 800;
        font-size: 13.5px;
        color: #0033a1;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    /* Punto Pick Up Selection Box & Modal */
    .pudo-box-unselected {
        background: #f8fafc;
        border: 2px dashed #0033a1;
        border-radius: var(--radius-md);
        padding: 18px;
        text-align: center;
        margin-top: 14px;
    }
    .pudo-box-confirmed {
        background: #f0fdf4;
        border: 2px solid #16a34a;
        border-radius: var(--radius-md);
        padding: 16px;
        margin-top: 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
    }

    /* Modal for Blue Express PUDO iframe */
    .pudo-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(15, 23, 42, 0.78);
        backdrop-filter: blur(5px);
        z-index: 99999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        box-sizing: border-box;
    }
    .pudo-modal-dialog {
        background: #ffffff;
        border-radius: 16px;
        width: 95vw;
        max-width: 1240px;
        height: 88vh;
        min-height: 600px;
        max-height: 860px;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    .pudo-modal-header {
        padding: 14px 22px;
        background: #f8fafc;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-shrink: 0;
    }
    .pudo-modal-close {
        background: none;
        border: none;
        font-size: 28px;
        line-height: 1;
        color: #64748b;
        cursor: pointer;
        padding: 4px 10px;
        border-radius: 8px;
        transition: all 0.2s;
    }
    .pudo-modal-close:hover {
        background: #e2e8f0;
        color: #0f172a;
    }
    .pudo-modal-body {
        flex: 1 1 auto;
        position: relative;
        width: 100%;
        height: 100%;
        min-height: 480px;
        background: #ffffff;
        overflow: hidden;
    }
    .pudo-modal-body iframe,
    #pudo-iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100% !important;
        height: 100% !important;
        border: none !important;
        display: block;
    }
    .pudo-modal-footer {
        padding: 12px 22px;
        background: #f8fafc;
        border-top: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 13.5px;
        color: #475569;
        flex-shrink: 0;
    }

    /* Mobile Accordion Preview */
    .mobile-summary-toggle {
        display: none;
        background: #f1f5f9;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 14px 18px;
        margin-bottom: 20px;
        cursor: pointer;
        align-items: center;
        justify-content: space-between;
        font-weight: 700;
        font-size: 14.5px;
        color: var(--navy-900);
    }
    .mobile-summary-content {
        display: none;
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 16px;
        margin-bottom: 20px;
    }

    /* Responsive adjustments */
    @media (max-width: 991px) {
        .checkout-grid {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .checkout-summary-column {
            position: static !important;
            order: 2; /* Forces order summary & pay button strictly to the very bottom */
            width: 100%;
        }
        .mobile-summary-toggle {
            display: flex;
        }
        .form-row {
            grid-template-columns: 1fr;
            gap: 0;
        }
        .shipping-method-grid {
            grid-template-columns: 1fr;
            gap: 10px;
        }
        .radio-cards {
            grid-template-columns: 1fr;
        }
        .checkout-wrapper {
            padding: 20px 12px 40px;
        }
    }

    @media (max-width: 768px) {
        .pudo-modal-overlay {
            padding: 0 !important;
        }
        .pudo-modal-dialog {
            width: 100vw !important;
            height: 100vh !important;
            max-width: 100vw !important;
            max-height: 100vh !important;
            min-height: 100vh !important;
            border-radius: 0 !important;
            border: none !important;
        }
        .pudo-modal-header {
            padding: 12px 14px !important;
        }
        .pudo-modal-header h3 {
            font-size: 15px !important;
        }
        .pudo-modal-header p {
            display: none !important;
        }
        .pudo-modal-body {
            height: calc(100vh - 110px) !important;
            min-height: 0 !important;
        }
        .pudo-modal-footer {
            padding: 10px 14px !important;
            font-size: 11.5px !important;
            flex-direction: column !important;
            gap: 6px !important;
            text-align: center !important;
        }
        .pudo-modal-footer button {
            width: 100% !important;
        }
    }
</style>

    <!-- Breadcrumb bar -->
    <div style="background:#f1f5f9; padding: 14px 0; border-bottom: 1px solid var(--border-color);">
        <div class="container" style="display:flex; align-items:center; gap:8px; font-size:13px; color:var(--text-muted);">
            <a href="{{ route('home') }}" style="color:var(--navy-800);">Inicio</a>
            <span>/</span>
            <a href="{{ route('cart.index') }}" style="color:var(--navy-800);">Carrito</a>
            <span>/</span>
            <span style="color:var(--primary); font-weight:600;">Checkout Seguro</span>
        </div>
    </div>

    <div class="checkout-wrapper">
        <h1 style="font-size:26px; font-weight:800; color:var(--navy-900); margin-bottom:20px;">Finalizar Pedido Seguro</h1>

        <!-- Mobile Top Toggle: Quick Items Preview -->
        <div class="mobile-summary-toggle" onclick="toggleMobileSummary()">
            <div style="display:flex; align-items:center; gap:8px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
                <span>Ver resumen del pedido ({{ count($cart) }} {{ count($cart) === 1 ? 'producto' : 'productos' }})</span>
                <span id="mobile-summary-arrow" style="font-size:12px; transition:transform 0.2s;">▼</span>
            </div>
            <span id="mobile-top-total" style="color:var(--primary); font-weight:800; font-family:'Plus Jakarta Sans';">
                ${{ number_format($total, 0, ',', '.') }} CLP
            </span>
        </div>

        <div id="mobile-summary-dropdown" class="mobile-summary-content">
            <div style="display:flex; flex-direction:column; gap:12px; margin-bottom:14px;">
                @foreach($cart as $item)
                    <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; font-size:13px;">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" style="width:36px; height:36px; object-fit:contain; border:1px solid var(--border-color); border-radius:6px; padding:2px;">
                            <div>
                                <div style="font-weight:600; line-height:1.2;">{{ $item['name'] }}</div>
                                <span style="font-size:11.5px; color:var(--text-muted);">Cant: {{ $item['quantity'] }}</span>
                            </div>
                        </div>
                        <span style="font-weight:700; white-space:nowrap;">${{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}</span>
                    </div>
                @endforeach
            </div>
            <div style="font-size:12px; color:var(--text-muted); border-top:1px solid var(--border-color); padding-top:8px;">
                El desglose detallado con costos de envío finales está disponible al final de la página.
            </div>
        </div>

        <form action="{{ route('checkout.process') }}" method="POST" id="checkout-form">
            @csrf

            <!-- Hidden Fields for Tracking Selection -->
            <input type="hidden" name="shipping_type" id="shipping_type" value="{{ $initialShippingType }}">
            <input type="hidden" name="agency_id" id="agency_id" value="">
            <input type="hidden" name="agency_name" id="agency_name" value="">
            <input type="hidden" name="agency_address" id="agency_address" value="">
            <input type="hidden" name="agency_city" id="agency_city" value="">
            <input type="hidden" name="agency_state" id="agency_state" value="">

            <div class="checkout-grid">
                
                <!-- Left Column: Step-by-Step Forms -->
                <div class="checkout-forms-column">
                    
                    <!-- 1. Customer Details (Nombre y Apellido separated) -->
                    <div class="checkout-card">
                        <h2 class="checkout-card-title">
                            <span class="step-number">1</span>
                            Información de Contacto
                        </h2>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="customer_first_name">Nombre *</label>
                                <input type="text" id="customer_first_name" name="customer_first_name" class="form-control" required placeholder="Ej: Gonzalo" value="{{ old('customer_first_name') }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="customer_last_name">Apellido *</label>
                                <input type="text" id="customer_last_name" name="customer_last_name" class="form-control" required placeholder="Ej: Martínez" value="{{ old('customer_last_name') }}">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="customer_email">Correo Electrónico *</label>
                                <input type="email" id="customer_email" name="customer_email" class="form-control" required placeholder="tu-email@empresa.cl" value="{{ old('customer_email') }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="customer_phone">Teléfono / WhatsApp *</label>
                                <input type="tel" id="customer_phone" name="customer_phone" class="form-control" required placeholder="+56 9 1234 5678" value="{{ old('customer_phone') }}">
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" for="customer_rut">RUT Comprador *</label>
                            <input type="text" id="customer_rut" name="customer_rut" class="form-control" required placeholder="Ej: 12.345.678-9" value="{{ old('customer_rut') }}">
                        </div>
                    </div>

                    <!-- 2. Document Type (Boleta vs Factura) -->
                    <div class="checkout-card">
                        <h2 class="checkout-card-title">
                            <span class="step-number">2</span>
                            Documento Tributario (SII Chile)
                        </h2>

                        <div class="radio-cards">
                            <label class="radio-card active" id="card-boleta">
                                <input type="radio" name="document_type" value="boleta" id="doc_type_boleta" checked style="accent-color:var(--primary);">
                                <div>
                                    <div style="font-weight:700; color:var(--navy-900);">Boleta Electrónica</div>
                                    <div style="font-size:12px; color:var(--text-muted);">Para personas naturales y consumo final</div>
                                </div>
                            </label>

                            <label class="radio-card" id="card-factura">
                                <input type="radio" name="document_type" value="factura" id="doc_type_factura" style="accent-color:var(--primary);">
                                <div>
                                    <div style="font-weight:700; color:var(--navy-900);">Factura Electrónica</div>
                                    <div style="font-size:12px; color:var(--text-muted);">Para empresas y contribuyentes de 1ª Categoría</div>
                                </div>
                            </label>
                        </div>

                        <!-- Dynamic Factura Fields -->
                        <div id="factura-fields" style="display:none; background:#f8fafc; border:1px solid var(--border-color); border-radius:var(--radius-md); padding:18px; margin-top:14px;">
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label" for="company_name">Razón Social *</label>
                                    <input type="text" id="company_name" name="company_name" class="form-control" placeholder="Nombre legal de la empresa" value="{{ old('company_name') }}">
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="company_rut">RUT Empresa *</label>
                                    <input type="text" id="company_rut" name="company_rut" class="form-control" placeholder="76.123.456-7" value="{{ old('company_rut') }}">
                                </div>
                            </div>
                            <div class="form-group" style="margin-bottom:0;">
                                <label class="form-label" for="company_giro">Giro Comercial *</label>
                                <input type="text" id="company_giro" name="company_giro" class="form-control" placeholder="Ej: Servicios de informática, consultoría..." value="{{ old('company_giro') }}">
                            </div>
                        </div>
                    </div>

                    <!-- 3. Delivery Method: Domicilio vs Punto Pick Up Blue Express -->
                    <div class="checkout-card">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px;">
                            <h2 class="checkout-card-title" style="margin-bottom:0;">
                                <span class="step-number">3</span>
                                Método de Entrega & Courier
                            </h2>
                            <div style="display:flex; align-items:center; gap:6px; background:#eff6ff; padding:4px 10px; border-radius:6px; border:1px solid #bfdbfe;">
                                <img src="{{ asset('images/blue-express.svg') }}" alt="Blue Express" style="height:18px; width:auto;">
                                <span style="font-size:11.5px; font-weight:700; color:#0033a1;">Courier Oficial</span>
                            </div>
                        </div>

                        <!-- Delivery Modality Selection (Domicilio vs Punto Pick Up) -->
                        <div class="shipping-method-grid">
                            <label class="shipping-option-card active" id="card-delivery-domicilio" onclick="setShippingType('domicilio')">
                                <input type="radio" name="delivery_choice" value="domicilio" checked style="margin-top:3px; accent-color:#0033a1;">
                                <div style="flex:1;">
                                    <div class="shipping-option-title">
                                        <span>🚚 Envío a Domicilio</span>
                                    </div>
                                    <div class="shipping-option-desc">Despacho directo con Blue Express a tu casa u oficina</div>
                                    <div class="shipping-option-rate" id="card-price-domicilio">
                                        ${{ number_format($quoteDomicilio['cost'] ?? 3067, 0, ',', '.') }} CLP
                                    </div>
                                </div>
                            </label>

                            <label class="shipping-option-card" id="card-delivery-pickup" onclick="setShippingType('pickup')">
                                <input type="radio" name="delivery_choice" value="pickup" style="margin-top:3px; accent-color:#0033a1;">
                                <div style="flex:1;">
                                    <div class="shipping-option-title">
                                        <span>🏪 Punto Pick Up Blue</span>
                                        <span class="badge" style="background:#0033a1; color:#fff; font-size:10px; padding:2px 6px; border-radius:4px;">Económico</span>
                                    </div>
                                    <div class="shipping-option-desc">Retiro en Pronto Copec, agencias y minimarkets oficiales</div>
                                    <div class="shipping-option-rate" id="card-price-pickup">
                                        ${{ number_format($quotePickup['cost'] ?? 2395, 0, ',', '.') }} CLP
                                    </div>
                                </div>
                            </label>
                        </div>

                        <!-- Destination Region and Commune Selectors -->
                        <div class="form-row" style="margin-top:16px;">
                            <div class="form-group">
                                <label class="form-label" for="shipping_region">Región de Destino *</label>
                                <select id="shipping_region" name="shipping_region_code" class="form-control" required onchange="handleRegionChange(this.value)">
                                    @foreach($regions as $rCode => $rData)
                                        <option value="{{ $rCode }}" {{ $rCode === $initialRegion ? 'selected' : '' }}>
                                            {{ $rData['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="shipping_region" id="shipping_region_name" value="{{ $regions[$initialRegion]['name'] ?? 'Región Metropolitana de Santiago' }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="shipping_city">Comuna de Destino *</label>
                                <select id="shipping_city" name="shipping_city" class="form-control" required onchange="handleCommuneChange(this.value)">
                                    @foreach($communes as $commune)
                                        <option value="{{ $commune['name'] }}" {{ $commune['name'] === $initialCommune ? 'selected' : '' }}>
                                            {{ $commune['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Blue Express Rate Card (Live Quote) -->
                        <div id="bluex-rate-card" style="background:#f8fafc; border:1.5px solid #0033a1; border-radius:10px; padding:14px 16px; display:flex; align-items:center; justify-content:space-between; gap:14px; box-shadow:0 1px 3px rgba(0,51,161,0.06); margin-top:10px;">
                            <div style="display:flex; align-items:center; gap:12px;">
                                <div style="width:44px; height:44px; border-radius:8px; background:#fff; border:1px solid #e2e8f0; display:flex; align-items:center; justify-content:center; padding:4px; flex-shrink:0;">
                                    <img src="{{ asset('images/blue-express.svg') }}" alt="Blue Express" style="max-height:34px; max-width:34px; object-fit:contain;">
                                </div>
                                <div>
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <span style="font-weight:700; color:#0033a1; font-size:14.5px;" id="bx-service-name">{{ $shippingServiceName }}</span>
                                    </div>
                                    <div style="font-size:12.5px; color:#64748b; margin-top:2px;" id="bx-promise-display">
                                        ⚡ Tiempo estimado: <strong style="color:#0f172a;" id="bx-promise-text">{{ $shippingPromise }}</strong>
                                    </div>
                                </div>
                            </div>
                            <div style="text-align:right;">
                                <div id="bx-cost-badge" style="font-size:18px; font-weight:800; font-family:'Plus Jakarta Sans'; color:{{ $shipping === 0 ? '#16a34a' : '#0033a1' }};">
                                    {{ $shipping === 0 ? 'GRATIS' : '$' . number_format($shipping, 0, ',', '.') . ' CLP' }}
                                </div>
                                <div id="bx-loading-spinner" style="display:none; font-size:11.5px; color:#0284c7; align-items:center; gap:4px; justify-content:flex-end;">
                                    <svg style="animation: spin 1s linear infinite;" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Cotizando...
                                </div>
                            </div>
                        </div>

                        <!-- SECTION A: Fields for "Envío a Domicilio" -->
                        <div id="section-domicilio-fields" style="margin-top:16px;">
                            <div class="form-group">
                                <label class="form-label" for="shipping_address">Dirección de Entrega (Calle y Número) *</label>
                                <input type="text" id="shipping_address" name="shipping_address" class="form-control" placeholder="Ej: Av. Andrés Bello 2457, Providencia" value="{{ old('shipping_address') }}">
                            </div>
                            <div class="form-group" style="margin-bottom:0;">
                                <label class="form-label" for="shipping_notes">Depto / Oficina / Referencias de Entrega (Opcional)</label>
                                <input type="text" id="shipping_notes" name="shipping_notes" class="form-control" placeholder="Depto 804 / Dejar en conserjería..." value="{{ old('shipping_notes') }}">
                            </div>
                        </div>

                        <!-- SECTION B: Fields for "Punto Pick Up Blue Express" -->
                        <div id="section-pickup-fields" style="display:none; margin-top:16px;">
                            <!-- State 1: Unselected -->
                            <div id="pudo-select-container" class="pudo-box-unselected" style="padding:22px 18px; background:#f0f7ff; border:2px dashed #0033a1; border-radius:12px;">
                                <div style="font-size:30px; margin-bottom:6px;">🏪</div>
                                <div style="font-weight:800; color:#0033a1; font-size:16px; margin-bottom:4px;">
                                    Selecciona tu Punto Blue Express de Retiro
                                </div>
                                <p style="font-size:13.5px; color:#475569; margin:0 auto 16px; max-width:460px; line-height:1.45;">
                                    Elige la sucursal, Pronto Copec o punto oficial más conveniente en tu comuna para retirar a tu propio ritmo.
                                </p>
                                <button type="button" class="btn btn-primary" onclick="openPudoModal()" style="background:#0033a1; border-color:#0033a1; font-size:14.5px; font-weight:700; padding:12px 26px; display:inline-flex; align-items:center; gap:10px; border-radius:8px; box-shadow:0 4px 14px rgba(0,51,161,0.22); cursor:pointer;">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon></svg>
                                    <span>Abrir Mapa Oficial de Puntos Blue Express</span>
                                </button>
                            </div>

                            <!-- State 2: Confirmed -->
                            <div id="pudo-confirmed-container" class="pudo-box-confirmed" style="display:none;">
                                <div style="display:flex; align-items:flex-start; gap:12px;">
                                    <div style="width:38px; height:38px; border-radius:50%; background:#dcfce7; color:#15803d; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:18px; font-weight:800;">
                                        ✓
                                    </div>
                                    <div>
                                        <span class="badge" style="background:#16a34a; color:#fff; font-size:11px; padding:2px 8px; border-radius:4px; font-weight:700;">
                                            PUNTO BLUE EXPRESS SELECCIONADO
                                        </span>
                                        <h4 id="display-agency-name" style="font-size:15.5px; font-weight:800; color:#0f172a; margin:4px 0 2px;">
                                            --
                                        </h4>
                                        <p id="display-agency-address" style="font-size:13px; color:#475569; margin:0;">
                                            --
                                        </p>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="openPudoModal()" style="font-size:12.5px; white-space:nowrap; padding:6px 12px;">
                                    Cambiar Punto
                                </button>
                            </div>
                        </div>

                    </div>

                    <!-- 4. Payment Method Selection -->
                    <div class="checkout-card">
                        <h2 class="checkout-card-title">
                            <span class="step-number">4</span>
                            Método de Pago en Pesos Chilenos (CLP)
                        </h2>

                        <div style="display:flex; flex-direction:column; gap:14px;">
                            <!-- Flow Pagos Chile (Webpay Plus, Servipag, Mach, etc.) -->
                            <label id="label-method-flow" style="border:2px solid #0957c3; border-radius:var(--radius-md); padding:16px; display:flex; align-items:flex-start; gap:14px; cursor:pointer; background:#f0f7ff; transition:all 0.2s ease;">
                                <input type="radio" name="payment_method" value="flow" checked style="margin-top:4px; accent-color:#0957c3;" onchange="handlePaymentMethodChange(this.value)">
                                <div style="flex:1;">
                                    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px;">
                                        <div style="display:flex; align-items:center; gap:8px;">
                                            <span style="font-weight:700; font-size:15px; color:var(--navy-900);">Flow Pagos Chile (Webpay Plus, Redcompra, Tarjetas, Servipag)</span>
                                            <span class="badge" style="background:#0957c3; color:#fff; font-size:11px; font-weight:700;">Recomendado</span>
                                        </div>
                                        <div style="display:flex; align-items:center; gap:6px; font-size:11px; color:#475569; font-weight:600; flex-wrap:wrap;">
                                            <span style="background:#e2e8f0; padding:2px 7px; border-radius:4px;">Webpay Plus</span>
                                            <span style="background:#e2e8f0; padding:2px 7px; border-radius:4px;">Redcompra</span>
                                            <span style="background:#e2e8f0; padding:2px 7px; border-radius:4px;">Servipag</span>
                                            <span style="background:#e2e8f0; padding:2px 7px; border-radius:4px;">Mach</span>
                                        </div>
                                    </div>
                                    <p style="font-size:13px; color:var(--text-muted); margin-top:6px; margin-bottom:0;">
                                        Paga de forma rápida y 100% segura con débito bancario chileno, crédito en hasta 12 cuotas, CuentaRUT, Servipag, Mach o Khipu a través de la pasarela Flow.
                                    </p>
                                </div>
                            </label>

                            <!-- Transferencia Bancaria Directa (5% OFF) -->
                            <label id="label-method-tf" style="border:1px solid var(--border-color); border-radius:var(--radius-md); padding:16px; display:flex; align-items:flex-start; gap:14px; cursor:pointer; transition:all 0.2s ease; background:#ffffff;">
                                <input type="radio" name="payment_method" value="transferencia" style="margin-top:4px; accent-color:var(--primary);" onchange="handlePaymentMethodChange(this.value)">
                                <div>
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <span style="font-weight:700; font-size:15px; color:var(--navy-900);">Transferencia Electrónica Directa</span>
                                        <span class="badge" style="background:#dcfce7; color:#15803d; font-size:11px; font-weight:800;">5% DE DESCUENTO INMEDIATO</span>
                                    </div>
                                    <p style="font-size:13px; color:var(--text-muted); margin-top:4px; margin-bottom:0;">
                                        Ahorra un 5% en tu compra pagando desde cualquier banco chileno (Banco de Chile, Santander, BCI, BancoEstado, etc.). Validación rápida con comprobante.
                                    </p>
                                </div>
                            </label>
                        </div>
                    </div>

                </div>

                <!-- Right Column (In Desktop: Sticky Sidebar. In Mobile: Full-width at the bottom) -->
                <div class="checkout-summary-column">
                    <div class="checkout-card" style="position:sticky; top:100px;">
                        <h3 style="font-size:18px; margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border-color); font-weight:800; color:var(--navy-900);">
                            Resumen de Compra
                        </h3>

                        <!-- Items list -->
                        <div style="max-height:260px; overflow-y:auto; margin-bottom:18px; display:flex; flex-direction:column; gap:12px; padding-right:6px;">
                            @foreach($cart as $item)
                                <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; font-size:13.5px;">
                                    <div style="display:flex; align-items:center; gap:10px;">
                                        <div style="width:40px; height:40px; border:1px solid var(--border-color); border-radius:6px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                            <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" style="max-height:34px; max-width:34px; object-fit:contain;">
                                        </div>
                                        <div>
                                            <div style="font-weight:600; line-height:1.2; max-width:180px; text-overflow:ellipsis; overflow:hidden; white-space:nowrap;">{{ $item['name'] }}</div>
                                            <span style="font-size:12px; color:var(--text-muted);">Cant: {{ $item['quantity'] }}</span>
                                        </div>
                                    </div>
                                    <span style="font-weight:700; font-family:'Plus Jakarta Sans';">${{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>

                        <!-- Calculations -->
                        <div style="border-top:1px solid var(--border-color); padding-top:14px; margin-bottom:18px; font-size:14px; display:flex; flex-direction:column; gap:8px;">
                            <div style="display:flex; justify-content:space-between;">
                                <span style="color:var(--text-muted);">Subtotal:</span>
                                <span style="font-weight:600;">${{ number_format($subtotal, 0, ',', '.') }} CLP</span>
                            </div>

                            <!-- 5% Transfer Discount Row -->
                            <div id="transfer-discount-row" style="display:none; justify-content:space-between; color:#15803d; font-weight:700;">
                                <span>Descuento Transferencia (5%):</span>
                                <span id="transfer-discount-val">-${{ number_format(round($subtotal * 0.05), 0, ',', '.') }} CLP</span>
                            </div>

                            <div style="display:flex; justify-content:space-between;">
                                <span style="color:var(--text-muted);" id="summary-shipping-label">Despacho Blue Express:</span>
                                <span id="summary-shipping-display" style="font-weight:700; color:{{ $shipping === 0 ? '#166534' : 'var(--text-main)' }};">
                                    {{ $shipping === 0 ? 'GRATIS' : '$' . number_format($shipping, 0, ',', '.') . ' CLP' }}
                                </span>
                            </div>
                            <div style="display:flex; justify-content:space-between; font-size:12.5px; color:#64748b;">
                                <span>IVA 19% (Incluido):</span>
                                <span>${{ number_format(round($subtotal * 0.19), 0, ',', '.') }} CLP</span>
                            </div>
                        </div>

                        <div style="border-top:2px solid var(--border-color); padding-top:14px; margin-bottom:24px; display:flex; justify-content:space-between; align-items:center;">
                            <span style="font-size:16px; font-weight:800; color:var(--navy-900);">Total Final:</span>
                            <span id="final-total-display" style="font-family:'Plus Jakarta Sans'; font-size:24px; font-weight:800; color:var(--primary);">
                                ${{ number_format($total, 0, ',', '.') }} CLP
                            </span>
                        </div>

                        <!-- Primary Payment Submission Button -->
                        <button type="submit" id="btn-submit-order" class="btn btn-primary btn-block btn-lg" style="height:50px; font-size:16px; font-weight:800;">
                            <span>Confirmar y Proceder al Pago</span>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </button>

                        <div style="margin-top:16px; text-align:center; font-size:12px; color:var(--text-muted); line-height:1.4;">
                            Al confirmar tu compra aceptas nuestros <a href="{{ route('page.terms') }}" target="_blank" style="text-decoration:underline;">Términos y Condiciones</a> y <a href="{{ route('page.returns') }}" target="_blank" style="text-decoration:underline;">Garantía Legal</a>.
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>

    <!-- Official Blue Express PUDO Modal Selector -->
    <div id="pudo-modal" class="pudo-modal-overlay" style="display:none;" onclick="handleModalBackdropClick(event)">
        <div class="pudo-modal-dialog">
            <div class="pudo-modal-header">
                <div style="display:flex; align-items:center; gap:12px;">
                    <img src="{{ asset('images/blue-express.svg') }}" alt="Blue Express" style="height:26px; width:auto;">
                    <div>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <h3 style="margin:0; font-size:16.5px; font-weight:800; color:#0033a1;">
                                Buscador Oficial de Puntos Blue Express
                            </h3>
                            <span class="badge" style="background:#0033a1; color:#fff; font-size:11px; padding:2px 8px; border-radius:4px; font-weight:700;">
                                Red Nacional
                            </span>
                        </div>
                        <p style="margin:2px 0 0; font-size:12.5px; color:#64748b;">
                            Ubica tu comuna o dirección y haz clic sobre el Punto de Retiro en el mapa
                        </p>
                    </div>
                </div>
                <button type="button" class="pudo-modal-close" onclick="closePudoModal()" aria-label="Cerrar">&times;</button>
            </div>
            <div class="pudo-modal-body">
                <iframe id="pudo-iframe" src="https://widget-pudo.blue.cl" title="Buscador Puntos Blue Express"></iframe>
            </div>
            <div class="pudo-modal-footer">
                <div style="display:flex; align-items:center; gap:8px; font-size:13px; color:#334155; font-weight:600;">
                    <span style="font-size:16px;">📍</span>
                    <span>Haz clic en tu sucursal o Punto Blue Express en el mapa para confirmar tu retiro.</span>
                </div>
                <button type="button" class="btn btn-secondary btn-sm" onclick="closePudoModal()" style="font-weight:700; padding:6px 20px;">
                    Cerrar
                </button>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
<script>
    const baseSubtotal = {{ $subtotal }};
    let currentShippingCost = {{ $shipping }};
    let currentPaymentMethod = 'flow';
    let currentShippingType = '{{ $initialShippingType }}';

    // Store calculated quotes for both options
    let quoteDomicilioCost = {{ $quoteDomicilio['cost'] ?? 3067 }};
    let quotePickupCost = {{ $quotePickup['cost'] ?? 2395 }};

    function updateTotals() {
        const discountRow = document.getElementById('transfer-discount-row');
        const discountVal = document.getElementById('transfer-discount-val');
        const totalDisplay = document.getElementById('final-total-display');
        const mobileTopTotal = document.getElementById('mobile-top-total');
        const shippingDisplay = document.getElementById('summary-shipping-display');
        const shippingLabel = document.getElementById('summary-shipping-label');

        let discount = 0;
        if (currentPaymentMethod === 'transferencia') {
            discount = Math.round(baseSubtotal * 0.05);
            discountRow.style.display = 'flex';
            discountVal.innerText = '-$' + discount.toLocaleString('es-CL') + ' CLP';
        } else {
            discountRow.style.display = 'none';
        }

        const total = (baseSubtotal - discount) + currentShippingCost;
        const formattedTotal = '$' + total.toLocaleString('es-CL') + ' CLP';
        totalDisplay.innerText = formattedTotal;
        if (mobileTopTotal) mobileTopTotal.innerText = formattedTotal;
        totalDisplay.style.color = currentPaymentMethod === 'transferencia' ? '#15803d' : 'var(--primary)';

        shippingLabel.innerText = currentShippingType === 'pickup' ? 'Punto Pick Up Blue Express:' : 'Despacho Domicilio Blue Express:';

        if (currentShippingCost === 0) {
            shippingDisplay.innerText = 'GRATIS';
            shippingDisplay.style.color = '#166534';
        } else {
            shippingDisplay.innerText = '$' + currentShippingCost.toLocaleString('es-CL') + ' CLP';
            shippingDisplay.style.color = 'var(--text-main)';
        }
    }

    function toggleMobileSummary() {
        const dropdown = document.getElementById('mobile-summary-dropdown');
        const arrow = document.getElementById('mobile-summary-arrow');
        if (dropdown.style.display === 'block') {
            dropdown.style.display = 'none';
            arrow.style.transform = 'rotate(0deg)';
        } else {
            dropdown.style.display = 'block';
            arrow.style.transform = 'rotate(180deg)';
        }
    }

    function handlePaymentMethodChange(method) {
        currentPaymentMethod = method;
        const flowLabel = document.getElementById('label-method-flow');
        const tfLabel = document.getElementById('label-method-tf');

        // Reset borders & backgrounds
        if (flowLabel) { flowLabel.style.border = '1px solid var(--border-color)'; flowLabel.style.background = '#ffffff'; }
        if (tfLabel) { tfLabel.style.border = '1px solid var(--border-color)'; tfLabel.style.background = '#ffffff'; }

        if (method === 'flow') {
            if (flowLabel) { flowLabel.style.border = '2px solid #0957c3'; flowLabel.style.background = '#f0f7ff'; }
        } else if (method === 'transferencia') {
            if (tfLabel) { tfLabel.style.border = '2px solid #16a34a'; tfLabel.style.background = '#f0fdf4'; }
        }
        updateTotals();
    }

    function setShippingType(type) {
        currentShippingType = type;
        document.getElementById('shipping_type').value = type;

        const cardDom = document.getElementById('card-delivery-domicilio');
        const cardPick = document.getElementById('card-delivery-pickup');
        const radioDom = document.querySelector('input[name="delivery_choice"][value="domicilio"]');
        const radioPick = document.querySelector('input[name="delivery_choice"][value="pickup"]');
        const secDom = document.getElementById('section-domicilio-fields');
        const secPick = document.getElementById('section-pickup-fields');
        const addressInput = document.getElementById('shipping_address');

        if (type === 'pickup') {
            cardDom.classList.remove('active');
            cardPick.classList.add('active');
            radioPick.checked = true;
            secDom.style.display = 'none';
            secPick.style.display = 'block';
            addressInput.removeAttribute('required');

            // If no point selected yet, open modal automatically to prompt customer
            const agencyId = document.getElementById('agency_id').value;
            if (!agencyId) {
                openPudoModal();
            }
        } else {
            cardPick.classList.remove('active');
            cardDom.classList.add('active');
            radioDom.checked = true;
            secPick.style.display = 'none';
            secDom.style.display = 'block';
            addressInput.setAttribute('required', 'required');
        }

        const currentCommune = document.getElementById('shipping_city').value;
        handleCommuneChange(currentCommune);
    }

    function openPudoModal() {
        const modal = document.getElementById('pudo-modal');
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closePudoModal() {
        const modal = document.getElementById('pudo-modal');
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }

    function handleModalBackdropClick(event) {
        if (event.target && event.target.id === 'pudo-modal') {
            closePudoModal();
        }
    }

    // Listen to official Blue Express PUDO postMessage
    window.addEventListener('message', function (event) {
        if (event.data && (event.data.type === 'pudo:select' || event.data.type === 'pudo:selected')) {
            handlePudoSelect(event.data.payload || event.data.data || event.data);
        }
    });

    function handlePudoSelect(payload) {
        if (!payload) return;

        const agencyId = payload.agency_id || payload.id || payload.agencyId || '';
        const agencyName = payload.agency_name || payload.name || payload.agencyName || 'Punto Blue Express';
        
        let street = '';
        let number = '';
        let city = '';
        let region = '';

        if (payload.location) {
            street = payload.location.street_name || payload.location.street || '';
            number = payload.location.street_number || payload.location.number || '';
            city = payload.location.city_name || payload.location.city || '';
            region = payload.location.region_code || payload.location.region || '';
        } else if (payload.address) {
            street = payload.address;
        }

        const fullAddress = [street, number].filter(Boolean).join(' ') + (city ? ', ' + city : '');

        // Populate hidden form fields
        document.getElementById('agency_id').value = agencyId;
        document.getElementById('agency_name').value = agencyName;
        document.getElementById('agency_address').value = fullAddress;
        document.getElementById('agency_city').value = city;
        document.getElementById('agency_state').value = region;

        // Update UI confirmed box
        document.getElementById('display-agency-name').innerText = agencyName;
        document.getElementById('display-agency-address').innerText = fullAddress || 'Dirección de retiro confirmada';
        document.getElementById('pudo-select-container').style.display = 'none';
        document.getElementById('pudo-confirmed-container').style.display = 'flex';

        closePudoModal();

        // If agency has a specific city, select it in the dropdown if available
        if (city) {
            const citySelect = document.getElementById('shipping_city');
            for (let i = 0; i < citySelect.options.length; i++) {
                if (citySelect.options[i].value.toLowerCase() === city.toLowerCase()) {
                    citySelect.selectedIndex = i;
                    break;
                }
            }
        }

        // Trigger rate recalculation
        handleCommuneChange(document.getElementById('shipping_city').value);
    }

    function handleRegionChange(regionCode) {
        const regionSelect = document.getElementById('shipping_region');
        const regionNameInput = document.getElementById('shipping_region_name');
        regionNameInput.value = regionSelect.options[regionSelect.selectedIndex].text.trim();

        const citySelect = document.getElementById('shipping_city');
        citySelect.innerHTML = '<option value="">Cargando comunas...</option>';
        citySelect.disabled = true;

        fetch('{{ url("/checkout/communes") }}/' + regionCode)
            .then(response => response.json())
            .then(data => {
                citySelect.innerHTML = '';
                if (data.communes && data.communes.length > 0) {
                    data.communes.forEach(c => {
                        const opt = document.createElement('option');
                        opt.value = c.name;
                        opt.textContent = c.name;
                        citySelect.appendChild(opt);
                    });
                    citySelect.disabled = false;
                    handleCommuneChange(data.communes[0].name);
                } else {
                    citySelect.innerHTML = '<option value="Principal">Principal</option>';
                    citySelect.disabled = false;
                }
            })
            .catch(err => {
                console.error('Error loading communes:', err);
                citySelect.disabled = false;
            });
    }

    let quoteTimeout = null;
    function handleCommuneChange(communeName) {
        if (!communeName) return;
        const regionCode = document.getElementById('shipping_region').value;

        const spinner = document.getElementById('bx-loading-spinner');
        const costBadge = document.getElementById('bx-cost-badge');
        const promiseText = document.getElementById('bx-promise-text');
        const serviceName = document.getElementById('bx-service-name');
        const cardPriceDom = document.getElementById('card-price-domicilio');
        const cardPricePick = document.getElementById('card-price-pickup');

        spinner.style.display = 'inline-flex';
        costBadge.style.opacity = '0.4';

        clearTimeout(quoteTimeout);
        quoteTimeout = setTimeout(() => {
            fetch('{{ route("checkout.quote_shipping") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    region_code: regionCode,
                    commune_name: communeName,
                    payment_method: currentPaymentMethod,
                    shipping_type: currentShippingType
                })
            })
            .then(res => res.json())
            .then(res => {
                spinner.style.display = 'none';
                costBadge.style.opacity = '1';

                if (res.success) {
                    currentShippingCost = res.shipping_cost;
                    costBadge.innerText = res.shipping_formatted;
                    costBadge.style.color = res.shipping_cost === 0 ? '#16a34a' : '#0033a1';
                    promiseText.innerText = res.promise_day;
                    if (res.service_name) {
                        serviceName.innerText = res.service_name;
                    }

                    if (currentShippingType === 'domicilio') {
                        cardPriceDom.innerText = res.shipping_formatted;
                    } else {
                        cardPricePick.innerText = res.shipping_formatted;
                    }

                    updateTotals();
                }
            })
            .catch(err => {
                console.error('Quote error:', err);
                spinner.style.display = 'none';
                costBadge.style.opacity = '1';
            });
        }, 200);
    }

    // Checkout form validation before submit
    document.getElementById('checkout-form').addEventListener('submit', function (e) {
        if (currentShippingType === 'pickup') {
            const agencyId = document.getElementById('agency_id').value;
            const agencyName = document.getElementById('agency_name').value;
            if (!agencyId && !agencyName) {
                e.preventDefault();
                alert('Por favor selecciona tu Punto Blue Express de retiro en el mapa antes de continuar.');
                openPudoModal();
                return false;
            }
        }
    });
</script>
@endsection
