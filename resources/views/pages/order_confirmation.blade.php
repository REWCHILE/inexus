@extends('layouts.app')

@section('title', 'Confirmación de Pedido #' . $order->order_number . ' | INEXUS Chile')

@section('content')

    <div class="container" style="padding: 50px 20px; max-width: 860px;">
        <div style="background:#ffffff; border:1px solid var(--border-color); border-radius:var(--radius-xl); padding:40px; box-shadow:var(--shadow-md);">
            
            <!-- Success Status Header -->
            <div style="text-align:center; margin-bottom:32px;">
                <div style="width:70px; height:70px; border-radius:50%; background:#dcfce7; color:#15803d; display:flex; align-items:center; justify-content:center; margin:0 auto 16px;">
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
                <span style="font-size:13px; font-weight:700; color:var(--primary); text-transform:uppercase; letter-spacing:1px;">
                    ¡Pedido Recibido con Éxito!
                </span>
                <h1 style="font-size:32px; color:var(--navy-900); margin:6px 0 10px;">
                    N° Pedido: <span style="color:var(--primary);">{{ $order->order_number }}</span>
                </h1>
                <p style="color:var(--text-muted); font-size:15px; max-width:540px; margin:0 auto;">
                    Hemos enviado los detalles y confirmación de la compra al correo <strong>{{ $order->customer_email }}</strong>.
                </p>
            </div>

            <!-- Order Status & Payment Banner -->
            <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:16px; background:#f8fafc; border:1px solid var(--border-color); border-radius:var(--radius-md); padding:18px; margin-bottom:30px; font-size:13.5px;">
                <div>
                    <span style="color:var(--text-muted); display:block;">Estado del Pedido:</span>
                    {!! $order->status_badge !!}
                </div>
                <div>
                    <span style="color:var(--text-muted); display:block;">Estado del Pago:</span>
                    {!! $order->payment_status_badge !!}
                </div>
                <div>
                    <span style="color:var(--text-muted); display:block;">Medio de Pago:</span>
                    <strong>{{ $order->payment_method_name }}</strong>
                    @if($order->payment_id)
                        <div style="font-size:11.5px; color:var(--text-light); font-family:monospace;">ID: {{ $order->payment_id }}</div>
                    @endif
                </div>
            </div>

            <!-- Order Items Table -->
            <h3 style="font-size:18px; margin-bottom:14px;">Detalle de Productos</h3>
            <div style="border:1px solid var(--border-color); border-radius:var(--radius-md); overflow:hidden; margin-bottom:28px;">
                <table style="width:100%; border-collapse:collapse; font-size:14px;">
                    <thead>
                        <tr style="background:#f8fafc; border-bottom:1px solid var(--border-color);">
                            <th style="padding:12px 16px; text-align:left;">Producto</th>
                            <th style="padding:12px 16px; text-align:center;">Cant.</th>
                            <th style="padding:12px 16px; text-align:right;">Precio</th>
                            <th style="padding:12px 16px; text-align:right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr style="border-bottom:1px solid var(--border-color);">
                                <td style="padding:14px 16px;">
                                    <strong>{{ $item->product_name }}</strong>
                                    <div style="font-size:12px; color:var(--text-light); font-family:monospace;">SKU: {{ $item->product_sku }}</div>
                                </td>
                                <td style="padding:14px 16px; text-align:center;">{{ $item->quantity }}</td>
                                <td style="padding:14px 16px; text-align:right;">{{ $item->formatted_price }}</td>
                                <td style="padding:14px 16px; text-align:right; font-weight:700;">{{ $item->formatted_subtotal }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background:#f8fafc;">
                            <td colspan="3" style="padding:10px 16px; text-align:right; color:var(--text-muted);">Subtotal:</td>
                            <td style="padding:10px 16px; text-align:right; font-weight:600;">{{ $order->formatted_subtotal }}</td>
                        </tr>
                        <tr style="background:#f8fafc;">
                            <td colspan="3" style="padding:10px 16px; text-align:right; color:var(--text-muted);">Despacho:</td>
                            <td style="padding:10px 16px; text-align:right; font-weight:600;">
                                {{ $order->shipping_cost == 0 ? 'GRATIS' : '$' . number_format($order->shipping_cost, 0, ',', '.') . ' CLP' }}
                            </td>
                        </tr>
                        <tr style="background:#f8fafc; border-top:2px solid var(--border-color);">
                            <td colspan="3" style="padding:14px 16px; text-align:right; font-weight:800; font-size:16px;">Total Pagado:</td>
                            <td style="padding:14px 16px; text-align:right; font-weight:800; font-size:18px; color:var(--primary); font-family:'Plus Jakarta Sans';">{{ $order->formatted_total }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Customer & Invoicing Details Summary -->
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:24px; margin-bottom:30px;">
                <div style="background:#f8fafc; border:1px solid var(--border-color); border-radius:var(--radius-md); padding:18px; font-size:13.5px;">
                    <h4 style="font-size:15px; margin-bottom:10px; color:var(--navy-900);">Datos de Entrega</h4>
                    <p style="margin-bottom:4px;"><strong>Destinatario:</strong> {{ $order->customer_name }}</p>
                    <p style="margin-bottom:4px;"><strong>Teléfono:</strong> {{ $order->customer_phone }}</p>
                    <p style="margin-bottom:4px;"><strong>Dirección:</strong> {{ $order->shipping_address }}</p>
                    <p><strong>Comuna / Región:</strong> {{ $order->shipping_city }}, {{ $order->shipping_region }}</p>
                </div>

                <div style="background:#f8fafc; border:1px solid var(--border-color); border-radius:var(--radius-md); padding:18px; font-size:13.5px;">
                    <h4 style="font-size:15px; margin-bottom:10px; color:var(--navy-900);">Documentación Tributaria</h4>
                    <p style="margin-bottom:4px;"><strong>Tipo:</strong> {{ strtoupper($order->document_type) }} Electrónica</p>
                    @if($order->document_type === 'factura')
                        <p style="margin-bottom:4px;"><strong>Razón Social:</strong> {{ $order->company_name }}</p>
                        <p style="margin-bottom:4px;"><strong>RUT Empresa:</strong> {{ $order->company_rut }}</p>
                        <p><strong>Giro Comercial:</strong> {{ $order->company_giro }}</p>
                    @else
                        <p><strong>RUT Comprador:</strong> {{ $order->customer_rut }}</p>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div style="text-align:center;">
                <a href="{{ route('shop.index') }}" class="btn btn-primary btn-lg" style="margin-right:12px;">
                    Seguir Explorando Tienda
                </a>
                <a href="{{ route('home') }}" class="btn btn-secondary btn-lg">
                    Ir al Inicio
                </a>
            </div>

        </div>
    </div>

@endsection
