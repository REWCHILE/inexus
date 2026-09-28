@extends('layouts.app')

@section('title', 'Políticas de Privacidad y Tratamiento de Datos | INEXUS Chile')

@section('content')

    <div style="background:#f1f5f9; padding: 24px 0; border-bottom: 1px solid var(--border-color);">
        <div class="container">
            <h1 style="font-size:32px; color:var(--navy-900);">Políticas de Privacidad</h1>
            <p style="font-size:14px; color:var(--text-muted); margin-top:4px;">Compromiso de protección de datos conforme a la Ley chilena Nº 19.628</p>
        </div>
    </div>

    <div class="container" style="padding: 50px 20px; max-width:960px;">
        <div style="background:#ffffff; border:1px solid var(--border-color); border-radius:var(--radius-xl); padding:40px; line-height:1.8; font-size:15px; color:var(--text-main);">
            
            <h2 style="font-size:20px; color:var(--navy-900); margin-bottom:12px;">1. Declaración de Privacidad</h2>
            <p style="margin-bottom:20px;">
                En <strong>INEXUS Chile</strong> nos comprometemos a resguardar rigurosamente la privacidad y la confidencialidad de la información personal y corporativa que nuestros usuarios y clientes nos proporcionan. El tratamiento de datos personales se efectúa en estricto cumplimiento de la <strong>Ley Nº 19.628 sobre Protección de la Vida Privada</strong> y las mejores prácticas internacionales en seguridad informática.
            </p>

            <h2 style="font-size:20px; color:var(--navy-900); margin-bottom:12px;">2. Información que Recopilamos</h2>
            <p style="margin-bottom:14px;">Para la tramitación de pedidos, facturación legal y despacho, solicitamos los siguientes datos:</p>
            <ul style="margin-left:24px; margin-bottom:20px;">
                <li>Nombre completo o Razón Social.</li>
                <li>RUT personal o RUT tributario de la empresa.</li>
                <li>Dirección de correo electrónico y teléfono de contacto/WhatsApp.</li>
                <li>Dirección de despacho y facturación en Chile.</li>
            </ul>

            <h2 style="font-size:20px; color:var(--navy-900); margin-bottom:12px;">3. Finalidad del Uso de Datos</h2>
            <p style="margin-bottom:20px;">
                Los datos recolectados se emplean exclusivamente para procesar las compras, gestionar los despachos con empresas de logística autorizadas, emitir los documentos tributarios ante el SII y brindar soporte postventa o asistencia en garantías. <strong>INEXUS Chile no vende, arrienda ni comparte bases de datos con terceros para fines publicitarios no autorizados.</strong>
            </p>

            <h2 style="font-size:20px; color:var(--navy-900); margin-bottom:12px;">4. Seguridad en Transacciones Electrónicas</h2>
            <p style="margin-bottom:20px;">
                Todas las comunicaciones entre el navegador del usuario y nuestro servidor se encuentran encriptadas mediante certificados de seguridad SSL de 256 bits. Las operaciones de pago se delegan enteramente a la infraestructura bancaria certificada de Mercado Pago y pasarelas reguladas en Chile.
            </p>

            <h2 style="font-size:20px; color:var(--navy-900); margin-bottom:12px;">5. Derechos del Titular de los Datos</h2>
            <p style="margin-bottom:20px;">
                El cliente puede ejercer en todo momento sus derechos de información, modificación o cancelación de sus datos personales enviando una solicitud formal a <strong>contacto@inexus.cl</strong>.
            </p>

        </div>
    </div>

@endsection
