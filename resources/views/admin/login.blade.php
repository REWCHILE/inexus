<!DOCTYPE html>
<html lang="es-CL">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso al Panel de Administración | INEXUS Chile</title>
    <link rel="stylesheet" href="{{ asset('css/inexus.css') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/categories/1.png') }}">
</head>
<body style="background: radial-gradient(circle at 50% 30%, #0f172a 0%, #0a192f 100%); display:flex; align-items:center; justify-content:center; min-height:100vh; padding:20px;">

    <div style="background:#ffffff; width:100%; max-width:440px; border-radius:var(--radius-xl); padding:40px; box-shadow:0 20px 40px rgba(0,0,0,0.3);">
        
        <div style="text-align:center; margin-bottom:30px;">
            <a href="{{ route('home') }}">
                <img src="{{ asset('images/logo.png') }}" alt="INEXUS" style="height:44px; margin:0 auto 14px;">
            </a>
            <h1 style="font-size:20px; color:var(--navy-900); margin:0;">Portal de Administración</h1>
            <p style="font-size:13px; color:var(--text-muted); margin-top:4px;">Ingresa tus credenciales autorizadas de INEXUS Chile</p>
        </div>

        @if($errors->any())
            <div style="background:#fef2f2; border:1px solid #fecaca; color:#b91c1c; padding:12px; border-radius:8px; font-size:13px; margin-bottom:20px;">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('admin.login.submit') }}" method="POST">
            @csrf

            <div class="form-group">
                <label class="form-label" for="email">Correo Electrónico Administrador</label>
                <input type="email" id="email" name="email" class="form-control" required placeholder="admin@inexus.cl" value="{{ old('email', 'admin@inexus.cl') }}">
            </div>

            <div class="form-group" style="margin-bottom:24px;">
                <label class="form-label" for="password">Contraseña de Seguridad</label>
                <input type="password" id="password" name="password" class="form-control" required placeholder="••••••••" value="inexus2026!">
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; font-size:13px;">
                <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                    <input type="checkbox" name="remember" checked style="accent-color:var(--primary);">
                    <span>Recordar sesión</span>
                </label>
                <a href="{{ route('home') }}" style="color:var(--primary); font-weight:600;">Volver a la tienda</a>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg">
                Iniciar Sesión en el Panel
            </button>
        </form>

        <div style="margin-top:24px; padding-top:20px; border-top:1px solid var(--border-color); text-align:center; font-size:12px; color:var(--text-muted);">
            Credencial por defecto: <strong>admin@inexus.cl</strong> / <strong>inexus2026!</strong>
        </div>

    </div>

</body>
</html>
