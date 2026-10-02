<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\SyncLog;
use App\Services\CatalogCrossEnricherService;
use App\Services\ScraperService;
use Illuminate\Console\Command;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BulkEnrichImagesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inexus:scrape-images 
                            {--limit=0 : Cantidad máxima de productos a procesar (0 = todos los pendientes)}
                            {--chunk=20 : Tamaño del lote concurrente (por defecto 20)}
                            {--force : Re-escanear productos que ya tienen imagen}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Busca y actualiza fotos HD, galerías y fichas técnicas desde Open Icecat y SoloTodo para todo el catálogo';

    public function handle(CatalogCrossEnricherService $enricher, ScraperService $scraper): int
    {
        $limit = (int) $this->option('limit');
        $chunkSize = max(5, min(50, (int) $this->option('chunk')));
        $force = (bool) $this->option('force');

        $this->info("==========================================================================");
        $this->info("      INEXUS CHILE - Extractor Masivo de Imágenes y Fichas Web            ");
        $this->info("==========================================================================");

        $query = Product::query();
        if (!$force) {
            $query->where(function ($q) {
                $q->whereNull('main_image')
                  ->orWhere('main_image', '')
                  ->orWhere('main_image', 'images/placeholder-product.svg')
                  ->orWhere('scraper_status', 'pending');
            });
        }

        $totalAvailable = $query->count();
        if ($totalAvailable === 0) {
            $this->info("✓ Todos los productos del catálogo ya cuentan con imágenes asignadas.");
            return Command::SUCCESS;
        }

        $toProcess = ($limit > 0 && $limit < $totalAvailable) ? $limit : $totalAvailable;

        $this->info("Total de productos a procesar: {$toProcess} (Disponibles pendientes: {$totalAvailable})");
        $this->info("Concurrencia: Bloques de {$chunkSize} solicitudes simultáneas con Http::pool");
        $this->newLine();

        $bar = $this->output->createProgressBar($toProcess);
        $bar->setFormat(" %current%/%max% [%bar%] %percent:3s%% -- %message%");
        $bar->setMessage("Iniciando escaneo...");
        $bar->start();

        $processed = 0;
        $foundCount = 0;
        $notFoundCount = 0;
        $sourcesStats = ['icecat' => 0, 'solotodo' => 0, 'winpy' => 0, 'spdigital' => 0, 'mercadolibre' => 0];

        $page = 1;
        $remaining = $toProcess;

        while ($remaining > 0) {
            $currentBatchSize = min($chunkSize, $remaining);
            
            // Re-fetch batch (when not forcing, already enriched products will not match the query)
            $products = (clone $query)->take($currentBatchSize)->get();

            if ($products->isEmpty()) {
                break;
            }

            // --- STEP 1: Concurrent Icecat Pool ---
            $icecatResponses = Http::pool(function (Pool $pool) use ($products) {
                $reqs = [];
                foreach ($products as $p) {
                    $brand = strtolower(trim((string) ($p->brand ?: '')));
                    $cleanBrand = preg_replace('/(\s+(inc|corporation|technologies|systems|pty|ltd|co|llc|spa)\b.*)/i', '', $brand);
                    $cleanBrand = trim($cleanBrand);

                    $vpn = trim((string) ($p->vendor_part_number ?: ''));
                    $cleanVpn = explode('#', $vpn)[0];
                    $cleanVpn = explode('/', $cleanVpn)[0];
                    $cleanVpn = trim($cleanVpn);

                    if (!empty($cleanBrand) && !empty($cleanVpn) && strlen($cleanVpn) >= 2) {
                        $url = "https://live.icecat.biz/api/?UserName=openIcecat-live&Language=es&Brand=" . urlencode($cleanBrand) . "&ProductCode=" . urlencode($cleanVpn);
                        $reqs[(string)$p->id] = $pool->as((string)$p->id)
                            ->timeout(6)
                            ->withHeaders(['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'])
                            ->get($url);
                    }
                }
                return $reqs;
            });

            $unresolvedProducts = [];

            foreach ($products as $p) {
                $pid = (string) $p->id;
                $res = $icecatResponses[$pid] ?? null;
                $icecatMatched = false;

                if ($res instanceof \Illuminate\Http\Client\Response && $res->successful()) {
                    $json = $res->json();
                    $data = $json['data'] ?? [];
                    $imgObj = $data['Image'] ?? [];
                    $pic = $imgObj['HighPic'] ?? ($imgObj['Pic500x500'] ?? ($imgObj['LowPic'] ?? null));

                    if ($pic) {
                        $gallery = [];
                        if (!empty($data['Gallery']) && is_array($data['Gallery'])) {
                            foreach ($data['Gallery'] as $g) {
                                $gPic = $g['Pic'] ?? ($g['Pic500x500'] ?? ($g['LowPic'] ?? null));
                                if ($gPic && !in_array($gPic, $gallery) && count($gallery) < 8) {
                                    $gallery[] = $gPic;
                                }
                            }
                        }

                        $specs = [];
                        if (!empty($data['FeaturesGroups']) && is_array($data['FeaturesGroups'])) {
                            foreach ($data['FeaturesGroups'] as $fg) {
                                if (!empty($fg['Features']) && is_array($fg['Features'])) {
                                    foreach ($fg['Features'] as $f) {
                                        $fName = $f['Feature']['Name']['Value'] ?? '';
                                        $fVal = $f['PresentationValue'] ?? ($f['Value'] ?? '');
                                        if ($fName && $fVal && count($specs) < 24) {
                                            $specs[$fName] = (string) $fVal;
                                        }
                                    }
                                }
                            }
                        }

                        $general = $data['GeneralInfo'] ?? [];
                        $title = $general['Title'] ?? null;
                        $shortDesc = $general['SummaryDescription']['ShortSummaryDescription'] ?? null;
                        $longDesc = $general['SummaryDescription']['LongSummaryDescription'] ?? null;
                        $bullets = $data['BulletPoints']['Values'] ?? [];

                        $descHtml = '';
                        if ($longDesc) $descHtml .= "<p>{$longDesc}</p>";
                        if (!empty($bullets)) {
                            $descHtml .= "<ul>";
                            foreach ($bullets as $b) {
                                $descHtml .= "<li>" . e($b) . "</li>";
                            }
                            $descHtml .= "</ul>";
                        }

                        $p->main_image = $pic;
                        if (!empty($gallery)) $p->gallery = $gallery;
                        if (!empty($specs)) $p->specifications = $specs;
                        if (!empty($title) && (Str::length($p->name) < 25 || Str::contains($p->name, ['-AME', 'IM-']))) {
                            $p->name = $title;
                        }
                        if ($shortDesc) $p->short_description = $shortDesc;
                        if ($descHtml && (empty($p->description) || strlen($p->description) < 50)) {
                            $p->description = $descHtml;
                        }

                        $p->scraper_source = 'icecat';
                        $p->scraper_status = 'found';
                        $p->scraper_last_run = now();
                        $p->save();

                        $foundCount++;
                        $sourcesStats['icecat']++;
                        $icecatMatched = true;
                    }
                }

                if (!$icecatMatched) {
                    $unresolvedProducts[] = $p;
                }
            }

            // --- STEP 2: Concurrent SoloTodo Pool for remaining ---
            if (!empty($unresolvedProducts)) {
                $solotodoResponses = Http::pool(function (Pool $pool) use ($unresolvedProducts) {
                    $reqs = [];
                    foreach ($unresolvedProducts as $p) {
                        $vpn = trim((string) ($p->vendor_part_number ?: ''));
                        $cleanVpn = explode('#', $vpn)[0];
                        $cleanVpn = explode('/', $cleanVpn)[0];
                        $cleanVpn = trim($cleanVpn);

                        $searchKey = !empty($cleanVpn) ? $cleanVpn : preg_replace('/^(IM-|KNG-|LEN-|HP-|ASUS-)/', '', (string)$p->sku);
                        if (!empty($searchKey) && strlen($searchKey) >= 3) {
                            $url = "https://api.solotodo.com/products/browse/?search=" . urlencode($searchKey);
                            $reqs[(string)$p->id] = $pool->as((string)$p->id)
                                ->timeout(6)
                                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'])
                                ->get($url);
                        }
                    }
                    return $reqs;
                });

                $stillUnresolved = [];

                foreach ($unresolvedProducts as $p) {
                    $pid = (string) $p->id;
                    $stRes = $solotodoResponses[$pid] ?? null;
                    $stMatched = false;

                    if ($stRes instanceof \Illuminate\Http\Client\Response && $stRes->successful()) {
                        $json = $stRes->json();
                        $entry = $json['results'][0]['product_entries'][0] ?? null;
                        if ($entry && !empty($entry['product']['picture_url'])) {
                            $prodData = $entry['product'];
                            $p->main_image = $prodData['picture_url'];

                            if (!empty($prodData['name']) && (Str::length($p->name) < 25 || Str::contains($p->name, ['-AME', 'IM-']))) {
                                $p->name = $prodData['name'];
                            }
                            if (!empty($prodData['description']) && (empty($p->description) || strlen($p->description) < 50)) {
                                $p->description = Str::markdown($prodData['description']);
                            }

                            $p->scraper_source = 'solotodo';
                            $p->scraper_status = 'found';
                            $p->scraper_last_run = now();
                            $p->save();

                            $foundCount++;
                            $sourcesStats['solotodo']++;
                            $stMatched = true;
                        }
                    }

                    if (!$stMatched) {
                        $stillUnresolved[] = $p;
                    }
                }

                // --- STEP 3: Fallback enricher for tough items ---
                foreach ($stillUnresolved as $p) {
                    $enrichRes = $enricher->enrichProduct($p, $force);
                    if ($enrichRes['status'] === 'found') {
                        $foundCount++;
                        $src = $enrichRes['source'] ?? 'scraped';
                        if (isset($sourcesStats[$src])) {
                            $sourcesStats[$src]++;
                        }
                    } else {
                        $notFoundCount++;
                    }
                }
            }

            $processed += $currentBatchSize;
            $remaining -= $currentBatchSize;

            $pct = $processed > 0 ? round(($foundCount / $processed) * 100, 1) : 0;
            $bar->setMessage("Fotos encontradas: {$foundCount} ({$pct}%) | Icecat: {$sourcesStats['icecat']} | SoloTodo: {$sourcesStats['solotodo']}");
            $bar->advance($currentBatchSize);
        }

        $bar->finish();
        $this->newLine(2);

        // Sync log
        SyncLog::log(
            'mass_image_scrape',
            $notFoundCount > 0 ? 'warning' : 'success',
            "Extracción masiva de imágenes web finalizada. Procesados: {$processed}, Fotos HD encontradas: {$foundCount}, Sin resultados: {$notFoundCount}.",
            $sourcesStats,
            $processed,
            $foundCount,
            $notFoundCount
        );

        $this->info("✓ Proceso de extracción masiva completado exitosamente:");
        $this->table(
            ['Métrica', 'Total'],
            [
                ['Productos Procesados', $processed],
                ['Imágenes Encontradas', $foundCount . " (" . ($processed > 0 ? round(($foundCount / $processed) * 100, 1) : 0) . "%)"],
                [' - Desde Open Icecat (Oficial Fabricante)', $sourcesStats['icecat']],
                [' - Desde SoloTodo (Hardware Chile)', $sourcesStats['solotodo']],
                [' - Desde Otros Canales (Winpy, SPDigital, ML)', $sourcesStats['winpy'] + $sourcesStats['spdigital'] + $sourcesStats['mercadolibre']],
                ['Sin Resultados (Mantiene Placeholder Oficial)', $notFoundCount],
            ]
        );

        return Command::SUCCESS;
    }
}
