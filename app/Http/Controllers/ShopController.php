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

        // By default, only show products with active price when browsing
        if (!$request->has('incluir_sin_precio') && !$request->filled('q')) {
            $query->where('regular_price', '>', 0);
        }

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

        // Sorting (Prioritize products with real price & stock first)
        $sort = $request->get('orden', 'mas_reciente');
        match ($sort) {
            'precio_menor' => $query->where('regular_price', '>', 0)->orderBy('regular_price', 'asc'),
            'precio_mayor' => $query->orderBy('regular_price', 'desc'),
            'nombre_az' => $query->orderBy('name', 'asc'),
            default => $query->orderByRaw('CASE WHEN stock > 0 AND regular_price > 0 THEN 1 WHEN regular_price > 0 THEN 2 ELSE 3 END')->latest(),
        };

        $products = $query->paginate(12)->withQueryString();

        // AJAX response for Infinite Scrolling
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('partials.product_cards', compact('products'))->render(),
                'has_more' => $products->hasMorePages(),
                'next_page_url' => $products->nextPageUrl(),
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'total' => $products->total(),
                'count' => $products->count(),
            ]);
        }

        $categories = Category::where('is_active', true)
            ->whereHas('products', function ($q) {
                $q->where('regular_price', '>', 0);
            })
            ->withCount(['products' => function ($q) {
                $q->where('regular_price', '>', 0);
            }])
            ->orderBy('name')
            ->get();

        $brands = Product::where('is_active', true)
            ->where('regular_price', '>', 0)
            ->whereNotNull('brand')
            ->where('brand', '!=', '')
            ->distinct()
            ->orderBy('brand')
            ->pluck('brand');

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
