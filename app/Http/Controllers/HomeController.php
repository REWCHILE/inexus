<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $categories = Category::where('is_active', true)
            ->has('products')
            ->withCount('products')
            ->orderBy('sort_order')
            ->get();

        $featuredProducts = Product::where('is_active', true)
            ->where('is_featured', true)
            ->with('category')
            ->take(8)
            ->get();

        $latestProducts = Product::where('is_active', true)
            ->with('category')
            ->latest()
            ->take(8)
            ->get();

        // Fetch products for each Hero Slide
        $slide1Products = Product::whereIn('id', [1, 2, 7])->with('category')->get();
        $slide2Products = Product::whereIn('id', [4, 3, 10])->with('category')->get();
        $slide3Products = Product::whereIn('id', [5, 6, 8])->with('category')->get();

        $heroSlides = [
            [
                'id' => 1,
                'tagline' => 'Mayorista & Retail IT Chile',
                'title' => 'Hardware de alta gama y <span>soluciones tecnológicas</span> integrales.',
                'description' => 'Abastecemos a empresas, instituciones y profesionales con el mejor equipamiento informático, servidores, notebooks de resistencia corporativa y componentes de última generación.',
                'primary_btn_text' => 'Explorar Catálogo',
                'primary_btn_url' => route('shop.index'),
                'secondary_btn_text' => 'Cotización para Empresas',
                'secondary_btn_url' => route('page.contact'),
                'badge' => 'Notebooks & Corporativo',
                'products' => $slide1Products,
            ],
            [
                'id' => 2,
                'tagline' => 'Workstations & Desktops Empresariales',
                'title' => 'Potencia y rendimiento extremo para <span>empresas y creativos</span>.',
                'description' => 'Computadores de escritorio y estaciones de trabajo configuradas para cargas críticas, arquitectura, ingeniería, desarrollo y análisis intensivo de datos.',
                'primary_btn_text' => 'Ver Equipos de Escritorio',
                'primary_btn_url' => route('shop.index', ['categoria' => 'computadores-de-escritorio']),
                'secondary_btn_text' => 'Cotizar Flota IT',
                'secondary_btn_url' => route('page.contact'),
                'badge' => 'Workstations & PCs',
                'products' => $slide2Products,
            ],
            [
                'id' => 3,
                'tagline' => 'Almacenamiento NVMe & Periféricos Pro',
                'title' => 'Actualiza tu infraestructura con <span>máxima velocidad</span>.',
                'description' => 'Unidades SSD M.2 PCIe 4.0 con transferencias de hasta 7.000 MB/s, monitores profesionales de alta fidelidad y periféricos ergonómicos certificados.',
                'primary_btn_text' => 'Explorar Almacenamiento',
                'primary_btn_url' => route('shop.index', ['categoria' => 'discos-de-estado-solido-ssd']),
                'secondary_btn_text' => 'Asesoría Especializada',
                'secondary_btn_url' => route('page.contact'),
                'badge' => 'Upgrade & Almacenamiento',
                'products' => $slide3Products,
            ],
        ];

        return view('pages.home', compact('categories', 'featuredProducts', 'latestProducts', 'heroSlides'));
    }
}
