@extends('layouts.app')

@section('title', 'Sobre Nosotros | INEXUS Chile - Soluciones de Tecnología y Hardware')

@section('content')

    <div style="background: linear-gradient(135deg, #0a192f 0%, #0369a1 100%); color:#ffffff; padding: 70px 0 60px;">
        <div class="container" style="text-align: center;">
            <span style="background:rgba(255,255,255,0.15); padding:4px 12px; border-radius:20px; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:1px;">
                Identidad Corporativa
            </span>
            <h1 style="color:#ffffff; font-size:40px; margin:14px 0 10px;">Sobre INEXUS Chile</h1>
            <p style="font-size:18px; color:#cbd5e1; max-width:650px; margin:0 auto;">
                Distribución estratégica de equipamiento informático, hardware empresarial e infraestructura de telecomunicaciones.
            </p>
        </div>
    </div>

    <div class="container" style="padding: 60px 20px; max-width:960px;">
        <div style="background:#ffffff; border:1px solid var(--border-color); border-radius:var(--radius-xl); padding:40px; box-shadow:var(--shadow-sm); line-height:1.8; font-size:16px; color:var(--text-main);">
            
            <h2 style="font-size:26px; color:var(--navy-900); margin-bottom:16px;">¿Quiénes Somos?</h2>
            <p style="margin-bottom:20px;">
                <strong>INEXUS Chile</strong> es una empresa chilena especializada en la comercialización, consultoría y suministro de hardware tecnológico de alta gama para empresas privadas, instituciones públicas, pymes y profesionales exigentes.
            </p>
            <p style="margin-bottom:28px;">
                Conectamos de forma directa los catálogos de los principales fabricantes mundiales (HP, Lenovo, Dell, Kingston, ASUS, Logitech, Epson) a través de alianzas con los mayores distribuidores autorizados de tecnología en Chile, como <strong>Ingram Micro Chile</strong>, garantizando stock genuino, trazabilidad 100% legal, soporte oficial y precios mayoristas competitivos.
            </p>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:24px; margin:36px 0;">
                <div style="background:#f8fafc; border:1px solid var(--border-color); border-radius:var(--radius-md); padding:24px;">
                    <h3 style="font-size:18px; color:var(--primary-dark); margin-bottom:10px;">Nuestra Misión</h3>
                    <p style="font-size:14.5px; color:var(--text-muted); margin:0;">
                        Facilitar el acceso al equipamiento informático más avanzado del mercado con un servicio de compras ágil, transparente, con facturación electrónica inmediata y despacho en tiempos récord a lo largo de todo el territorio nacional.
                    </p>
                </div>

                <div style="background:#f8fafc; border:1px solid var(--border-color); border-radius:var(--radius-md); padding:24px;">
                    <h3 style="font-size:18px; color:var(--primary-dark); margin-bottom:10px;">Nuestra Visión</h3>
                    <p style="font-size:14.5px; color:var(--text-muted); margin:0;">
                        Posicionarnos como el socio tecnológico predilecto de las empresas en Chile para la renovación continua de infraestructura, servidores, computadores portátiles y componentes de alto rendimiento.
                    </p>
                </div>
            </div>

            <h2 style="font-size:24px; color:var(--navy-900); margin:36px 0 16px;">¿Por qué elegir a INEXUS?</h2>
            <ul style="margin-left:24px; margin-bottom:24px;">
                <li><strong>Catálogo 100% Oficial:</strong> No comercializamos productos remanufacturados ni de importación paralela sin soporte local. Cada equipo cuenta con garantía formal en Chile.</li>
                <li><strong>Facturación Electrónica Transparente:</strong> Validación automática ante el Servicio de Impuestos Internos (SII) con emisión de Boletas y Facturas.</li>
                <li><strong>Logística Integral:</strong> Despacho asegurado a todas las regiones de Chile mediante transportistas especializados.</li>
                <li><strong>Atención Humana y Experta:</strong> Ingenieros y asesores comerciales disponibles para revisar tus requerimientos técnicos vía WhatsApp y teléfono.</li>
            </ul>

            <div style="text-align:center; margin-top:40px; padding-top:30px; border-top:1px solid var(--border-color);">
                <a href="{{ route('shop.index') }}" class="btn btn-primary btn-lg" style="margin-right:12px;">Explorar Tienda Online</a>
                <a href="{{ route('page.contact') }}" class="btn btn-secondary btn-lg">Contactar a un Asesor</a>
            </div>

        </div>
    </div>

@endsection
