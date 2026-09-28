@extends('layouts.admin')

@section('title', 'Inventario de Productos')
@section('header_title', 'Administración de Productos & Márgenes')

@section('content')

    <!-- Search & Filter Bar -->
    <div style="background:#ffffff; border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:20px; margin-bottom:24px;">
        <form action="{{ route('admin.products.index') }}" method="GET" style="display:grid; grid-template-columns: 2fr 1.2fr 1fr auto; gap:14px; align-items:center;">
            <input type="text" name="q" placeholder="Buscar por Nombre, SKU, Part Number o Marca..." value="{{ request('q') }}" class="form-control">

            <select name="category_id" class="form-control">
                <option value="">Todas las categorías</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }} (Margen: {{ $cat->margin_percentage ?? 'Global' }}%)
                    </option>
                @endforeach
            </select>

            <select name="scraper_status" class="form-control">
                <option value="">Estado Scraper</option>
                <option value="found" {{ request('scraper_status') === 'found' ? 'selected' : '' }}>Encontrado (Con foto)</option>
                <option value="not_found" {{ request('scraper_status') === 'not_found' ? 'selected' : '' }}>Sin Foto / No encontrado</option>
                <option value="pending" {{ request('scraper_status') === 'pending' ? 'selected' : '' }}>Pendiente</option>
            </select>

            <div style="display:flex; gap:8px;">
                <button type="submit" class="btn btn-primary" style="padding:10px 16px;">Filtrar</button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary" style="padding:10px 14px;">Limpiar</a>
            </div>
        </form>
    </div>

    <!-- Products Table -->
    <div class="admin-table-card">
        <div class="admin-table-header">
            <h3 style="font-size:16px; margin:0;">Listado de Productos ({{ $products->total() }})</h3>
            <span style="font-size:12.5px; color:var(--text-muted);">
                Tasa USD actual: <strong>${{ number_format(\App\Models\Setting::get('ingram_usd_exchange_rate', 965.0), 0) }} CLP</strong>
            </span>
        </div>

        <div style="overflow-x:auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width:60px;">Foto</th>
                        <th>Producto / Identificadores</th>
                        <th>Categoría</th>
                        <th>Costo Mayorista</th>
                        <th>Margen Aplicado</th>
                        <th>Precio Venta CLP</th>
                        <th>Stock</th>
                        <th>Scraper</th>
                        <th style="text-align:right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <!-- Image -->
                            <td>
                                <div style="width:48px; height:48px; border:1px solid var(--border-color); border-radius:6px; padding:2px; display:flex; align-items:center; justify-content:center; background:#ffffff;">
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" style="max-height:42px; max-width:42px; object-fit:contain;">
                                </div>
                            </td>

                            <!-- Title & SKU -->
                            <td>
                                <div style="font-weight:700; color:var(--navy-900); max-width:320px; line-height:1.3; margin-bottom:4px;">
                                    <a href="{{ route('admin.products.edit', $product->id) }}">{{ $product->name }}</a>
                                </div>
                                <div style="font-size:11.5px; color:var(--text-muted); display:flex; gap:12px; font-family:monospace;">
                                    <span><strong>SKU:</strong> {{ $product->sku }}</span>
                                    @if($product->vendor_part_number)
                                        <span><strong>P/N:</strong> {{ $product->vendor_part_number }}</span>
                                    @endif
                                    @if($product->brand)
                                        <span style="color:var(--primary); font-weight:700;">{{ $product->brand }}</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Category -->
                            <td>
                                <span style="font-size:12.5px; font-weight:600; color:var(--navy-800);">
                                    {{ $product->category?->name ?: 'Sin categoría' }}
                                </span>
                            </td>

                            <!-- Wholesale Cost -->
                            <td>
                                @if($product->cost_price_usd > 0)
                                    <div style="font-weight:700; color:var(--navy-900);">${{ number_format($product->cost_price_usd, 2) }} USD</div>
                                    <div style="font-size:11.5px; color:var(--text-muted);">${{ number_format($product->cost_price_clp, 0, ',', '.') }} CLP</div>
                                @else
                                    <span style="color:var(--text-light); font-size:12px;">No definido</span>
                                @endif
                            </td>

                            <!-- Margin % -->
                            <td>
                                @if(!is_null($product->margin_percentage))
                                    <span class="badge" style="background:#dbeafe; color:#1e40af;" title="Margen específico individual configurado en este producto">
                                        {{ $product->margin_percentage }}% (Individual)
                                    </span>
                                @elseif($product->category && !is_null($product->category->margin_percentage))
                                    <span class="badge" style="background:#f1f5f9; color:#475569;" title="Heredado de la categoría">
                                        {{ $product->category->margin_percentage }}% (Cat)
                                    </span>
                                @else
                                    <span class="badge" style="background:#f1f5f9; color:#64748b;" title="Margen global por defecto">
                                        {{ \App\Models\Setting::get('ingram_global_margin', 18.0) }}% (Global)
                                    </span>
                                @endif
                            </td>

                            <!-- Retail Price CLP -->
                            <td>
                                <div style="font-family:'Plus Jakarta Sans'; font-weight:800; font-size:14.5px; color:var(--primary-dark);">
                                    {{ $product->formatted_price }}
                                </div>
                            </td>

                            <!-- Stock -->
                            <td>
                                @if($product->stock > 0)
                                    <span class="badge badge-success">{{ $product->stock }} un.</span>
                                @else
                                    <span class="badge badge-danger">0 un.</span>
                                @endif
                            </td>

                            <!-- Scraper Status -->
                            <td>
                                @if($product->scraper_status === 'found')
                                    <span class="badge badge-success" title="Imagen encontrada en {{ $product->scraper_source }}">OK: {{ $product->scraper_source }}</span>
                                @elseif($product->scraper_status === 'not_found')
                                    <span class="badge badge-danger" title="No encontrada. Requiere revisión.">No Encontrada</span>
                                @else
                                    <span class="badge badge-warning">Pendiente</span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td style="text-align:right;">
                                <div style="display:flex; justify-content:flex-end; gap:6px;">
                                    <form action="{{ route('admin.products.scrape', $product->id) }}" method="POST" style="margin:0;">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary" style="padding:6px 10px; font-size:12px;" title="Ejecutar Scraping para buscar foto y descripción">
                                            🔍 Scrapear
                                        </button>
                                    </form>

                                    <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-primary" style="padding:6px 12px; font-size:12px;">
                                        Editar
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align:center; padding:40px; color:var(--text-muted);">
                                No se encontraron productos coincidentes con los filtros.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="padding:16px 20px; border-top:1px solid var(--border-color); display:flex; justify-content:center;">
            {{ $products->links() }}
        </div>
    </div>

@endsection
