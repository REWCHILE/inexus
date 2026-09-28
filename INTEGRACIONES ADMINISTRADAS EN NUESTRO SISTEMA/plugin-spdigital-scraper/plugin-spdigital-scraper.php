<?php
/**
 * Plugin Name:       Google Shopping Product Scraper
 * Plugin URI:        https://www.rew.cl
 * Description:       Enrich WooCommerce products with images and descriptions from Google Shopping.
 * Version:           2.0.0
 * Author:            REW
 * Author URI:        https://www.rew.cl
 * Text Domain:       gs-scraper
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define constants
define( 'GS_SCRAPER_VERSION', '2.0.0' );
define( 'GS_SCRAPER_PATH', plugin_dir_path( __FILE__ ) );
define( 'GS_SCRAPER_URL', plugin_dir_url( __FILE__ ) );

// Include classes
require_once GS_SCRAPER_PATH . 'includes/class-gs-scraper.php';
require_once GS_SCRAPER_PATH . 'includes/class-ml-scraper.php';
require_once GS_SCRAPER_PATH . 'includes/class-sp-scraper.php';
require_once GS_SCRAPER_PATH . 'includes/class-admin-ui.php';

// Initialize
function gs_scraper_init() {
    GS_Scraper::get_instance();
    SP_Scraper::get_instance();
    ML_Admin_UI::get_instance();
}
add_action( 'plugins_loaded', 'gs_scraper_init' );
