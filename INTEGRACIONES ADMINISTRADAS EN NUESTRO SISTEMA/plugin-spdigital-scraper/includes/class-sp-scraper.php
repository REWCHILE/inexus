<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SP_Scraper {
    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // AJAX for processing
        add_action( 'wp_ajax_sp_scraper_process_batch', array( $this, 'ajax_process_batch' ) );
    }

    /**
     * Fetch URL with standard headers to avoid blocks
     */
    /**
     * Fetch URL with advanced camouflage
     */
    private function fetch_url( $url, $referer = '' ) {
        $user_agents = array(
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36'
        );

        $headers = array(
            'Accept'                    => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
            'Accept-Language'           => 'es-CL,es;q=0.9,en;q=0.8',
            'Cache-Control'             => 'max-age=0',
            'Upgrade-Insecure-Requests' => '1',
            'Sec-Fetch-Dest'            => 'document',
            'Sec-Fetch-Mode'            => 'navigate',
            'Sec-Fetch-Site'            => $referer ? 'same-origin' : 'none',
            'Sec-Fetch-User'            => '?1',
            'Sec-Ch-Ua'                 => '"Not A(Brand";v="99", "Google Chrome";v="122", "Chromium";v="122"',
            'Sec-Ch-Ua-Mobile'          => '?0',
            'Sec-Ch-Ua-Platform'        => '"Windows"',
            'DNT'                       => '1',
            'Connection'                => 'keep-alive',
        );

        if ( $referer ) {
            $headers['Referer'] = $referer;
        }

        $args = array(
            'timeout'    => 30,
            'user-agent' => $user_agents[ array_rand($user_agents) ],
            'headers'    => $headers,
        );

        // Human-like jitter
        usleep( rand( 800000, 2000000 ) );

        $response = wp_remote_get( $url, $args );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        if ( 200 !== $code ) {
            return new WP_Error( 'http_error', "SP Digital returned HTTP $code" );
        }

        return wp_remote_retrieve_body( $response );
    }

    /**
     * Scrape product data for a specific SKU
     */
    public function scrape_product_data_for_sku( $sku ) {
        if ( empty( $sku ) ) {
            return new WP_Error( 'empty_sku', 'SKU is empty' );
        }

        $search_url = "https://www.spdigital.cl/search/?q=" . urlencode( $sku );
        // Search request (no referer)
        $body = $this->fetch_url( $search_url );

        if ( is_wp_error( $body ) ) {
            return $body;
        }

        $data = array(
            'image_url'   => '',
            'description' => '',
            'product_url' => '',
        );

        // 1. Try to find product link in search results
        if ( preg_match( '/class="[^"]*Fractal-ProductCard--image[^"]*"[^>]*href="([^">]+)"/is', $body, $matches ) ) {
            $data['product_url'] = "https://www.spdigital.cl" . $matches[1];
        }

        // 2. If we have product URL, go there for better data
        if ( ! empty( $data['product_url'] ) ) {
            // Product request (referer is the search page)
            $product_body = $this->fetch_url( $data['product_url'], $search_url );
            if ( ! is_wp_error( $product_body ) ) {
                // Get high-res image
                if ( preg_match( '/Fractal-ProductImage.*?src="([^">]+)"/is', $product_body, $img_matches ) ) {
                    $data['image_url'] = $img_matches[1];
                }
                
                // Get description
                if ( preg_match_all( '/class="Fractal-Tabs__content--container\s*"[^>]*>(.*?)<\/span>/is', $product_body, $desc_matches ) ) {
                    foreach ( $desc_matches[1] as $content ) {
                        if ( strpos( $content, '<p' ) !== false || strpos( $content, '<table' ) !== false ) {
                            $data['description'] = trim( $content );
                            break;
                        }
                    }
                }
            }
        }

        // 3. Fallback for image from search results if not found yet
        if ( empty( $data['image_url'] ) ) {
            if ( preg_match( '/class="[^"]*Fractal-ProductCard--image[^"]*"[^>]*>.*?<img[^>]+src="([^">]+)"/is', $body, $matches ) ) {
                $data['image_url'] = $matches[1];
            }
        }

        if ( empty( $data['image_url'] ) && empty( $data['description'] ) ) {
            return new WP_Error( 'no_data_found', 'Could not find image or description for this SKU' );
        }

        return $data;
    }

    /**
     * Sideload image to WordPress
     */
    public function sideload_image( $url, $product_id ) {
        if ( ! $url || ! $product_id ) {
            return false;
        }

        require_once( ABSPATH . 'wp-admin/includes/image.php' );
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        require_once( ABSPATH . 'wp-admin/includes/media.php' );

        // Download the image
        $tmp = download_url( $url );
        if ( is_wp_error( $tmp ) ) {
            return $tmp;
        }

        $file_array = array(
            'name'     => basename( $url ),
            'tmp_name' => $tmp,
        );

        // Check for extension if missing
        if ( ! pathinfo( $file_array['name'], PATHINFO_EXTENSION ) ) {
            $file_array['name'] .= '.jpg';
        }

        $id = media_handle_sideload( $file_array, $product_id );

        // If error, unlink tmp file
        if ( is_wp_error( $id ) ) {
            @unlink( $file_array['tmp_name'] );
            return $id;
        }

        return $id;
    }

    /**
     * AJAX Batch Processor
     */
    public function ajax_process_batch() {
        check_ajax_referer( 'sp_scraper_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Unauthorized' );
        }

        $batch_size = 5;
        
        // Get products without featured image
        $args = array(
            'post_type'      => 'product',
            'posts_per_page' => $batch_size,
            'meta_query'     => array(
                array(
                    'key'     => '_thumbnail_id',
                    'compare' => 'NOT EXISTS',
                ),
            ),
            'orderby'        => 'ID',
            'order'          => 'DESC',
        );

        $query = new WP_Query( $args );
        $products = $query->posts;

        $results = array();
        foreach ( $products as $product ) {
            $product_id = $product->ID;
            $sku = get_post_meta( $product_id, '_sku', true );
            
            if ( empty( $sku ) ) {
                $results[] = array(
                    'id'      => $product_id,
                    'sku'     => 'N/A',
                    'status'  => 'error',
                    'message' => 'No SKU defined',
                );
                continue;
            }

            $product_data = $this->scrape_product_data_for_sku( $sku );

            if ( is_wp_error( $product_data ) ) {
                $results[] = array(
                    'id'      => $product_id,
                    'sku'     => $sku,
                    'status'  => 'error',
                    'message' => $product_data->get_error_message(),
                );
                update_post_meta( $product_id, '_sp_scraper_failed', time() );
                continue;
            }

            $success_messages = array();
            
            // Handle Image
            if ( ! empty( $product_data['image_url'] ) ) {
                $attach_id = $this->sideload_image( $product_data['image_url'], $product_id );
                if ( is_wp_error( $attach_id ) ) {
                    $success_messages[] = 'Image failed: ' . $attach_id->get_error_message();
                } else {
                    set_post_thumbnail( $product_id, $attach_id );
                    $success_messages[] = 'Image imported';
                }
            }

            // Handle Description
            if ( ! empty( $product_data['description'] ) ) {
                // Update product content
                wp_update_post( array(
                    'ID'           => $product_id,
                    'post_content' => $product_data['description'],
                ) );
                $success_messages[] = 'Description imported';
            }

            $results[] = array(
                'id'      => $product_id,
                'sku'     => $sku,
                'status'  => 'success',
                'message' => implode( ', ', $success_messages ),
            );
        }

        wp_send_json_success( array(
            'processed' => count( $results ),
            'results'   => $results,
            'remaining' => $this->get_remaining_count(),
        ) );
    }

    public function get_remaining_count() {
        $args = array(
            'post_type'      => 'product',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'meta_query'     => array(
                array(
                    'key'     => '_thumbnail_id',
                    'compare' => 'NOT EXISTS',
                ),
            ),
        );
        $query = new WP_Query( $args );
        return $query->found_posts;
    }
}
