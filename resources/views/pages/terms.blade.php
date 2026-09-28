@extends('layouts.app')

@section('title', 'Términos y Condiciones de Uso y Venta | INEXUS Chile')

@section('content')

    <div style="background:#f1f5f9; padding: 24px 0; border-bottom: 1px solid var(--border-color);">
        <div class="container">
            <h1 style="font-size:32px; color:var(--navy-900);">Términos y Condiciones</h1>
            <p style="font-size:14px; color:var(--text-muted); margin-top:4px;">Última actualización: Septiembre 2026 | Aplicable para compras en territorio chileno</p>
        </div>
    </div>

    <div class="container" style="padding: 50px 20px; max-width:960px;">
        <div style="background:#ffffff; border:1px solid var(--border-color); border-radius:var(--radius-xl); padding:40px; line-height:1.8; font-size:15px; color:var(--text-main);">
            
            <h2 style="font-size:20px; color:var(--navy-900); margin-bottom:12px;">1. Aspectos Generales y Marco Regulatorio</h2>
            <p style="margin-bottom:20px;">
                El presente documento regula los términos y condiciones bajo los cuales los usuarios acceden y utilizan el sitio web <strong>inexus.cl</strong> y adquieren productos de hardware y tecnología suministrados por INEXUS Chile. La relación contractual se rige estrictamente por la legislación vigente de la República de Chile, especialmente por la <strong>Ley Nº 19.496 sobre Protección de los Derechos de los Consumidores</strong> y la <strong>Ley Nº 19.799 sobre Documentos Electrónicos y Firma Electrónica</strong>.
            </p>

            <h2 style="font-size:20px; color:var(--navy-900); margin-bottom:12px;">2. Precios, Moneda e Impuestos</h2>
            <p style="margin-bottom:20px;">
                Todos los precios exhibidos en la tienda en línea se encuentran denominados en <strong>Pesos Chilenos (CLP)</strong> e incluyen el Impuesto al Valor Agregado (<strong>IVA 19%</strong>). Los precios informados en el checkout son finales e invariables para cada pedido una vez generada la confirmación de compra correspondiente.
            </p>

            <h2 style="font-size:20px; color:var(--navy-900); margin-bottom:12px;">3. Emisión de Facturas y Boletas Electrónicas (SII)</h2>
            <p style="margin-bottom:20px;">
                INEXUS Chile emite documentación tributaria electrónica autorizada por el Servicio de Impuestos Internos (SII). El cliente puede elegir libremente al momento del pago entre:
            </p>
            <ul style="margin-left:24px; margin-bottom:20px;">
                <li><strong>Boleta Electrónica:</strong> Asociada al RUT de persona natural indicado en el checkout.</li>
                <li><strong>Factura Electrónica:</strong> Requiere que el usuario proporcione la Razón Social, RUT de empresa, Giro comercial y Domicilio tributario válido y registrado ante el SII.</li>
            </ul>

            <h2 style="font-size:20px; color:var(--navy-900); margin-bottom:12px;">4. Medios de Pago y Seguridad</h2>
            <p style="margin-bottom:20px;">
                Los pagos son procesados a través de pasarelas bancarias y electrónicas certificadas, principalmente <strong>Mercado Pago Chile</strong> (tarjetas de crédito, débito Redcompra y fondos disponibles en cuenta) o mediante <strong>Transferencia Bancaria Electrónica directa</strong>. INEXUS Chile no almacena datos confidenciales de tarjetas ni códigos de seguridad bancarios.
            </p>

            <h2 style="font-size:20px; color:var(--navy-900); margin-bottom:12px;">5. Despacho y Entrega de Mercaderías</h2>
            <p style="margin-bottom:20px;">
                Los envíos son despachados a través de empresas de transporte express con código de seguimiento en línea. En la Región Metropolitana el plazo de entrega es de 24 a 48 horas hábiles tras la confirmación del pago. Para el resto de las regiones del país, los plazos oscilan entre 2 y 4 días hábiles dependiendo de la localidad de destino.
            </p>

            <h2 style="font-size:20px; color:var(--navy-900); margin-bottom:12px;">6. Garantías y Derecho a Retracto</h2>
            <p style="margin-bottom:20px;">
                Todos los productos gozan de la garantía legal de 6 meses regulada por el SERNAC y las leyes chilenas de protección al consumidor, conforme se detalla en nuestra sección de <a href="{{ route('page.returns') }}" style="color:var(--primary); font-weight:700;">Cambios y Devoluciones</a>.
            </p>

        </div>
    </div>

@endsection
