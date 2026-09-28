<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Setting;
use App\Models\SyncLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ScraperService
{
    protected array $userAgents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36',
    ];

    /**
     * Fetch URL content using realistic browser camouflage
     */
    protected function fetchUrl(string $url, string $referer = ''): ?string
    {
        $headers = [
            'User-Agent' => $this->userAgents[array_rand($this->userAgents)],
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
            'Accept-Language' => 'es-CL,es;q=0.9,en;q=0.8',
            'Cache-Control' => 'no-cache',
            'Pragma' => 'no-cache',
            'Sec-Ch-Ua' => '"Chromium";v="123", "Google Chrome";v="123"',
            'Sec-Ch-Ua-Mobile' => '?0',
            'Sec-Ch-Ua-Platform' => '"Windows"',
        ];

        if ($referer) {
            $headers['Referer'] = $referer;
        }

        try {
            // Human-like slight jitter
            usleep(rand(200000, 600000));

            $response = Http::withHeaders($headers)
                ->timeout(20)
                ->get($url);

            if ($response->successful()) {
                return $response->body();
            }
        } catch (\Throwable $e) {
            Log::warning("Scraper fetch error on {$url}: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Scrape SPDigital for SKU
     */
    public function scrapeSpDigital(string $sku, string $name = ''): ?array
    {
        $searchTerm = !empty($sku) ? $sku : $name;
        $searchUrl = "https://www.spdigital.cl/search/?q=" . urlencode($searchTerm);
        $html = $this->fetchUrl($searchUrl);

        if (!$html) {
            return null;
        }

        $productUrl = null;

        // Try Fractal-ProductCard or any /p/ link
        if (preg_match('/class="[^"]*Fractal-ProductCard--image[^"]*"[^>]*href="([^">]+)"/is', $html, $matches)) {
            $productUrl = $matches[1];
        } elseif (preg_match('/href="(\/p\/[^">]+)"/is', $html, $matches)) {
            $productUrl = $matches[1];
        }

        if (!$productUrl) {
            return null;
        }

        if (!Str::startsWith($productUrl, 'http')) {
            $productUrl = "https://www.spdigital.cl" . $productUrl;
        }

        $detailHtml = $this->fetchUrl($productUrl, $searchUrl);
        if (!$detailHtml) {
            return null;
        }

        $imageUrl = null;
        // Find high-res image
        if (preg_match('/Fractal-ProductImage[^>]*src="([^">]+)"/is', $detailHtml, $imgMatches)) {
            $imageUrl = $imgMatches[1];
        } elseif (preg_match('/property="og:image"\s+content="([^"]+)"/is', $detailHtml, $imgMatches)) {
            $imageUrl = $imgMatches[1];
        }

        // Clean image URL
        if ($imageUrl && Str::startsWith($imageUrl, '//')) {
            $imageUrl = 'https:' . $imageUrl;
        }

        // Description
        $description = null;
        if (preg_match('/class="[^"]*description[^"]*"[^>]*>(.*?)<\/div>/is', $detailHtml, $descMatches)) {
            $description = trim(strip_tags($descMatches[1], '<p><br><ul><li><strong><b>'));
        }

        return [
            'source' => 'spdigital',
            'product_url' => $productUrl,
            'image_url' => $imageUrl,
            'description' => $description,
            'title' => null,
        ];
    }

    /**
     * Scrape MercadoLibre Chile
     */
    public function scrapeMercadoLibre(string $sku, string $name = ''): ?array
    {
        $searchTerm = !empty($sku) ? $sku : $name;
        $searchUrl = "https://listado.mercadolibre.cl/" . urlencode(str_replace(' ', '-', $searchTerm));
        $html = $this->fetchUrl($searchUrl);

        if (!$html) {
            return null;
        }

        // Match first organic product card link
        $productUrl = null;
        if (preg_match('/href="(https:\/\/articulo\.mercadolibre\.cl\/MLC-[^"]+)"/is', $html, $m)) {
            $productUrl = $m[1];
        }

        if (!$productUrl) {
            return null;
        }

        $detailHtml = $this->fetchUrl($productUrl, $searchUrl);
        if (!$detailHtml) {
            return null;
        }

        $imageUrl = null;
        if (preg_match('/property="og:image"\s+content="([^"]+)"/is', $detailHtml, $imgM)) {
            $imageUrl = $imgM[1];
        } elseif (preg_match('/class="ui-pdp-image[^"]*"\s+src="([^"]+)"/is', $detailHtml, $imgM)) {
            $imageUrl = $imgM[1];
        }

        $description = null;
        if (preg_match('/class="ui-pdp-description__content"[^>]*>(.*?)<\/div>/is', $detailHtml, $descM)) {
            $description = trim(strip_tags($descM[1], '<p><br>'));
        }

        return [
            'source' => 'mercadolibre',
            'product_url' => $productUrl,
            'image_url' => $imageUrl,
            'description' => $description,
            'title' => null,
        ];
    }

    /**
     * Comprehensive Scraper: iterates enabled sources until finding clean, coherent image & description
     */
    public function scrapeProduct(Product $product): array
    {
        $sku = trim((string) ($product->vendor_part_number ?: $product->sku));
        $name = trim((string) $product->name);

        $result = null;

        // Try SPDigital first
        $result = $this->scrapeSpDigital($sku, $name);

        // Fallback to MercadoLibre if no image found
        if (empty($result['image_url'])) {
            $mlResult = $this->scrapeMercadoLibre($sku, $name);
            if (!empty($mlResult['image_url'])) {
                $result = $mlResult;
            }
        }

        // Evaluate if coherent image was found
        if (!empty($result['image_url'])) {
            $product->main_image = $result['image_url'];
            $product->scraper_source = $result['source'];
            $product->scraper_status = 'found';
            $product->scraper_last_run = now();

            if (!empty($result['description']) && (empty($product->description) || strlen($product->description) < 50)) {
                $product->description = $result['description'];
            }

            $product->save();

            return [
                'success' => true,
                'status' => 'found',
                'source' => $result['source'],
                'image_url' => $result['image_url'],
                'description' => $result['description'] ?? null,
                'message' => "Datos e imagen encontrados exitosamente en {$result['source']}.",
            ];
        }

        // Mark as not_found so system shows clean placeholder and indicates it in admin
        $product->scraper_status = 'not_found';
        $product->scraper_last_run = now();
        if (empty($product->main_image)) {
            $product->main_image = 'images/placeholder-product.svg';
        }
        $product->save();

        return [
            'success' => false,
            'status' => 'not_found',
            'source' => null,
            'image_url' => asset('images/placeholder-product.svg'),
            'message' => 'No se encontraron imágenes coherentes en los canales de scraping. Se asignó imagen de referencia INEXUS.',
        ];
    }

    /**
     * Batch scrape products with pending or missing status
     */
    public function batchScrape(int $limit = 10): array
    {
        $products = Product::where('scraper_status', 'pending')
            ->orWhereNull('main_image')
            ->orWhere('main_image', 'images/placeholder-product.svg')
            ->take($limit)
            ->get();

        $processed = 0;
        $found = 0;
        $notFound = 0;

        foreach ($products as $product) {
            $processed++;
            $res = $this->scrapeProduct($product);
            if ($res['status'] === 'found') {
                $found++;
            } else {
                $notFound++;
            }
        }

        SyncLog::log(
            'scraper_batch',
            $notFound > 0 ? 'warning' : 'success',
            "Scraping por lotes completado. Procesados: $processed. Imágenes encontradas: $found. Sin resultados: $notFound.",
            null,
            $processed,
            $found,
            $notFound
        );

        return [
            'processed' => $processed,
            'found' => $found,
            'not_found' => $notFound,
        ];
    }
}
