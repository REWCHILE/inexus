<?php
/**
 * Plugin Name:       Ingram Micro WooCommerce Connector
 * Plugin URI:        https://www.rew.cl
 * Description:       Sync products and orders with Ingram Micro Reseller API for Chile.
 * Version:           2.0.0
 * Author:            REW
 * Author URI:        https://www.rew.cl
 * Text Domain:       ingram-woo
 * Domain Path:       /languages
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define constants
define( 'INGRAM_WOO_VERSION', '2.0.0' );
define( 'INGRAM_WOO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'INGRAM_WOO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Include required files
// NOTE: the first constant had a non‑ASCII character (Cyrillic М) which caused
// a fatal error when attempting to include the settings file. Use the correctly
// defined constant instead.
require_once INGRAM_WOO_PLUGIN_DIR . 'includes/class-settings.php';
require_once INGRAM_WOO_PLUGIN_DIR . 'includes/class-ingram-api.php';
require_once INGRAM_WOO_PLUGIN_DIR . 'includes/class-product-sync.php';

// Initialize plugin
function ingram_woo_init() {
    // load textdomain
    load_plugin_textdomain( 'ingram-woo', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'ingram_woo_init' );

// Instantiate settings class
function ingram_woo_setup() {
    if ( class_exists( 'Ingram_Woo_Settings' ) ) {
        Ingram_Woo_Settings::get_instance();
    }
    if ( class_exists( 'Ingram_Woo_Product_Sync' ) ) {
        Ingram_Woo_Product_Sync::get_instance();
    }
}
add_action( 'plugins_loaded', 'ingram_woo_setup', 20 );

register_activation_hook( __FILE__, 'ingram_woo_activate' );
function ingram_woo_activate() {
    if ( class_exists( 'Ingram_Woo_Product_Sync' ) ) {
        Ingram_Woo_Product_Sync::get_instance()->check_cron_schedule();
    }
}
