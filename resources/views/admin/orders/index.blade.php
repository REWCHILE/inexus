@extends('layouts.admin')

@section('title', 'Pedidos y Facturas')
@section('header_title', 'Administración de Pedidos & Ventas')

@section('content')

    <!-- Search & Filter Bar -->
    <div style="background:#ffffff; border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:20px; margin-bottom:24px;">
        <form action="{{ route('admin.orders.index') }}" method="GET" style="display:grid; grid-template-columns: 2fr 1fr 1fr auto; gap:14px; align-items:center;">
            <input type="text" name="q" placeholder="Buscar por N° Pedido, Cliente, RUT o Email..." value="{{ request('q') }}" class="form-control">

            <select name="status" class="form-control">
                <option value="">Todos los Estados</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pendiente</option>
                <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>En Proceso</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completado</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelado</option>
            </select>

            <select name="payment_status" class="form-control">
                <option value="">Estado de Pago</option>
                <option value="approved" {{ request('payment_status') === 'approved' ? 'selected' : '' }}>Pagado / Aprobado</option>
                <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>Pago Pendiente</option>
                <option value="rejected" {{ request('payment_status') === 'rejected' ? 'selected' : '' }}>Rechazado</option>
            </select>

            <div style="display:flex; gap:8px;">
                <button type="submit" class="btn btn-primary" style="padding:10px 16px;">Filtrar</button>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary" style="padding:10px 14px;">Limpiar</a>
            </div>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="admin-table-card">
        <div class="admin-table-header">
            <h3 style="font-size:16px; margin:0;">Registro de Pedidos ({{ $orders->total() }})</h3>
        </div>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>N° Pedido</th>
                    <th>Fecha</th>
                    <th>Cliente / Empresa</th>
                    <th>Documento</th>
                    <th>Medio de Pago</th>
                    <th>Total Pagado (CLP)</th>
                    <th>Estado Pago</th>
                    <th>Estado Pedido</th>
                    <th style="text-align:right;">Detalle</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td>
                            <a href="{{ route('admin.orders.show', $order->id) }}" style="font-weight:700; color:var(--primary);">
                                {{ $order->order_number }}
                            </a>
                        </td>
                        <td style="font-size:12.5px; color:var(--text-muted);">
                            {{ $order->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td>
                            <div style="font-weight:600; color:var(--navy-900);">{{ $order->customer_name }}</div>
                            <div style="font-size:12px; color:var(--text-muted);">{{ $order->customer_email }}</div>
                        </td>
                        <td>
                            <span class="badge" style="background:#f1f5f9; color:var(--navy-800);">
                                {{ strtoupper($order->document_type) }}
                            </span>
                            @if($order->document_type === 'factura')
                                <div style="font-size:11px; color:var(--text-muted); font-family:monospace;">{{ $order->company_rut }}</div>
                            @endif
                        </td>
                        <td>
                            <span style="font-size:13px; font-weight:600;">{{ strtoupper($order->payment_method) }}</span>
                        </td>
                        <td>
                            <span style="font-family:'Plus Jakarta Sans'; font-weight:800; font-size:14px; color:var(--navy-900);">
                                {{ $order->formatted_total }}
                            </span>
                        </td>
                        <td>{!! $order->payment_status_badge !!}</td>
                        <td>{!! $order->status_badge !!}</td>
                        <td style="text-align:right;">
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-secondary" style="padding:6px 12px; font-size:12px;">
                                Ver Ficha →
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align:center; padding:40px; color:var(--text-muted);">
                            No hay pedidos registrados con estos criterios.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="padding:16px 20px; border-top:1px solid var(--border-color); display:flex; justify-content:center;">
            {{ $orders->links() }}
        </div>
    </div>

@endsection
