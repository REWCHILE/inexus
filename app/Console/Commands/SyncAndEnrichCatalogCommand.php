<?php

namespace App\Console\Commands;

use App\Services\CatalogCrossEnricherService;
use Illuminate\Console\Command;

class SyncAndEnrichCatalogCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inexus:sync-catalog 
                            {--all : Descarga todas las páginas del catálogo completo de Ingram Micro (13.465 productos)}
                            {--max-pages=0 : Límite de páginas a descargar (50 productos por página, 0 = todas)}
                            {--limit=25 : Cantidad de productos a procesar en consulta individual} 
                            {--page=1 : Número de página en el catálogo Ingram} 
                            {--enrich : Cruzar y enriquecer con Scraper en vivo durante la descarga}
                            {--only-enrich : Solo ejecuta el cruce y enriquecimiento sobre productos existentes en base de datos} 
                            {--force : Fuerza re-enriquecimiento de productos ya procesados}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza el catálogo desde Ingram Micro (individual o los 13.465 productos) y cruza automáticamente con el scraper';

    /**
     * Execute the console command.
     */
    public function handle(CatalogCrossEnricherService $enricher): int
    {
        $all = (bool) $this->option('all');
        $maxPages = (int) $this->option('max-pages');
        $limit = (int) $this->option('limit');
        $page = (int) $this->option('page');
        $enrich = (bool) $this->option('enrich');
        $onlyEnrich = (bool) $this->option('only-enrich');
        $force = (bool) $this->option('force');

        $this->info("========================================================================");
        $this->info("         INEXUS CHILE - Pipeline de Sincronización y Cruce Web          ");
        $this->info("========================================================================");

        // MODE 1: Only enrich existing products
        if ($onlyEnrich) {
            $this->info("Ejecutando cruce y enriquecimiento sobre catálogo existente en base de datos...");
            $this->info("Límite: {$limit} productos | Forzar: " . ($force ? 'SÍ' : 'NO'));

            $result = $enricher->enrichBatch($limit, $force);

            $this->newLine();
            $this->info("✓ Cruce por lotes completado con éxito:");
            $this->table(
                ['Métrica', 'Total'],
                [
                    ['Procesados', $result['processed']],
                    ['Enriquecidos (Fotos HD, Fichas, Specs)', $result['found']],
                    ['Sin Resultados (Con Placeholder)', $result['not_found']],
                ]
            );

            return Command::SUCCESS;
        }

        // MODE 2: Bulk import all (or up to max-pages) from Ingram Micro
        if ($all || $maxPages > 0) {
            $this->info("Iniciando descarga masiva desde catálogo mayorista de Ingram Micro...");
            $this->info("Modo: " . ($all ? "Catálogo Completo (13.465 productos)" : "Hasta {$maxPages} páginas (~" . ($maxPages * 50) . " productos)"));
            $this->info("Cruce inmediato con Scraper: " . ($enrich ? "ACTIVADO" : "DESACTIVADO (Recomendado para velocidad; enriquecer después)"));
            $this->newLine();

            $result = $enricher->syncAllFromIngram(
                $maxPages, 
                $enrich, 
                function ($page, $totalPages, $recordsFound, $totalImported, $inPage) {
                    $pct = $recordsFound > 0 ? round(($totalImported / $recordsFound) * 100, 1) : 0;
                    $this->line(" [Página {$page}/{$totalPages}] +{$inPage} productos | Total acumulado: {$totalImported} de {$recordsFound} ({$pct}%)");
                }
            );

            $this->newLine();
            $this->info("✓ " . $result['message']);
            $this->table(
                ['Métrica', 'Total'],
                [
                    ['Registros Totales en Ingram Micro', $result['records_found']],
                    ['Páginas Procesadas', $result['pages_processed']],
                    ['Productos Importados a Base de Datos', $result['total_imported']],
                    ['Enriquecidos con Scraper', $result['total_enriched']],
                ]
            );

            return Command::SUCCESS;
        }

        // MODE 3: Single page import & enrich
        $this->info("Extrayendo productos desde API de Ingram Micro (Página {$page}, Límite {$limit})...");
        $result = $enricher->syncAndEnrichFromIngram($limit, $page, true);

        if (!$result['success']) {
            $this->error("✕ Error: " . $result['message']);
            return Command::FAILURE;
        }

        $this->newLine();
        $this->info("✓ " . $result['message']);
        $this->table(
            ['Métrica', 'Total'],
            [
                ['Importados desde Ingram Micro', $result['imported']],
                ['Enriquecidos y Cruzados con Scraper', $result['enriched']],
            ]
        );

        return Command::SUCCESS;
    }
}
