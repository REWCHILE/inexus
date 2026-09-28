@extends('layouts.app')

@section('title', 'Políticas de Cambios, Devoluciones y Garantía Legal | INEXUS Chile')

@section('content')

    <div style="background:#f1f5f9; padding: 24px 0; border-bottom: 1px solid var(--border-color);">
        <div class="container">
            <h1 style="font-size:32px; color:var(--navy-900);">Cambios y Devoluciones</h1>
            <p style="font-size:14px; color:var(--text-muted); margin-top:4px;">Garantía Legal de 6 Meses según Ley Nº 19.496 del Consumidor en Chile</p>
        </div>
    </div>

    <div class="container" style="padding: 50px 20px; max-width:960px;">
        <div style="background:#ffffff; border:1px solid var(--border-color); border-radius:var(--radius-xl); padding:40px; line-height:1.8; font-size:15px; color:var(--text-main);">
            
            <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:var(--radius-md); padding:20px; margin-bottom:28px;">
                <h3 style="color:#15803d; font-size:18px; margin-bottom:6px; display:flex; align-items:center; gap:8px;">
                    <span>🛡</span> Garantía Legal Pro-Consumidor (6 Meses)
                </h3>
                <p style="color:#166534; font-size:14.5px; margin:0;">
                    Si el producto adquirido presenta fallas de fábrica o no cumple con las características técnicas descritas, cuentas con <strong>6 meses</strong> a partir de la fecha de entrega para elegir libremente entre: <strong>reparación gratuita</strong>, <strong>cambio por un producto nuevo</strong> o la <strong>devolución del 100% de tu dinero</strong>.
                </p>
            </div>

            <h2 style="font-size:20px; color:var(--navy-900); margin-bottom:12px;">1. Requisitos para Hacer Efectiva la Garantía</h2>
            <ul style="margin-left:24px; margin-bottom:20px;">
                <li>Presentar la <strong>Boleta o Factura Electrónica</strong> emitida por INEXUS Chile.</li>
                <li>El producto debe venir con sus accesorios originales, cables, fuentes de poder y manuales.</li>
                <li>El número de serie (S/N) o SKU debe ser legible y coincidir con el registro de despacho.</li>
            </ul>

            <h2 style="font-size:20px; color:var(--navy-900); margin-bottom:12px;">2. Procedimiento Paso a Paso</h2>
            <ol style="margin-left:24px; margin-bottom:24px;">
                <li style="margin-bottom:8px;"><strong>Contacto Inicial:</strong> Envía un correo a <strong>garantias@inexus.cl</strong> o contáctanos por WhatsApp indicando tu número de pedido (Ej: <em>INX-ABCD-1234</em>), descripción de la falla y fotos/videos de respaldo.</li>
                <li style="margin-bottom:8px;"><strong>Recepción y Diagnóstico:</strong> Nuestro equipo técnico coordinará la recepción del equipo en Santiago o el retiro con orden de transporte para regiones.</li>
                <li style="margin-bottom:8px;"><strong>Resolución Inmediata:</strong> En un plazo máximo de 3 a 5 días hábiles se emite el informe técnico y se procede a la solución seleccionada por el cliente.</li>
            </ol>

            <h2 style="font-size:20px; color:var(--navy-900); margin-bottom:12px;">3. Exclusiones de Garantía</h2>
            <p style="margin-bottom:14px;">La garantía no cubre daños atribuibles a:</p>
            <ul style="margin-left:24px; margin-bottom:24px;">
                <li>Golpes, caídas, quiñes o rotura física de pantallas y conectores.</li>
                <li>Exposición a líquidos, humedad extrema o derrame de sustancias corrosivas.</li>
                <li>Sobretensiones eléctricas provocadas por la red eléctrica domiciliaria sin protección UPS/regulador.</li>
                <li>Alteración o apertura de sellos de seguridad por personal no autorizado.</li>
            </ul>

            <h2 style="font-size:20px; color:var(--navy-900); margin-bottom:12px;">4. Garantía Oficial del Fabricante (Lenovo, HP, Dell, Kingston)</h2>
            <p style="margin-bottom:20px;">
                Muchos de nuestros equipos corporativos (como líneas Lenovo ThinkPad o HP ProBook) cuentan además con garantías extendidas del fabricante de <strong>12 a 36 meses con atención On-Site</strong> en las dependencias de tu empresa, gestionadas directamente por el soporte oficial de la marca en Chile.
            </p>

            <div style="background:#f8fafc; border:1px solid var(--border-color); border-radius:var(--radius-md); padding:20px; text-align:center; margin-top:30px;">
                <p style="margin-bottom:12px; font-weight:600; color:var(--navy-900);">¿Tienes dudas con la garantía de tu producto?</p>
                <a href="{{ route('page.contact') }}" class="btn btn-primary">Hablar con Soporte de Garantías</a>
            </div>

        </div>
    </div>

@endsection
