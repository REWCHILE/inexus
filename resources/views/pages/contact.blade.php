@extends('layouts.app')

@section('title', 'Contacto y Cotizaciones | INEXUS Chile')

@section('content')

    <div style="background:#f1f5f9; padding: 24px 0; border-bottom: 1px solid var(--border-color);">
        <div class="container">
            <h1 style="font-size:32px; color:var(--navy-900);">Contacto & Cotizaciones</h1>
            <p style="font-size:14px; color:var(--text-muted); margin-top:4px;">Estamos a tu disposición para asesorarte en compras corporativas y soluciones tecnológicas</p>
        </div>
    </div>

    <div class="container" style="padding: 50px 20px;">
        <div style="display:grid; grid-template-columns: 1.2fr 0.8fr; gap:40px; align-items:start;">
            
            <!-- Contact Form Card -->
            <div style="background:#ffffff; border:1px solid var(--border-color); border-radius:var(--radius-xl); padding:36px; box-shadow:var(--shadow-sm);">
                <h2 style="font-size:22px; color:var(--navy-900); margin-bottom:8px;">Envíanos un Mensaje</h2>
                <p style="color:var(--text-muted); font-size:14px; margin-bottom:24px;">Completa el formulario y te responderemos en un plazo máximo de 2 horas hábiles.</p>

                <form action="{{ route('page.contact.submit') }}" method="POST">
                    @csrf

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="contact_name">Nombre Completo *</label>
                            <input type="text" id="contact_name" name="name" class="form-control" required placeholder="Ej: Marcela Soto" value="{{ old('name') }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="contact_email">Correo Electrónico *</label>
                            <input type="email" id="contact_email" name="email" class="form-control" required placeholder="tu-correo@empresa.cl" value="{{ old('email') }}">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="contact_phone">Teléfono / Celular</label>
                            <input type="text" id="contact_phone" name="phone" class="form-control" placeholder="+56 9 9876 5432" value="{{ old('phone') }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="contact_subject">Motivo de Contacto *</label>
                            <select id="contact_subject" name="subject" class="form-control" required>
                                <option value="Cotización Formal B2B">Cotización Formal para Empresas (B2B)</option>
                                <option value="Consulta sobre Stock o Producto">Consulta sobre Stock o Producto Específico</option>
                                <option value="Consulta sobre Despacho o Pedido">Consulta sobre Estado de Despacho</option>
                                <option value="Servicio Técnico y Garantías">Servicio Técnico y Garantías</option>
                                <option value="Otro Asunto">Otro Asunto</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="contact_message">Mensaje o Requerimiento Técnico *</label>
                        <textarea id="contact_message" name="message" class="form-control" style="height:130px; resize:vertical; padding:12px;" required placeholder="Describe las cantidades, modelos o consultas que requieres...">{{ old('message') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg btn-block" style="margin-top:10px;">
                        Enviar Mensaje
                    </button>
                </form>
            </div>

            <!-- Contact Information Sidebar -->
            <div>
                <div style="background:#ffffff; border:1px solid var(--border-color); border-radius:var(--radius-xl); padding:32px; margin-bottom:24px; box-shadow:var(--shadow-sm);">
                    <h3 style="font-size:18px; color:var(--navy-900); margin-bottom:20px; padding-bottom:12px; border-bottom:1px solid var(--border-color);">
                        Información Directa
                    </h3>

                    <div style="display:flex; flex-direction:column; gap:20px; font-size:14.5px;">
                        <div style="display:flex; align-items:flex-start; gap:14px;">
                            <div style="width:40px; height:40px; border-radius:10px; background:var(--primary-light); color:var(--primary); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                </svg>
                            </div>
                            <div>
                                <span style="font-size:12px; color:var(--text-muted); display:block;">Central Telefónica</span>
                                <strong style="color:var(--navy-900);">+56 2 2987 6543</strong>
                            </div>
                        </div>

                        <div style="display:flex; align-items:flex-start; gap:14px;">
                            <div style="width:40px; height:40px; border-radius:10px; background:#dcfce7; color:#166534; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.699c.971.53 2.01.815 3.094.815 3.181 0 5.767-2.586 5.768-5.766 0-3.18-2.586-5.766-5.768-5.766zm9.969 5.766c0 5.518-4.482 10-10 10-1.748 0-3.385-.45-4.815-1.238l-7.185 1.886 1.92-7.009c-.846-1.47-1.32-3.179-1.32-4.999 0-5.518 4.482-10 10-10 5.518 0 10 4.482 10 10z"/>
                                </svg>
                            </div>
                            <div>
                                <span style="font-size:12px; color:var(--text-muted); display:block;">WhatsApp Ventas B2B</span>
                                <strong style="color:var(--navy-900);">+56 9 8765 4321</strong>
                            </div>
                        </div>

                        <div style="display:flex; align-items:flex-start; gap:14px;">
                            <div style="width:40px; height:40px; border-radius:10px; background:var(--primary-light); color:var(--primary); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                    <polyline points="22,6 12,13 2,6"></polyline>
                                </svg>
                            </div>
                            <div>
                                <span style="font-size:12px; color:var(--text-muted); display:block;">Correo Corporativo</span>
                                <strong style="color:var(--navy-900);">ventas@inexus.cl</strong>
                            </div>
                        </div>

                        <div style="display:flex; align-items:flex-start; gap:14px;">
                            <div style="width:40px; height:40px; border-radius:10px; background:#f1f5f9; color:var(--navy-800); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                            </div>
                            <div>
                                <span style="font-size:12px; color:var(--text-muted); display:block;">Oficinas Comerciales</span>
                                <strong style="color:var(--navy-900);">Av. Providencia 1208, Of. 601, Providencia, Santiago, Chile</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="background:#0a192f; color:#ffffff; border-radius:var(--radius-xl); padding:26px;">
                    <h4 style="color:#ffffff; font-size:16px; margin-bottom:10px;">Horario de Atención</h4>
                    <p style="font-size:14px; color:#94a3b8; line-height:1.6; margin:0;">
                        Lunes a Viernes: 09:00 a 18:30 hrs (Horario Continuado).<br>
                        Sábados y Domingos: Guardia técnica vía correo y cotizaciones automatizadas.
                    </p>
                </div>
            </div>

        </div>
    </div>

@endsection
