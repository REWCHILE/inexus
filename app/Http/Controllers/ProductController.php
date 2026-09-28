<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function show(string $slug)
    {
        $product = Product::where('slug', $slug)
            ->where('is_active', true)
            ->with('category')
            ->firstOrFail();

        $relatedProducts = Product::where('is_active', true)
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->take(4)
            ->get();

        // SEO JSON-LD Schemas
        $productSchema = $product->toJsonLd();
        $faqSchema = $product->toFaqJsonLd();
        
        $breadcrumbSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Inicio',
                    'item' => url('/')
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => $product->category?->name ?: 'Catálogo',
                    'item' => $product->category ? route('shop.index', ['categoria' => $product->category->slug]) : route('shop.index')
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 3,
                    'name' => $product->name,
                    'item' => route('product.show', $product->slug)
                ]
            ]
        ];

        return view('pages.product', compact('product', 'relatedProducts', 'productSchema', 'faqSchema', 'breadcrumbSchema'));
    }
}
