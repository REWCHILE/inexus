@extends('layouts.admin')

@section('title', 'Editar Producto: ' . $product->name)
@section('header_title', 'Editar Producto')

@section('content')

    <div style="margin-bottom:20px; display:flex; justify-content:space-between; align-items:center;">
        <a href="{{ route('admin.products.index') }}" style="font-size:13.5px; color:var(--primary); font-weight:700;">
            ← Volver a la Lista de Productos
        </a>

        <div style="display:flex; gap:10px;">
            <a href="{{ route('product.show', $product->slug) }}" target="_blank" class="btn btn-secondary" style="font-size:12.5px; padding:8px 14px;">
                Ver en Tienda Pública ↗
            </a>
            <form action="{{ route('admin.products.scrape', $product->id) }}" method="POST" style="margin:0;">
                @csrf
                <button type="submit" class="btn btn-secondary" style="font-size:12.5px; padding:8px 14px;">
                    🔍 Disparar Scraper Ahora
                </button>
            </form>
        </div>
    </div>

    <form action="{{ route('admin.products.update', $product->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div style="display:grid; grid-template-columns: 2fr 1fr; gap:24px; align-items:start;">
            
            <!-- Left Column: Product Data, Specs, FAQs -->
            <div>
                <!-- General Info Card -->
                <div class="checkout-card" style="margin-bottom:24px;">
                    <h3 style="font-size:17px; margin-bottom:18px;">Información Principal</h3>

                    <div class="form-group">
                        <label class="form-label" for="name">Nombre del Producto *</label>
                        <input type="text" id="name" name="name" class="form-control" required value="{{ old('name', $product->name) }}">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="sku">SKU Identificador *</label>
                            <input type="text" id="sku" name="sku" class="form-control" required value="{{ old('sku', $product->sku) }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="vendor_part_number">Part Number Fabricante (P/N)</label>
                            <input type="text" id="vendor_part_number" name="vendor_part_number" class="form-control" value="{{ old('vendor_part_number', $product->vendor_part_number) }}">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="category_id">Categoría</label>
                            <select id="category_id" name="category_id" class="form-control">
                                <option value="">Sin categoría</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }} (Margen Cat: {{ $cat->margin_percentage ?? 'Global' }}%)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="brand">Marca</label>
                            <input type="text" id="brand" name="brand" class="form-control" value="{{ old('brand', $product->brand) }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="short_description">Descripción Breve (Resumen para catálogo)</label>
                        <textarea id="short_description" name="short_description" class="form-control" style="height:80px;">{{ old('short_description', $product->short_description) }}</textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="description">Descripción Detallada (HTML permitido)</label>
                        <textarea id="description" name="description" class="form-control" style="height:160px;">{{ old('description', $product->description) }}</textarea>
                    </div>
                </div>

                <!-- Technical Specifications Card (Key / Value Editor) -->
                <div class="checkout-card" style="margin-bottom:24px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                        <h3 style="font-size:17px; margin:0;">Características Técnicas (Ficha)</h3>
                        <button type="button" class="btn btn-secondary" onclick="addSpecRow()" style="font-size:12px; padding:6px 12px;">+ Agregar Característica</button>
                    </div>

                    <div id="specs-container" style="display:flex; flex-direction:column; gap:10px;">
                        @if(!empty($product->specifications) && is_array($product->specifications))
                            @foreach($product->specifications as $key => $val)
                                <div class="form-row spec-row" style="align-items:center;">
                                    <input type="text" name="spec_keys[]" value="{{ $key }}" placeholder="Ej: Procesador, RAM, Pantalla" class="form-control">
                                    <input type="text" name="spec_values[]" value="{{ $val }}" placeholder="Ej: Intel Core i7, 16GB DDR4" class="form-control">
                                    <button type="button" onclick="this.closest('.spec-row').remove()" style="background:none; border:none; color:#ef4444; cursor:pointer; font-size:16px; padding:4px;">✕</button>
                                </div>
                            @endforeach
                        @else
                            <div class="form-row spec-row" style="align-items:center;">
                                <input type="text" name="spec_keys[]" placeholder="Característica (Ej: RAM)" class="form-control">
                                <input type="text" name="spec_values[]" placeholder="Valor (Ej: 16 GB DDR4)" class="form-control">
                                <button type="button" onclick="this.closest('.spec-row').remove()" style="background:none; border:none; color:#ef4444; cursor:pointer; font-size:16px; padding:4px;">✕</button>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Product FAQs Card (Question / Answer Editor) -->
                <div class="checkout-card" style="margin-bottom:24px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                        <div>
                            <h3 style="font-size:17px; margin:0 0 2px;">Preguntas Frecuentes del Producto (FAQs)</h3>
                            <span style="font-size:12px; color:var(--text-muted);">Genera automáticamente rich snippets FAQPage para SEO</span>
                        </div>
                        <button type="button" class="btn btn-secondary" onclick="addFaqRow()" style="font-size:12px; padding:6px 12px;">+ Agregar FAQ</button>
                    </div>

                    <div id="faqs-container" style="display:flex; flex-direction:column; gap:14px;">
                        @if(!empty($product->faqs) && is_array($product->faqs))
                            @foreach($product->faqs as $faq)
                                <div class="faq-row" style="background:#f8fafc; border:1px solid var(--border-color); border-radius:8px; padding:12px; position:relative;">
                                    <button type="button" onclick="this.closest('.faq-row').remove()" style="position:absolute; top:8px; right:8px; background:none; border:none; color:#ef4444; cursor:pointer; font-size:14px;">✕</button>
                                    <input type="text" name="faq_questions[]" value="{{ $faq['question'] ?? '' }}" placeholder="Pregunta (Ej: ¿Es compatible con...?)" class="form-control" style="margin-bottom:8px; font-weight:600;">
                                    <textarea name="faq_answers[]" placeholder="Respuesta detallada..." class="form-control" style="height:60px;">{{ $faq['answer'] ?? '' }}</textarea>
                                </div>
                            @endforeach
                        @else
                            <div class="faq-row" style="background:#f8fafc; border:1px solid var(--border-color); border-radius:8px; padding:12px; position:relative;">
                                <button type="button" onclick="this.closest('.faq-row').remove()" style="position:absolute; top:8px; right:8px; background:none; border:none; color:#ef4444; cursor:pointer; font-size:14px;">✕</button>
                                <input type="text" name="faq_questions[]" placeholder="Pregunta (Ej: ¿Emite factura para empresas?)" class="form-control" style="margin-bottom:8px; font-weight:600;">
                                <textarea name="faq_answers[]" placeholder="Respuesta..." class="form-control" style="height:60px;"></textarea>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Column: Margins, Pricing, Stock, Images -->
            <div>
                <!-- Pricing & Profit Margin Configuration -->
                <div class="checkout-card" style="margin-bottom:24px;">
                    <h3 style="font-size:17px; margin-bottom:16px;">Configuración de Margen & Precios</h3>

                    <div class="form-group">
                        <label class="form-label" for="cost_price_usd">Costo Mayorista USD (Ingram Micro)</label>
                        <input type="number" step="0.01" id="cost_price_usd" name="cost_price_usd" class="form-control" value="{{ old('cost_price_usd', $product->cost_price_usd) }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="cost_price_clp">Costo Equivalente en CLP</label>
                        <input type="number" step="1" id="cost_price_clp" name="cost_price_clp" class="form-control" value="{{ old('cost_price_clp', $product->cost_price_clp) }}">
                    </div>

                    <!-- Individual Product Profit Margin Override -->
                    <div class="form-group" style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; padding:14px;">
                        <label class="form-label" for="margin_percentage" style="color:#1e40af; font-weight:700;">
                            Margen Individual de Aporte (%)
                        </label>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <input type="number" step="0.01" id="margin_percentage" name="margin_percentage" class="form-control" placeholder="Ej: 20.0" value="{{ old('margin_percentage', $product->margin_percentage) }}" style="background:#fff;">
                            <span style="font-weight:700; color:#1e40af;">%</span>
                        </div>
                        <span style="font-size:11.5px; color:#3b82f6; display:block; margin-top:6px; line-height:1.4;">
                            * Si se deja en blanco, hereda el margen de la categoría ({{ $product->category?->margin_percentage ?? 'Global' }}%) o el margen global general ({{ \App\Models\Setting::get('ingram_global_margin', 18) }}%).
                        </span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="regular_price">Precio de Venta Final al Público (CLP) *</label>
                        <input type="number" step="1" id="regular_price" name="regular_price" class="form-control" required value="{{ old('regular_price', $product->regular_price) }}" style="font-weight:800; font-size:16px; color:var(--primary);">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="sale_price">Precio Oferta Especial (CLP, opcional)</label>
                        <input type="number" step="1" id="sale_price" name="sale_price" class="form-control" value="{{ old('sale_price', $product->sale_price) }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="stock">Inventario / Stock Disponible *</label>
                        <input type="number" id="stock" name="stock" class="form-control" required value="{{ old('stock', $product->stock) }}">
                    </div>
                </div>

                <!-- Product Image & Scraper Card -->
                <div class="checkout-card" style="margin-bottom:24px;">
                    <h3 style="font-size:17px; margin-bottom:16px;">Imagen Principal & Scraper</h3>

                    <div style="text-align:center; margin-bottom:16px; background:#f8fafc; border:1px solid var(--border-color); border-radius:8px; padding:16px;">
                        <img src="{{ $product->image_url }}" alt="Preview" style="max-height:150px; max-width:100%; margin:0 auto; object-fit:contain;">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="main_image">URL de la Imagen</label>
                        <input type="text" id="main_image" name="main_image" class="form-control" value="{{ old('main_image', $product->main_image) }}" placeholder="https://... o images/...">
                    </div>

                    <div style="font-size:12.5px; color:var(--text-muted); margin-bottom:14px;">
                        <strong>Estado Scraper:</strong> {{ ucfirst($product->scraper_status) }}
                        @if($product->scraper_source)
                            ({{ $product->scraper_source }})
                        @endif
                    </div>
                </div>

                <!-- Visibility Options -->
                <div class="checkout-card" style="margin-bottom:24px;">
                    <h3 style="font-size:17px; margin-bottom:14px;">Visibilidad</h3>

                    <label style="display:flex; align-items:center; gap:10px; margin-bottom:12px; cursor:pointer;">
                        <input type="checkbox" name="is_featured" value="1" {{ $product->is_featured ? 'checked' : '' }} style="accent-color:var(--primary); width:18px; height:18px;">
                        <span style="font-weight:600; font-size:14px;">Destacado en Página Principal (Home)</span>
                    </label>

                    <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                        <input type="checkbox" name="is_active" value="1" {{ $product->is_active ? 'checked' : '' }} style="accent-color:var(--primary); width:18px; height:18px;">
                        <span style="font-weight:600; font-size:14px;">Activo y Visible en Tienda</span>
                    </label>
                </div>

                <!-- Save Actions -->
                <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-bottom:12px;">
                    Guardar Cambios del Producto
                </button>
            </div>

        </div>
    </form>

