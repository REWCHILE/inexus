@extends('layouts.admin')

@section('title', 'Pasarelas de Pago')
@section('header_title', 'Configuración de Pasarelas de Pago & Cobros')

@section('content')

    <div style="background:#ffffff; border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:24px; margin-bottom:24px;">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
            <div>
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:4px;">
                    <h2 style="font-size:20px; color:var(--navy-900); margin:0;">Pasarelas de Pago Electrónico en Chile</h2>
                    @if(!empty($settings['flow_api_key']) && !str_starts_with($settings['flow_api_key'], 'FLOW_TEST'))
                        <span class="badge" style="background:#dcfce7; color:#15803d; font-size:12px;">Flow Configurado</span>
                    @else
                        <span class="badge" style="background:#fef3c7; color:#b45309; font-size:12px;">Flow en Modo Simulación Sandbox</span>
                    @endif
                </div>
                <p style="font-size:13.5px; color:var(--text-muted); margin:0;">
                    Administra las credenciales de la API de <strong>Flow Chile</strong> (Webpay Plus, Redcompra, Servipag, Mach, Multicaja), Mercado Pago y las condiciones de transferencia electrónica bancaria.
                </p>
            </div>
            <div style="display:flex; gap:12px;">
                <button type="button" class="btn btn-primary" id="btn-test-flow" style="font-size:13px; display:inline-flex; align-items:center; gap:6px;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                    </svg>
                    Probar Conexión Flow API
                </button>
            </div>
        </div>

        <div id="flow-test-result" style="display:none; margin-top:16px;"></div>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="background:#dcfce7; border:1px solid #86efac; color:#15803d; padding:14px 18px; border-radius:var(--radius-md); margin-bottom:24px; font-size:14px;">
            {{ session('success') }}
        </div>
    @endif

    <div class="tabs-container" style="margin-top:0;">
        <div class="tabs-header">
            <button type="button" class="tab-btn active" data-target="tab-flow">
                ⚡ 1. Pasarela Flow Chile (Recomendado)
            </button>
            <button type="button" class="tab-btn" data-target="tab-mercadopago">
                💳 2. Mercado Pago
            </button>
            <button type="button" class="tab-btn" data-target="tab-transfer">
                🏦 3. Transferencia Bancaria
            </button>
        </div>

        <form action="{{ route('admin.payments.update') }}" method="POST">
            @csrf

            <!-- TAB 1: Flow Chile -->
            <div class="tab-pane" id="tab-flow" style="display:block;">
                <div class="checkout-card">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:18px; border-bottom:1px solid var(--border-color); padding-bottom:14px;">
                        <div>
                            <h3 style="font-size:18px; margin:0 0 4px; color:var(--navy-900);">Configuración de API Flow (flow.cl)</h3>
                            <p style="font-size:13px; color:var(--text-muted); margin:0;">
                                Permite procesar pagos con tarjetas de débito (Redcompra), crédito en cuotas bancarias, CuentaRUT, Servipag, Mach y Khipu.
                            </p>
                        </div>
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-weight:700; font-size:14px;">
                            <input type="checkbox" name="flow_active" value="1" {{ ($settings['flow_active'] ?? true) ? 'checked' : '' }} style="accent-color:var(--primary); width:18px; height:18px;">
                            Habilitar Flow en Checkout
                        </label>
                    </div>

                    <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; padding:14px 16px; margin-bottom:20px; font-size:13px; color:#1e40af;">
                        <strong>ℹ️ ¿Dónde obtener las credenciales de Flow?</strong><br>
                        1. Ingresa a tu cuenta de Flow en <a href="https://www.flow.cl" target="_blank" style="color:#1d4ed8; text-decoration:underline; font-weight:600;">flow.cl</a> (para producción) o <a href="https://sandbox.flow.cl" target="_blank" style="color:#1d4ed8; text-decoration:underline; font-weight:600;">sandbox.flow.cl</a> (para pruebas).<br>
                        2. Ve a <strong>Mis datos &gt; Integración</strong> para copiar tu <code>API Key</code> y tu <code>Secret Key</code>.<br>
                        3. Si dejas los campos vacíos o con datos de prueba, la tienda utilizará el <em>simulador sandbox interactivo</em> de Flow para permitir pruebas locales sin errores.
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="flow_api_key">Flow API Key</label>
                            <input type="text" id="flow_api_key" name="flow_api_key" class="form-control"
                                   value="{{ old('flow_api_key', $settings['flow_api_key'] ?? '') }}"
                                   placeholder="Ej: 3DF64B8E-..." style="font-family:monospace;">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="flow_secret_key">Flow Secret Key</label>
                            <input type="password" id="flow_secret_key" name="flow_secret_key" class="form-control"
                                   value="{{ old('flow_secret_key', $settings['flow_secret_key'] ?? '') }}"
                                   placeholder="••••••••••••••••••••••••••••••••" style="font-family:monospace;">
                            <small style="color:var(--text-muted); font-size:11.5px; margin-top:4px; display:block;">
                                Utilizada para la firma digital HMAC-SHA256 de todas las solicitudes y callbacks.
                            </small>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top:16px;">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-weight:600;">
                            <input type="checkbox" name="flow_sandbox" value="1" {{ ($settings['flow_sandbox'] ?? true) ? 'checked' : '' }} style="accent-color:var(--primary); width:16px; height:16px;">
                            <span>Modo Sandbox de Pruebas (utiliza <code>https://sandbox.flow.cl/api</code>)</span>
                        </label>
                        <small style="display:block; color:var(--text-muted); margin-left:24px; font-size:12px;">
                            Desmarca esta casilla cuando vayas a recibir pagos reales con tu cuenta de Flow en producción.
                        </small>
                    </div>

                    <div style="border-top:1px solid var(--border-color); padding-top:18px; margin-top:20px;">
                        <h4 style="font-size:14px; color:var(--navy-900); margin-bottom:10px;">Endpoints de Notificación y Retorno Registrados</h4>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; font-size:12.5px;">
                            <div style="background:#f8fafc; border:1px solid var(--border-color); border-radius:6px; padding:12px;">
                                <strong style="color:var(--navy-900); display:block; margin-bottom:4px;">URL de Retorno (urlReturn):</strong>
                                <code style="word-break:break-all; color:#0284c7;">{{ route('checkout.flow.return') }}</code>
                            </div>
                            <div style="background:#f8fafc; border:1px solid var(--border-color); border-radius:6px; padding:12px;">
                                <strong style="color:var(--navy-900); display:block; margin-bottom:4px;">URL de Confirmación / Webhook (urlConfirmation):</strong>
                                <code style="word-break:break-all; color:#0284c7;">{{ route('checkout.flow.confirm') }}</code>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: Mercado Pago -->
            <div class="tab-pane" id="tab-mercadopago" style="display:none;">
                <div class="checkout-card">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:18px; border-bottom:1px solid var(--border-color); padding-bottom:14px;">
                        <div>
                            <h3 style="font-size:18px; margin:0 0 4px; color:var(--navy-900);">Configuración Mercado Pago Chile</h3>
                            <p style="font-size:13px; color:var(--text-muted); margin:0;">
                                Cobro mediante Checkout Pro de Mercado Pago.
                            </p>
                        </div>
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-weight:700; font-size:14px;">
                            <input type="checkbox" name="mercadopago_active" value="1" {{ ($settings['mercadopago_active'] ?? true) ? 'checked' : '' }} style="accent-color:var(--primary); width:18px; height:18px;">
                            Habilitar Mercado Pago en Checkout
                        </label>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="mercadopago_public_key">Public Key</label>
                            <input type="text" id="mercadopago_public_key" name="mercadopago_public_key" class="form-control"
                                   value="{{ old('mercadopago_public_key', $settings['mercadopago_public_key'] ?? '') }}"
                                   placeholder="APP_USR-..." style="font-family:monospace;">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="mercadopago_access_token">Access Token</label>
                            <input type="password" id="mercadopago_access_token" name="mercadopago_access_token" class="form-control"
                                   value="{{ old('mercadopago_access_token', $settings['mercadopago_access_token'] ?? '') }}"
                                   placeholder="••••••••••••••••••••••••••••••••" style="font-family:monospace;">
                        </div>
                    </div>

                    <div class="form-group" style="margin-top:16px;">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-weight:600;">
                            <input type="checkbox" name="mercadopago_sandbox" value="1" {{ ($settings['mercadopago_sandbox'] ?? true) ? 'checked' : '' }} style="accent-color:var(--primary); width:16px; height:16px;">
                            <span>Modo Sandbox de Mercado Pago</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- TAB 3: Transferencia Bancaria -->
            <div class="tab-pane" id="tab-transfer" style="display:none;">
                <div class="checkout-card">
                    <h3 style="font-size:18px; margin-bottom:18px; color:var(--navy-900);">Transferencia Bancaria Directa</h3>

                    <div class="form-group" style="max-width:320px;">
                        <label class="form-label" for="transfer_discount_percentage">Porcentaje de Descuento por Transferencia (%)</label>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <input type="number" step="0.5" min="0" max="50" id="transfer_discount_percentage" name="transfer_discount_percentage"
                                   class="form-control" value="{{ old('transfer_discount_percentage', $settings['transfer_discount_percentage'] ?? 5) }}">
                            <span style="font-weight:700; color:var(--text-muted);">%</span>
                        </div>
                        <small style="color:var(--text-muted); font-size:12px; margin-top:4px; display:block;">
                            Descuento incentivo para transferencias directas sin comisiones de pasarela. Actualmente configurado al {{ $settings['transfer_discount_percentage'] ?? 5 }}%.
                        </small>
                    </div>
                </div>
            </div>

            <div style="margin-top:20px; display:flex; justify-content:flex-end;">
                <button type="submit" class="btn btn-primary btn-lg" style="padding:12px 30px; font-weight:700;">
                    💾 Guardar Cambios en Pasarelas de Pago
                </button>
            </div>
        </form>
    </div>

@endsection

@section('scripts')
<script>
    // Tab switching logic
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-pane').forEach(p => p.style.display = 'none');
            
            this.classList.add('active');
            const target = document.getElementById(this.dataset.target);
            if (target) {
                target.style.display = 'block';
            }
        });
    });

    // Test Flow connection button
    document.getElementById('btn-test-flow')?.addEventListener('click', function() {
        const btn = this;
        const resultDiv = document.getElementById('flow-test-result');
        btn.disabled = true;
        btn.innerHTML = '⏳ Conectando con Flow API...';
        resultDiv.style.display = 'none';

        fetch("{{ route('admin.payments.test_flow') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '⚡ Probar Conexión Flow API';
            resultDiv.style.display = 'block';

            if (data.success) {
                resultDiv.innerHTML = `
                    <div style="background:#dcfce7; border:1px solid #86efac; color:#15803d; padding:14px 18px; border-radius:8px; font-size:14px;">
                        <strong>✓ ¡Conexión Exitosa con Flow!</strong>
                        <p style="margin:4px 0 0; font-size:13px;">${data.message}</p>
                    </div>
                `;
            } else {
                resultDiv.innerHTML = `
                    <div style="background:#fee2e2; border:1px solid #fca5a5; color:#b91c1c; padding:14px 18px; border-radius:8px; font-size:14px;">
                        <strong>✕ Error de conexión con Flow:</strong>
                        <p style="margin:4px 0 0; font-size:13px;">${data.message}</p>
                    </div>
                `;
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '⚡ Probar Conexión Flow API';
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = `
                <div style="background:#fee2e2; border:1px solid #fca5a5; color:#b91c1c; padding:14px 18px; border-radius:8px; font-size:14px;">
                    <strong>✕ Error al procesar la solicitud:</strong>
                    <p style="margin:4px 0 0; font-size:13px;">${err.message}</p>
                </div>
            `;
        });
    });
</script>
@endsection
