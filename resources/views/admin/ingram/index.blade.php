@extends('layouts.admin')

@section('title', 'Integración Ingram Micro')
@section('header_title', 'Administración de Conector Ingram Micro Chile (API V6)')

@section('content')

    <div style="background:#ffffff; border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:24px; margin-bottom:24px;">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
            <div>
                <h2 style="font-size:20px; color:var(--navy-900); margin:0 0 4px;">Ingram Micro Reseller API Integration (Chile)</h2>
                <p style="font-size:13.5px; color:var(--text-muted); margin:0;">
                    Conexión directa con el catálogo de hardware mayorista, actualización de precios de compra en USD, stock en tiempo real y reglas automáticas de margen comercial en CLP.
                </p>
            </div>
            <div style="display:flex; gap:12px;">
                <button type="button" class="btn btn-secondary" id="btn-test-conn" style="font-size:13px;">
                    ⚡ Probar Conexión OAuth
                </button>
                <form action="{{ route('admin.ingram.recalculate') }}" method="POST" style="margin:0;">
                    @csrf
                    <button type="submit" class="btn btn-primary" style="font-size:13px;" onclick="return confirm('¿Recalcular los precios de venta de todo el catálogo según los márgenes actuales?');">
                        🔄 Recalcular Precios CLP
                    </button>
                </form>
            </div>
        </div>

        <div id="test-conn-result" style="display:none; margin-top:16px;"></div>
    </div>

    <!-- Tab Navigation -->
    <div class="tabs-container" style="margin-top:0;">
        <div class="tabs-header">
            <button type="button" class="tab-btn active" data-target="tab-credentials">1. Credenciales & Entorno</button>
            <button type="button" class="tab-btn" data-target="tab-margins">2. Reglas de Margen & Divisas</button>
            <button type="button" class="tab-btn" data-target="tab-explorer">3. Explorador de Catálogo</button>
            <button type="button" class="tab-btn" data-target="tab-sync">4. Sincronización & Logs</button>
        </div>

        <!-- TAB 1: API Credentials -->
        <div class="tab-pane" id="tab-credentials" style="display:block;">
            <div class="checkout-card">
                <form action="{{ route('admin.ingram.settings') }}" method="POST">
                    @csrf

                    <h3 style="font-size:17px; margin-bottom:18px;">Credenciales de Desarrollador Ingram Micro</h3>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="ingram_client_id">OAuth Client ID *</label>
                            <input type="text" id="ingram_client_id" name="ingram_client_id" value="{{ $settings['ingram_client_id'] ?? '' }}" class="form-control" required placeholder="Ingresa tu Client ID otorgado por Ingram Micro">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="ingram_client_secret">OAuth Client Secret *</label>
                            <input type="password" id="ingram_client_secret" name="ingram_client_secret" value="{{ $settings['ingram_client_secret'] ?? '' }}" class="form-control" required placeholder="••••••••••••••••••••••••••">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="ingram_customer_number">Customer Number (Cuenta Cliente) *</label>
                            <input type="text" id="ingram_customer_number" name="ingram_customer_number" value="{{ $settings['ingram_customer_number'] ?? '' }}" class="form-control" required placeholder="Ej: 20-847291">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="ingram_country_code">Código de País</label>
                            <input type="text" id="ingram_country_code" name="ingram_country_code" value="{{ $settings['ingram_country_code'] ?? 'CL' }}" class="form-control" readonly style="background:#f8fafc; font-weight:700;">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="ingram_environment">Ambiente de Operación</label>
                        <select id="ingram_environment" name="ingram_environment" class="form-control" style="max-width:320px;">
                            <option value="sandbox" {{ ($settings['ingram_environment'] ?? 'sandbox') === 'sandbox' ? 'selected' : '' }}>
                                Sandbox (Ambiente de Pruebas y Homologación)
                            </option>
                            <option value="production" {{ ($settings['ingram_environment'] ?? '') === 'production' ? 'selected' : '' }}>
                                Production (Ambiente Productivo Real)
                            </option>
                        </select>
                    </div>

                    <div class="form-group" style="margin-top:20px;">
                        <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                            <input type="checkbox" name="ingram_preserve_scraped_data" value="1" {{ !empty($settings['ingram_preserve_scraped_data']) ? 'checked' : '' }} style="accent-color:var(--primary); width:18px; height:18px;">
                            <span style="font-weight:600; font-size:14px; color:var(--navy-900);">
                                Preservar fotos y descripciones obtenidas por Scraping al sincronizar catálogo
                            </span>
                        </label>
                        <span style="font-size:12px; color:var(--text-muted); margin-left:28px; display:block;">
                            Evita que las descripciones básicas o falta de imágenes de Ingram sobreescriban los datos enriquecidos por SPDigital/ML.
                        </span>
                    </div>

                    <div style="margin-top:24px; padding-top:18px; border-top:1px solid var(--border-color);">
                        <button type="submit" class="btn btn-primary btn-lg">
                            Guardar Credenciales Ingram
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- TAB 2: Margins & Currency -->
        <div class="tab-pane" id="tab-margins" style="display:none;">
            <div class="checkout-card">
                <form action="{{ route('admin.ingram.settings') }}" method="POST">
                    @csrf

                    <h3 style="font-size:17px; margin-bottom:10px;">Jerarquía de Márgenes Comerciales</h3>
                    <p style="font-size:13.5px; color:var(--text-muted); margin-bottom:24px;">
                        La fórmula de fijación de precio de venta en CLP es: <code>Precio Final = (Costo USD × Tasa Cambio) × (1 + Margen % / 100)</code>.<br>
                        <strong>Prioridad:</strong> Margen Individual del Producto > Margen de la Categoría > Margen Global.
                    </p>

                    <div class="form-row" style="background:#f0f9ff; border:1px solid #bae6fd; border-radius:var(--radius-md); padding:20px; margin-bottom:24px;">
                        <div class="form-group" style="margin:0;">
                            <label class="form-label" for="ingram_global_margin" style="color:#0369a1; font-weight:700;">
                                Margen Global de Aporte General (%) *
                            </label>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <input type="number" step="0.01" id="ingram_global_margin" name="ingram_global_margin" value="{{ $settings['ingram_global_margin'] ?? '18.0' }}" class="form-control" style="background:#fff; font-size:16px; font-weight:700; width:140px;" required>
                                <span style="font-weight:700; color:#0369a1; font-size:16px;">%</span>
                            </div>
                            <span style="font-size:12px; color:#0284c7; margin-top:4px; display:block;">
                                Margen por defecto aplicado a cualquier producto que no tenga regla específica.
                            </span>
                        </div>

                        <div class="form-group" style="margin:0;">
                            <label class="form-label" for="ingram_usd_exchange_rate" style="color:#0369a1; font-weight:700;">
                                Tipo de Cambio USD a CLP ($) *
                            </label>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <span style="font-weight:700; color:#0369a1; font-size:16px;">$</span>
                                <input type="number" step="0.5" id="ingram_usd_exchange_rate" name="ingram_usd_exchange_rate" value="{{ $settings['ingram_usd_exchange_rate'] ?? '965.0' }}" class="form-control" style="background:#fff; font-size:16px; font-weight:700; width:140px;" required>
                                <span style="font-weight:700; color:#0369a1; font-size:16px;">CLP</span>
                            </div>
                            <span style="font-size:12px; color:#0284c7; margin-top:4px; display:block;">
                                Conversión de moneda para listas mayoristas cotizadas en dólares estadounidenses.
                            </span>
                        </div>
                    </div>

                    <!-- Category Margins Table -->
                    <h4 style="font-size:15px; margin:24px 0 12px; color:var(--navy-900);">Márgenes Específicos por Categoría</h4>
                    <table class="admin-table" style="border:1px solid var(--border-color); border-radius:8px; overflow:hidden;">
                        <thead>
                            <tr>
                                <th>Categoría</th>
                                <th>Margen Asignado (%)</th>
                                <th>Margen Efectivo Resultado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $cat)
                                <tr>
                                    <td>
                                        <div style="display:flex; align-items:center; gap:10px;">
                                            <img src="{{ $cat->icon_url }}" alt="" style="width:24px; height:24px; object-fit:contain;">
                                            <strong>{{ $cat->name }}</strong>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="display:flex; align-items:center; gap:6px;">
                                            <input type="number" step="0.01" name="category_margins[{{ $cat->id }}]" value="{{ $cat->margin_percentage }}" class="form-control" style="width:100px; font-weight:600;" placeholder="Hereda Global">
                                            <span>%</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span style="font-weight:700; color:var(--primary);">
                                            {{ $cat->margin_percentage ?? ($settings['ingram_global_margin'] ?? 18.0) }}%
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div style="margin-top:24px; padding-top:18px; border-top:1px solid var(--border-color);">
                        <button type="submit" class="btn btn-primary btn-lg">
                            Guardar Parámetros de Margen & Tipo de Cambio
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- TAB 3: Catalog Explorer -->
        <div class="tab-pane" id="tab-explorer" style="display:none;">
            <div class="checkout-card">
                <h3 style="font-size:17px; margin-bottom:8px;">Explorador en Vivo de la API Ingram</h3>
                <p style="font-size:13.5px; color:var(--text-muted); margin-bottom:20px;">
                    Consulta el catálogo de Ingram Micro directamente y visualiza los datos crudos devueltos antes de importar.
                </p>

                <div style="display:flex; gap:12px; margin-bottom:20px;">
                    <input type="text" id="api-search-keyword" placeholder="Buscar por palabra clave o SKU (ej: ThinkPad, Kingston, RTX)..." class="form-control" style="flex:1;">
                    <button type="button" id="btn-api-search" class="btn btn-primary" style="padding:10px 20px;">
                        Consultar API
                    </button>
                </div>

                <div id="api-search-loading" style="display:none; text-align:center; padding:30px;">
                    <span style="font-weight:600; color:var(--primary);">Consultando catálogo de Ingram Micro API V6...</span>
                </div>

                <div id="api-search-results" style="display:none;"></div>
            </div>
        </div>

        <!-- TAB 4: Sync & Logs -->
        <div class="tab-pane" id="tab-sync" style="display:none;">
            <div class="checkout-card" style="margin-bottom:24px;">
                <h3 style="font-size:17px; margin-bottom:8px;">Disparador de Sincronización Manual</h3>
                <p style="font-size:13.5px; color:var(--text-muted); margin-bottom:20px;">
                    Descarga los productos desde el catálogo de Ingram Micro, calcula los precios en CLP aplicando tus márgenes e ingresa o actualiza el inventario local.
                </p>

                <div style="display:flex; gap:14px; align-items:center; flex-wrap:wrap;">
                    <input type="text" id="sync-keyword" placeholder="Filtro opcional (ej: Notebook, SSD)" class="form-control" style="max-width:240px;">
                    <select id="sync-size" class="form-control" style="max-width:140px;">
                        <option value="20">20 ítems</option>
                        <option value="50">50 ítems</option>
                    </select>
                    <button type="button" id="btn-run-sync" class="btn btn-primary">
                        Ejecutar Sincronización Ahora
                    </button>
                </div>

                <div id="sync-status-box" style="display:none; margin-top:20px; padding:16px; border-radius:8px; background:#f8fafc; border:1px solid var(--border-color);"></div>
            </div>

            <!-- Sync Logs Table -->
            <div class="admin-table-card">
                <div class="admin-table-header">
                    <h3 style="font-size:16px; margin:0;">Registro Histórico de Sincronizaciones</h3>
                </div>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Mensaje</th>
                            <th>Procesados</th>
                            <th>Éxito</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td style="font-size:12.5px; color:var(--text-muted);">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                <td><code>{{ $log->type }}</code></td>
                                <td>
                                    <span class="badge {{ $log->status === 'success' ? 'badge-success' : ($log->status === 'error' ? 'badge-danger' : 'badge-warning') }}">
                                        {{ strtoupper($log->status) }}
                                    </span>
                                </td>
                                <td style="font-size:13px;">{{ $log->message }}</td>
                                <td style="font-weight:700;">{{ $log->items_processed }}</td>
                                <td style="font-weight:700; color:#15803d;">{{ $log->items_success }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align:center; padding:30px; color:var(--text-muted);">
                                    No hay registros de sincronización disponibles.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

@endsection

@section('scripts')
<script>
    // 1. Test OAuth Connection
    document.getElementById('btn-test-conn').addEventListener('click', async () => {
        const btn = document.getElementById('btn-test-conn');
        const box = document.getElementById('test-conn-result');
        btn.disabled = true;
        btn.innerHTML = 'Probando...';
        box.style.display = 'block';
        box.innerHTML = '<div style="padding:12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px;">Autenticando con Ingram Micro OAuth 2.0...</div>';

        try {
            const res = await fetch("{{ route('admin.ingram.test') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            const data = await res.json();
            if (data.success) {
                box.innerHTML = `<div style="padding:14px; background:#f0fdf4; border:1px solid #bbf7d0; color:#15803d; border-radius:6px; font-weight:600;">✓ ${data.message}</div>`;
            } else {
                box.innerHTML = `<div style="padding:14px; background:#fef2f2; border:1px solid #fecaca; color:#b91c1c; border-radius:6px; font-weight:600;">✕ ${data.message}</div>`;
            }
        } catch (e) {
            box.innerHTML = `<div style="padding:14px; background:#fef2f2; border:1px solid #fecaca; color:#b91c1c; border-radius:6px;">Error de red: ${e.message}</div>`;
        } finally {
            btn.disabled = false;
            btn.innerHTML = '⚡ Probar Conexión OAuth';
        }
    });

    // 2. Search API Preview
    document.getElementById('btn-api-search').addEventListener('click', async () => {
        const keyword = document.getElementById('api-search-keyword').value;
        const loading = document.getElementById('api-search-loading');
        const results = document.getElementById('api-search-results');

        loading.style.display = 'block';
        results.style.display = 'none';

        try {
            const res = await fetch("{{ route('admin.ingram.preview') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ keyword })
            });
            const data = await res.json();
            loading.style.display = 'none';
            results.style.display = 'block';

            if (data.success && data.data && data.data.catalog) {
                const items = data.data.catalog;
                let html = `<div style="margin-bottom:12px; font-weight:700;">Resultados obtenidos (${items.length} productos devueltos):</div>`;
                html += `<div style="max-height:400px; overflow-y:auto; border:1px solid #e2e8f0; border-radius:8px;"><table class="admin-table"><thead><tr><th>IPN</th><th>Descripción</th><th>Costo Neto USD</th><th>Disponibilidad</th></tr></thead><tbody>`;
                items.forEach(i => {
                    const price = i.pricing?.netPrice || i.pricing?.customerPrice || 'N/A';
                    const stock = i.availability?.totalAvailability || 'Consultar';
                    html += `<tr><td><strong>${i.ingramPartNumber}</strong></td><td>${i.description}</td><td>$${price} USD</td><td>${stock}</td></tr>`;
                });
                html += `</tbody></table></div>`;
                results.innerHTML = html;
            } else {
                results.innerHTML = `<div style="padding:16px; background:#fffbeb; border:1px solid #fde68a; color:#b45309; border-radius:6px;">${data.message || 'No se obtuvieron registros o la API devolvió una respuesta vacía.'}</div>`;
            }
        } catch (e) {
            loading.style.display = 'none';
            results.style.display = 'block';
            results.innerHTML = `<div style="padding:14px; background:#fef2f2; color:#b91c1c; border-radius:6px;">Error: ${e.message}</div>`;
        }
    });

    // 3. Run Sync
    document.getElementById('btn-run-sync').addEventListener('click', async () => {
        const btn = document.getElementById('btn-run-sync');
        const box = document.getElementById('sync-status-box');
        const keyword = document.getElementById('sync-keyword').value;
        const pageSize = document.getElementById('sync-size').value;

        btn.disabled = true;
        btn.innerHTML = 'Sincronizando...';
        box.style.display = 'block';
        box.innerHTML = 'Iniciando proceso de sincronización con Ingram Micro...';

        try {
            const res = await fetch("{{ route('admin.ingram.sync') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ keyword, page_size: pageSize })
            });
            const data = await res.json();
            if (data.success) {
                box.innerHTML = `<div style="color:#15803d; font-weight:700;">✓ ${data.message}</div>`;
                setTimeout(() => location.reload(), 2000);
            } else {
                box.innerHTML = `<div style="color:#b91c1c; font-weight:700;">✕ ${data.message}</div>`;
            }
        } catch (e) {
            box.innerHTML = `<div style="color:#b91c1c;">Error: ${e.message}</div>`;
        } finally {
            btn.disabled = false;
            btn.innerHTML = 'Ejecutar Sincronización Ahora';
        }
    });
</script>
@endsection
