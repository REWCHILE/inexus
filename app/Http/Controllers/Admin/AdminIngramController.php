<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\SyncLog;
use App\Services\CatalogCrossEnricherService;
use App\Services\IngramMicroService;
use Illuminate\Http\Request;

class AdminIngramController extends Controller
{
    protected IngramMicroService $ingram;
    protected CatalogCrossEnricherService $enricher;

    public function __construct(IngramMicroService $ingram, CatalogCrossEnricherService $enricher)
    {
        $this->ingram = $ingram;
        $this->enricher = $enricher;
    }

    public function index()
    {
        $settings = Setting::getGroup('store');
        $categories = Category::orderBy('sort_order')->get();
        $logs = SyncLog::where('type', 'like', 'ingram%')->latest()->paginate(10);
        $totalSyncedProducts = Product::whereNotNull('ingram_part_number')->count();

        return view('admin.ingram.index', compact('settings', 'categories', 'logs', 'totalSyncedProducts'));
    }

    public function updateSettings(Request $request)
    {
        $fields = [
            'ingram_client_id' => 'text',
            'ingram_client_secret' => 'text',
            'ingram_customer_number' => 'text',
            'ingram_country_code' => 'text',
            'ingram_environment' => 'text',
            'ingram_global_margin' => 'float',
            'ingram_usd_exchange_rate' => 'float',
            'ingram_preserve_scraped_data' => 'boolean',
        ];

        foreach ($fields as $field => $type) {
            if ($type === 'boolean') {
                Setting::set($field, $request->has($field), 'boolean', 'store');
            } elseif ($request->filled($field)) {
                Setting::set($field, $request->get($field), $type, 'store');
            }
        }

        // Update category margins if submitted
        if ($request->has('category_margins') && is_array($request->category_margins)) {
            foreach ($request->category_margins as $catId => $marginVal) {
                Category::where('id', $catId)->update([
                    'margin_percentage' => is_numeric($marginVal) ? (float) $marginVal : null
                ]);
            }
        }

        return redirect()->back()->with('success', 'Configuraciones de Ingram Micro guardadas exitosamente.');
    }

    public function testConnection()
    {
        $result = $this->ingram->testConnection();
        return response()->json($result);
    }

    public function preview(Request $request)
    {
        $keyword = $request->get('keyword', '');
        $page = (int) $request->get('page', 1);

        $params = [
            'pageNumber' => $page,
            'pageSize' => 10,
        ];
        if (!empty($keyword)) {
            $params['keyword'] = $keyword;
        }

        $result = $this->ingram->searchCatalog($params);
        return response()->json($result);
    }

    public function sync(Request $request)
    {
        $pageSize = (int) $request->get('page_size', 20);
        $pageNumber = (int) $request->get('page_number', 1);
        $autoEnrich = $request->boolean('enrich', true);

        if ($autoEnrich) {
            $result = $this->enricher->syncAndEnrichFromIngram($pageSize, $pageNumber, true);
            return response()->json($result);
        }

        $keyword = $request->get('keyword', '');
        $response = $this->ingram->searchCatalog([
            'keyword' => $keyword,
            'pageSize' => $pageSize,
            'pageNumber' => $pageNumber
        ]);

        if (!$response['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Error al conectar con la API de Ingram: ' . ($response['message'] ?? 'Verifique credenciales y permisos.')
            ]);
        }

        $items = $response['data']['catalog'] ?? [];
        if (empty($items)) {
            return response()->json([
                'success' => false,
                'message' => 'La consulta no devolvió productos en el catálogo de Ingram Micro.'
            ]);
        }

        $preserve = (bool) Setting::get('ingram_preserve_scraped_data', true);
        $syncResult = $this->ingram->syncCatalog($items, $preserve);

        return response()->json([
            'success' => true,
            'message' => "Sincronización completada. Procesados: {$syncResult['processed']}, Éxitos: {$syncResult['success']}, Fallidos: {$syncResult['failed']}.",
            'details' => $syncResult
        ]);
    }

    public function recalculateAllPrices()
    {
        $products = Product::all();
        $updated = 0;

        foreach ($products as $product) {
            $product->calculated_price_clp = $product->calculateRetailPrice();
            if ($product->regular_price <= 0 || abs($product->regular_price - $product->calculated_price_clp) > 0) {
                $product->regular_price = $product->calculated_price_clp;
            }
            $product->save();
            $updated++;
        }

        return redirect()->back()->with('success', "Precios recalculados para {$updated} productos en base a márgenes y tipo de cambio actual.");
    }
}
