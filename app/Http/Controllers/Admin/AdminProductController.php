<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\ScraperService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminProductController extends Controller
{
    protected ScraperService $scraper;

    public function __construct(ScraperService $scraper)
    {
        $this->scraper = $scraper;
    }

    public function index(Request $request)
    {
        $query = Product::with('category');

        if ($request->filled('q')) {
            $term = trim($request->get('q'));
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('sku', 'like', "%{$term}%")
                  ->orWhere('vendor_part_number', 'like', "%{$term}%")
                  ->orWhere('brand', 'like', "%{$term}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->get('category_id'));
        }

        if ($request->filled('scraper_status')) {
            $query->where('scraper_status', $request->get('scraper_status'));
        }

        $products = $query->latest()->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function edit(int $id)
    {
        $product = Product::findOrFail($id);
        $categories = Category::orderBy('name')->get();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, int $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:100|unique:products,sku,' . $product->id,
            'category_id' => 'nullable|exists:categories,id',
            'brand' => 'nullable|string|max:100',
            'cost_price_usd' => 'nullable|numeric|min:0',
            'cost_price_clp' => 'nullable|numeric|min:0',
            'margin_percentage' => 'nullable|numeric|min:0|max:1000',
            'regular_price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'main_image' => 'nullable|string|max:500',
        ]);

        $margin = $request->filled('margin_percentage') ? (float) $request->margin_percentage : null;
        $costUsd = (float) $request->cost_price_usd;
        $costClp = (float) $request->cost_price_clp;

        // Auto-calculate CLP cost if USD provided and CLP is 0
        if ($costClp <= 0 && $costUsd > 0) {
            $rate = (float) \App\Models\Setting::get('ingram_usd_exchange_rate', 965.0);
            $costClp = $costUsd * $rate;
        }

        // Recalculate price if margin is provided
        $calculatedPrice = (float) $request->regular_price;
        if (!is_null($margin) && $costClp > 0) {
            $calculatedPrice = round(($costClp * (1 + ($margin / 100))) / 100) * 100;
        }

        // Parse Specs
        $specs = [];
        if ($request->has('spec_keys') && is_array($request->spec_keys)) {
            foreach ($request->spec_keys as $index => $key) {
                $val = $request->spec_values[$index] ?? '';
                if (!empty(trim($key))) {
                    $specs[trim($key)] = trim($val);
                }
            }
        }

        // Parse FAQs
        $faqs = [];
        if ($request->has('faq_questions') && is_array($request->faq_questions)) {
            foreach ($request->faq_questions as $index => $q) {
                $a = $request->faq_answers[$index] ?? '';
                if (!empty(trim($q)) && !empty(trim($a))) {
                    $faqs[] = [
                        'question' => trim($q),
                        'answer' => trim($a),
                    ];
                }
            }
        }

        $product->update([
            'name' => $request->name,
            'sku' => $request->sku,
            'category_id' => $request->category_id,
            'brand' => $request->brand,
            'cost_price_usd' => $costUsd,
            'cost_price_clp' => $costClp,
            'margin_percentage' => $margin,
            'calculated_price_clp' => $calculatedPrice,
            'regular_price' => $request->filled('regular_price') ? (float) $request->regular_price : $calculatedPrice,
            'sale_price' => $request->filled('sale_price') ? (float) $request->sale_price : null,
            'stock' => (int) $request->stock,
            'stock_status' => (int) $request->stock > 0 ? 'in_stock' : 'out_of_stock',
            'main_image' => $request->main_image,
            'short_description' => $request->short_description,
            'description' => $request->description,
            'specifications' => $specs,
            'faqs' => $faqs,
            'is_featured' => $request->has('is_featured'),
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.products.index')->with('success', 'Producto actualizado con éxito.');
    }

    public function triggerScrape(int $id)
    {
        $product = Product::findOrFail($id);
        $res = $this->scraper->scrapeProduct($product);

        if ($res['success']) {
            return redirect()->back()->with('success', "Scraping exitoso desde {$res['source']}. Imagen y datos actualizados.");
        }

        return redirect()->back()->with('warning', $res['message']);
    }
}
