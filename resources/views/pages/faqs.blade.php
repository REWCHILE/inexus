@extends('layouts.app')

@section('title', 'Preguntas Frecuentes (FAQs) | INEXUS Chile')

@section('content')

    <div style="background:#f1f5f9; padding: 24px 0; border-bottom: 1px solid var(--border-color);">
        <div class="container">
            <h1 style="font-size:32px; color:var(--navy-900);">Preguntas Frecuentes (FAQs)</h1>
            <p style="font-size:14px; color:var(--text-muted); margin-top:4px;">Resolvemos tus principales dudas sobre compras, facturación y despachos en INEXUS Chile</p>
        </div>
    </div>

    <div class="container" style="padding: 50px 20px; max-width:880px;">
        <div class="faqs-wrapper">

            <div class="faq-accordion-item open">
                <button type="button" class="faq-question-btn">
                    <span>¿Cómo solicito Factura Electrónica para mi empresa?</span>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </button>
                <div class="faq-answer-content">
                    <p>Durante el paso 2 del proceso de checkout, selecciona la opción "Factura Electrónica" e ingresa la Razón Social, RUT Empresa, Giro Comercial y Dirección Tributaria. Nuestro sistema emitirá automáticamente el documento tributario electrónico válido ante el SII y lo enviará al correo registrado.</p>
                </div>
            </div>

            <div class="faq-accordion-item">
                <button type="button" class="faq-question-btn">
                    <span>¿Los productos comercializados son nuevos y originales?</span>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </button>
                <div class="faq-answer-content">
                    <p>Absolutamente todos los productos comercializados en INEXUS son 100% nuevos, sellados de fábrica y cuentan con número de serie homologado para Chile. Trabajamos conectados con los inventarios oficiales de mayoristas certificados como Ingram Micro Chile.</p>
                </div>
            </div>

            <div class="faq-accordion-item">
                <button type="button" class="faq-question-btn">
                    <span>¿Qué medios de pago aceptan en la tienda en línea?</span>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </button>
                <div class="faq-answer-content">
                    <p>Aceptamos pagos en Pesos Chilenos (CLP) a través de Mercado Pago (tarjetas de crédito en cuotas, tarjetas de débito Redcompra y Webpay) y Transferencia Bancaria Electrónica directa a nuestra cuenta corriente corporativa.</p>
                </div>
            </div>

            <div class="faq-accordion-item">
                <button type="button" class="faq-question-btn">
                    <span>¿Cuáles son los plazos y costos de despacho a regiones?</span>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </button>
                <div class="faq-answer-content">
                    <p>Para la Región Metropolitana, las entregas se realizan en 24 a 48 horas hábiles. Para regiones, los envíos toman entre 2 y 4 días hábiles dependiendo de la zona geográfica. Además, el despacho es GRATIS para todos los pedidos superiores a $150.000 CLP.</p>
                </div>
            </div>

            <div class="faq-accordion-item">
                <button type="button" class="faq-question-btn">
                    <span>¿Cómo funciona la garantía legal de 6 meses?</span>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </button>
                <div class="faq-answer-content">
                    <p>En conformidad con la Ley Pro-Consumidor (SERNAC), dispones de un plazo de 6 meses desde la recepción del producto ante cualquier falla de fábrica para optar libremente entre la reparación gratuita, el cambio por una unidad nueva o la devolución íntegra de tu dinero.</p>
                </div>
            </div>

            <div class="faq-accordion-item">
                <button type="button" class="faq-question-btn">
                    <span>¿Puedo solicitar una cotización formal para compras por volumen?</span>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </button>
                <div class="faq-answer-content">
                    <p>Sí, disponemos de una mesa de ayuda B2B para emitir cotizaciones formales con validez de precio por 15 días, ficha técnica y condiciones de crédito corporativo para compras por volumen de computadores, monitores o servidores.</p>
                </div>
            </div>

        </div>

        <div style="text-align:center; margin-top:40px; background:#ffffff; border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:30px;">
            <h3 style="font-size:18px; margin-bottom:8px;">¿Tienes otra consulta que no aparece aquí?</h3>
            <p style="color:var(--text-muted); font-size:14.5px; margin-bottom:20px;">Nuestro equipo técnico está listo para ayudarte en tiempo real.</p>
            <a href="https://wa.me/56987654321?text=Hola%20INEXUS,%20tengo%20una%20consulta" target="_blank" class="btn btn-whatsapp" style="margin-right:12px;">
                Consultar por WhatsApp
            </a>
            <a href="{{ route('page.contact') }}" class="btn btn-secondary">
                Formulario de Contacto
            </a>
        </div>
    </div>

@endsection
