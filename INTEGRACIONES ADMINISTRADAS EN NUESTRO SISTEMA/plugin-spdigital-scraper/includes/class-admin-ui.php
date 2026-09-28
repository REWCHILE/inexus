<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ML_Admin_UI {
    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    public function add_menu_page() {
        add_menu_page(
            'Google Shopping Scraper',
            'GS Scraper',
            'manage_options',
            'gs-scraper',
            array( $this, 'render_page' ),
            'dashicons-google',
            58
        );
    }

    public function enqueue_assets( $hook ) {
        if ( 'toplevel_page_gs-scraper' !== $hook ) {
            return;
        }

        wp_enqueue_style( 'gs-scraper-admin', GS_SCRAPER_URL . 'assets/admin.css', array(), GS_SCRAPER_VERSION );
        wp_enqueue_script( 'gs-scraper-admin', GS_SCRAPER_URL . 'assets/admin.js', array( 'jquery' ), GS_SCRAPER_VERSION, true );

        wp_localize_script( 'gs-scraper-admin', 'gsScraper', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'gs_scraper_nonce' ),
        ) );
    }

    public function render_page() {
        $remaining = GS_Scraper::get_instance()->get_remaining_count();
        ?>
        <div class="wrap sp-scraper-wrap">
            <div class="sp-scraper-header">
                <h1>Google Shopping Product Scraper</h1>
                <p>Import missing product images and descriptions by searching SKUs on Google Shopping.</p>
            </div>

            <div class="sp-scraper-stats card">
                <div class="stat-item">
                    <span class="label">Products to process (no image):</span>
                    <span class="value" id="remaining-count"><?php echo esc_html( $remaining ); ?></span>
                </div>
                <div class="stat-item">
                    <span class="label">Status:</span>
                    <span class="value" id="sync-status">Idle</span>
                </div>
            </div>

            <div class="sp-scraper-controls card">
                <button id="start-scraper" class="button button-primary button-hero" <?php echo $remaining == 0 ? 'disabled' : ''; ?>>
                    Start Scraping
                </button>
                <button id="stop-scraper" class="button button-secondary button-hero" disabled>
                    Stop
                </button>
            </div>

            <div class="sp-scraper-progress card" style="display:none;">
                <div class="progress-bar-container">
                    <div class="progress-bar-fill" style="width: 0%;"></div>
                </div>
                <div class="progress-stats">
                    Processed: <span id="processed-count">0</span> | 
                    Successful: <span id="success-count">0</span> | 
                    Failed: <span id="failed-count">0</span>
                </div>
            </div>

            <div class="sp-scraper-legend card">
                <h3 style="margin-top:0; font-size:1.1em; color:#1e293b; border-bottom:1px solid #f1f5f9; padding-bottom:10px; display:flex; align-items:center; gap:8px;">
                    <span class="dashicons dashicons-info" style="color:#0072ff;"></span> Infografía y Guía de Estados
                </h3>
                <div class="legend-grid">
                    <div class="legend-item">
                        <span class="legend-badge legend-green">✓ Ficha técnica / Descripción</span>
                        <p>Extrajo e insertó la tabla de especificaciones completa desde Mercado Libre o SP Digital.</p>
                    </div>
                    <div class="legend-item">
                        <span class="legend-badge legend-blue">✓ Imagen cargada</span>
                        <p>Descargó y asignó la foto destacada del producto a la biblioteca de medios.</p>
                    </div>
                    <div class="legend-item">
                        <span class="legend-badge legend-gray">✓ Revisado (sin cambios)</span>
                        <p>El producto ya contaba con su foto y descripción (preservó la información guardada).</p>
                    </div>
                    <div class="legend-item">
                        <span class="legend-badge legend-red">X Error</span>
                        <p>No se encontraron datos en las fuentes para ese SKU o término.</p>
                    </div>
                </div>
            </div>

            <div class="sp-scraper-log-container card">
                <h2>Activity Log</h2>
                <div id="scraper-log" class="log-window">
                    <p class="log-entry">Ready to start.</p>
                </div>
            </div>
        </div>
        <?php
    }
}
