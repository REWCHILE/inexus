<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\SyncLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CatalogCrossEnricherService
{
    protected IngramMicroService $ingram;
    protected ScraperService $scraper;

    public function __construct(IngramMicroService $ingram, ScraperService $scraper)
    {
        $this->ingram = $ingram;
        $this->scraper = $scraper;
    }

    /**
     * Cross-match and enrich a single product with web/Winpy data
     */
    public function enrichProduct(Product $product, bool $force = false): array
    {
        if (!$force && $product->scraper_status === 'found' && !empty($product->main_image) && $product->main_image !== 'images/placeholder-product.svg') {
            return [
                'success' => true,
                'status' => 'already_enriched',
                'product_id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->name,
                'source' => $product->scraper_source,
                'message' => "El producto ya se encuentra enriquecido desde {$product->scraper_source}."
            ];
        }

        $vpn = trim((string) ($product->vendor_part_number ?: ''));
        $sku = trim((string) $product->sku);
        $name = trim((string) $product->name);
        $brand = trim((string) ($product->brand ?: ''));

        $data = null;

        // 1. Try Open Icecat API (official manufacturer high-res imagery, gallery, bullet points, specs)
        if (!empty($brand) && !empty($vpn)) {
            $data = $this->scraper->queryIcecat($brand, $vpn, $name);
        }

        // 2. Try querying Tech Catalog (api.solotodo.com & stores including Winpy)
        if (empty($data['main_image']) && !empty($vpn)) {
            $data = $this->scraper->queryTechCatalog($vpn, $brand, $name);
        }

        if (empty($data['main_image']) && !empty($sku)) {
            $cleanSku = preg_replace('/^(IM-|KNG-|LEN-|HP-|ASUS-)/', '', $sku);
            $data = $this->scraper->queryTechCatalog($cleanSku, $brand, $name);
        }

        if (empty($data['main_image']) && !empty($name)) {
            $data = $this->scraper->queryTechCatalog($name, $brand, $name);
        }

        // 3. Fallback to SPDigital
        if (empty($data['main_image'])) {
            $spData = $this->scraper->scrapeSpDigital($sku, $name);
            if (!empty($spData['image_url'])) {
                $data = [
                    'source' => 'spdigital',
                    'title' => null,
                    'brand' => $brand,
                    'short_description' => null,
                    'description' => $spData['description'] ?? null,
                    'main_image' => $spData['image_url'],
                    'gallery' => [],
                    'specifications' => [],
                ];
            }
        }

        // 4. Fallback to Winpy direct if still missing
        if (empty($data['main_image'])) {
            $searchKey = !empty($vpn) ? $vpn : (!empty($sku) ? $sku : $name);
            $winpyData = $this->scraper->scrapeWinpy($searchKey, $name);
            if (!empty($winpyData['image_url'])) {
                $data = [
                    'source' => 'winpy',
                    'title' => $winpyData['title'] ?? null,
                    'brand' => $winpyData['brand'] ?? $brand,
                    'short_description' => null,
                    'description' => $winpyData['description'] ?? null,
                    'main_image' => $winpyData['image_url'],
                    'gallery' => [],
                    'specifications' => $winpyData['specifications'] ?? [],
                    'market_normal_price' => $winpyData['normal_price'] ?? null,
                    'market_offer_price' => $winpyData['transfer_price'] ?? null,
                ];
            }
        }

        // 5. Fallback to MercadoLibre Chile
        if (empty($data['main_image'])) {
            $searchKey = !empty($vpn) ? $vpn : (!empty($sku) ? $sku : $name);
            $ml = $this->scraper->scrapeMercadoLibre($searchKey, $name);
            if (!empty($ml['image_url'])) {
                $data = [
                    'source' => 'mercadolibre',
                    'title' => null,
                    'brand' => $brand,
                    'short_description' => null,
                    'description' => $ml['description'] ?? null,
                    'main_image' => $ml['image_url'],
                    'gallery' => [],
                    'specifications' => [],
                    'market_normal_price' => null,
                    'market_offer_price' => null,
                ];
            }
        }

        // Apply enriched data if found
        if (!empty($data['main_image'])) {
            // Update Title if it was raw wholesale distributor text
            if (!empty($data['title'])) {
                $isRawDistributorTitle = Str::length($product->name) < 25 || Str::contains($product->name, ['-AME', 'GEN-AME', 'IM-']);
                if ($isRawDistributorTitle || empty($product->name)) {
                    $product->name = $data['title'];
                }
            }

            if (!empty($data['brand']) && empty($product->brand)) {
                $product->brand = $data['brand'];
            }

            $product->main_image = $data['main_image'];

            if (!empty($data['gallery']) && is_array($data['gallery'])) {
                $product->gallery = array_values(array_unique($data['gallery']));
            }

            if (!empty($data['specifications']) && is_array($data['specifications'])) {
                $product->specifications = $data['specifications'];
            }

            if (!empty($data['description'])) {
                $product->description = $data['description'];
            }

            if (!empty($data['short_description'])) {
                $product->short_description = $data['short_description'];
            }

            // Market reference pricing fallback if cost/price is zero
            if (($product->regular_price <= 0 || $product->calculated_price_clp <= 0) && !empty($data['market_normal_price'])) {
                $refPrice = (float) $data['market_normal_price'];
                if ($refPrice > 0) {
                    $product->regular_price = $refPrice;
                    $product->calculated_price_clp = $refPrice;
                }
            }

            // Ensure baseline inventory for sellable status
            if ($product->stock <= 0) {
                $product->stock = 5;
                $product->stock_status = 'in_stock';
            }

            // Generate dynamic category FAQs if empty
            if (empty($product->faqs) || count($product->faqs) === 0) {
                $product->faqs = $this->generateFaqsForProduct($product);
            }

            $product->scraper_source = $data['source'] ?? 'scraped';
            $product->scraper_status = 'found';
            $product->scraper_last_run = now();
            $product->save();

            return [
                'success' => true,
                'status' => 'found',
                'product_id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->name,
                'source' => $product->scraper_source,
                'main_image' => $product->image_url,
                'gallery_count' => count($product->gallery_images),
                'specs_count' => is_array($product->specifications) ? count($product->specifications) : 0,
                'message' => "Producto enriquecido exitosamente desde " . strtoupper($product->scraper_source) . "."
            ];
        }

        // Fallback placeholder
        $product->scraper_status = 'not_found';
        $product->scraper_last_run = now();
        if (empty($product->main_image)) {
            $product->main_image = 'images/placeholder-product.svg';
        }
        $product->save();

        return [
            'success' => false,
            'status' => 'not_found',
            'product_id' => $product->id,
            'sku' => $product->sku,
            'name' => $product->name,
            'source' => null,
            'message' => "No se encontraron fichas o imágenes coherentes en los canales web. Se asignó imagen oficial INEXUS Chile."
        ];
    }

    /**
     * Batch enrich products
     */
    public function enrichBatch(int $limit = 20, bool $force = false): array
    {
        $query = Product::query();
        if (!$force) {
            $query->where(function ($q) {
                $q->where('scraper_status', 'pending')
                  ->orWhereNull('main_image')
                  ->orWhere('main_image', 'images/placeholder-product.svg');
            });
        }

        $products = $query->take($limit)->get();

        $processed = 0;
        $found = 0;
        $notFound = 0;
        $results = [];

        foreach ($products as $product) {
            $processed++;
            $res = $this->enrichProduct($product, $force);
            $results[] = $res;
            if ($res['status'] === 'found' || $res['status'] === 'already_enriched') {
                $found++;
            } else {
                $notFound++;
            }
        }

        SyncLog::log(
            'cross_enrichment_batch',
            $notFound > 0 ? 'warning' : 'success',
            "Cruce y enriquecimiento por lotes completado. Procesados: $processed, Enriquecidos: $found, Sin resultados: $notFound.",
            $results,
            $processed,
            $found,
            $notFound
        );

        return [
            'processed' => $processed,
            'found' => $found,
            'not_found' => $notFound,
            'results' => $results,
        ];
    }

    /**
     * Full Pipeline: Pull from Ingram Micro API and immediately cross-enrich each product
     */
    public function syncAndEnrichFromIngram(int $pageSize = 25, int $pageNumber = 1, bool $enrich = true): array
    {
        // 1. Fetch from Ingram
        $catalogRes = $this->ingram->searchCatalog([
            'pageSize' => $pageSize,
            'pageNumber' => $pageNumber
        ]);

        if (!$catalogRes['success']) {
            return [
                'success' => false,
                'message' => 'Error al consultar catálogo de Ingram Micro: ' . ($catalogRes['message'] ?? 'Error desconocido'),
                'details' => $catalogRes
            ];
        }

        $items = $catalogRes['data']['catalog'] ?? [];
        if (empty($items)) {
            return [
                'success' => true,
                'message' => 'No se encontraron artículos en la página consultada de Ingram Micro.',
                'imported' => 0,
                'enriched' => 0
            ];
        }

        // 2. Ingest into database
        $syncResult = $this->ingram->syncCatalog($items, true);

        // 3. Immediately cross-enrich each product
        $enrichedCount = 0;
        $enrichDetails = [];

        if ($enrich) {
            foreach ($items as $item) {
                $sku = $item['ingramPartNumber'] ?? $item['vendorPartNumber'] ?? null;
                if (!$sku) continue;

                $product = Product::where('sku', $sku)->first();
                if ($product) {
                    $enrichRes = $this->enrichProduct($product, false);
                    $enrichDetails[] = $enrichRes;
                    if ($enrichRes['status'] === 'found') {
                        $enrichedCount++;
                    }
                }
            }
        }

        SyncLog::log(
            'ingram_cross_pipeline',
            'success',
            "Pipeline completo ejecutado. Importados desde Ingram: {$syncResult['success']}, Enriquecidos y cruzados con Scraper: {$enrichedCount}.",
            $enrichDetails,
            count($items),
            $enrichedCount,
            count($items) - $enrichedCount
        );

        return [
            'success' => true,
            'message' => "Sincronización y cruce completados. {$syncResult['success']} productos importados desde Ingram Micro, {$enrichedCount} enriquecidos con imágenes de alta calidad, fichas técnicas y precios de referencia.",
            'imported' => $syncResult['success'],
            'enriched' => $enrichedCount,
            'items' => $enrichDetails
        ];
    }

    /**
     * Batch / Multi-page Ingram Micro Importer
     * Capable of ingesting all 13,465 products from Ingram Micro API
     */
    public function syncAllFromIngram(int $maxPages = 0, bool $enrich = false, ?callable $progressCallback = null): array
    {
        $pageSize = 25; // Ingram Micro Chile catalog caps at 25 per page
        $page = 1;
        $totalImported = 0;
        $totalEnriched = 0;
        $recordsFound = 0;
        $pagesProcessed = 0;

        do {
            $response = $this->ingram->searchCatalog([
                'pageSize' => $pageSize,
                'pageNumber' => $page
            ]);

            if (!$response['success']) {
                Log::error("Failed fetching page {$page} from Ingram Micro: " . ($response['message'] ?? 'Error desconocido'));
                break;
            }

            $data = $response['data'] ?? [];
            $recordsFound = (int) ($data['recordsFound'] ?? $recordsFound);
            $items = $data['catalog'] ?? [];

            if (empty($items)) {
                break;
            }

            // Sync items to database
            $syncResult = $this->ingram->syncCatalog($items, true);
            $importedInPage = $syncResult['success'] ?? 0;
            $totalImported += $importedInPage;
            $pagesProcessed++;

            // Enrich if requested
            if ($enrich) {
                foreach ($items as $item) {
                    $sku = $item['ingramPartNumber'] ?? $item['vendorPartNumber'] ?? null;
                    if ($sku) {
                        $prod = Product::where('sku', $sku)->first();
                        if ($prod) {
                            $res = $this->enrichProduct($prod, false);
                            if ($res['status'] === 'found') {
                                $totalEnriched++;
                            }
                        }
                    }
                }
            }

            $totalPages = $pageSize > 0 ? (int) ceil($recordsFound / $pageSize) : 1;

            if (is_callable($progressCallback)) {
                $progressCallback($page, $totalPages, $recordsFound, $totalImported, $importedInPage);
            }

            $page++;

            if ($maxPages > 0 && $pagesProcessed >= $maxPages) {
                break;
            }

            if (empty($data['nextPage'])) {
                break;
            }

            usleep(250000); // 0.25s pause between API requests
            gc_collect_cycles();
        } while (true);

        SyncLog::log(
            'ingram_bulk_import',
            'success',
            "Importación masiva completada. Páginas: {$pagesProcessed}, Total productos importados: {$totalImported} de {$recordsFound} disponibles en Ingram Micro.",
            null,
            $totalImported,
            $totalImported,
            0
        );

        return [
            'success' => true,
            'records_found' => $recordsFound,
            'pages_processed' => $pagesProcessed,
            'total_imported' => $totalImported,
            'total_enriched' => $totalEnriched,
            'message' => "Importación completada. Se importaron {$totalImported} productos de {$recordsFound} disponibles en el catálogo mayorista de Ingram Micro."
        ];
    }

    /**
     * Generate dynamic technical FAQs for product
     */
    protected function generateFaqsForProduct(Product $product): array
    {
        $faqs = [
            [
                'question' => "¿El producto {$product->name} cuenta con garantía oficial?",
                'answer' => "Sí, todos los productos distribuidos por INEXUS Chile cuentan con garantía oficial directa del fabricante y respaldo legal conforme a la Ley del Consumidor en Chile por 6 meses."
            ],
            [
                'question' => "¿Emiten factura electrónica para empresas con RUT?",
                'answer' => "Sí, en el proceso de compra puedes seleccionar Boleta o Factura Electrónica ingresando el RUT, Razón Social y Giro comercial de tu empresa."
            ],
            [
                'question' => "¿Cuáles son los tiempos de despacho y cobertura?",
                'answer' => "Despachamos a todo Chile. En la Región Metropolitana entregamos en 24 a 48 horas hábiles, y en regiones entre 2 a 4 días hábiles mediante Couriers express autorizados."
            ]
        ];

        // Specific category questions
        if (Str::contains(strtolower($product->name), ['ssd', 'nvme', 'm.2', 'disco'])) {
            $faqs[] = [
                'question' => "¿Es compatible con PlayStation 5 o placas madre PCIe 3.0 / 4.0?",
                'answer' => "Las unidades NVMe PCIe 4.0 cuentan con retrocompatibilidad total con PCIe 3.0 (a velocidades de la interfaz del bus) y cumplen los requerimientos de velocidad recomendados para expansión de almacenamiento en PS5 y PC de alto rendimiento."
            ];
        }

        if (Str::contains(strtolower($product->name), ['pencil', 'ipad', 'apple'])) {
            $faqs[] = [
                'question' => "¿Es 100% original con número de serie verificable?",
                'answer' => "Sí, el producto proviene directamente del canal mayorista oficial con número de serie único registrable ante el fabricante."
            ];
        }

        return $faqs;
    }
}
