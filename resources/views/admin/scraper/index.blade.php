@extends('layouts.admin')

@section('title', 'Scraper de Productos')
@section('header_title', 'Scraper de Imágenes & Enriquecimiento de Catálogo')

@section('content')

    <!-- Metrics Bar -->
    <div class="metrics-grid">
        <div class="metric-card">
            <div>
                <div class="metric-title">Productos con Foto Encontrada</div>
                <div class="metric-value" style="color:#15803d;">{{ $foundProducts }}</div>
                <div style="font-size:12px; color:var(--text-muted); margin-top:4px;">Fotos de alta calidad</div>
            </div>
            <div style="width:44px; height:44px; border-radius:10px; background:#dcfce7; color:#15803d; display:flex; align-items:center; justify-content:center;">
                ✓
            </div>
        </div>

        <div class="metric-card">
            <div>
                <div class="metric-title">Pendientes de Scraping</div>
                <div class="metric-value" style="color:#d97706;">{{ $pendingProducts }}</div>
                <div style="font-size:12px; color:var(--text-muted); margin-top:4px;">Requieren ejecución</div>
            </div>
            <div style="width:44px; height:44px; border-radius:10px; background:#fef3c7; color:#d97706; display:flex; align-items:center; justify-content:center;">
                ⏳
            </div>
        </div>

        <div class="metric-card">
            <div>
                <div class="metric-title">Sin Resultados (Con Placeholder)</div>
                <div class="metric-value" style="color:#ef4444;">{{ $notFoundProducts }}</div>
                <div style="font-size:12px; color:var(--text-muted); margin-top:4px;">Marcados con foto oficial INEXUS</div>
            </div>
            <div style="width:44px; height:44px; border-radius:10px; background:#fee2e2; color:#ef4444; display:flex; align-items:center; justify-content:center;">
                ✕
            </div>
        </div>

        <div class="metric-card">
            <div>
                <div class="metric-title">Canales Activos</div>
                <div class="metric-value" style="font-size:18px; color:var(--primary);">Winpy + SPDigital + ML</div>
                <div style="font-size:12px; color:var(--text-muted); margin-top:4px;">Camuflaje de User-Agents</div>
            </div>
            <div style="width:44px; height:44px; border-radius:10px; background:var(--primary-light); color:var(--primary); display:flex; align-items:center; justify-content:center;">
                🌐
            </div>
        </div>
    </div>

    <!-- 2 Column Section: Live Tester & Batch Runner -->
    <div style="display:grid; grid-template-columns: 1.2fr 0.8fr; gap:24px; margin-bottom:30px;">
        
        <!-- Live Single SKU Scraper Tester -->
        <div class="checkout-card" style="margin:0;">
            <h3 style="font-size:17px; margin-bottom:6px;">Prueba de Scraping en Vivo (Enlace o SKU)</h3>
            <p style="font-size:13px; color:var(--text-muted); margin-bottom:18px;">
                Pega directamente una URL de producto (<strong>Winpy</strong>, SPDigital, MercadoLibre) o ingresa un SKU/código de fabricante para rastrear en vivo.
            </p>

            <div style="display:flex; gap:10px; margin-bottom:16px;">
                <input type="text" id="live-sku-input" placeholder="Ej: https://www.winpy.cl/venta/... o SKC3000S/1024G" class="form-control" style="flex:1;">
                <select id="live-source-select" class="form-control" style="max-width:160px;">
                    <option value="all">Todos los Canales</option>
                    <option value="winpy" selected>Winpy Chile</option>
                    <option value="spdigital">SPDigital Chile</option>
                    <option value="mercadolibre">Mercado Libre</option>
                </select>
                <button type="button" id="btn-run-live-scrape" class="btn btn-primary" style="padding:10px 18px;">
                    Rastrear
                </button>
            </div>

            <!-- Live Scraping Results Box -->
            <div id="live-scrape-result" style="display:none; margin-top:16px; border:1px solid var(--border-color); border-radius:8px; padding:18px; background:#f8fafc;"></div>
        </div>

        <!-- Batch Scraper Runner -->
        <div class="checkout-card" style="margin:0;">
            <h3 style="font-size:17px; margin-bottom:6px;">Scraping Masivo por Lotes</h3>
            <p style="font-size:13px; color:var(--text-muted); margin-bottom:18px;">
                Procesa automáticamente los productos que carecen de fotos en el catálogo.
            </p>

            <div style="display:flex; align-items:center; gap:12px; margin-bottom:16px;">
                <label for="batch-limit" style="font-size:13px; font-weight:600;">Lote:</label>
                <select id="batch-limit" class="form-control" style="max-width:130px;">
                    <option value="5">5 productos</option>
                    <option value="10" selected>10 productos</option>
                    <option value="25">25 productos</option>
                </select>
                <button type="button" id="btn-run-batch-scrape" class="btn btn-primary" style="flex:1;">
                    Iniciar Lote Automático
                </button>
            </div>

            <div id="batch-scrape-status" style="display:none; padding:14px; border-radius:6px; font-size:13.5px;"></div>

            <div style="margin-top:18px; padding-top:14px; border-top:1px solid var(--border-color); font-size:12px; color:var(--text-muted); line-height:1.5;">
                ℹ Si el rastreador no encuentra fotos coherentes, asigna la imagen oficial de catálogo con marca de agua INEXUS Chile para asegurar la estética de la tienda.
            </div>
        </div>

    </div>

    <!-- Recently Scraped Products Table -->
    <div class="admin-table-card">
        <div class="admin-table-header">
            <h3 style="font-size:16px; margin:0;">Últimos Productos Procesados por el Scraper</h3>
        </div>
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width:60px;">Foto</th>
                    <th>Producto / SKU</th>
                    <th>Canal Origen</th>
                    <th>Estado</th>
                    <th>Última Ejecución</th>
                    <th style="text-align:right;">Acción</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentProducts as $p)
                    <tr>
                        <td>
                            <div style="width:44px; height:44px; border:1px solid var(--border-color); border-radius:6px; padding:2px; display:flex; align-items:center; justify-content:center; background:#fff;">
                                <img src="{{ $p->image_url }}" alt="" style="max-height:38px; max-width:38px; object-fit:contain;">
                            </div>
                        </td>
                        <td>
                            <strong>{{ $p->name }}</strong>
                            <div style="font-size:11.5px; color:var(--text-muted); font-family:monospace;">SKU: {{ $p->sku }}</div>
                        </td>
                        <td>
                            <span style="font-size:12px; font-weight:700; color:var(--navy-900); text-transform:uppercase;">
                                {{ $p->scraper_source ?: 'Sin canal' }}
                            </span>
                        </td>
                        <td>
                            @if($p->scraper_status === 'found')
                                <span class="badge badge-success">Encontrada</span>
                            @elseif($p->scraper_status === 'not_found')
                                <span class="badge badge-danger">No encontrada (Placeholder)</span>
                            @else
                                <span class="badge badge-warning">Pendiente</span>
                            @endif
                        </td>
                        <td style="font-size:12.5px; color:var(--text-muted);">
                            {{ $p->scraper_last_run ? $p->scraper_last_run->diffForHumans() : 'Nunca' }}
                        </td>
                        <td style="text-align:right;">
                            <a href="{{ route('admin.products.edit', $p->id) }}" class="btn btn-secondary" style="padding:6px 12px; font-size:12px;">
                                Revisar
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align:center; padding:30px; color:var(--text-muted);">
                            No hay productos scrapeados recientemente.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

