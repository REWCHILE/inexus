<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class GS_Scraper {
    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // AJAX for processing
        add_action( 'wp_ajax_gs_scraper_process_batch', array( $this, 'ajax_process_batch' ) );
        
        // Allow WebP and bypass strict extension checks
        add_filter( 'upload_mimes', array( $this, 'allow_webp_uploads' ), 99 );
        add_filter( 'wp_check_filetype_and_ext', array( $this, 'bypass_extension_check' ), 99, 4 );
    }

    public function allow_webp_uploads( $mimes ) {
        $mimes['webp'] = 'image/webp';
        return $mimes;
    }

    public function bypass_extension_check( $data, $file, $filename, $mimes ) {
        if ( empty( $data['ext'] ) ) {
            $filetype = wp_check_filetype( $filename, $mimes );
            $data['ext']  = $filetype['ext'];
            $data['type'] = $filetype['type'];
        }
        return $data;
    }

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
            'Sec-Fetch-Site'            => 'none',
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
            return new WP_Error( 'http_error', "Google returned HTTP $code" );
        }

        return wp_remote_retrieve_body( $response );
    }

    /**
     * Scrape product data using multiple engines (ML -> Google -> DDG)
     */
    public function scrape_product_data( $search_term ) {
        if ( empty( $search_term ) ) {
            return new WP_Error( 'empty_search', 'Search term is empty' );
        }

        // Try searching with the full term first, then just the title if it fails
        $results = $this->execute_multi_layer_search( $search_term );
        
        if ( is_wp_error( $results ) && preg_match( '/\s[0-9]{5,10}$/', $search_term ) ) {
            // Fallback: Remove the SKU from the end of the title and try again
            $clean_term = preg_replace( '/\s[0-9]{5,10}$/', '', $search_term );
            $results = $this->execute_multi_layer_search( $clean_term );
        }

        return $results;
    }

    private function scrape_from_mercadolibre_api( $term ) {
        $search_url = "https://api.mercadolibre.com/sites/MLC/search?q=" . urlencode( $term );
        $response = wp_remote_get( $search_url, array( 'timeout' => 12, 'user-agent' => 'Mozilla/5.0' ) );

        if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
            return false;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( empty( $body['results'] ) || ! is_array( $body['results'] ) ) {
            return false;
        }

        $item = $body['results'][0];
        $image_url = '';
        if ( ! empty( $item['thumbnail'] ) ) {
            $image_url = preg_replace( '/-I\.(jpg|jpeg|png|webp)/i', '-O.$1', $item['thumbnail'] );
        }

        $description_text = '';
        $attrs_html = '';
        if ( ! empty( $item['attributes'] ) && is_array( $item['attributes'] ) ) {
            $attrs_html .= '<table class="shop_attributes" style="width:100%; border-collapse:collapse; margin-bottom:15px; font-size:0.88rem;"><tbody>';
            foreach ( $item['attributes'] as $attr ) {
                if ( ! empty( $attr['name'] ) && ! empty( $attr['value_name'] ) ) {
                    $attrs_html .= sprintf(
                        '<tr style="border-bottom:1px solid #eee;"><th style="text-align:left; padding:6px 8px; font-weight:600; width:35%%; color:#1e293b;">%s</th><td style="padding:6px 8px; color:#475569;">%s</td></tr>',
                        esc_html( $attr['name'] ),
                        esc_html( $attr['value_name'] )
                    );
                }
            }
            $attrs_html .= '</tbody></table>';
        }

        if ( ! empty( $item['id'] ) ) {
            $desc_url = "https://api.mercadolibre.com/items/" . $item['id'] . "/description";
            $desc_res = wp_remote_get( $desc_url, array( 'timeout' => 10, 'user-agent' => 'Mozilla/5.0' ) );
            if ( ! is_wp_error( $desc_res ) && 200 === wp_remote_retrieve_response_code( $desc_res ) ) {
                $desc_data = json_decode( wp_remote_retrieve_body( $desc_res ), true );
                if ( ! empty( $desc_data['plain_text'] ) ) {
                    $description_text = wpautop( esc_html( trim( $desc_data['plain_text'] ) ) );
                }
            }
        }

        $full_description = $attrs_html . $description_text;

        if ( ! empty( $image_url ) || ! empty( $full_description ) ) {
            return array(
                'image_url'   => $image_url,
                'description' => $full_description,
                'source'      => 'Mercado Libre API',
            );
        }

        return false;
    }

    private function execute_multi_layer_search( $term ) {
        // --- LAYER 1: Mercado Libre Official Public API (Fast, structured, rich attributes) ---
        $ml_data = $this->scrape_from_mercadolibre_api( $term );
        if ( $ml_data ) {
            return $ml_data;
        }

        // --- LAYER 2: SP Digital Scraper ---
        if ( class_exists( 'SP_Scraper' ) ) {
            $sp_data = SP_Scraper::get_instance()->scrape_product_data_for_sku( $term );
            if ( ! is_wp_error( $sp_data ) && ( ! empty( $sp_data['image_url'] ) || ! empty( $sp_data['description'] ) ) ) {
                return array(
                    'image_url'   => $sp_data['image_url'] ?? '',
                    'description' => $sp_data['description'] ?? '',
                    'source'      => 'SP Digital',
                );
            }
        }

        // --- LAYER 3: Google Images (Fallback) ---
        $url = "https://www.google.cl/search?tbm=isch&q=" . urlencode( $term );
        $body = $this->fetch_url( $url, 'https://www.google.cl/' );
        if ( ! is_wp_error( $body ) ) {
            $image = $this->extract_image_from_google( $body );
            if ( $image ) {
                return array( 'image_url' => $image, 'description' => '', 'source' => 'Google' );
            }
        }

        // --- LAYER 4: DuckDuckGo HTML Fallback ---
        $url = "https://duckduckgo.com/html/?q=" . urlencode( $term );
        $body = $this->fetch_url( $url );
        if ( ! is_wp_error( $body ) ) {
            if ( preg_match( '/<img[^>]+src="([^">]+gstatic[^">]+)"/i', $body, $matches ) ) {
                return array( 'image_url' => $matches[1], 'description' => '', 'source' => 'DuckDuckGo' );
            }
            if ( preg_match( '/<img[^>]+src="(\/\/external-content[^">]+)"/i', $body, $matches ) ) {
                return array( 'image_url' => 'https:' . $matches[1], 'description' => '', 'source' => 'DuckDuckGo' );
            }
        }

        return new WP_Error( 'no_data_found', 'No se encontraron datos para: ' . $term );
    }

    private function extract_image_from_google( $body ) {
        if ( preg_match( '/(https:\/\/encrypted-tbn[0-9]\.gstatic\.com\/images\?q=[^"\'\s<>]+)/i', $body, $matches ) ) {
            return str_replace( '&amp;', '&', $matches[1] );
        }
        return false;
    }

    /**
     * Custom sideload to use our camouflage
     */
    public function sideload_image( $url, $product_id ) {
        if ( ! $url || ! $product_id ) {
            return false;
        }

        require_once( ABSPATH . 'wp-admin/includes/image.php' );
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        require_once( ABSPATH . 'wp-admin/includes/media.php' );

        $image_data = $this->fetch_url( $url );
        
        if ( is_wp_error( $image_data ) ) {
            $proxy_url = "https://external-content.duckduckgo.com/iu/?u=" . urlencode( $url );
            $image_data = $this->fetch_url( $proxy_url );
        }

        if ( is_wp_error( $image_data ) ) {
            return $image_data;
        }

        $finfo = new finfo( FILEINFO_MIME_TYPE );
        $mime_type = $finfo->buffer( $image_data );
        
        $ext = '.jpg';
        if ( $mime_type === 'image/webp' ) $ext = '.webp';
        elseif ( $mime_type === 'image/png' ) $ext = '.png';
        elseif ( $mime_type === 'image/gif' ) $ext = '.gif';

        $filename = 'gs-' . $product_id . '-' . time() . $ext;
        $upload_file = wp_upload_bits( $filename, null, $image_data );

        if ( ! $upload_file['error'] ) {
            $file_array = array(
                'name'     => $filename,
                'tmp_name' => $upload_file['file'],
            );

            $id = media_handle_sideload( $file_array, $product_id );
            
            if ( is_wp_error( $id ) ) {
                @unlink( $upload_file['file'] );
                return new WP_Error( 'sideload_failed', $id->get_error_message() . ' (Type: ' . $mime_type . ')' );
            }
            return array( 'id' => $id, 'filename' => $filename );
        }

        return new WP_Error( 'upload_error', $upload_file['error'] . ' (Type: ' . $mime_type . ')' );
    }

    /**
     * AJAX Batch Processor
     */
    public function ajax_process_batch() {
        check_ajax_referer( 'gs_scraper_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Unauthorized' );
        }

        @set_time_limit( 180 );

        $batch_size = 1;
        
        global $wpdb;
        $product_ids = $wpdb->get_col( "
            SELECT p.ID 
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm ON (p.ID = pm.post_id AND pm.meta_key = '_thumbnail_id')
            LEFT JOIN {$wpdb->postmeta} pm_done ON (p.ID = pm_done.post_id AND pm_done.meta_key = '_gs_scraper_processed')
            WHERE p.post_type = 'product' 
              AND p.post_status = 'publish'
              AND pm_done.meta_value IS NULL
              AND (pm.meta_value IS NULL OR LENGTH(p.post_content) < 100)
            ORDER BY p.ID DESC
            LIMIT $batch_size
        " );

        if ( empty( $product_ids ) ) {
            wp_send_json_success( array(
                'processed' => 0,
                'results'   => array(),
                'remaining' => 0
            ) );
        }

        $results = array();
        foreach ( $product_ids as $product_id ) {
            $title = get_the_title( $product_id );
            $sku = get_post_meta( $product_id, '_sku', true );
            
            // Mark processed immediately to prevent looping if an image or request fails
            update_post_meta( $product_id, '_gs_scraper_processed', time() );

            $search_term = $title;
            if ( ! empty( $sku ) ) {
                $search_term .= ' ' . $sku;
            }

            $product_data = $this->scrape_product_data( $search_term );

            if ( is_wp_error( $product_data ) ) {
                $results[] = array(
                    'id'      => $product_id,
                    'sku'     => $sku ?: 'N/A',
                    'title'   => $title,
                    'status'  => 'error',
                    'message' => $product_data->get_error_message(),
                );
                continue;
            }

            $success_messages = array();
            
            if ( ! empty( $product_data['image_url'] ) && ! get_post_thumbnail_id( $product_id ) ) {
                $sideload = $this->sideload_image( $product_data['image_url'], $product_id );
                if ( ! is_wp_error( $sideload ) ) {
                    set_post_thumbnail( $product_id, $sideload['id'] );
                    $success_messages[] = 'Imagen cargada';
                }
            }

            if ( ! empty( $product_data['description'] ) ) {
                $current_post = get_post( $product_id );
                $curr_desc = trim( $current_post ? $current_post->post_content : '' );
                if ( empty( $curr_desc ) || strlen( $curr_desc ) < 100 || $curr_desc === $title ) {
                    wp_update_post( array(
                        'ID'           => $product_id,
                        'post_content' => $product_data['description'],
                    ) );
                    $success_messages[] = 'Ficha técnica / Descripción actualizada (' . ($product_data['source'] ?? 'ML API') . ')';
                }
            }

            $results[] = array(
                'id'      => $product_id,
                'sku'     => $sku ?: 'N/A',
                'status'  => 'success',
                'message' => ! empty( $success_messages ) ? implode( ', ', $success_messages ) : 'Revisado (sin cambios)',
            );
        }

        wp_send_json_success( array(
            'processed' => count( $results ),
            'results'   => $results,
            'remaining' => $this->get_remaining_count(),
        ) );
    }

    public function get_remaining_count() {
        global $wpdb;
        $count = $wpdb->get_var( "
            SELECT COUNT(p.ID) 
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm ON (p.ID = pm.post_id AND pm.meta_key = '_thumbnail_id')
            LEFT JOIN {$wpdb->postmeta} pm_done ON (p.ID = pm_done.post_id AND pm_done.meta_key = '_gs_scraper_processed')
            WHERE p.post_type = 'product' 
              AND p.post_status = 'publish'
              AND pm_done.meta_value IS NULL
              AND (pm.meta_value IS NULL OR LENGTH(p.post_content) < 100)
        " );
        return intval( $count );
    }
}
