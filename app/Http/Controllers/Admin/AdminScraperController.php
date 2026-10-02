<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Setting;
use App\Models\SyncLog;
use App\Services\CatalogCrossEnricherService;
use App\Services\ScraperService;
use Illuminate\Http\Request;

class AdminScraperController extends Controller
{
    protected ScraperService $scraper;
    protected CatalogCrossEnricherService $enricher;

    public function __construct(ScraperService $scraper, CatalogCrossEnricherService $enricher)
    {
        $this->scraper = $scraper;
        $this->enricher = $enricher;
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
                'message' => 'Por favor ingrese un SKU, enlace (URL) o nombre de producto para realizar la búsqueda.',
            ]);
        }

        $result = null;

        // Check if input is a direct product URL (e.g. Winpy, Solotodo, etc.)
        if (\Illuminate\Support\Str::startsWith($sku, ['http://', 'https://']) || \Illuminate\Support\Str::contains($sku, ['winpy.cl', 'solotodo.cl'])) {
            $result = $this->scraper->scrapeUrl($sku);
        } else {
            // 1. Try Icecat
            if ($source === 'icecat' || $source === 'all') {
                $icecatRes = $this->scraper->queryIcecat($request->get('brand', 'hp'), $sku, $name);
                if (!empty($icecatRes['main_image'])) {
                    $result = [
                        'source' => 'icecat',
                        'product_url' => null,
                        'image_url' => $icecatRes['main_image'],
                        'title' => $icecatRes['title'] ?? null,
                        'brand' => $icecatRes['brand'] ?? null,
                        'description' => $icecatRes['description'] ?? null,
                        'specifications' => $icecatRes['specifications'] ?? [],
                    ];
                }
            }

            // 2. Try SoloTodo
            if (empty($result['image_url']) && ($source === 'solotodo' || $source === 'all')) {
                $st = $this->scraper->queryTechCatalog($sku, $request->get('brand', ''), $name);
                if (!empty($st['main_image'])) {
                    $result = [
                        'source' => 'solotodo',
                        'product_url' => $st['winpy_url'] ?? null,
                        'image_url' => $st['main_image'],
                        'title' => $st['title'] ?? null,
                        'brand' => $st['brand'] ?? null,
                        'description' => $st['description'] ?? null,
                        'specifications' => $st['specifications'] ?? [],
                        'transfer_price' => $st['market_offer_price'] ?? null,
                        'normal_price' => $st['market_normal_price'] ?? null,
                    ];
                }
            }

            // 3. Try Winpy
            if (empty($result['image_url']) && ($source === 'winpy' || $source === 'all')) {
                $result = $this->scraper->scrapeWinpy($sku, $name);
            }

            // 4. Try SPDigital
            if (empty($result['image_url']) && ($source === 'spdigital' || $source === 'all')) {
                $result = $this->scraper->scrapeSpDigital($sku, $name);
            }

            // 5. Try MercadoLibre
            if (empty($result['image_url']) && ($source === 'mercadolibre' || $source === 'all')) {
                $ml = $this->scraper->scrapeMercadoLibre($sku, $name);
                if (!empty($ml['image_url'])) {
                    $result = $ml;
                }
            }
        }

        if (!empty($result['image_url'])) {
            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => "Scraping exitoso desde " . strtoupper($result['source'] ?? 'canal') . ".",
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
        $limit = max(1, min(500, (int) $request->get('limit', 10)));
        $result = $this->scraper->batchScrape($limit);

        return response()->json([
            'success' => true,
            'message' => "Scraping por lotes completado. Procesados: {$result['processed']}, Encontrados: {$result['found']}, Sin resultados: {$result['not_found']}.",
            'details' => $result,
        ]);
    }

    public function crossMatch(Request $request)
    {
        $limit = max(1, min(500, (int) $request->get('limit', 15)));
        $force = $request->boolean('force', false);
        $result = $this->enricher->enrichBatch($limit, $force);

        return response()->json([
            'success' => true,
            'message' => "Cruce y enriquecimiento completado. Procesados: {$result['processed']}, Enriquecidos: {$result['found']}, Sin resultados: {$result['not_found']}.",
            'details' => $result,
        ]);
    }

    public function applyToProduct(Request $request)
    {
        $productId = $request->get('product_id');
        $imageUrl = $request->get('image_url');
        $description = $request->get('description');
        $specs = $request->get('specifications');
        $regularPrice = $request->get('regular_price');
        $salePrice = $request->get('sale_price');
        $source = $request->get('source', 'scraper');

        $product = Product::findOrFail($productId);
        if ($imageUrl) {
            $product->main_image = $imageUrl;
            $product->scraper_status = 'found';
        }
        if ($description) {
            $product->description = $description;
        }
        if (!empty($specs) && is_array($specs)) {
            $product->specifications = $specs;
        }
        if ($regularPrice && $regularPrice > 0) {
            $product->regular_price = (float) $regularPrice;
        }
        if ($salePrice && $salePrice > 0) {
            $product->sale_price = (float) $salePrice;
        }
        $product->scraper_source = $source;
        $product->scraper_last_run = now();
        $product->save();

        return response()->json([
            'success' => true,
            'message' => "Datos, especificaciones e imágenes aplicados correctamente al producto {$product->name}.",
        ]);
    }
}