@endsection

@section('scripts')
<script>
    // 1. Live Single SKU Scraper
    document.getElementById('btn-run-live-scrape').addEventListener('click', async () => {
        const sku = document.getElementById('live-sku-input').value.trim();
        const source = document.getElementById('live-source-select').value;
        const btn = document.getElementById('btn-run-live-scrape');
        const box = document.getElementById('live-scrape-result');

        if (!sku) {
            alert('Por favor escribe un SKU o nombre de producto.');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = 'Rastreando...';
        box.style.display = 'block';
        box.innerHTML = 'Buscando en canales de comercio electrónico chilenos...';

        try {
            const res = await fetch("{{ route('admin.scraper.test') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ sku, source })
            });
            const data = await res.json();

            if (data.success && data.data) {
                const item = data.data;
                const priceTransfer = item.transfer_price ? `$${Number(item.transfer_price).toLocaleString('es-CL')} CLP` : null;
                const priceNormal = item.normal_price ? `$${Number(item.normal_price).toLocaleString('es-CL')} CLP` : null;

                box.innerHTML = `
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                        <span style="color:#15803d; font-weight:700;">✓ ${data.message}</span>
                        <span class="badge" style="background:#0284c7; color:#fff; text-transform:uppercase;">Canal: ${item.source}</span>
                    </div>
                    <div style="display:flex; gap:18px; align-items:flex-start;">
                        <img src="${item.image_url}" alt="" style="max-height:110px; max-width:110px; border-radius:8px; border:1px solid #e2e8f0; background:#fff; object-fit:contain; padding:4px;">
                        <div style="flex:1;">
                            <h4 style="font-size:15px; margin:0 0 6px; color:var(--navy-900); font-weight:700;">${item.title || 'Producto Encontrado'}</h4>
                            <div style="font-size:12px; color:var(--text-muted); margin-bottom:10px;">
                                <strong>SKU / N° Parte:</strong> <code style="background:#e2e8f0; padding:2px 6px; border-radius:4px;">${item.sku || item.vendor_part_number || 'N/A'}</code>
                                ${item.brand ? ` | <strong>Marca:</strong> ${item.brand}` : ''}
                            </div>

                            ${priceTransfer ? `
                                <div style="display:flex; gap:16px; align-items:center; background:#f1f5f9; padding:8px 12px; border-radius:6px; margin-bottom:10px;">
                                    <div>
                                        <div style="font-size:11px; text-transform:uppercase; color:#15803d; font-weight:700;">Precio Transferencia / Efectivo</div>
                                        <div style="font-size:16px; font-weight:800; color:#15803d;">${priceTransfer}</div>
                                    </div>
                                    <div style="border-left:1px solid #cbd5e1; height:28px;"></div>
                                    <div>
                                        <div style="font-size:11px; text-transform:uppercase; color:var(--text-muted); font-weight:700;">Precio Tarjeta / Normal</div>
                                        <div style="font-size:16px; font-weight:700; color:var(--navy-900);">${priceNormal}</div>
                                    </div>
                                </div>
                            ` : ''}

                            <div style="display:flex; gap:12px; align-items:center;">
                                <a href="${item.product_url}" target="_blank" class="btn btn-secondary" style="font-size:12px; padding:5px 12px;">
                                    Ver en Winpy original ↗
                                </a>
                                <a href="{{ route('shop.index') }}?q=${encodeURIComponent(item.sku || 'KC3000')}" target="_blank" class="btn btn-primary" style="font-size:12px; padding:5px 12px;">
                                    Ver en Tienda INEXUS ↗
                                </a>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                box.innerHTML = `
                    <div style="color:#b91c1c; font-weight:700; margin-bottom:12px;">✕ ${data.message}</div>
                    <div style="display:flex; gap:16px; align-items:center;">
                        <img src="${data.fallback_image}" alt="" style="max-height:80px; max-width:80px; border-radius:6px; border:1px solid #e2e8f0; background:#fff;">
                        <span style="font-size:13px; color:var(--text-muted);">Se utilizará la imagen institucional oficial de INEXUS Chile con ficha homologada.</span>
                    </div>
                `;
            }
        } catch (e) {
            box.innerHTML = `<div style="color:#b91c1c;">Error: ${e.message}</div>`;
        } finally {
            btn.disabled = false;
            btn.innerHTML = 'Rastrear';
        }
    });

    // 2. Batch Scraper
    document.getElementById('btn-run-batch-scrape').addEventListener('click', async () => {
        const btn = document.getElementById('btn-run-batch-scrape');
        const box = document.getElementById('batch-scrape-status');
        const limit = document.getElementById('batch-limit').value;

        btn.disabled = true;
        btn.innerHTML = 'Procesando Lote...';
        box.style.display = 'block';
        box.style.background = '#f0f9ff';
        box.style.color = '#0369a1';
        box.innerHTML = `Ejecutando scraping sobre lote de ${limit} productos... Por favor espera.`;

        try {
            const res = await fetch("{{ route('admin.scraper.batch') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ limit })
            });
            const data = await res.json();
            if (data.success) {
                box.style.background = '#f0fdf4';
                box.style.color = '#15803d';
                box.innerHTML = `✓ ${data.message}`;
                setTimeout(() => location.reload(), 2500);
            } else {
                box.style.background = '#fef2f2';
                box.style.color = '#b91c1c';
                box.innerHTML = `✕ Error en el lote.`;
            }
        } catch (e) {
            box.style.background = '#fef2f2';
            box.style.color = '#b91c1c';
            box.innerHTML = `Error: ${e.message}`;
        } finally {
            btn.disabled = false;
            btn.innerHTML = 'Iniciar Lote Automático';
        }
    });
</script>
@endsection
