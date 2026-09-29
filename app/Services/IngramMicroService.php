<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\SyncLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class IngramMicroService
{
    protected string $clientId;
    protected string $clientSecret;
    protected string $customerNumber;
    protected string $countryCode;
    protected string $environment;
    protected string $baseUrl;
    protected string $tokenUrl = 'https://api.ingrammicro.com/oauth/oauth30/token';

    public function __construct()
    {
        $this->clientId = trim((string) Setting::get('ingram_client_id', config('services.ingram.client_id', '')));
        $this->clientSecret = trim((string) Setting::get('ingram_client_secret', config('services.ingram.client_secret', '')));
        $this->customerNumber = trim((string) Setting::get('ingram_customer_number', config('services.ingram.customer_number', '')));
        $this->countryCode = trim((string) Setting::get('ingram_country_code', 'CL')) ?: 'CL';
        $this->environment = trim((string) Setting::get('ingram_environment', 'sandbox')) ?: 'sandbox';

        if ($this->environment === 'production') {
            $this->baseUrl = 'https://api.ingrammicro.com/resellers/v6';
        } else {
            $this->baseUrl = 'https://api.ingrammicro.com/sandbox/resellers/v6';
        }
    }

    /**
     * Authenticate and get Bearer token
     */
    public function getToken(): ?string
    {
        if (empty($this->clientId) || empty($this->clientSecret)) {
            return null;
        }

        $cachedToken = cache()->get('ingram_access_token');
        if ($cachedToken) {
            return $cachedToken;
        }

        try {
            $response = Http::asForm()->timeout(25)->post($this->tokenUrl, [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['access_token'])) {
                    $token = $data['access_token'];
                    $expiresIn = isset($data['expires_in']) ? (int) $data['expires_in'] - 60 : 3500;
                    cache()->put('ingram_access_token', $token, max(60, $expiresIn));
                    return $token;
                }
            }

            Log::error('Ingram Auth failed: ' . $response->body());
        } catch (\Throwable $e) {
            Log::error('Ingram Auth exception: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Send authenticated API request
     */
    public function request(string $method, string $endpoint, array $data = [], array $query = [])
    {
        $token = $this->getToken();
        if (!$token) {
            return [
                'success' => false,
                'message' => 'No se pudo obtener el token de autenticación de Ingram Micro. Verifique credenciales.',
                'code' => 401
            ];
        }

        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'IM-CustomerNumber' => $this->customerNumber,
            'IM-CountryCode' => $this->countryCode,
            'IM-CorrelationID' => substr(str_replace('-', '', (string) Str::uuid()), 0, 32),
            'Accept' => 'application/json',
        ];

        try {
            $http = Http::withHeaders($headers)->timeout(30);

            if (strtoupper($method) === 'GET') {
                $response = $http->get($url, array_merge($data, $query));
            } else {
                $response = $http->withQueryParameters($query)->post($url, $data);
            }

            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'data' => $response->json(),
                'raw' => $response->body(),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'status' => 500,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Test connection
     */
    public function testConnection(): array
    {
        if (empty($this->clientId) || empty($this->clientSecret)) {
            return [
                'success' => false,
                'message' => 'Faltan credenciales: Client ID o Client Secret no configurados.'
            ];
        }

        $token = $this->getToken();
        if (!$token) {
            return [
                'success' => false,
                'message' => 'Falló la autenticación con Ingram Micro OAuth 2.0. Verifique Client ID y Secret.'
            ];
        }

        // Test catalog ping
        $result = $this->request('GET', 'catalog', ['pageSize' => 1, 'pageNumber' => 1]);
        if ($result['success']) {
            return [
                'success' => true,
                'message' => 'Conexión exitosa con Ingram Micro API (' . strtoupper($this->environment) . ').',
                'sample' => $result['data'] ?? null
            ];
        }

        return [
            'success' => false,
            'message' => 'Autenticación correcta pero error al consultar catálogo: ' . ($result['message'] ?? 'Código ' . ($result['status'] ?? 'desconocido')),
            'details' => $result
        ];
    }

    /**
     * Search products in Ingram catalog
     */
    public function searchCatalog(array $params = []): array
    {
        $defaultParams = [
            'pageNumber' => 1,
            'pageSize' => 25,
        ];
        return $this->request('GET', 'catalog', array_merge($defaultParams, $params));
    }

    /**
     * Get price & availability
     */
    public function getPriceAndAvailability(array $skus): array
    {
        $products = [];
        foreach ($skus as $sku) {
            $products[] = ['ingramPartNumber' => $sku];
        }

        return $this->request('POST', 'catalog/priceandavailability', [
            'products' => $products
        ], [
            'includeAvailability' => 'true',
            'includePricing' => 'true'
        ]);
    }

    /**
     * Get details for single IPN
     */
    public function getProductDetails(string $sku): array
    {
        return $this->request('GET', "catalog/details/{$sku}");
    }

    /**
     * Calculate retail price using margin hierarchy:
     * 1. Product specific margin override
     * 2. Category margin
     * 3. Global margin
     */
    public function calculatePrice(float $costUsd, ?float $productMargin = null, ?Category $category = null): array
    {
        $rate = (float) Setting::get('ingram_usd_exchange_rate', 965.0);
        $globalMargin = (float) Setting::get('ingram_global_margin', 18.0);

        $appliedMargin = $globalMargin;
        $marginSource = 'global';

        if (!is_null($category) && !is_null($category->margin_percentage) && $category->margin_percentage > 0) {
            $appliedMargin = (float) $category->margin_percentage;
            $marginSource = 'category (' . $category->name . ')';
        }

        if (!is_null($productMargin) && $productMargin > 0) {
            $appliedMargin = (float) $productMargin;
            $marginSource = 'product_override';
        }

        $costClp = $costUsd * $rate;
        $retailClp = $costClp * (1 + ($appliedMargin / 100));

        // Chilean pricing rounding (round to nearest 100 CLP)
        $finalRetailClp = round($retailClp / 100) * 100;

        return [
            'cost_usd' => $costUsd,
            'exchange_rate' => $rate,
            'cost_clp' => $costClp,
            'applied_margin' => $appliedMargin,
            'margin_source' => $marginSource,
            'retail_clp' => $finalRetailClp,
        ];
    }

    /**
     * Sync catalog items to database
     */
    public function syncCatalog(array $items, bool $preserveScraped = true): array
    {
        $processed = 0;
        $success = 0;
        $failed = 0;

        // Batch query Price & Availability if not already present
        $skusToQuery = [];
        foreach ($items as $it) {
            if (empty($it['pricing']['customerPrice']) && empty($it['pricing']['netPrice']) && !empty($it['ingramPartNumber'])) {
                $skusToQuery[] = $it['ingramPartNumber'];
            }
        }
        if (!empty($skusToQuery)) {
            $pnaRes = $this->getPriceAndAvailability($skusToQuery);
            if ($pnaRes['success'] && !empty($pnaRes['data'])) {
                $pnaMap = [];
                foreach ($pnaRes['data'] as $p) {
                    if (!empty($p['ingramPartNumber'])) {
                        $pnaMap[$p['ingramPartNumber']] = $p;
                    }
                }
                foreach ($items as &$it) {
                    $ipn = $it['ingramPartNumber'] ?? null;
                    if ($ipn && isset($pnaMap[$ipn])) {
                        $it['pricing'] = $pnaMap[$ipn]['pricing'] ?? [];
                        $it['availability'] = $pnaMap[$ipn]['availability'] ?? [];
                    }
                }
                unset($it);
            }
        }

        foreach ($items as $item) {
            $processed++;
            try {
                $sku = $item['ingramPartNumber'] ?? $item['vendorPartNumber'] ?? null;
                if (!$sku) {
                    $failed++;
                    continue;
                }

                $name = $item['description'] ?? $item['vendorProductDescription'] ?? 'Producto ' . $sku;
                $brand = $item['vendorName'] ?? null;
                $costUsd = 0.0;

                if (!empty($item['pricing']['customerPrice'])) {
                    $costUsd = (float) $item['pricing']['customerPrice'];
                } elseif (!empty($item['pricing']['netPrice'])) {
                    $costUsd = (float) $item['pricing']['netPrice'];
                } elseif (!empty($item['pricing']['retailPrice'])) {
                    $costUsd = (float) $item['pricing']['retailPrice'];
                }

                // Match or create Category
                $categoryName = $item['category'] ?? $item['subCategory'] ?? 'Tecnología General';
                $category = Category::firstOrCreate(
                    ['slug' => Str::slug($categoryName)],
                    ['name' => $categoryName, 'icon' => 'images/categories/servidores.svg', 'is_active' => true]
                );

                $existingProduct = Product::where('sku', $sku)->first();

                // Compute price with margin hierarchy
                $productMargin = $existingProduct?->margin_percentage;
                $pricing = $this->calculatePrice($costUsd, $productMargin, $category);

                // Stock
                $stock = 0;
                if (isset($item['availability']['totalAvailability'])) {
                    $stock = (int) $item['availability']['totalAvailability'];
                } elseif (isset($item['availability']['quantityAvailable'])) {
                    $stock = (int) $item['availability']['quantityAvailable'];
                }

                $shortDesc = $item['extraDescription'] ?? $name;

                $productData = [
                    'category_id' => $category->id,
                    'ingram_part_number' => $item['ingramPartNumber'] ?? $sku,
                    'vendor_part_number' => $item['vendorPartNumber'] ?? null,
                    'name' => $existingProduct && $preserveScraped && !empty($existingProduct->name) ? $existingProduct->name : $name,
                    'short_description' => $existingProduct && !empty($existingProduct->short_description) ? $existingProduct->short_description : $shortDesc,
                    'slug' => $existingProduct && !empty($existingProduct->slug) ? $existingProduct->slug : Str::slug(mb_substr($name, 0, 80) . '-' . $sku),
                    'brand' => $brand,
                    'cost_price_usd' => $costUsd,
                    'cost_price_clp' => $pricing['cost_clp'],
                    'calculated_price_clp' => $pricing['retail_clp'],
                    'regular_price' => $pricing['retail_clp'],
                    'stock' => $stock,
                    'stock_status' => $stock > 0 ? 'in_stock' : 'out_of_stock',
                    'main_image' => $existingProduct && !empty($existingProduct->main_image) ? $existingProduct->main_image : 'images/placeholder-product.svg',
                    'is_active' => true,
                ];

                // If not preserving scraped or new product, allow updating main image if Ingram provides it
                if (!$existingProduct || !$preserveScraped || empty($existingProduct->main_image)) {
                    $image = null;
                    if (!empty($item['mediaLinks']) && is_array($item['mediaLinks'])) {
                        foreach ($item['mediaLinks'] as $media) {
                            if (($media['mediaType'] ?? '') === 'Image' && !empty($media['url'])) {
                                $image = $media['url'];
                                break;
                            }
                        }
                    }
                    if ($image) {
                        $productData['main_image'] = $image;
                        $productData['scraper_status'] = 'found';
                    }
                }

                Product::updateOrCreate(['sku' => $sku], $productData);
                $success++;
            } catch (\Throwable $e) {
                $failed++;
                Log::error("Ingram Sync error on item: " . $e->getMessage());
            }
        }

        SyncLog::log(
            'ingram_catalog',
            $failed > 0 ? 'warning' : 'success',
            "Sincronización de catálogo Ingram Micro completada. Procesados: $processed, Éxitos: $success, Errores: $failed.",
            null,
            $processed,
            $success,
            $failed
        );

        return [
            'processed' => $processed,
            'success' => $success,
            'failed' => $failed,
        ];
    }
}
