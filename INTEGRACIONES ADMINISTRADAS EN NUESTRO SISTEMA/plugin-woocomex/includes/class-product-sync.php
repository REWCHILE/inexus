<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Ingram_Woo_Product_Sync {
    private static $instance = null;
    private $api;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->api = Ingram_Woo_API::get_instance();

        // AJAX handler for modern sync
        add_action( 'wp_ajax_ingram_ajax_start_sync', array( $this, 'ajax_start_sync' ) );
        add_action( 'wp_ajax_ingram_ajax_get_sync_status', array( $this, 'ajax_get_sync_status' ) );
        add_action( 'wp_ajax_ingram_ajax_sync_next_batch', array( $this, 'ajax_sync_next_batch' ) );
        add_action( 'wp_ajax_ingram_ajax_force_reset_sync', array( $this, 'ajax_force_reset_sync' ) );
        add_action( 'wp_ajax_ingram_ajax_get_local_catalog', array( $this, 'ajax_get_local_catalog' ) );
        add_action( 'wp_ajax_ingram_ajax_manage_catalog', array( $this, 'ajax_manage_catalog' ) );
        add_action( 'wp_ajax_ingram_ajax_test_sync_category', array( $this, 'ajax_test_sync_category' ) );

        add_action( 'ingram_woo_manage_category_batch', array( $this, 'process_management_batch' ), 10, 3 );
        add_action( 'ingram_woo_batch_sync_category', array( $this, 'process_category_batch' ), 10, 4 );
        add_action( 'ingram_woo_import_single_product', array( $this, 'process_single_product_import' ), 10, 2 );

        // Cron events and option hooks
        add_action( 'ingram_woo_cron_sync_event', array( $this, 'sync_products' ) );
        add_action( 'update_option_ingram_woo_options', array( $this, 'update_cron_schedule' ), 10, 2 );
        $this->check_cron_schedule();
        $this->handle_external_cron_trigger();
    }

    /**
     * Start sync: just save state. The browser will drive the actual processing.
     */
    public function ajax_start_sync() {
        check_ajax_referer( 'ingram_preview_nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( array( 'message' => 'Unauthorized' ) );

        $selections = isset( $_POST['selections'] ) ? (array) $_POST['selections'] : array();
        if ( empty( $selections ) ) wp_send_json_error( array( 'message' => 'No selections received' ) );

        // Build a queue of work items: each selection starts at page 1
        $queue = array();
        foreach ( $selections as $sel ) {
            $queue[] = array(
                'cat'  => isset($sel['cat']) ? $sel['cat'] : '',
                'sub'  => isset($sel['sub']) ? $sel['sub'] : '',
                'page' => 1
            );
        }

        update_option( 'ingram_woo_active_sync', array(
            'total_categories'     => count($selections),
            'completed_categories' => 0,
            'current_category'     => $queue[0]['cat'] . ($queue[0]['sub'] ? ' > ' . $queue[0]['sub'] : ''),
            'status'               => 'processing',
            'queue'                => $queue,
            'queue_index'          => 0,
            'products_imported'    => 0,
            'last_updated'         => current_time( 'timestamp' )
        ) );

        $this->log_sync_event( 'Sincronización iniciada: ' . count($selections) . ' secciones.' );
        wp_send_json_success( array( 'message' => 'Sincronización iniciada.' ) );
    }

    public function ajax_get_sync_status() {
        check_ajax_referer( 'ingram_preview_nonce' );
        
        $active_sync = get_option( 'ingram_woo_active_sync', array() );
        
        // Count pending background tasks in Action Scheduler
        $pending_count = 0;
        if ( function_exists( 'as_get_scheduled_actions' ) ) {
            $actions = as_get_scheduled_actions( array(
                'group'  => 'ingram_sync',
                'status' => \ActionScheduler_Store::STATUS_PENDING,
            ), 'count' );
            $pending_count = intval( $actions );
        }

        $response = $active_sync;
        unset($response['queue']);
        
        $response['pending_batches'] = $pending_count;
        $response['server_time']     = current_time( 'timestamp' );
        
        wp_send_json_success( $response );
    }

    /**
     * Browser-driven sync: process one batch of products synchronously.
     * The browser calls this in a loop until all work is done.
     */
    public function ajax_sync_next_batch() {
        check_ajax_referer( 'ingram_preview_nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( array( 'message' => 'Sin permisos' ) );

        // Increase time limit for this request
        @set_time_limit(120);

        $state = get_option( 'ingram_woo_active_sync', array() );
        if ( empty($state) || empty($state['queue']) ) {
            wp_send_json_success( array( 'done' => true, 'message' => 'No hay trabajo pendiente.' ) );
        }

        $idx   = isset($state['queue_index']) ? intval($state['queue_index']) : 0;
        $queue = $state['queue'];

        // If we've processed all queue items, we're done
        if ( $idx >= count($queue) ) {
            $state['status'] = 'completed';
            update_option( 'ingram_woo_active_sync', $state );
            wp_send_json_success( array( 'done' => true, 'message' => 'Sincronizaci\u00f3n completada.' ) );
        }

        $current  = $queue[$idx];
        $category = $current['cat'];
        $sub      = $current['sub'];
        $page     = intval($current['page']);
        $batch_size = 20;

        // Update UI state
        $state['current_category'] = $category . ($sub ? ' > ' . $sub : '');
        $state['last_updated'] = current_time('timestamp');
        update_option( 'ingram_woo_active_sync', $state );

        // 1. Search products for this category/page
        $params = array(
            'category'   => $category,
            'pageNumber' => $page,
            'pageSize'   => $batch_size
        );
        if ( ! empty($sub) ) {
            $params['subCategory'] = $sub;
        }

        $response = $this->api->search_products( $params );

        if ( is_wp_error($response) || 200 !== $response['code'] ) {
            $err = is_wp_error($response) ? $response->get_error_message() : 'HTTP ' . $response['code'];
            $this->log_sync_event( "Error API en $category p$page: $err" );
            
            // Skip to next queue item on persistent error
            $state['queue_index'] = $idx + 1;
            $state['completed_categories'] = isset($state['completed_categories']) ? $state['completed_categories'] + 1 : 1;
            update_option( 'ingram_woo_active_sync', $state );
            
            wp_send_json_success( array(
                'done'    => false,
                'error'   => $err,
                'message' => "Error en $category, saltando al siguiente..."
            ) );
        }

        $catalog     = isset( $response['body']['catalog'] ) ? $response['body']['catalog'] : array();
        $total_found = isset( $response['body']['recordsFound'] ) ? intval( $response['body']['recordsFound'] ) : 0;
        $imported    = 0;
        $items_in_batch = count($catalog);

        if ( ! empty($catalog) ) {
            // 2. Try to get batch pricing (non-critical if it fails)
            $skus = array_column( $catalog, 'ingramPartNumber' );
            $batch_pricing = array();
            $price_response = $this->api->get_price_and_availability( $skus );
            if ( ! is_wp_error($price_response) && isset($price_response['code']) && 200 === $price_response['code'] ) {
                $prices_list = isset( $price_response['body'] ) ? $price_response['body'] : array();
                if ( isset($prices_list[0]) ) {
                    foreach ( $prices_list as $p ) {
                        if ( ! empty($p['ingramPartNumber']) ) {
                            $batch_pricing[ $p['ingramPartNumber'] ] = $p;
                        }
                    }
                }
            }

            // 3. Import each product synchronously
            foreach ( $catalog as $item ) {
                $sku = isset($item['ingramPartNumber']) ? $item['ingramPartNumber'] : '';
                if ( empty($sku) ) continue;
                
                $price_data = isset($batch_pricing[$sku]) ? $batch_pricing[$sku] : null;
                $this->import_product( $item, $price_data );
                $imported++;
            }

            $state['products_imported'] = intval($state['products_imported'] ?? 0) + $imported;
        }

        // 4. Pagination: if we got a full batch, there are likely more pages.
        //    If we got fewer items than batch_size, this category is done.
        $has_more_pages = ($items_in_batch >= $batch_size);

        $completed_cats = intval($state['completed_categories'] ?? 0);

        if ( $has_more_pages ) {
            // More pages: update current queue item's page number
            $state['queue'][$idx]['page'] = $page + 1;
            $this->log_sync_event( "$category p$page: $imported productos. Siguiente página..." );
        } else {
            // Category done, move to next queue item
            $state['queue_index'] = $idx + 1;
            $completed_cats++;
            $state['completed_categories'] = $completed_cats;
            $this->log_sync_event( "✓ Completada: $category" . ($sub ? " > $sub" : "") . " ($imported prod.)" );
        }

        $state['last_updated'] = current_time('timestamp');
        
        $total_cats = intval($state['total_categories'] ?? 0);
        $queue_idx  = intval($state['queue_index'] ?? $idx);
        $all_done   = $queue_idx >= count($queue);
        
        if ( $all_done ) {
            $state['status'] = 'completed';
            $state['completed_categories'] = $total_cats;
        }
        
        update_option( 'ingram_woo_active_sync', $state );

        wp_send_json_success( array(
            'done'                => $all_done,
            'imported_this_batch' => $imported,
            'total_imported'      => intval($state['products_imported'] ?? 0),
            'current_category'    => $state['current_category'] ?? '',
            'completed'           => $all_done ? $total_cats : $completed_cats,
            'total'               => $total_cats,
            'page'                => $page,
            'message'             => $imported . ' productos importados en esta ronda.'
        ) );
    }

    public function ajax_test_sync_category() {
        check_ajax_referer( 'ingram_preview_nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( array( 'message' => 'Unauthorized' ) );

        $category = isset( $_POST['category'] ) ? sanitize_text_field( $_POST['category'] ) : '';
        if ( empty( $category ) ) wp_send_json_error( array( 'message' => 'No category provided' ) );

        // Increased time limit for sample import (images can take time)
        @set_time_limit(180);

        $params = array(
            'category'   => $category,
            'pageSize'   => 5,
            'pageNumber' => 1
        );

        $response = $this->api->search_products( $params );
        if ( is_wp_error( $response ) || 200 !== $response['code'] ) {
            $msg = is_wp_error($response) ? $response->get_error_message() : 'API Error ' . $response['code'];
            wp_send_json_error( array( 'message' => $msg ) );
        }

        $catalog = isset( $response['body']['catalog'] ) ? $response['body']['catalog'] : array();
        if ( empty( $catalog ) ) {
            wp_send_json_error( array( 'message' => 'No products found in this category' ) );
        }

        $imported = 0;
        foreach ( $catalog as $item ) {
            $sku = isset($item['ingramPartNumber']) ? $item['ingramPartNumber'] : '';
            if ( empty($sku) ) continue;

            // Try to get individual price for better accuracy
            $price_response = $this->api->get_price_and_availability( array($sku) );
            $price_data = null;
            if ( ! is_wp_error($price_response) && 200 === $price_response['code'] ) {
                $price_data = isset($price_response['body'][0]) ? $price_response['body'][0] : null;
            }

            $this->import_product( $item, $price_data );
            $imported++;
        }

        wp_send_json_success( array( 'message' => sprintf( 'Se han importado %d productos de muestra de la categoría %s.', $imported, $category ) ) );
    }

    /**
     * Force reset a stuck sync.
     */
    public function ajax_force_reset_sync() {
        check_ajax_referer( 'ingram_preview_nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( array( 'message' => 'Unauthorized' ) );

        // 1. Wipe Active Sync Option
        delete_option( 'ingram_woo_active_sync' );

        // 2. Kill all pending Action Scheduler tasks in OUR groups
        if ( function_exists( 'as_unschedule_all_actions' ) ) {
            as_unschedule_all_actions( null, null, 'ingram_sync' );
            as_unschedule_all_actions( null, null, 'ingram_management' );
            
            // Aggressive cleanup: search and destroy any action prefixed with ingram_woo_
            // to ensure 'past-due' or 'failed' tasks are truly cleared.
            global $wpdb;
            $table = $wpdb->prefix . 'actionscheduler_actions';
            $wpdb->query( $wpdb->prepare( "DELETE FROM $table WHERE hook LIKE %s", 'ingram_woo_%' ) );
        }

        $this->log_sync_event( 'Reinicio de motor forzado por el usuario (Limpieza total).' );

        wp_send_json_success( array( 'message' => 'Motor reiniciado y colas vaciadas correctamente.' ) );
    }


    public function check_cron_schedule() {
        $opts = get_option( 'ingram_woo_options', array() );
        $schedule = isset( $opts['cron_schedule'] ) ? $opts['cron_schedule'] : 'daily';

        if ( $schedule !== 'disabled' ) {
            if ( ! wp_next_scheduled( 'ingram_woo_cron_sync_event' ) ) {
                wp_schedule_event( time(), $schedule, 'ingram_woo_cron_sync_event' );
            }
        } else {
            $timestamp = wp_next_scheduled( 'ingram_woo_cron_sync_event' );
            if ( $timestamp ) {
                wp_unschedule_event( $timestamp, 'ingram_woo_cron_sync_event' );
            }
        }
    }

    public function handle_external_cron_trigger() {
        if ( isset( $_GET['ingram_cron_sync'] ) ) {
            $opts = get_option( 'ingram_woo_options', array() );
            $secret = isset( $opts['cron_secret_key'] ) ? $opts['cron_secret_key'] : '';
            if ( empty( $secret ) ) {
                $secret = get_option( 'ingram_woo_cron_secret', '' );
                if ( empty( $secret ) ) {
                    $secret = md5( ( defined( 'SECURE_AUTH_KEY' ) ? SECURE_AUTH_KEY : 'ingram' ) . 'cron_key' );
                    update_option( 'ingram_woo_cron_secret', $secret );
                }
                $opts['cron_secret_key'] = $secret;
                update_option( 'ingram_woo_options', $opts );
            }
            $passed_key = isset( $_GET['key'] ) ? trim( sanitize_text_field( $_GET['key'] ) ) : '';
            
            if ( ( ! empty( $secret ) && $passed_key === $secret ) || current_user_can( 'manage_options' ) ) {
                $this->sync_products();
                status_header( 200 );
                header( 'Content-Type: text/plain' );
                echo 'OK: Ingram sync triggered via external Cron at ' . current_time( 'mysql' );
                exit;
            } else {
                status_header( 403 );
                header( 'Content-Type: text/plain' );
                echo 'Forbidden: Invalid key';
                exit;
            }
        }
    }

    public function update_cron_schedule( $old_value, $value ) {
        $timestamp = wp_next_scheduled( 'ingram_woo_cron_sync_event' );
        if ( $timestamp ) {
            wp_unschedule_event( $timestamp, 'ingram_woo_cron_sync_event' );
        }

        $new_schedule = isset( $value['cron_schedule'] ) ? $value['cron_schedule'] : 'daily';
        if ( $new_schedule !== 'disabled' ) {
            wp_schedule_event( time(), $new_schedule, 'ingram_woo_cron_sync_event' );
        }
    }

    public function sync_products_handler() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Unauthorized', 'ingram-woo' ) );
        }

        if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'ingram_sync_action' ) ) {
            wp_die( __( 'Nonce verification failed', 'ingram-woo' ) );
        }

        $this->sync_products();
        wp_redirect( admin_url( 'admin.php?page=ingram-woo-settings&tab=sync&synced=1' ) );
        exit;
    }

    /**
     * Fetch products by category and import into WooCommerce.
     */
    /**
     * Batch Worker for Action Scheduler
     */
    public function process_category_batch( $category, $sub_category, $page_number, $batch_size ) {
        $this->log_sync_event( sprintf( "Batch sync: %s (%s) - Page %d", $category, $sub_category ? $sub_category : 'All', $page_number ) );

        // Update status for UI
        $active = get_option( 'ingram_woo_active_sync', array() );
        if ( ! empty( $active ) ) {
            $active['current_category'] = $category . ($sub_category ? ' > ' . $sub_category : '');
            $active['last_updated']     = current_time( 'timestamp' );
            update_option( 'ingram_woo_active_sync', $active );
        }

        $params = array( 
            'category'    => $category, 
            'pageNumber'  => $page_number,
            'pageSize'    => $batch_size 
        );
        
        if ( ! empty( $sub_category ) ) {
            $params['subCategory'] = $sub_category;
        }

        $response = $this->api->search_products( $params );

        if ( is_wp_error( $response ) || 200 !== $response['code'] ) {
            $err = is_wp_error($response) ? $response->get_error_message() : "HTTP ".$response['code'];
            $this->log_sync_event( "Batch failed for $category ($sub_category) p$page_number: " . $err );
            
            // Mark sync as failed in tracker
            $active = get_option( 'ingram_woo_active_sync', array() );
            if ( ! empty( $active ) ) {
                $active['status'] = 'failed';
                $active['error_message'] = 'Error en Ingram API: ' . $err;
                update_option( 'ingram_woo_active_sync', $active );
            }
            return;
        }

        $catalog = isset( $response['body']['catalog'] ) ? $response['body']['catalog'] : array();
        $total_found = isset( $response['body']['recordsFound'] ) ? intval( $response['body']['recordsFound'] ) : count( $catalog );

        if ( ! empty( $catalog ) ) {
            // STEP 2: Batch enrichment - Fetch real-time Prices & Avail for all SKUs in this batch
            $skus = array_column( $catalog, 'ingramPartNumber' );
            $price_response = $this->api->get_price_and_availability( $skus );
            
            $batch_pricing = array();
            if ( ! is_wp_error( $price_response ) && 200 === $price_response['code'] ) {
                $body = isset( $price_response['body'] ) ? $price_response['body'] : array();
                // V6 API returns a direct array of product pricing objects (not wrapped under 'results')
                // Handle both just in case the structure changes between sandbox/production
                $prices = isset( $body['results'] ) ? $body['results'] : ( isset( $body[0] ) ? $body : array() );
                foreach ( $prices as $p_item ) {
                    if ( ! empty( $p_item['ingramPartNumber'] ) ) {
                        $batch_pricing[ $p_item['ingramPartNumber'] ] = $p_item;
                    }
                }
            }

            // --- ULTRA-LIGHT REFACTOR ---
            // Instead of importing in this same heavy process, we enqueue individual tasks for each product.
            foreach ( $catalog as $item ) {
                $sku = $item['ingramPartNumber'];
                $price_data = isset( $batch_pricing[ $sku ] ) ? $batch_pricing[ $sku ] : array();
                
                as_enqueue_async_action( 'ingram_woo_import_single_product', array( 
                    'product_data' => $item, 
                    'price_data'   => $price_data 
                ), 'ingram_sync' );
            }
            
            $this->log_sync_event( "Encolados " . count($catalog) . " productos para importación individual." );
        }

        // --- Intelligent Pagination ---
        $processed_so_far = $page_number * $batch_size;

        if ( $processed_so_far < $total_found && ! empty( $catalog ) ) {
            as_enqueue_async_action( 'ingram_woo_batch_sync_category', array( 
                'category'     => $category, 
                'sub_category' => $sub_category,
                'page_number'  => $page_number + 1,
                'batch_size'   => $batch_size
            ), 'ingram_sync' );
            $this->log_sync_event( "Scheduled next page: " . ($page_number + 1) );
        } else {
            // End of category. Update status.
            $active = get_option( 'ingram_woo_active_sync', array() );
            if ( ! empty( $active ) ) {
                $active['completed_categories']++;
                
                // If all categories are done, mark as completed
                if ( $active['completed_categories'] >= $active['total_categories'] ) {
                    $active['status'] = 'completed';
                }
                
                update_option( 'ingram_woo_active_sync', $active );
            }
            $this->log_sync_event( "Finished: $category" . ($sub_category ? " ($sub_category)" : "") );
        }
    }

    public function sync_products( $keyword = '' ) {
        // Daily automated sync: trigger all previously selected categories
        $opts = get_option( 'ingram_woo_options', array() );
        $selections = isset( $opts['selected_categories'] ) ? (array) $opts['selected_categories'] : array();
        
        if ( empty( $selections ) ) {
             $this->log_sync_event( 'Daily sync skipped: No categories selected.' );
             return;
        }

        $this->log_sync_event( 'Daily automated synchronization started.' );
        
        foreach ( $selections as $selection ) {
            $cat = isset($selection['cat']) ? $selection['cat'] : '';
            $sub = isset($selection['sub']) ? $selection['sub'] : '';

            as_enqueue_async_action( 'ingram_woo_batch_sync_category', array( 
                'category'     => $cat, 
                'sub_category' => $sub,
                'page_number'  => 1,
                'batch_size'   => 20 // Reduced from 50 to prevent timeouts
            ), 'ingram_sync' );
        }
    }

    private function log_sync_event( $message ) {
        $logs = get_option( 'ingram_woo_sync_logs', array() );
        $time = current_time( 'mysql' );
        array_unshift( $logs, array( 'time' => $time, 'message' => $message ) );
        
        if ( count( $logs ) > 50 ) {
            $logs = array_slice( $logs, 0, 50 );
        }
        
        update_option( 'ingram_woo_sync_logs', $logs );
    }

    /**
     * Individual product import worker. Ultra-light and isolation-safe.
     */
    public function process_single_product_import( $data, $price_data ) {
        $sku = isset($data['ingramPartNumber']) ? sanitize_text_field($data['ingramPartNumber']) : '';
        if ( empty($sku) ) return;

        // Perform the actual import/update logic
        $this->import_product( $data, $price_data );
        
        // Update the heartbeat to prevent timeout detection
        $active = get_option( 'ingram_woo_active_sync', array() );
        if ( ! empty( $active ) ) {
            $active['last_updated'] = current_time( 'timestamp' );
            update_option( 'ingram_woo_active_sync', $active );
        }
    }

    private function import_product( $data, $price_data = null ) {
        if ( empty( $data['ingramPartNumber'] ) ) return;

        $sku = sanitize_text_field( $data['ingramPartNumber'] );
        $existing = wc_get_product_id_by_sku( $sku );
        
        if ( $existing ) {
            $product = wc_get_product( $existing );
            if ( ! $product ) return; // safety check — product ID exists but object could not be loaded
        } else {
            $product = new WC_Product_Simple();
            $product->set_sku( $sku );
        }

        $opts = get_option( 'ingram_woo_options', array() );
        $preserve_desc  = isset( $opts['preserve_description'] ) ? intval( $opts['preserve_description'] ) : 1;
        $preserve_title = isset( $opts['preserve_title'] ) ? intval( $opts['preserve_title'] ) : 0;

        if ( ! $existing || ! $preserve_title || empty( trim( (string) $product->get_name() ) ) ) {
            $product->set_name( isset( $data['description'] ) ? sanitize_text_field( $data['description'] ) : 'Ingram Product ' . $sku );
        }

        $desc_to_set = ! empty( $data['extraDescription'] ) ? sanitize_text_field( $data['extraDescription'] ) : ( isset( $data['description'] ) ? sanitize_text_field( $data['description'] ) : '' );

        if ( ! $existing ) {
            $product->set_description( $desc_to_set );
        } else {
            $current_desc = trim( (string) $product->get_description() );
            if ( ! $preserve_desc || empty( $current_desc ) ) {
                $product->set_description( $desc_to_set );
            }
        }
        
        // --- Pricing Logic (Ingram API V6 Mapping) ---
        // V6 P&A response: pricing is nested under a 'pricing' key.
        // Key order of preference: netPrice > customerPrice > retailPrice > listPrice
        $price = 0;

        if ( ! empty( $price_data ) ) {
            if ( ! empty( $price_data['pricing']['netPrice'] ) ) {
                $price = floatval( $price_data['pricing']['netPrice'] );
            } elseif ( ! empty( $price_data['pricing']['customerPrice'] ) ) {
                $price = floatval( $price_data['pricing']['customerPrice'] );
            } elseif ( ! empty( $price_data['pricing']['retailPrice'] ) ) {
                $price = floatval( $price_data['pricing']['retailPrice'] );
            } elseif ( ! empty( $price_data['pricing']['listPrice'] ) ) {
                $price = floatval( $price_data['pricing']['listPrice'] );
            }
            // V6 flat fallback (some sandbox responses surface price at root level)
            if ( $price <= 0 && ! empty( $price_data['netPrice'] ) ) {
                $price = floatval( $price_data['netPrice'] );
            }
        }

        // Priority 2: Fallback to Catalog search data
        if ( $price <= 0 ) {
            if ( ! empty( $data['pricing']['netPrice'] ) ) {
                $price = floatval( $data['pricing']['netPrice'] );
            } elseif ( ! empty( $data['pricing']['customerPrice'] ) ) {
                $price = floatval( $data['pricing']['customerPrice'] );
            } elseif ( ! empty( $data['pricing']['retailPrice'] ) ) {
                $price = floatval( $data['pricing']['retailPrice'] );
            } elseif ( ! empty( $data['pricing']['listPrice'] ) ) {
                $price = floatval( $data['pricing']['listPrice'] );
            }
        }

        if ( $price > 0 ) {
            $opts   = get_option( 'ingram_woo_options', array() );
            $margin = isset( $opts['price_margin'] ) ? floatval( $opts['price_margin'] ) : 0;
            $rate   = isset( $opts['usd_exchange_rate'] ) ? floatval( $opts['usd_exchange_rate'] ) : 1;

            // Apply Exchange Rate (API returns USD for Ingram Micro Chile)
            if ( $rate > 0 ) {
                $price = $price * $rate;
            }

            // Apply Profit Margin
            if ( $margin > 0 ) {
                $price = $price * ( 1 + ( $margin / 100 ) );
            }

            $product->set_regular_price( round( $price, 2 ) );
        }

        // --- Stock Management (Ingram API V6 Mapping) ---
        // V6 P&A: availability.totalAvailability or availability.availabilityByWarehouse[].quantityAvailable
        $product->set_manage_stock( true );
        $stock_qty = 0;

        if ( ! empty( $price_data ) ) {
            if ( isset( $price_data['availability']['totalAvailability'] ) ) {
                $stock_qty = intval( $price_data['availability']['totalAvailability'] );
            } elseif ( isset( $price_data['availability']['quantityAvailable'] ) ) {
                $stock_qty = intval( $price_data['availability']['quantityAvailable'] );
            } elseif ( ! empty( $price_data['availability']['availabilityByWarehouse'] ) ) {
                // Sum stock across all warehouses
                foreach ( $price_data['availability']['availabilityByWarehouse'] as $wh ) {
                    $stock_qty += intval( $wh['quantityAvailable'] ?? 0 );
                }
            }
            // V6 flat fallback
            if ( $stock_qty <= 0 && isset( $price_data['quantityAvailable'] ) ) {
                $stock_qty = intval( $price_data['quantityAvailable'] );
            }
        }

        // Fallback to catalog availability data
        if ( $stock_qty <= 0 && isset( $data['availability']['totalAvailability'] ) ) {
            $stock_qty = intval( $data['availability']['totalAvailability'] );
        }

        $product->set_stock_quantity( $stock_qty );
        $product->set_stock_status( $stock_qty > 0 ? 'instock' : 'outofstock' );

        // --- Hierarchical Categories ---
        $cat_ids = array();
        if ( ! empty( $data['category'] ) ) {
            $parent_name = trim( $data['category'] );
            $sub_name    = isset( $data['subCategory'] ) ? trim( $data['subCategory'] ) : '';
            
            $parent_id = $this->get_or_create_category( $parent_name );
            if ( $parent_id ) {
                $cat_ids[] = $parent_id;
                
                if ( ! empty( $sub_name ) ) {
                    $sub_id = $this->get_or_create_category( $sub_name, $parent_id );
                    if ( $sub_id ) {
                        $cat_ids[] = $sub_id;
                    }
                }
            }
        }
        
        if ( ! empty( $cat_ids ) ) {
            $product->set_category_ids( $cat_ids );
        }

        $product->set_status( 'publish' );
        $product_id = $product->save();

        // --- Image Enrichment (Smart Cache) ---
        // The Ingram Chile production API does NOT return images in the Details endpoint.
        // We use a transient to track if images have ever been found. If not after 3 tries,
        // we stop making the extra API call to avoid wasting ~2-5s per product.
        $existing_thumbnail = get_post_thumbnail_id( $product_id );
        $existing_height = $product->get_height();
        $existing_width  = $product->get_width();
        $existing_length = $product->get_length();
        $existing_weight = $product->get_weight();
        $missing_dimensions = empty( $existing_height ) || empty( $existing_width ) || empty( $existing_length ) || empty( $existing_weight );

        $img_attempts = intval( get_transient( 'ingram_img_attempts' ) );
        $img_found    = get_transient( 'ingram_img_found' );

        $needs_details = ( ! $existing_thumbnail && ( $img_found || $img_attempts < 3 ) ) || $missing_dimensions;

        if ( $needs_details ) {
            $images = array();
            $details_response = $this->api->get_product_details( $sku );

            if ( ! is_wp_error( $details_response ) && 200 === $details_response['code'] ) {
                $details = $details_response['body'];

                // --- Save dimensions and weight if missing ---
                if ( ! empty( $details['additionalInformation'] ) ) {
                    $info = $details['additionalInformation'];
                    $updated_dimensions = false;

                    if ( empty( $existing_length ) && ! empty( $info['length'] ) ) {
                        $product->set_length( floatval( $info['length'] ) );
                        $updated_dimensions = true;
                    }
                    if ( empty( $existing_width ) && ! empty( $info['width'] ) ) {
                        $product->set_width( floatval( $info['width'] ) );
                        $updated_dimensions = true;
                    }
                    if ( empty( $existing_height ) && ! empty( $info['height'] ) ) {
                        $product->set_height( floatval( $info['height'] ) );
                        $updated_dimensions = true;
                    }

                    if ( empty( $existing_weight ) && ! empty( $info['productWeight'] ) && is_array( $info['productWeight'] ) ) {
                        $weight = 0;
                        foreach ( $info['productWeight'] as $pw ) {
                            if ( isset( $pw['weight'] ) ) {
                                $weight = floatval( $pw['weight'] );
                                if ( isset( $pw['plantId'] ) && $pw['plantId'] === 'CL02' ) {
                                    // Prioritize Chilean warehouse plant CL02
                                    break;
                                }
                            }
                        }
                        if ( $weight > 0 ) {
                            $product->set_weight( $weight );
                            $updated_dimensions = true;
                        }
                    }

                    if ( $updated_dimensions ) {
                        $product->save();
                    }
                }

                // V6: mediaLinks array
                if ( ! empty( $details['mediaLinks'] ) && is_array( $details['mediaLinks'] ) ) {
                    foreach ( $details['mediaLinks'] as $media ) {
                        if ( empty( $media['url'] ) ) continue;
                        $type = strtolower( $media['mediaType'] ?? '' );
                        if ( strpos( $type, 'image' ) !== false || strpos( $type, 'photo' ) !== false || strpos( $type, 'thumb' ) !== false ) {
                            $images[] = $media['url'];
                        }
                    }
                }

                // Fallback fields
                if ( empty( $images ) && ! empty( $details['imageLink'] ) ) {
                    $images[] = $details['imageLink'];
                }
                if ( empty( $images ) && ! empty( $details['primaryImageUrl'] ) ) {
                    $images[] = $details['primaryImageUrl'];
                }
            }

            if ( ! empty( $images ) ) {
                set_transient( 'ingram_img_found', true, HOUR_IN_SECONDS );
                $this->import_product_images( $product_id, $images );
            } else {
                $vpn = isset($data['vendorPartNumber']) ? trim($data['vendorPartNumber']) : '';
                $search_term = $vpn ?: ($data['customerDescription'] ?? $sku);

                // --- IMAGE FALLBACK LAYER 1: SP DIGITAL SCRAPER ---
                if ( class_exists( 'SP_Scraper' ) && ! empty( $vpn ) ) {
                    $sp_data = SP_Scraper::get_instance()->scrape_product_data_for_sku( $vpn );
                    if ( ! is_wp_error( $sp_data ) && ! empty( $sp_data['image_url'] ) ) {
                        $this->log_sync_event( "SCRAPER: Foto encontrada en SP Digital para VPN $vpn" );
                        $this->import_product_images( $product_id, array( $sp_data['image_url'] ) );
                    }
                }

                // --- IMAGE FALLBACK LAYER 2: GLOBAL SCRAPER (Google/ML) ---
                if ( ! get_post_thumbnail_id( $product_id ) && class_exists( 'GS_Scraper' ) ) {
                    $gs_data = GS_Scraper::get_instance()->scrape_product_data( $data['customerDescription'] ?? '', $vpn );
                    if ( ! is_wp_error( $gs_data ) && ! empty( $gs_data['image_url'] ) ) {
                        $this->log_sync_event( "SCRAPER: Foto encontrada via " . ($gs_data['source'] ?? 'Global') . " para $search_term" );
                        $sideload = GS_Scraper::get_instance()->sideload_image( $gs_data['image_url'], $product_id );
                        if ( ! is_wp_error( $sideload ) ) {
                            set_post_thumbnail( $product_id, $sideload['id'] );
                        }
                    }
                }

                // --- IMAGE FALLBACK LAYER 3: CRID (Ingram US Sandbox) ---
                if ( ! get_post_thumbnail_id( $product_id ) && ! empty( $vpn ) ) {
                    $crid_images = $this->api->discover_images_cross_region( $vpn );
                    if ( ! empty( $crid_images ) ) {
                        $this->log_sync_event( "CRID: Encontradas " . count($crid_images) . " fotos en Sandbox US para VPN $vpn" );
                        $this->import_product_images( $product_id, $crid_images );
                    }
                }
                
                if ( ! get_post_thumbnail_id( $product_id ) ) {
                    set_transient( 'ingram_img_attempts', $img_attempts + 1, HOUR_IN_SECONDS );
                }
            }

            // --- INDEPENDENT DESCRIPTION ENRICHMENT LAYER ---
            $current_desc = trim( (string) $product->get_description() );
            $title_text   = isset( $data['description'] ) ? trim( $data['description'] ) : '';
            $needs_rich_desc = empty( $current_desc ) || strlen( $current_desc ) < 100 || $current_desc === $title_text;

            if ( $needs_rich_desc ) {
                $vpn = isset( $data['vendorPartNumber'] ) ? trim( $data['vendorPartNumber'] ) : '';
                $rich_desc = '';

                if ( class_exists( 'SP_Scraper' ) && ! empty( $vpn ) ) {
                    $sp_data = SP_Scraper::get_instance()->scrape_product_data_for_sku( $vpn );
                    if ( ! is_wp_error( $sp_data ) && ! empty( $sp_data['description'] ) && strlen( $sp_data['description'] ) > 50 ) {
                        $rich_desc = $sp_data['description'];
                    }
                }

                if ( empty( $rich_desc ) && class_exists( 'GS_Scraper' ) ) {
                    $gs_data = GS_Scraper::get_instance()->scrape_product_data( $title_text, $vpn );
                    if ( ! is_wp_error( $gs_data ) && ! empty( $gs_data['description'] ) && strlen( $gs_data['description'] ) > 50 && $gs_data['description'] !== $title_text ) {
                        $rich_desc = $gs_data['description'];
                    }
                }

                if ( ! empty( $rich_desc ) ) {
                    $product->set_description( $rich_desc );
                    $product->save();
                    $this->log_sync_event( "SCRAPER: Ficha técnica enriquecida para SKU $sku" );
                }
            }
        }
    }

    /**
     * Sideload remote images into WordPress Media Library and assign to product.
     */
    private function import_product_images( $product_id, $image_urls ) {
        if ( empty( $image_urls ) ) return;

        // Limit to top 5 images to prevent massive downloads per product
        $image_urls = array_slice( $image_urls, 0, 5 );

        require_once( ABSPATH . 'wp-admin/includes/image.php' );
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        require_once( ABSPATH . 'wp-admin/includes/media.php' );

        $gallery_ids = array();
        $featured_set = get_post_thumbnail_id( $product_id );

        foreach ( $image_urls as $index => $url ) {
            // Check if this image was already imported to prevent duplicates
            // We store the remote URL in a meta field for tracking
            $existing_id = $this->get_attachment_id_by_url( $url );
            
            if ( $existing_id ) {
                $attach_id = $existing_id;
            } else {
                $attach_id = media_handle_sideload( array(
                    'name'     => basename( $url ),
                    'tmp_name' => download_url( $url )
                ), $product_id );

                if ( ! is_wp_error( $attach_id ) ) {
                    update_post_meta( $attach_id, '_ingram_remote_url', $url );
                }
            }

            if ( ! is_wp_error( $attach_id ) ) {
                 if ( 0 === $index && ! $featured_set ) {
                      set_post_thumbnail( $product_id, $attach_id );
                      $featured_set = true;
                 } else {
                      $gallery_ids[] = $attach_id;
                 }
            }
        }

        if ( ! empty( $gallery_ids ) ) {
             update_post_meta( $product_id, '_product_image_gallery', implode( ',', $gallery_ids ) );
        }
    }

    public function ajax_get_local_catalog() {
        check_ajax_referer( 'ingram_preview_nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( array( 'message' => 'Unauthorized' ) );

        global $wpdb;
        $terms = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false ) );
        $data = array();
        foreach ( $terms as $term ) {
            // Skip the 'Uncategorized' default theme category
            if ($term->slug === 'uncategorized') continue;

            // Get total stock and in-stock count for this category
            $product_ids = get_posts( array(
                'post_type'   => 'product',
                'post_status' => array( 'publish', 'draft' ),
                'numberposts' => -1,
                'fields'      => 'ids',
                'tax_query'   => array( array(
                    'taxonomy' => 'product_cat',
                    'field'    => 'term_id',
                    'terms'    => $term->term_id,
                    'include_children' => false
                ) )
            ) );

            $total_stock = 0;
            $in_stock_count = 0;
            $with_price_count = 0;

            if ( ! empty( $product_ids ) ) {
                foreach ( $product_ids as $pid ) {
                    $qty   = intval( get_post_meta( $pid, '_stock', true ) );
                    $price = floatval( get_post_meta( $pid, '_regular_price', true ) );
                    $total_stock += $qty;
                    if ( $qty > 0 ) $in_stock_count++;
                    if ( $price > 0 ) $with_price_count++;
                }
            }

            $data[] = array(
                'id'              => $term->term_id,
                'name'            => $term->name,
                'slug'            => $term->slug,
                'count'           => $term->count,
                'parent'          => $term->parent,
                'total_stock'     => $total_stock,
                'in_stock_count'  => $in_stock_count,
                'with_price'      => $with_price_count
            );
        }
        wp_send_json_success( $data );
    }

    public function ajax_manage_catalog() {
        check_ajax_referer( 'ingram_preview_nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( array( 'message' => 'Unauthorized' ) );

        $category_ids = isset($_POST['ids']) ? (array)$_POST['ids'] : array();
        $action = isset($_POST['job_action']) ? sanitize_text_field($_POST['job_action']) : '';

        if ( empty($category_ids) || empty($action) ) {
             wp_send_json_error( array( 'message' => 'Invalid parameters' ) );
        }

        foreach($category_ids as $id) {
            as_enqueue_async_action('ingram_woo_manage_category_batch', array(
                'category_id' => intval($id),
                'job_action'  => $action,
                'page'        => 1
            ), 'ingram_management');
        }

        $this->log_sync_event( "Catalog management JOB started: $action on " . count($category_ids) . " sections." );
        wp_send_json_success( array( 'message' => 'Management job started.' ) );
    }

    public function process_management_batch($category_id, $action, $page) {
        $batch_size = 100;
        
        // Define which products we are looking for based on the action
        $search_status = 'any';
        if ($action === 'hide') $search_status = 'publish';
        if ($action === 'show') $search_status = 'draft';

        $args = array(
            'post_type'      => 'product',
            'posts_per_page' => $batch_size,
            'post_status'    => $search_status,
            'fields'         => 'ids',
            'tax_query' => array(
                array(
                    'taxonomy' => 'product_cat',
                    'field'    => 'term_id',
                    'terms'    => $category_id,
                    'include_children' => true
                )
            )
        );
        
        $product_ids = get_posts($args);
        
        if (empty($product_ids)) {
            if ($action === 'delete') {
                wp_delete_term($category_id, 'product_cat');
            }
            return;
        }

        foreach($product_ids as $p_id) {
            if ($action === 'delete') {
                wp_delete_post($p_id, true);
            } elseif ($action === 'hide') {
                wp_update_post(array('ID' => $p_id, 'post_status' => 'draft'));
            } elseif ($action === 'show') {
                wp_update_post(array('ID' => $p_id, 'post_status' => 'publish'));
            }
        }

        // Schedule next batch
        as_enqueue_async_action('ingram_woo_manage_category_batch', array(
            'category_id' => $category_id,
            'job_action'  => $action,
            'page'        => 1 // Always 1 because the current set is now processed
        ), 'ingram_management');
    }

    private function get_attachment_id_by_url( $url ) {
        global $wpdb;
        return $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_ingram_remote_url' AND meta_value = %s", $url ) );
    }

    private function get_or_create_category( $name, $parent_id = 0 ) {
        $term = get_term_by( 'name', $name, 'product_cat' );
        
        // If we have a name match, check if parent matches too (for subcategories)
        if ( $term && $term->parent == $parent_id ) {
            return $term->term_id;
        }

        // If term exists but parent is different, we might have multiple cats with same name.
        // For simplicity, we create specific terms if parent is defined.
        $new_cat = wp_insert_term( $name, 'product_cat', array( 'parent' => $parent_id ) );
        if ( ! is_wp_error( $new_cat ) ) {
            return $new_cat['term_id'];
        } elseif ( isset( $new_cat->error_data['term_exists'] ) ) {
            return $new_cat->error_data['term_exists'];
        }
        
        return false;
    }
}

