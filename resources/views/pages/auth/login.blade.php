@extends('layouts.app')

@section('title', 'Iniciar Sesión | INEXUS Chile')

@section('content')
<div style="background: linear-gradient(180deg, #f0f9ff 0%, #f8fafc 100%); padding: 60px 0 80px; min-height: 75vh;">
    <div class="container" style="max-width: 480px;">
        <div style="background:#ffffff; border:1px solid var(--border-color); border-radius:16px; padding:36px; box-shadow:0 10px 25px -5px rgba(0,0,0,0.06);">
            
            <div style="text-align:center; margin-bottom:28px;">
                <div style="width:48px; height:48px; background:#e0f2fe; border-radius:12px; display:inline-flex; align-items:center; justify-content:center; color:var(--primary); margin-bottom:12px;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </div>
                <h1 style="font-size:24px; font-weight:800; color:var(--navy-900); margin:0 0 6px;">Iniciar Sesión</h1>
                <p style="font-size:14px; color:var(--text-muted); margin:0;">Accede a tu cuenta de cliente o portal administrativo</p>
            </div>

            @if(session('success'))
                <div style="background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; padding:12px 16px; border-radius:8px; font-size:13.5px; margin-bottom:20px; display:flex; align-items:center; gap:8px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div style="background:#fef2f2; border:1px solid #fecaca; color:#991b1b; padding:12px 16px; border-radius:8px; font-size:13.5px; margin-bottom:20px;">
                    <ul style="margin:0; padding-left:18px;">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('login.submit') }}" method="POST">
                @csrf

                <div style="margin-bottom:18px;">
                    <label style="display:block; font-size:13px; font-weight:700; color:var(--navy-800); margin-bottom:6px;">Correo Electrónico</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="tu@correo.cl" 
                        style="width:100%; padding:11px 14px; border:1px solid #cbd5e1; border-radius:8px; font-size:14px; outline:none; transition:border 0.2s;"
                        onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='#cbd5e1'">
                </div>

                <div style="margin-bottom:20px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                        <label style="font-size:13px; font-weight:700; color:var(--navy-800); margin:0;">Contraseña</label>
                    </div>
                    <input type="password" name="password" required placeholder="••••••••" 
                        style="width:100%; padding:11px 14px; border:1px solid #cbd5e1; border-radius:8px; font-size:14px; outline:none; transition:border 0.2s;"
                        onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='#cbd5e1'">
                </div>

                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
                    <label style="display:flex; align-items:center; gap:8px; font-size:13px; color:#475569; cursor:pointer;">
                        <input type="checkbox" name="remember" value="1" style="accent-color:var(--primary); width:16px; height:16px;">
                        <span>Recordar mi sesión</span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%; padding:12px; font-size:15px; font-weight:700; border-radius:8px;">
                    Ingresar a mi Cuenta
                </button>
            </form>

            <div style="margin-top:28px; padding-top:20px; border-top:1px solid var(--border-color); text-align:center;">
                <p style="font-size:13.5px; color:var(--text-muted); margin:0;">
                    ¿Aún no tienes cuenta? 
                    <a href="{{ route('register') }}" style="color:var(--primary); font-weight:700; text-decoration:none;">Regístrate aquí</a>
                </p>
            </div>

            <div style="margin-top:20px; background:#f8fafc; border:1px dashed #cbd5e1; border-radius:8px; padding:12px; font-size:12px; color:#64748b;">
                <strong>Credenciales de Prueba:</strong><br>
                • <strong>Cliente Empresa:</strong> cliente@inexus.cl / cliente2026!<br>
                • <strong>Administrador:</strong> admin@inexus.cl / inexus2026!
            </div>
        </div>
    </div>
</div>
@endsection
