@extends('layouts.app')

@section('title', 'Finalizar Compra Seguro | INEXUS Chile')

@section('content')

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

    <div class="container" style="padding: 40px 20px;">
        <h1 style="font-size:28px; margin-bottom:24px;">Finalizar Pedido Seguro</h1>

        <form action="{{ route('checkout.process') }}" method="POST">
            @csrf

            <div class="checkout-grid">
                
                <!-- Left Column: Forms -->
                <div>
                    
                    <!-- 1. Customer Details -->
                    <div class="checkout-card">
                        <h2 class="checkout-card-title">
                            <span style="width:28px; height:28px; border-radius:50%; background:var(--primary); color:#fff; display:flex; align-items:center; justify-content:center; font-size:13px;">1</span>
                            Información de Contacto
                        </h2>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="customer_name">Nombre y Apellido *</label>
                                <input type="text" id="customer_name" name="customer_name" class="form-control" required placeholder="Ej: Gonzalo Martínez" value="{{ old('customer_name') }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="customer_email">Correo Electrónico *</label>
                                <input type="email" id="customer_email" name="customer_email" class="form-control" required placeholder="tu-email@empresa.cl" value="{{ old('customer_email') }}">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="customer_phone">Teléfono / WhatsApp *</label>
                                <input type="text" id="customer_phone" name="customer_phone" class="form-control" required placeholder="+56 9 1234 5678" value="{{ old('customer_phone') }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="customer_rut">RUT *</label>
                                <input type="text" id="customer_rut" name="customer_rut" class="form-control" required placeholder="Ej: 12.345.678-9" value="{{ old('customer_rut') }}">
                            </div>
                        </div>
                    </div>

                    <!-- 2. Document Type (Boleta vs Factura) -->
                    <div class="checkout-card">
                        <h2 class="checkout-card-title">
                            <span style="width:28px; height:28px; border-radius:50%; background:var(--primary); color:#fff; display:flex; align-items:center; justify-content:center; font-size:13px;">2</span>
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
                            <div class="form-group">
                                <label class="form-label" for="company_giro">Giro Comercial *</label>
                                <input type="text" id="company_giro" name="company_giro" class="form-control" placeholder="Ej: Servicios de informática, consultoría..." value="{{ old('company_giro') }}">
                            </div>
                        </div>
                    </div>

                    <!-- 3. Shipping Address -->
                    <div class="checkout-card">
                        <h2 class="checkout-card-title">
                            <span style="width:28px; height:28px; border-radius:50%; background:var(--primary); color:#fff; display:flex; align-items:center; justify-content:center; font-size:13px;">3</span>
                            Dirección de Despacho
                        </h2>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="shipping_region">Región *</label>
                                <select id="shipping_region" name="shipping_region" class="form-control" required>
                                    <option value="Región Metropolitana de Santiago" selected>Región Metropolitana de Santiago</option>
                                    <option value="Región de Valparaíso">Región de Valparaíso</option>
                                    <option value="Región del Biobío">Región del Biobío</option>
                                    <option value="Región de Antofagasta">Región de Antofagasta</option>
                                    <option value="Región de Coquimbo">Región de Coquimbo</option>
                                    <option value="Región de O'Higgins">Región del Libertador General Bernardo O'Higgins</option>
                                    <option value="Región del Maule">Región del Maule</option>
                                    <option value="Región de La Araucanía">Región de La Araucanía</option>
                                    <option value="Región de Los Lagos">Región de Los Lagos</option>
                                    <option value="Otras Regiones">Otras Regiones de Chile</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="shipping_city">Comuna / Ciudad *</label>
                                <input type="text" id="shipping_city" name="shipping_city" class="form-control" required placeholder="Ej: Providencia, Santiago, Las Condes..." value="{{ old('shipping_city') }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="shipping_address">Dirección (Calle y Número) *</label>
                            <input type="text" id="shipping_address" name="shipping_address" class="form-control" required placeholder="Ej: Av. Andrés Bello 2457" value="{{ old('shipping_address') }}">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="shipping_notes">Instrucciones o Referencias de Entrega (Opcional)</label>
                            <input type="text" id="shipping_notes" name="shipping_notes" class="form-control" placeholder="Depto 804 / Dejar en conserjería..." value="{{ old('shipping_notes') }}">
                        </div>
                    </div>

                    <!-- 4. Payment Method Selection -->
                    <div class="checkout-card">
                        <h2 class="checkout-card-title">
                            <span style="width:28px; height:28px; border-radius:50%; background:var(--primary); color:#fff; display:flex; align-items:center; justify-content:center; font-size:13px;">4</span>
                            Método de Pago en Pesos Chilenos (CLP)
                        </h2>

                        <div style="display:flex; flex-direction:column; gap:14px;">
                            <!-- Mercado Pago -->
                            <label id="label-method-mp" style="border:2px solid var(--primary); border-radius:var(--radius-md); padding:16px; display:flex; align-items:flex-start; gap:14px; cursor:pointer; background:#f0f9ff; transition:all 0.2s ease;">
                                <input type="radio" name="payment_method" value="mercadopago" checked style="margin-top:4px; accent-color:var(--primary);" onchange="handlePaymentMethodChange(this.value)">
                                <div>
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <span style="font-weight:700; font-size:15px; color:var(--navy-900);">Mercado Pago (Tarjetas Débito / Crédito / Webpay)</span>
                                        <span class="badge badge-info" style="font-size:11px;">Recomendado</span>
                                    </div>
                                    <p style="font-size:13px; color:var(--text-muted); margin-top:4px;">
                                        Paga en hasta 12 cuotas con tarjetas bancarias chilenas, Redcompra, Cuenta RUT o saldo en Mercado Pago en CLP.
                                    </p>
                                </div>
                            </label>

                            <!-- Transferencia Bancaria Directa (5% OFF) -->
                            <label id="label-method-tf" style="border:1px solid var(--border-color); border-radius:var(--radius-md); padding:16px; display:flex; align-items:flex-start; gap:14px; cursor:pointer; transition:all 0.2s ease;">
                                <input type="radio" name="payment_method" value="transferencia" style="margin-top:4px; accent-color:var(--primary);" onchange="handlePaymentMethodChange(this.value)">
                                <div>
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <span style="font-weight:700; font-size:15px; color:var(--navy-900);">Transferencia Electrónica Directa</span>
                                        <span class="badge" style="background:#dcfce7; color:#15803d; font-size:11px; font-weight:800;">5% DE DESCUENTO INMEDIATO</span>
                                    </div>
                                    <p style="font-size:13px; color:var(--text-muted); margin-top:4px;">
                                        Ahorra un 5% en tu compra pagando desde cualquier banco chileno (Banco de Chile, Santander, BCI, BancoEstado, etc.). Validación rápida con comprobante.
                                    </p>
                                </div>
                            </label>
                        </div>
                    </div>

                </div>

                <!-- Right Column: Order Summary -->
                <div>
                    <div class="checkout-card" style="position:sticky; top:100px;">
                        <h3 style="font-size:18px; margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border-color);">
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
                                <span style="color:var(--text-muted);">Despacho Express:</span>
                                <span style="font-weight:600; color:{{ $shipping === 0 ? '#166534' : 'var(--text-main)' }};">
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

                        <button type="submit" class="btn btn-primary btn-block btn-lg">
                            <span>Confirmar y Proceder al Pago</span>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </button>

                        <div style="margin-top:16px; text-align:center; font-size:12px; color:var(--text-muted);">
                            Al confirmar tu compra aceptas nuestros <a href="{{ route('page.terms') }}" target="_blank" style="text-decoration:underline;">Términos y Condiciones</a> y <a href="{{ route('page.returns') }}" target="_blank" style="text-decoration:underline;">Garantía Legal</a>.
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>

