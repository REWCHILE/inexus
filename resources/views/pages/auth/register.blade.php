@extends('layouts.app')

@section('title', 'Registro de Cliente | INEXUS Chile')

@section('content')
<div style="background: linear-gradient(180deg, #f0f9ff 0%, #f8fafc 100%); padding: 50px 0 80px; min-height: 80vh;">
    <div class="container" style="max-width: 640px;">
        <div style="background:#ffffff; border:1px solid var(--border-color); border-radius:16px; padding:36px; box-shadow:0 10px 25px -5px rgba(0,0,0,0.06);">
            
            <div style="text-align:center; margin-bottom:28px;">
                <div style="width:48px; height:48px; background:#e0f2fe; border-radius:12px; display:inline-flex; align-items:center; justify-content:center; color:var(--primary); margin-bottom:12px;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="8.5" cy="7" r="4"></circle>
                        <line x1="20" y1="8" x2="20" y2="14"></line>
                        <line x1="23" y1="11" x2="17" y2="11"></line>
                    </svg>
                </div>
                <h1 style="font-size:24px; font-weight:800; color:var(--navy-900); margin:0 0 6px;">Crear Cuenta en INEXUS</h1>
                <p style="font-size:14px; color:var(--text-muted); margin:0;">Regístrate como Persona o Empresa para compras ágiles y seguimiento de pedidos</p>
            </div>

            @if($errors->any())
                <div style="background:#fef2f2; border:1px solid #fecaca; color:#991b1b; padding:12px 16px; border-radius:8px; font-size:13.5px; margin-bottom:20px;">
                    <ul style="margin:0; padding-left:18px;">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('register.submit') }}" method="POST" id="register-form">
                @csrf

                <!-- Account Type Selector (Persona vs Empresa) -->
                <div style="margin-bottom:24px;">
                    <label style="display:block; font-size:13px; font-weight:700; color:var(--navy-800); margin-bottom:8px;">Tipo de Cuenta / Facturación</label>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <label id="type-personal-card" style="border:2px solid {{ old('account_type', 'personal') === 'personal' ? 'var(--primary)' : '#e2e8f0' }}; background:{{ old('account_type', 'personal') === 'personal' ? '#f0f9ff' : '#ffffff' }}; border-radius:10px; padding:14px; cursor:pointer; display:flex; align-items:center; gap:10px; transition:0.2s;">
                            <input type="radio" name="account_type" value="personal" {{ old('account_type', 'personal') === 'personal' ? 'checked' : '' }} onchange="toggleAccountType('personal')" style="accent-color:var(--primary);">
                            <div>
                                <strong style="display:block; font-size:14px; color:var(--navy-900);">Persona Natural</strong>
                                <span style="font-size:12px; color:#64748b;">Boleta electrónica</span>
                            </div>
                        </label>
                        <label id="type-company-card" style="border:2px solid {{ old('account_type') === 'company' ? 'var(--primary)' : '#e2e8f0' }}; background:{{ old('account_type') === 'company' ? '#f0f9ff' : '#ffffff' }}; border-radius:10px; padding:14px; cursor:pointer; display:flex; align-items:center; gap:10px; transition:0.2s;">
                            <input type="radio" name="account_type" value="company" {{ old('account_type') === 'company' ? 'checked' : '' }} onchange="toggleAccountType('company')" style="accent-color:var(--primary);">
                            <div>
                                <strong style="display:block; font-size:14px; color:var(--navy-900);">Empresa / B2B</strong>
                                <span style="font-size:12px; color:#64748b;">Factura con IVA crédito</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                    <div>
                        <label style="display:block; font-size:13px; font-weight:700; color:var(--navy-800); margin-bottom:6px;">Nombre y Apellido *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="Carlos Valenzuela"
                            style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13.5px; outline:none;">
                    </div>
                    <div>
                        <label style="display:block; font-size:13px; font-weight:700; color:var(--navy-800); margin-bottom:6px;">Teléfono de Contacto</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" placeholder="+56 9 1234 5678"
                            style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13.5px; outline:none;">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                    <div>
                        <label style="display:block; font-size:13px; font-weight:700; color:var(--navy-800); margin-bottom:6px;">Correo Electrónico *</label>
                        <input type="email" name="email" value="{{ old('email') }}" required placeholder="nombre@ejemplo.cl"
                            style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13.5px; outline:none;">
                    </div>
                    <div id="personal-rut-field">
                        <label style="display:block; font-size:13px; font-weight:700; color:var(--navy-800); margin-bottom:6px;">RUT Personal</label>
                        <input type="text" name="rut" value="{{ old('rut') }}" placeholder="12.345.678-9"
                            style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13.5px; outline:none;">
                    </div>
                </div>

                <!-- Company Specific Fields (Hidden when personal) -->
                <div id="company-fields" style="display: {{ old('account_type') === 'company' ? 'block' : 'none' }}; background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:16px; margin-bottom:18px;">
                    <div style="font-size:13px; font-weight:700; color:var(--primary); text-transform:uppercase; margin-bottom:12px; letter-spacing:0.5px;">
                        Datos de Facturación Empresa
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:12px;">
                        <div>
                            <label style="display:block; font-size:12.5px; font-weight:700; color:var(--navy-800); margin-bottom:4px;">Razón Social</label>
                            <input type="text" name="company_name" value="{{ old('company_name') }}" placeholder="Tecnología SpA"
                                style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:13px; background:#ffffff;">
                        </div>
                        <div>
                            <label style="display:block; font-size:12.5px; font-weight:700; color:var(--navy-800); margin-bottom:4px;">RUT Empresa</label>
                            <input type="text" name="company_rut" value="{{ old('company_rut') }}" placeholder="76.123.456-7"
                                style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:13px; background:#ffffff;">
                        </div>
                    </div>
                    <div>
                        <label style="display:block; font-size:12.5px; font-weight:700; color:var(--navy-800); margin-bottom:4px;">Giro Comercial</label>
                        <input type="text" name="company_giro" value="{{ old('company_giro') }}" placeholder="Servicios Informáticos y Venta de Hardware"
                            style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:13px; background:#ffffff;">
                    </div>
                </div>

                <!-- Shipping Address -->
                <div style="margin-bottom:16px;">
                    <label style="display:block; font-size:13px; font-weight:700; color:var(--navy-800); margin-bottom:6px;">Dirección de Despacho Habitual</label>
                    <input type="text" name="shipping_address" value="{{ old('shipping_address') }}" placeholder="Av. Providencia 1234, Of. 501"
                        style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13.5px; outline:none;">
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">
                    <div>
                        <label style="display:block; font-size:13px; font-weight:700; color:var(--navy-800); margin-bottom:6px;">Comuna / Ciudad</label>
                        <input type="text" name="shipping_city" value="{{ old('shipping_city') }}" placeholder="Providencia, Santiago"
                            style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13.5px; outline:none;">
                    </div>
                    <div>
                        <label style="display:block; font-size:13px; font-weight:700; color:var(--navy-800); margin-bottom:6px;">Región</label>
                        <input type="text" name="shipping_region" value="{{ old('shipping_region', 'Metropolitana') }}" placeholder="Región Metropolitana"
                            style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13.5px; outline:none;">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:24px;">
                    <div>
                        <label style="display:block; font-size:13px; font-weight:700; color:var(--navy-800); margin-bottom:6px;">Contraseña *</label>
                        <input type="password" name="password" required placeholder="Mínimo 6 caracteres"
                            style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13.5px; outline:none;">
                    </div>
                    <div>
                        <label style="display:block; font-size:13px; font-weight:700; color:var(--navy-800); margin-bottom:6px;">Confirmar Contraseña *</label>
                        <input type="password" name="password_confirmation" required placeholder="Repite tu contraseña"
                            style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13.5px; outline:none;">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%; padding:12px; font-size:15px; font-weight:700; border-radius:8px;">
                    Registrarme en INEXUS
                </button>
            </form>

            <div style="margin-top:24px; padding-top:18px; border-top:1px solid var(--border-color); text-align:center;">
                <p style="font-size:13.5px; color:var(--text-muted); margin:0;">
                    ¿Ya tienes una cuenta creada? 
                    <a href="{{ route('login') }}" style="color:var(--primary); font-weight:700; text-decoration:none;">Inicia sesión aquí</a>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
function toggleAccountType(type) {
    const companyFields = document.getElementById('company-fields');
    const personalCard = document.getElementById('type-personal-card');
    const companyCard = document.getElementById('type-company-card');

    if (type === 'company') {
        companyFields.style.display = 'block';
        companyCard.style.borderColor = 'var(--primary)';
        companyCard.style.background = '#f0f9ff';
        personalCard.style.borderColor = '#e2e8f0';
        personalCard.style.background = '#ffffff';
    } else {
        companyFields.style.display = 'none';
        personalCard.style.borderColor = 'var(--primary)';
        personalCard.style.background = '#f0f9ff';
        companyCard.style.borderColor = '#e2e8f0';
        companyCard.style.background = '#ffffff';
    }
}
</script>
@endsection
