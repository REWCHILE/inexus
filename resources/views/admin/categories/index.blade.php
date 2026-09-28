@extends('layouts.admin')

@section('title', 'Categorías y Márgenes')
@section('header_title', 'Administración de Categorías & Márgenes de Ganancia')

@section('content')

    <div style="background:#ffffff; border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:20px; margin-bottom:24px; display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h3 style="font-size:16px; margin:0 0 4px;">Márgenes por Categoría</h3>
            <p style="font-size:13px; color:var(--text-muted); margin:0;">
                Configura el porcentaje de margen de aporte comercial para cada familia de hardware. Los productos sin margen individual heredarán este valor.
            </p>
        </div>
        <div>
            <form action="{{ route('admin.ingram.recalculate') }}" method="POST" style="margin:0;">
                @csrf
                <button type="submit" class="btn btn-secondary" onclick="return confirm('¿Deseas recalcular los precios de venta de todo el inventario?');">
                    Recalcular Todos los Precios CLP
                </button>
            </form>
        </div>
    </div>

    <div class="admin-table-card">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width:60px;">Icono</th>
                    <th>Nombre de Categoría</th>
                    <th>Slug / URL</th>
                    <th>Productos</th>
                    <th>Margen de Aporte (%)</th>
                    <th style="text-align:right;">Guardar Cambios</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $category)
                    <tr>
                        <form action="{{ route('admin.categories.update', $category->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <!-- Icon -->
                            <td>
                                <div style="width:44px; height:44px; border:1px solid var(--border-color); border-radius:8px; padding:4px; display:flex; align-items:center; justify-content:center; background:#ffffff;">
                                    <img src="{{ $category->icon_url }}" alt="{{ $category->name }}" style="max-height:36px; max-width:36px; object-fit:contain;">
                                </div>
                            </td>

                            <!-- Name -->
                            <td>
                                <input type="text" name="name" value="{{ $category->name }}" class="form-control" style="font-weight:700; max-width:240px;" required>
                            </td>

                            <!-- Slug -->
                            <td>
                                <code style="font-size:12px; color:var(--text-muted);">{{ $category->slug }}</code>
                            </td>

                            <!-- Count -->
                            <td>
                                <span class="badge" style="background:#e0f2fe; color:#0369a1; font-size:12px;">
                                    {{ $category->products_count }} productos
                                </span>
                            </td>

                            <!-- Margin % -->
                            <td>
                                <div style="display:flex; align-items:center; gap:6px;">
                                    <input type="number" step="0.01" name="margin_percentage" value="{{ $category->margin_percentage }}" class="form-control" style="width:90px; font-weight:700; text-align:right;" placeholder="{{ \App\Models\Setting::get('ingram_global_margin', 18) }}">
                                    <span style="font-weight:700; color:var(--navy-800);">%</span>
                                </div>
                                <label style="display:flex; align-items:center; gap:6px; font-size:11.5px; color:var(--text-muted); margin-top:4px; cursor:pointer;">
                                    <input type="checkbox" name="recalculate_products" value="1" checked style="accent-color:var(--primary);">
                                    <span>Recalcular precios de esta categoría</span>
                                </label>
                            </td>

                            <!-- Action -->
                            <td style="text-align:right;">
                                <button type="submit" class="btn btn-primary" style="padding:8px 16px; font-size:12.5px;">
                                    Actualizar
                                </button>
                            </td>
                        </form>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

@endsection
