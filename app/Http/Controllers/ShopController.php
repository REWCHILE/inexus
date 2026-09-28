<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::where('is_active', true)->with('category');

        // Search query
        if ($request->filled('q')) {
            $term = trim($request->get('q'));
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('sku', 'like', "%{$term}%")
                  ->orWhere('vendor_part_number', 'like', "%{$term}%")
                  ->orWhere('brand', 'like', "%{$term}%")
                  ->orWhere('short_description', 'like', "%{$term}%");
            });
        }

        // Category filter
        if ($request->filled('categoria')) {
            $catSlug = $request->get('categoria');
            $query->whereHas('category', function ($q) use ($catSlug) {
                $q->where('slug', $catSlug);
            });
        }

        // Brand filter
        if ($request->filled('marca')) {
            $query->where('brand', $request->get('marca'));
        }

        // Stock filter
        if ($request->has('en_stock') && $request->get('en_stock') == '1') {
            $query->where('stock', '>', 0);
        }

        // Price range
        if ($request->filled('min_price')) {
            $query->where('regular_price', '>=', (float) $request->get('min_price'));
        }
        if ($request->filled('max_price')) {
            $query->where('regular_price', '<=', (float) $request->get('max_price'));
        }

        // Sorting
        $sort = $request->get('orden', 'mas_reciente');
        match ($sort) {
            'precio_menor' => $query->orderBy('regular_price', 'asc'),
            'precio_mayor' => $query->orderBy('regular_price', 'desc'),
            'nombre_az' => $query->orderBy('name', 'asc'),
            default => $query->latest(),
        };

        $products = $query->paginate(12)->withQueryString();

        $categories = Category::where('is_active', true)->withCount('products')->get();
        $brands = Product::whereNotNull('brand')->where('brand', '!=', '')->distinct()->pluck('brand');

        $currentCategory = $request->filled('categoria') 
            ? Category::where('slug', $request->get('categoria'))->first() 
            : null;

        return view('pages.shop', compact('products', 'categories', 'brands', 'currentCategory', 'sort'));
    }

    public function category(string $slug)
    {
        return redirect()->route('shop.index', ['categoria' => $slug]);
    }
}
