<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PageController extends Controller
{
    public function about()
    {
        return view('pages.about');
    }

    public function terms()
    {
        return view('pages.terms');
    }

    public function privacy()
    {
        return view('pages.privacy');
    }

    public function returns()
    {
        return view('pages.returns');
    }

    public function faqs()
    {
        return view('pages.faqs');
    }

    public function contact()
    {
        return view('pages.contact');
    }

    public function contactSubmit(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'phone' => 'nullable|string|max:30',
            'subject' => 'required|string|max:150',
            'message' => 'required|string|max:2000',
        ]);

        return redirect()->back()->with('success', '¡Gracias por contactarnos! Un ejecutivo especialista responderá tu solicitud a la brevedad.');
    }

    public function sitemap()
    {
        $products = Product::where('is_active', true)->select('slug', 'updated_at')->get();
        $categories = Category::where('is_active', true)->select('slug', 'updated_at')->get();

        $content = view('pages.sitemap', compact('products', 'categories'))->render();

        return response($content, 200)
            ->header('Content-Type', 'text/xml');
    }
}
