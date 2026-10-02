@extends('layouts.admin')

@section('title', 'Pedido #' . $order->order_number)
@section('header_title', 'Ficha de Pedido #' . $order->order_number)

@section('content')

    <div style="margin-bottom:20px;">
        <a href="{{ route('admin.orders.index') }}" style="font-size:13.5px; color:var(--primary); font-weight:700;">
            ← Volver a la Lista de Pedidos
        </a>
    </div>

    <div style="display:grid; grid-template-columns: 2fr 1fr; gap:24px; align-items:start;">
        
        <!-- Left: Order Items & Customer Data -->
        <div>
            <!-- Items Card -->
            <div class="checkout-card" style="margin-bottom:24px;">
                <h3 style="font-size:17px; margin-bottom:16px;">Ítems del Pedido</h3>
                
                <table class="admin-table" style="border:1px solid var(--border-color); border-radius:8px; overflow:hidden;">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th style="text-align:center;">Cantidad</th>
                            <th style="text-align:right;">Precio Unitario</th>
                            <th style="text-align:right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->product_name }}</strong>
                                    <div style="font-size:12px; color:var(--text-muted); font-family:monospace;">SKU: {{ $item->product_sku }}</div>
                                </td>
                                <td style="text-align:center; font-weight:700;">{{ $item->quantity }}</td>
                                <td style="text-align:right;">{{ $item->formatted_price }}</td>
                                <td style="text-align:right; font-weight:700;">{{ $item->formatted_subtotal }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background:#f8fafc;">
                            <td colspan="3" style="text-align:right; font-weight:600;">Subtotal Productos:</td>
                            <td style="text-align:right; font-weight:600;">{{ $order->formatted_subtotal }}</td>
                        </tr>
                        <tr style="background:#f8fafc;">
                            <td colspan="3" style="text-align:right; font-weight:600;">Costo de Despacho:</td>
                            <td style="text-align:right; font-weight:600;">
                                {{ $order->shipping_cost == 0 ? 'GRATIS' : '$' . number_format($order->shipping_cost, 0, ',', '.') . ' CLP' }}
                            </td>
                        </tr>
                        <tr style="background:#f8fafc; border-top:2px solid var(--border-color);">
                            <td colspan="3" style="text-align:right; font-weight:800; font-size:15px;">Total Facturado:</td>
                            <td style="text-align:right; font-weight:800; font-size:17px; color:var(--primary);">{{ $order->formatted_total }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Customer & Invoicing Data Card -->
            <div class="checkout-card">
                <h3 style="font-size:17px; margin-bottom:16px;">Datos del Cliente & Facturación SII</h3>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; font-size:14px;">
                    <div>
                        <h4 style="font-size:14px; color:var(--navy-900); margin-bottom:8px; text-transform:uppercase; letter-spacing:0.5px;">Contacto</h4>
                        <p style="margin-bottom:4px;"><strong>Nombre:</strong> {{ $order->customer_name }}</p>
                        <p style="margin-bottom:4px;"><strong>Email:</strong> {{ $order->customer_email }}</p>
                        <p style="margin-bottom:4px;"><strong>Teléfono:</strong> {{ $order->customer_phone }}</p>
                        <p><strong>RUT Comprador:</strong> {{ $order->customer_rut }}</p>
                    </div>

                    <div>
                        <h4 style="font-size:14px; color:var(--navy-900); margin-bottom:8px; text-transform:uppercase; letter-spacing:0.5px;">Datos Tributarios</h4>
                        <p style="margin-bottom:4px;"><strong>Documento:</strong> {{ strtoupper($order->document_type) }} Electrónica</p>
                        @if($order->document_type === 'factura')
                            <p style="margin-bottom:4px;"><strong>Razón Social:</strong> {{ $order->company_name }}</p>
                            <p style="margin-bottom:4px;"><strong>RUT Empresa:</strong> {{ $order->company_rut }}</p>
                            <p><strong>Giro Comercial:</strong> {{ $order->company_giro }}</p>
                        @endif
                    </div>
                </div>

                <div style="margin-top:20px; padding-top:16px; border-top:1px solid var(--border-color);">
                    <h4 style="font-size:14px; color:var(--navy-900); margin-bottom:8px; text-transform:uppercase; letter-spacing:0.5px;">Dirección de Despacho</h4>
                    <p style="margin-bottom:4px;"><strong>Dirección:</strong> {{ $order->shipping_address }}</p>
                    <p style="margin-bottom:4px;"><strong>Comuna:</strong> {{ $order->shipping_city }}</p>
                    <p style="margin-bottom:4px;"><strong>Región:</strong> {{ $order->shipping_region }}</p>
                    @if($order->shipping_notes)
                        <p style="color:var(--text-muted); font-style:italic;"><strong>Instrucciones:</strong> {{ $order->shipping_notes }}</p>
                    @endif
                    @if($order->notes)
                        <div style="margin-top:10px; padding:10px 12px; background:#f0f9ff; border:1px solid #bae6fd; border-radius:6px; font-size:12.5px; color:#0369a1;">
                            <strong>📦 Detalle de Envío:</strong> {{ $order->notes }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right: Status Update Form -->
        <div>
            <div class="checkout-card">
                <h3 style="font-size:17px; margin-bottom:16px;">Estado del Pedido & Pago</h3>

                <form action="{{ route('admin.orders.status', $order->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label class="form-label" for="status">Estado del Despacho / Pedido</label>
                        <select id="status" name="status" class="form-control">
                            <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pendiente</option>
                            <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>En Proceso / Preparación</option>
                            <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>Completado / Entregado</option>
                            <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="payment_status">Estado del Pago</label>
                        <select id="payment_status" name="payment_status" class="form-control">
                            <option value="pending" {{ $order->payment_status === 'pending' ? 'selected' : '' }}>Pago Pendiente</option>
                            <option value="approved" {{ $order->payment_status === 'approved' ? 'selected' : '' }}>Pago Aprobado / Confirmado</option>
                            <option value="rejected" {{ $order->payment_status === 'rejected' ? 'selected' : '' }}>Pago Rechazado</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="notes">Notas Internas</label>
                        <textarea id="notes" name="notes" class="form-control" style="height:90px;" placeholder="Número de seguimiento courier, notas de despacho...">{{ old('notes', $order->notes) }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        Actualizar Estado del Pedido
                    </button>
                </form>

                <div style="margin-top:20px; padding-top:16px; border-top:1px solid var(--border-color); font-size:12.5px; color:var(--text-muted);">
                    <div><strong>Medio de Pago:</strong> {{ $order->payment_method_name }}</div>
                    @if($order->payment_id)
                        <div style="margin-top:4px;"><strong>ID Transacción:</strong> <code>{{ $order->payment_id }}</code></div>
                    @endif
                    <div style="margin-top:4px;"><strong>Registrado:</strong> {{ $order->created_at->format('d/m/Y H:i:s') }}</div>
                </div>
            </div>
        </div>

    </div>

@endsection
