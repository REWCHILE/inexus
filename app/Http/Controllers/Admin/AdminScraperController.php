<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Setting;
use App\Models\SyncLog;
use App\Services\ScraperService;
use Illuminate\Http\Request;

class AdminScraperController extends Controller
{
    protected ScraperService $scraper;

    public function __construct(ScraperService $scraper)
    {
        $this->scraper = $scraper;
    }

    public function index()
    {
        $pendingProducts = Product::where('scraper_status', 'pending')
            ->orWhereNull('main_image')
            ->orWhere('main_image', 'images/placeholder-product.svg')
            ->count();

        $foundProducts = Product::where('scraper_status', 'found')->count();
        $notFoundProducts = Product::where('scraper_status', 'not_found')->count();

        $logs = SyncLog::where('type', 'like', 'scraper%')->latest()->paginate(10);
        $recentProducts = Product::whereNotNull('scraper_last_run')->latest('scraper_last_run')->take(10)->get();

        return view('admin.scraper.index', compact('pendingProducts', 'foundProducts', 'notFoundProducts', 'logs', 'recentProducts'));
    }

    public function testScrape(Request $request)
    {
        $sku = trim((string) $request->get('sku'));
        $name = trim((string) $request->get('name', ''));
        $source = $request->get('source', 'all');

        if (empty($sku) && empty($name)) {
            return response()->json([
                'success' => false,
                'message' => 'Por favor ingrese un SKU o nombre de producto para realizar la búsqueda.',
            ]);
        }

        $result = null;

        if ($source === 'spdigital' || $source === 'all') {
            $result = $this->scraper->scrapeSpDigital($sku, $name);
        }

        if ((empty($result['image_url'])) && ($source === 'mercadolibre' || $source === 'all')) {
            $ml = $this->scraper->scrapeMercadoLibre($sku, $name);
            if (!empty($ml['image_url'])) {
                $result = $ml;
            }
        }

        if (!empty($result['image_url'])) {
            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => "Scraping exitoso desde {$result['source']}.",
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No se encontraron imágenes o información en los canales seleccionados. Se usará el placeholder oficial.',
            'fallback_image' => asset('images/placeholder-product.svg'),
        ]);
    }

    public function runBatch(Request $request)
    {
        $limit = max(1, min(50, (int) $request->get('limit', 10)));
        $result = $this->scraper->batchScrape($limit);

        return response()->json([
            'success' => true,
            'message' => "Scraping por lotes completado. Procesados: {$result['processed']}, Encontrados: {$result['found']}, Sin resultados: {$result['not_found']}.",
            'details' => $result,
        ]);
    }

    public function applyToProduct(Request $request)
    {
        $productId = $request->get('product_id');
        $imageUrl = $request->get('image_url');
        $description = $request->get('description');

        $product = Product::findOrFail($productId);
        if ($imageUrl) {
            $product->main_image = $imageUrl;
            $product->scraper_status = 'found';
        }
        if ($description && empty($product->description)) {
            $product->description = $description;
        }
        $product->scraper_last_run = now();
        $product->save();

        return response()->json([
            'success' => true,
            'message' => "Imagen y datos aplicados correctamente al producto {$product->name}.",
        ]);
    }
}