@endsection

@section('scripts')
<script>
    const baseSubtotal = {{ $subtotal }};
    const shippingCost = {{ $shipping }};
    const transferDiscountAmount = Math.round(baseSubtotal * 0.05);

    function handlePaymentMethodChange(method) {
        const mpLabel = document.getElementById('label-method-mp');
        const tfLabel = document.getElementById('label-method-tf');
        const discountRow = document.getElementById('transfer-discount-row');
        const totalDisplay = document.getElementById('final-total-display');

        if (method === 'transferencia') {
            tfLabel.style.border = '2px solid #16a34a';
            tfLabel.style.background = '#f0fdf4';
            mpLabel.style.border = '1px solid var(--border-color)';
            mpLabel.style.background = '#ffffff';

            discountRow.style.display = 'flex';
            const newTotal = (baseSubtotal - transferDiscountAmount) + shippingCost;
            totalDisplay.innerText = '$' + newTotal.toLocaleString('es-CL') + ' CLP';
            totalDisplay.style.color = '#15803d';
        } else {
            mpLabel.style.border = '2px solid var(--primary)';
            mpLabel.style.background = '#f0f9ff';
            tfLabel.style.border = '1px solid var(--border-color)';
            tfLabel.style.background = '#ffffff';

            discountRow.style.display = 'none';
            const normalTotal = baseSubtotal + shippingCost;
            totalDisplay.innerText = '$' + normalTotal.toLocaleString('es-CL') + ' CLP';
            totalDisplay.style.color = 'var(--primary)';
        }
    }
</script>
@endsection
