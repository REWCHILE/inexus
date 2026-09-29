<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportDatabaseDumpCommand extends Command
{
    protected $signature = 'inexus:import-dump {--file=database/inexus_dump.sql : Ruta al archivo .sql}';
    protected $description = 'Importa el volcado completo de base de datos con los 6.812 productos de Ingram Micro';

    public function handle(): int
    {
        $file = base_path($this->option('file'));

        if (!File::exists($file)) {
            $this->error("El archivo no existe: {$file}");
            return Command::FAILURE;
        }

        $this->info("Iniciando importación de la base de datos INEXUS desde {$file}...");
        $this->info("Esto restaurará los 6.812 productos, categorías y configuraciones de Ingram Micro.");

        try {
            DB::unprepared(File::get($file));
            $this->info("✓ Base de datos importada exitosamente.");
            $this->info("Total de productos en base de datos: " . \App\Models\Product::count());
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Error al importar el dump: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
