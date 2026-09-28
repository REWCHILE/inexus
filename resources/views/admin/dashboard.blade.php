@extends('layouts.admin')

@section('title', 'Dashboard')
@section('header_title', 'Resumen General del Sistema')

@section('content')

    <!-- Metrics Cards Grid -->
    <div class="metrics-grid">
        <div class="metric-card">
            <div>
                <div class="metric-title">Productos en Catálogo</div>
                <div class="metric-value">{{ $totalProducts }}</div>
                <div style="font-size:12px; color:var(--text-muted); margin-top:4px;">{{ $totalCategories }} categorías activas</div>
            </div>
            <div style="width:44px; height:44px; border-radius:10px; background:var(--primary-light); color:var(--primary); display:flex; align-items:center; justify-content:center;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                </svg>
            </div>
        </div>

        <div class="metric-card">
            <div>
                <div class="metric-title">Ventas Totales Pagadas</div>
                <div class="metric-value">${{ number_format($totalSales, 0, ',', '.') }}</div>
                <div style="font-size:12px; color:#15803d; margin-top:4px; font-weight:600;">Moneda oficial CLP</div>
            </div>
            <div style="width:44px; height:44px; border-radius:10px; background:#dcfce7; color:#15803d; display:flex; align-items:center; justify-content:center;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="1" x2="12" y2="23"></line>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
            </div>
        </div>

        <div class="metric-card">
            <div>
                <div class="metric-title">Total de Pedidos</div>
                <div class="metric-value">{{ $totalOrders }}</div>
                <div style="font-size:12px; color:var(--text-muted); margin-top:4px;">Facturas y Boletas SII</div>
            </div>
            <div style="width:44px; height:44px; border-radius:10px; background:#f1f5f9; color:var(--navy-800); display:flex; align-items:center; justify-content:center;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
            </div>
        </div>

        <div class="metric-card">
            <div>
                <div class="metric-title">Pendientes de Scrapeo</div>
                <div class="metric-value" style="color:{{ $pendingScrapes > 0 ? '#d97706' : 'var(--navy-900)' }};">{{ $pendingScrapes }}</div>
                <div style="font-size:12px; color:var(--text-muted); margin-top:4px;">Fotos o datos faltantes</div>
            </div>
            <div style="width:44px; height:44px; border-radius:10px; background:#fef3c7; color:#d97706; display:flex; align-items:center; justify-content:center;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </div>
        </div>
    </div>

    <!-- Quick Integrations Actions Bar -->
    <div style="background:#ffffff; border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:20px; margin-bottom:30px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
        <div>
            <h3 style="font-size:16px; margin:0 0 4px;">Acciones Rápidas del Ecosistema</h3>
            <p style="font-size:13px; color:var(--text-muted); margin:0;">Control de conectores Ingram Micro, scrapers SPDigital/ML y reajuste masivo de márgenes.</p>
        </div>
        <div style="display:flex; gap:12px; flex-wrap:wrap;">
            <a href="{{ route('admin.ingram.index') }}" class="btn btn-primary" style="font-size:13px; padding:9px 16px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="2" y1="12" x2="22" y2="12"></line>
                </svg>
                <span>Sincronizar Ingram</span>
            </a>
            <a href="{{ route('admin.scraper.index') }}" class="btn btn-secondary" style="font-size:13px; padding:9px 16px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <span>Scraper de Imágenes</span>
            </a>
            <form action="{{ route('admin.ingram.recalculate') }}" method="POST" style="margin:0;">
                @csrf
                <button type="submit" class="btn btn-secondary" style="font-size:13px; padding:9px 16px;" onclick="return confirm('¿Recalcular los precios de venta de todo el catálogo según los márgenes actuales?');">
                    <span>Recalcular Precios CLP</span>
                </button>
            </form>
        </div>
    </div>

    <!-- 2 Column Section: Recent Orders & Recent Sync Logs -->
    <div style="display:grid; grid-template-columns: 1.2fr 0.8fr; gap:24px;">
        
        <!-- Recent Orders -->
        <div class="admin-table-card">
            <div class="admin-table-header">
                <h3 style="font-size:16px; margin:0;">Últimos Pedidos Registrados</h3>
                <a href="{{ route('admin.orders.index') }}" style="font-size:12.5px; color:var(--primary); font-weight:700;">Ver Todos →</a>
            </div>
            @if($recentOrders->count() > 0)
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>N° Pedido</th>
                            <th>Cliente</th>
                            <th>Total CLP</th>
                            <th>Pago</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentOrders as $order)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.orders.show', $order->id) }}" style="font-weight:700; color:var(--primary);">
                                        {{ $order->order_number }}
                                    </a>
                                </td>
                                <td>
                                    <div style="font-weight:600;">{{ $order->customer_name }}</div>
                                    <div style="font-size:11.5px; color:var(--text-light);">{{ strtoupper($order->document_type) }}</div>
                                </td>
                                <td style="font-weight:700; font-family:'Plus Jakarta Sans';">
                                    {{ $order->formatted_total }}
                                </td>
                                <td>{!! $order->payment_status_badge !!}</td>
                                <td>{!! $order->status_badge !!}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div style="padding:30px; text-align:center; color:var(--text-muted); font-size:14px;">
                    No hay pedidos recientes registrados aún.
                </div>
            @endif
        </div>

        <!-- Recent Logs -->
        <div class="admin-table-card">
            <div class="admin-table-header">
                <h3 style="font-size:16px; margin:0;">Registro de Actuación (Logs)</h3>
            </div>
            @if($recentLogs->count() > 0)
                <div style="padding:16px 20px; display:flex; flex-direction:column; gap:12px;">
                    @foreach($recentLogs as $log)
                        <div style="border-left:3px solid {{ $log->status === 'success' ? '#10b981' : ($log->status === 'error' ? '#ef4444' : '#f59e0b') }}; padding-left:12px;">
                            <div style="display:flex; justify-content:space-between; font-size:12px; color:var(--text-light); margin-bottom:2px;">
                                <span style="font-weight:700; text-transform:uppercase;">{{ $log->type }}</span>
                                <span>{{ $log->created_at->diffForHumans() }}</span>
                            </div>
                            <div style="font-size:13px; color:var(--navy-900); line-height:1.4;">{{ $log->message }}</div>
                        </div>
                    @endforeach
                </div>
            @else
                <div style="padding:30px; text-align:center; color:var(--text-muted); font-size:14px;">
                    No se han registrado eventos de sincronización recientes.
                </div>
            @endif
        </div>

    </div>

@endsection