@endsection

@section('scripts')
<script>
    function addSpecRow() {
        const container = document.getElementById('specs-container');
        const div = document.createElement('div');
        div.className = 'form-row spec-row';
        div.style.alignItems = 'center';
        div.innerHTML = `
            <input type="text" name="spec_keys[]" placeholder="Característica" class="form-control">
            <input type="text" name="spec_values[]" placeholder="Valor" class="form-control">
            <button type="button" onclick="this.closest('.spec-row').remove()" style="background:none; border:none; color:#ef4444; cursor:pointer; font-size:16px; padding:4px;">✕</button>
        `;
        container.appendChild(div);
    }

    function addFaqRow() {
        const container = document.getElementById('faqs-container');
        const div = document.createElement('div');
        div.className = 'faq-row';
        div.style.cssText = 'background:#f8fafc; border:1px solid var(--border-color); border-radius:8px; padding:12px; position:relative;';
        div.innerHTML = `
            <button type="button" onclick="this.closest('.faq-row').remove()" style="position:absolute; top:8px; right:8px; background:none; border:none; color:#ef4444; cursor:pointer; font-size:14px;">✕</button>
            <input type="text" name="faq_questions[]" placeholder="Pregunta..." class="form-control" style="margin-bottom:8px; font-weight:600;">
            <textarea name="faq_answers[]" placeholder="Respuesta..." class="form-control" style="height:60px;"></textarea>
        `;
        container.appendChild(div);
    }
</script>
@endsection
