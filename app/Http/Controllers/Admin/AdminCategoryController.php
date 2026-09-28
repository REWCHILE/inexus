<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class AdminCategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->orderBy('sort_order')->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function update(Request $request, int $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:150',
            'margin_percentage' => 'nullable|numeric|min:0|max:1000',
            'icon' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $newMargin = $request->filled('margin_percentage') ? (float) $request->margin_percentage : null;

        $category->update([
            'name' => $request->name,
            'margin_percentage' => $newMargin,
            'icon' => $request->icon,
            'description' => $request->description,
        ]);

        // Optional: Recalculate prices for products without individual override
        if ($request->has('recalculate_products') && !is_null($newMargin)) {
            $products = Product::where('category_id', $category->id)
                ->whereNull('margin_percentage')
                ->get();

            foreach ($products as $p) {
                $p->calculated_price_clp = $p->calculateRetailPrice();
                $p->regular_price = $p->calculated_price_clp;
                $p->save();
            }
        }

        return redirect()->back()->with('success', "Categoría {$category->name} actualizada correctamente.");
    }
}
