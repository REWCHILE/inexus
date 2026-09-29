@extends('layouts.app')

@section('title', 'Mi Cuenta | INEXUS Chile')

@section('content')
<div style="background: #f8fafc; padding: 40px 0 80px; min-height: 80vh;">
    <div class="container" style="max-width: 1100px;">
        
        <!-- Account Header -->
        <div style="background:#ffffff; border:1px solid var(--border-color); border-radius:16px; padding:28px; margin-bottom:28px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
            <div style="display:flex; align-items:center; gap:16px;">
                <div style="width:56px; height:56px; background:linear-gradient(135deg, #0284c7, #0369a1); border-radius:14px; display:flex; align-items:center; justify-content:center; color:#ffffff; font-weight:800; font-size:22px;">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <h1 style="font-size:22px; font-weight:800; color:var(--navy-900); margin:0 0 4px;">Hola, {{ $user->name }}</h1>
                    <div style="font-size:13.5px; color:var(--text-muted); display:flex; align-items:center; gap:10px;">
                        <span>{{ $user->email }}</span>
                        <span>•</span>
                        <span style="background:{{ $user->account_type === 'company' ? '#e0f2fe' : '#f1f5f9' }}; color:{{ $user->account_type === 'company' ? '#0369a1' : '#475569' }}; padding:2px 8px; border-radius:4px; font-size:11.5px; font-weight:700; text-transform:uppercase;">
                            {{ $user->account_type === 'company' ? 'Cliente Empresa' : 'Persona Natural' }}
                        </span>
                        @if($user->is_admin)
                            <span style="background:#fef3c7; color:#92400e; padding:2px 8px; border-radius:4px; font-size:11.5px; font-weight:800; text-transform:uppercase;">
                                Administrador
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div style="display:flex; gap:10px; align-items:center;">
                @if($user->is_admin)
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-primary" style="padding:10px 18px; font-size:13.5px; background:linear-gradient(135deg, #0284c7, #0f172a);">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:6px;"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                        <span>Mi Portal (Administración)</span>
                    </a>
                @endif
                <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                    @csrf
                    <button type="submit" class="btn btn-secondary" style="padding:10px 18px; font-size:13.5px;">
                        Cerrar Sesión
                    </button>
                </form>
            </div>
        </div>

        @if(session('success'))
            <div style="background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; padding:14px 18px; border-radius:10px; font-size:14px; margin-bottom:24px; display:flex; align-items:center; gap:8px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <div style="display:grid; grid-template-columns: 1.2fr 1fr; gap:28px;">
            
            <!-- Left: Order History -->
            <div>
                <div style="background:#ffffff; border:1px solid var(--border-color); border-radius:16px; padding:24px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.03);">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px;">
                        <h2 style="font-size:17px; font-weight:800; color:var(--navy-900); margin:0;">Historial de Pedidos</h2>
                        <span style="font-size:12.5px; color:#64748b;">{{ count($orders) }} pedidos</span>
                    </div>

                    @if(count($orders) > 0)
                        <div style="display:flex; flex-direction:column; gap:12px;">
                            @foreach($orders as $order)
                                <div style="border:1px solid #e2e8f0; border-radius:10px; padding:16px; transition:0.2s;" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor='#e2e8f0'">
                                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:10px;">
                                        <div>
                                            <a href="{{ route('order.confirmation', $order->order_number) }}" style="font-weight:700; color:var(--primary); font-size:15px; text-decoration:none;">
                                                Pedido #{{ $order->order_number }}
                                            </a>
                                            <div style="font-size:12px; color:#64748b; margin-top:2px;">
                                                {{ $order->created_at->format('d/m/Y H:i') }}
                                            </div>
                                        </div>
                                        <div>
                                            @if($order->status === 'completed' || $order->payment_status === 'paid')
                                                <span style="background:#dcfce7; color:#15803d; padding:3px 10px; border-radius:20px; font-size:11.5px; font-weight:700;">Pagado</span>
                                            @elseif($order->status === 'cancelled')
                                                <span style="background:#fee2e2; color:#b91c1c; padding:3px 10px; border-radius:20px; font-size:11.5px; font-weight:700;">Cancelado</span>
                                            @else
                                                <span style="background:#fef3c7; color:#b45309; padding:3px 10px; border-radius:20px; font-size:11.5px; font-weight:700;">Pendiente</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid #f1f5f9; padding-top:10px; font-size:13px;">
                                        <span style="color:#64748b;">
                                            Pago: {{ $order->payment_method === 'transfer' ? 'Transferencia Bancaria' : 'Tarjeta / MercadoPago' }}
                                        </span>
                                        <strong style="color:var(--navy-900); font-size:15px;">
                                            $ {{ number_format($order->total_clp, 0, ',', '.') }} CLP
                                        </strong>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div style="text-align:center; padding:40px 20px; color:#64748b;">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" style="margin-bottom:10px;"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                            <p style="margin:0 0 12px; font-size:14px;">Aún no has realizado compras con esta cuenta.</p>
                            <a href="{{ route('shop.index') }}" class="btn btn-primary" style="font-size:13px; padding:8px 16px;">Ir a la Tienda</a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right: Edit Profile & Invoicing Information -->
            <div>
                <div style="background:#ffffff; border:1px solid var(--border-color); border-radius:16px; padding:24px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.03);">
                    <h2 style="font-size:17px; font-weight:800; color:var(--navy-900); margin:0 0 16px;">Datos de Facturación y Despacho</h2>

                    <form action="{{ route('customer.account.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div style="margin-bottom:14px;">
                            <label style="display:block; font-size:12.5px; font-weight:700; color:var(--navy-800); margin-bottom:4px;">Tipo de Cuenta</label>
                            <div style="display:flex; gap:16px; font-size:13px;">
                                <label style="display:flex; align-items:center; gap:6px; cursor:pointer;">
                                    <input type="radio" name="account_type" value="personal" {{ old('account_type', $user->account_type) === 'personal' ? 'checked' : '' }} onchange="toggleAccountFields('personal')" style="accent-color:var(--primary);">
                                    <span>Persona Natural</span>
                                </label>
                                <label style="display:flex; align-items:center; gap:6px; cursor:pointer;">
                                    <input type="radio" name="account_type" value="company" {{ old('account_type', $user->account_type) === 'company' ? 'checked' : '' }} onchange="toggleAccountFields('company')" style="accent-color:var(--primary);">
                                    <span>Empresa / Factura</span>
                                </label>
                            </div>
                        </div>

                        <div style="margin-bottom:12px;">
                            <label style="display:block; font-size:12.5px; font-weight:700; color:var(--navy-800); margin-bottom:4px;">Nombre Completo</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required style="width:100%; padding:8px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:13.5px;">
                        </div>

                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px;">
                            <div>
                                <label style="display:block; font-size:12.5px; font-weight:700; color:var(--navy-800); margin-bottom:4px;">Teléfono</label>
                                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+56 9 8765 4321" style="width:100%; padding:8px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:13.5px;">
                            </div>
                            <div>
                                <label style="display:block; font-size:12.5px; font-weight:700; color:var(--navy-800); margin-bottom:4px;">RUT Personal</label>
                                <input type="text" name="rut" value="{{ old('rut', $user->rut) }}" placeholder="12.345.678-9" style="width:100%; padding:8px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:13.5px;">
                            </div>
                        </div>

                        <!-- Company section -->
                        <div id="account-company-box" style="display: {{ old('account_type', $user->account_type) === 'company' ? 'block' : 'none' }}; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px; margin-bottom:14px;">
                            <div style="margin-bottom:10px;">
                                <label style="display:block; font-size:12px; font-weight:700; color:var(--navy-800); margin-bottom:3px;">Razón Social Empresa</label>
                                <input type="text" name="company_name" value="{{ old('company_name', $user->company_name) }}" style="width:100%; padding:7px 10px; border:1px solid #cbd5e1; border-radius:6px; font-size:13px; background:#fff;">
                            </div>
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:10px;">
                                <div>
                                    <label style="display:block; font-size:12px; font-weight:700; color:var(--navy-800); margin-bottom:3px;">RUT Empresa</label>
                                    <input type="text" name="company_rut" value="{{ old('company_rut', $user->company_rut) }}" style="width:100%; padding:7px 10px; border:1px solid #cbd5e1; border-radius:6px; font-size:13px; background:#fff;">
                                </div>
                                <div>
                                    <label style="display:block; font-size:12px; font-weight:700; color:var(--navy-800); margin-bottom:3px;">Giro Comercial</label>
                                    <input type="text" name="company_giro" value="{{ old('company_giro', $user->company_giro) }}" style="width:100%; padding:7px 10px; border:1px solid #cbd5e1; border-radius:6px; font-size:13px; background:#fff;">
                                </div>
                            </div>
                        </div>

                        <div style="margin-bottom:12px;">
                            <label style="display:block; font-size:12.5px; font-weight:700; color:var(--navy-800); margin-bottom:4px;">Dirección de Despacho</label>
                            <input type="text" name="shipping_address" value="{{ old('shipping_address', $user->shipping_address) }}" style="width:100%; padding:8px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:13.5px;">
                        </div>

                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
                            <div>
                                <label style="display:block; font-size:12.5px; font-weight:700; color:var(--navy-800); margin-bottom:4px;">Ciudad / Comuna</label>
                                <input type="text" name="shipping_city" value="{{ old('shipping_city', $user->shipping_city) }}" style="width:100%; padding:8px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:13.5px;">
                            </div>
                            <div>
                                <label style="display:block; font-size:12.5px; font-weight:700; color:var(--navy-800); margin-bottom:4px;">Región</label>
                                <input type="text" name="shipping_region" value="{{ old('shipping_region', $user->shipping_region) }}" style="width:100%; padding:8px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:13.5px;">
                            </div>
                        </div>

                        <div style="border-top:1px solid #f1f5f9; padding-top:14px; margin-bottom:16px;">
                            <label style="display:block; font-size:12.5px; font-weight:700; color:var(--navy-800); margin-bottom:4px;">Cambiar Contraseña (opcional)</label>
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                                <input type="password" name="password" placeholder="Nueva contraseña" style="width:100%; padding:8px 10px; border:1px solid #cbd5e1; border-radius:6px; font-size:12.5px;">
                                <input type="password" name="password_confirmation" placeholder="Confirmar contraseña" style="width:100%; padding:8px 10px; border:1px solid #cbd5e1; border-radius:6px; font-size:12.5px;">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width:100%; padding:10px; font-size:14px; font-weight:700;">
                            Guardar Cambios
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function toggleAccountFields(type) {
    const box = document.getElementById('account-company-box');
    if (box) {
        box.style.display = (type === 'company') ? 'block' : 'none';
    }
}
</script>
@endsection
