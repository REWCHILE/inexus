<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Ingram_Woo_API {
    private static $instance = null;
    private $token = '';
    private $base_url = '';
    private $token_url = 'https://api.ingrammicro.com/oauth/oauth30/token';

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // maybe load cached token
        $this->token = get_option( 'ingram_woo_access_token', '' );
        
        $opts = get_option( 'ingram_woo_options', array() );
        $env = isset( $opts['environment'] ) ? $opts['environment'] : 'sandbox';
        
        if ( $env === 'sandbox' ) {
            $this->base_url = 'https://api.ingrammicro.com/sandbox/resellers/v6';
        } else {
            $this->base_url = 'https://api.ingrammicro.com/resellers/v6';
        }
        
        // Register AJAX handlers for preview tab
        add_action( 'wp_ajax_ingram_ajax_preview_products', array( $this, 'ajax_preview_products' ) );
        add_action( 'wp_ajax_ingram_ajax_preview_categories', array( $this, 'ajax_preview_categories' ) );
        add_action( 'wp_ajax_ingram_ajax_discover_categories', array( $this, 'ajax_discover_categories' ) );
        add_action( 'wp_ajax_ingram_ajax_run_diagnostic', array( $this, 'ajax_run_diagnostic' ) );
    }

    /**
     * Retrieve OAuth2 token using client credentials grant.
     */
    public function authenticate() {
        $opts = get_option( 'ingram_woo_options', array() );
        if ( empty( $opts['client_id'] ) || empty( $opts['client_secret'] ) ) {
            return new WP_Error( 'missing_credentials', __( 'API credentials are not set.', 'ingram-woo' ) );
        }

        $response = wp_remote_post( $this->token_url, array(
            'body' => array(
                'grant_type' => 'client_credentials',
                'client_id' => $opts['client_id'],
                'client_secret' => $opts['client_secret'],
            ),
            'timeout' => 20,
        ) );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        if ( 200 !== $code ) {
            return new WP_Error( 'auth_failed', 'Authentication failed (' . $code . ')' );
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( isset( $body['access_token'] ) ) {
            $this->token = $body['access_token'];
            update_option( 'ingram_woo_access_token', $this->token );
            return $this->token;
        }

        return new WP_Error( 'no_token', 'Token not present in response' );
    }

    /**
     * Generic GET request helper
     */
    public function get( $endpoint, $args = array() ) {
        return $this->request( 'GET', $endpoint, array( 'body' => $args ) );
    }

    /**
     * Generic POST request helper
     */
    public function post( $endpoint, $body = array(), $query = array() ) {
        return $this->request( 'POST', $endpoint, array( 'body' => wp_json_encode( $body ), 'headers' => array( 'Content-Type' => 'application/json' ) ), $query );
    }

    private function request( $method, $endpoint, $args = array(), $query = array() ) {
        if ( empty( $this->token ) ) {
            $auth = $this->authenticate();
            if ( is_wp_error( $auth ) ) {
                return $auth;
            }
        }

        $opts = get_option( 'ingram_woo_options', array() );
        $customer_no = isset( $opts['customer_number'] ) ? trim($opts['customer_number']) : '';
        $country_code = (isset( $opts['country_code'] ) && !empty(trim($opts['country_code']))) ? trim($opts['country_code']) : 'CL';
        $env = isset( $opts['environment'] ) ? $opts['environment'] : 'sandbox';

        if ( $env === 'production' && empty($customer_no) ) {
            return new WP_Error( 'missing_headers', sprintf( 
                'Error: Falta el Customer Number. Detectado: [%s]. Por favor, llénalo y pulsa "Save Changes".', 
                $customer_no
            ) );
        }

        $url = trailingslashit( $this->base_url ) . ltrim( $endpoint, '/' );
        if ( ! empty( $query ) ) {
            $url = add_query_arg( $query, $url );
        }

        $response = $this->do_http_request( $method, $url, $args );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        
        // If 401 Unauthorized, token might be expired. Clear and retry once.
        if ( 401 === $code || 403 === $code ) {
            delete_option( 'ingram_woo_access_token' );
            $this->token = '';
            
            $auth = $this->authenticate();
            if ( is_wp_error( $auth ) ) {
                return $auth;
            }
            
            // Re-attempt request with new token
            $response = $this->do_http_request( $method, $url, $args );
            
            if ( is_wp_error( $response ) ) {
                return $response;
            }
            $code = wp_remote_retrieve_response_code( $response );
        }

        $body = wp_remote_retrieve_body( $response );
        $decoded = json_decode( $body, true );

        // Enhanced 403 handling
        if ( 403 === $code ) {
            $fault = isset( $decoded['faultstring'] ) ? $decoded['faultstring'] : 'Forbidden';
            $error_msg = "API Error 403: $fault. ";
            if ( strpos( $fault, 'exist' ) !== false ) {
                $error_msg .= "Esto suele significar que tu Client ID no tiene permisos para el API de 'Catalog' o 'Search' en el portal de Ingram.";
            }
            return new WP_Error( 'api_403', $error_msg, array( 'body' => $decoded, 'url' => $url ) );
        }

        return array( 'code' => $code, 'body' => $decoded, 'raw' => $body );
    }

    private function do_http_request( $method, $url, $args ) {
        $opts = get_option( 'ingram_woo_options', array() );
        $default_headers = array(
            'Authorization' => 'Bearer ' . $this->token,
            'IM-CustomerNumber' => isset( $opts['customer_number'] ) ? trim($opts['customer_number']) : '',
            'IM-CountryCode'   => (isset( $opts['country_code'] ) && !empty(trim($opts['country_code']))) ? trim($opts['country_code']) : 'CL',
            'IM-CorrelationID' => substr( str_replace( '-', '', wp_generate_uuid4() ), 0, 32 ),
            'Accept' => 'application/json',
        );

        $args = wp_parse_args( $args, array(
            'method'  => $method,
            'timeout' => 30,
        ) );

        $args['headers'] = wp_parse_args( 
            isset( $args['headers'] ) ? $args['headers'] : array(), 
            $default_headers 
        );

        if ( isset( $args['body'] ) && is_array( $args['body'] ) && 'GET' === $method ) {
             $url = add_query_arg( $args['body'], $url );
             unset($args['body']);
        }

        // Debug logging
        $log_file = dirname(__FILE__) . '/debug_api.log';
        $log_entry = "[" . date('Y-m-d H:i:s') . "] REQUEST: " . $method . " " . $url . "\n";
        $log_entry .= "HEADERS: " . print_r($args['headers'], true) . "\n";
        if (isset($args['body'])) $log_entry .= "BODY: " . (is_string($args['body']) ? $args['body'] : print_r($args['body'], true)) . "\n";
        file_put_contents($log_file, $log_entry, FILE_APPEND);

        $response = wp_remote_request( $url, $args );
        
        $code = wp_remote_retrieve_response_code( $response );
        $body = wp_remote_retrieve_body( $response );
        $log_entry = "RESPONSE CODE: " . $code . "\n";
        $log_entry .= "RESPONSE BODY: " . $body . "\n\n";
        file_put_contents($log_file, $log_entry, FILE_APPEND);

        return $response;
    }

    /*
     * Convenience wrappers for endpoints we care about
     */
    public function search_products( $params ) {
        return $this->get( 'catalog', $params );
    }

    /**
     * Fetch full product details including mediaLinks (Images).
     */
    public function get_product_details( $sku ) {
        return $this->get( 'catalog/details/' . $sku );
    }

    /**
     * Fetch real-time price and availability for a list of SKUs.
     * Batch size up to 50.
     */
    public function get_price_and_availability( $skus ) {
        $items = array();
        foreach ( $skus as $sku ) {
            $items[] = array( 'ingramPartNumber' => $sku );
        }

        return $this->post( 'catalog/priceandavailability', array(
            'products' => $items
        ), array(
            'includeAvailability' => 'true',
            'includePricing'     => 'true'
        ) );
    }

    public function price_and_availability( $product_list ) {
        $body = array( 'products' => $product_list );
        return $this->post( 'catalog/priceandavailability', $body );
    }

    public function product_details( $ingramPartNumber ) {
        return $this->get( "catalog/details/{$ingramPartNumber}" );
    }

    /*
     * AJAX Handlers for Data Preview Tab
     */
    public function ajax_preview_products() {
        check_ajax_referer( 'ingram_preview_nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( array( 'message' => 'Unauthorized' ) );

        // Request 10 products
        $params = array( 'pageSize' => 10 );
        $response = $this->search_products( $params );

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( array( 'message' => $response->get_error_message() ) );
        }

        if ( 200 !== $response['code'] ) {
            wp_send_json_error( array( 'message' => 'API Error: HTTP ' . $response['code'] . ' - ' . wp_strip_all_tags( json_encode( $response['body'] ) ) ) );
        }

        $catalog = isset( $response['body']['catalog'] ) ? $response['body']['catalog'] : array();
        $total_records = isset( $response['body']['recordsFound'] ) ? $response['body']['recordsFound'] : count($catalog);

        if ( empty( $catalog ) ) {
            wp_send_json_success( array( 'html' => '<p>No products found or connection failed.</p>' ) );
        }

        $html = '<div style="margin-bottom: 15px; font-weight: 600; color: #0f172a;">' . sprintf( esc_html__( 'Total de productos en API: %d', 'ingram-woo' ), $total_records ) . '</div>';
        $html .= '<table class="wp-list-table widefat fixed striped">';
        $html .= '<thead><tr><th>SKU</th><th>Descripción</th><th>Análisis de Datos (FOTOS/PRECIOS)</th></tr></thead><tbody>';
        
        foreach ( $catalog as $item ) {
            $sku = isset( $item['ingramPartNumber'] ) ? esc_html( $item['ingramPartNumber'] ) : '-';
            $desc = isset( $item['description'] ) ? esc_html( $item['description'] ) : '-';
            
            // Data Mining Logic
            $findings = array();
            $raw_string = wp_json_encode($item);

            // Look for images
            if (preg_match_all('/"([^"]+)":"(https?:\/\/[^"]+\.(?:jpg|png|gif|jpeg)[^"]*)"/i', $raw_string, $matches)) {
                foreach($matches[1] as $idx => $key) {
                    $findings[] = '<span style="color:#059669;">📸 Foto en campo <b>[' . $key . ']</b></span>';
                }
            }

            // Projection Logic
            $projected_price = '';
            $base_price = 0;
            if ( ! empty( $item['pricing']['customerPrice'] ) ) {
                $base_price = floatval( $item['pricing']['customerPrice'] );
            } elseif ( ! empty( $item['pricing']['netPrice'] ) ) {
                $base_price = floatval( $item['pricing']['netPrice'] );
            }

            if ( $base_price > 0 ) {
                $opts   = get_option( 'ingram_woo_options', array() );
                $margin = isset( $opts['price_margin'] ) ? floatval( $opts['price_margin'] ) : 0;
                $rate   = isset( $opts['usd_exchange_rate'] ) ? floatval( $opts['usd_exchange_rate'] ) : 1;
                $final  = $base_price * $rate * ( 1 + ( $margin / 100 ) );
                $projected_price = sprintf( 
                    '<div style="margin-top:8px; padding:8px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:4px; font-size:11px;">
                        <b>Proyección de Precio:</b><br/>
                        USD %s &times; Tasa %s = %s CLP<br/>
                        <b>Final (+%s%%): %s CLP</b>
                    </div>',
                    number_format($base_price, 2),
                    number_format($rate, 2),
                    number_format($base_price * $rate, 0, ',', '.'),
                    $margin,
                    number_format($final, 0, ',', '.')
                );
            }

            if (empty($findings)) $findings[] = '<span style="color:#ef4444;">❌ No se detectaron fotos ni precios en los datos base.</span>';
            if ($projected_price) $findings[] = $projected_price;

            $preview_json = strlen($raw_string) > 500 ? substr($raw_string, 0, 500) . '...' : $raw_string;

            $html .= sprintf( '<tr><td>%s</td><td>%s</td><td>%s<br/><details><summary style="font-size:10px; cursor:pointer; color:#94a3b8;">Ver JSON Raw</summary><code style="font-size:10px; word-break: break-all;">%s</code></details></td></tr>', 
                $sku, 
                $desc, 
                implode('<br/>', $findings),
                esc_html($preview_json) 
            );
        }
        
        $html .= '</tbody></table>';
        wp_send_json_success( array( 'html' => $html ) );
    }

    public function ajax_preview_categories() {
        check_ajax_referer( 'ingram_preview_nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( array( 'message' => 'Unauthorized' ) );

        // To test categories, let's fetch a list of products and extract distinct categories.
        // Or if Ingram has a specific categories endpoint, hit that. (Assuming from search catalog for now)
        $params = array( 'pageSize' => 50 ); // get more to have variety
        $response = $this->search_products( $params );

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( array( 'message' => $response->get_error_message() ) );
        }

        if ( 200 !== $response['code'] ) {
            wp_send_json_error( array( 'message' => 'API Error: HTTP ' . $response['code'] . ' - ' . wp_strip_all_tags( json_encode( $response['body'] ) ) ) );
        }

        $catalog = isset( $response['body']['catalog'] ) ? $response['body']['catalog'] : array();
        
        $categories = array();
        foreach ( $catalog as $item ) {
            if ( ! empty( $item['category'] ) ) {
                $categories[ $item['category'] ] = isset( $item['subCategory'] ) ? $item['subCategory'] : 'No subcategory';
            }
        }

        if ( empty( $categories ) ) {
            wp_send_json_success( array( 'html' => '<p>No categories found in the sample data.</p>' ) );
        }

        $html = '<table class="wp-list-table widefat fixed striped">';
        $html .= '<thead><tr><th>Category (Parent)</th><th>Sample SubCategory</th><th style="width:150px;">Action</th></tr></thead><tbody>';
        
        foreach ( $categories as $cat => $sub ) {
            // Get count for this category
            $cat_response = $this->search_products( array( 'category' => $cat, 'pageSize' => 1 ) );
            $count = ( ! is_wp_error( $cat_response ) && isset( $cat_response['body']['recordsFound'] ) ) ? $cat_response['body']['recordsFound'] : '?';
            
            $btn = sprintf( '<button type="button" class="button button-small btn-import-category" data-cat="%s">Import 5 Samples</button>', esc_attr( $cat ) );
            $html .= sprintf( '<tr><td>%s <strong>(%s)</strong></td><td>%s</td><td>%s</td></tr>', esc_html( $cat ), esc_html( $count ), esc_html( $sub ), $btn );
        }
        
        $html .= '</tbody></table>';
        
        $summary = '<div style="margin-bottom: 15px; padding: 10px; background: #eff6ff; border-radius: 6px; color: #1e40af; font-weight: 500;">';
        $summary .= sprintf( esc_html__( 'Detected %d unique categories in a sample of 50 products.', 'ingram-woo' ), count($categories) );
        $summary .= '</div>';
        
        wp_send_json_success( array( 'html' => $summary . $html ) );
    }

    public function ajax_run_diagnostic() {
        check_ajax_referer( 'ingram_preview_nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( array( 'message' => 'Unauthorized' ) );

        @set_time_limit(120);
        $opts = get_option( 'ingram_woo_options', array() );
        $env = isset( $opts['environment'] ) ? $opts['environment'] : 'sandbox';

        $html = "<h3>🔬 Deep Diagnostics Report — Ingram Micro API V6</h3>";
        $html .= "<strong>Environment:</strong> " . esc_html( $env ) . "<br/>";
        $html .= "<strong>Base URL:</strong> " . esc_html( $this->base_url ) . "<br/><br/>";

        // --- TEST 1: Authentication ---
        $html .= "<h4>1. 🔑 Authentication Test</h4>";
        delete_option( 'ingram_woo_access_token' );
        $this->token = '';
        
        $auth = $this->authenticate();
        if ( is_wp_error( $auth ) ) {
            $html .= "<div style='color:red;'><strong>Auth Failed:</strong> " . esc_html( $auth->get_error_message() ) . "</div>";
            wp_send_json_success( array( 'html' => $html ) );
            return;
        }
        $html .= "<div style='color:green;'><strong>✅ Auth Success!</strong> Token: " . esc_html( substr( $this->token, 0, 15 ) ) . "...</div>";

        // --- TEST 1.5: Endpoint Discovery ---
        $html .= "<h4>1.5 🔍 Endpoint Discovery Test</h4>";
        $test_urls = array(
            'V6 Standard' => 'catalog',
            'V6 Capitalized' => 'Catalog',
            'V6 Chile Prefix' => 'cl/catalog',
            'V5 Standard' => '../../v5/Catalog',
        );

        foreach ( $test_urls as $label => $endpoint ) {
            $check_url = trailingslashit( $this->base_url ) . $endpoint;
            $res = $this->do_http_request( 'GET', $check_url, array( 'body' => array( 'pageSize' => 1 ) ) );
            $code = wp_remote_retrieve_response_code( $res );
            $status_color = ( 200 === $code ) ? 'green' : ( ( 404 === $code ) ? '#94a3b8' : 'red' );
            $html .= "Path <code>$endpoint</code>: <span style='color:$status_color;'><strong>$code</strong></span><br/>";
        }
        $html .= "<br/>";

        // --- TEST 2: Catalog Search (get 1 product to use as test subject) ---
        $html .= "<h4>2. 📦 Catalog Search Response</h4>";
        $test_sku = '';
        $first_product = array();

        // Try 1: Search with a known category (production API requires category param)
        $search_attempts = array(
            array( 'pageSize' => 1 ),
            array( 'pageSize' => 1, 'category' => 'Componentes de Sistema' ),
            array( 'pageSize' => 1, 'category' => 'Networking' ),
        );

        foreach ( $search_attempts as $attempt_params ) {
            $catalog_response = $this->search_products( $attempt_params );
            
            if ( ! is_wp_error( $catalog_response ) && 200 === $catalog_response['code'] ) {
                $catalog = isset( $catalog_response['body']['catalog'] ) ? $catalog_response['body']['catalog'] : array();
                if ( ! empty( $catalog[0] ) ) {
                    $first_product = $catalog[0];
                    $test_sku = $first_product['ingramPartNumber'] ?? '';
                    $html .= "<div style='color:green;'>✅ Catalog found with params: <code>" . esc_html( json_encode( $attempt_params ) ) . "</code></div>";
                    break;
                }
            }
        }

        // Fallback: Grab a SKU from already-imported WooCommerce products
        if ( empty( $test_sku ) ) {
            $html .= "<div style='background:#fef3c7; padding:10px; border-radius:8px; border:1px solid #fde68a; margin:10px 0;'>";
            $html .= "⚠️ Catalog search returned 404 (production API requires category). Using an existing WooCommerce product SKU for P&A and Details tests.";
            $html .= "</div>";

            // Get any WooCommerce product that has a SKU
            $woo_products = get_posts( array(
                'post_type'   => 'product',
                'post_status' => 'any',
                'numberposts' => 1,
                'meta_query'  => array( array( 'key' => '_sku', 'compare' => '!=', 'value' => '' ) )
            ) );
            if ( ! empty( $woo_products ) ) {
                $test_sku = get_post_meta( $woo_products[0]->ID, '_sku', true );
                $html .= "<div style='color:green;'>✅ Found WooCommerce product SKU: <code>" . esc_html( $test_sku ) . "</code> (" . esc_html( $woo_products[0]->post_title ) . ")</div>";
            }
        }

        if ( ! empty( $first_product ) ) {
            $html .= "<strong>Test SKU:</strong> <code>" . esc_html( $test_sku ) . "</code><br/>";
            $html .= "<strong>Product Name:</strong> " . esc_html( $first_product['description'] ?? 'N/A' ) . "<br/><br/>";
            
            $html .= "<div style='background:#f0fdf4; padding:12px; border-radius:8px; border:1px solid #bbf7d0; margin-bottom:15px;'>";
            $html .= "<strong>📋 Top-level keys in catalog item:</strong><br/>";
            $html .= "<code>" . esc_html( implode( ', ', array_keys( $first_product ) ) ) . "</code>";
            $html .= "</div>";

            $html .= "<details><summary style='cursor:pointer; font-weight:600; color:#2563eb;'>📄 Full Catalog Item JSON (click to expand)</summary>";
            $html .= "<pre style='background:#1e293b; color:#e2e8f0; padding:15px; border-radius:8px; max-height:400px; overflow:auto; font-size:12px;'>" . esc_html( json_encode( $first_product, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE ) ) . "</pre>";
            $html .= "</details><br/>";
        }

        if ( empty( $test_sku ) ) {
            $html .= "<div style='background:#fee2e2; padding:15px; border-radius:10px; border:1px solid #fecaca; margin:15px 0; color:#991b1b;'>";
            $html .= "<strong>❌ Error de Contexto: No hay productos para probar.</strong><br/><br/>";
            $html .= "La API de producción de Ingram Chile no permite búsquedas globales (Error 404). <br/><br/>";
            $html .= "<strong>Siguiente paso recomendado:</strong><br/>";
            $html .= "1. Cierra este reporte.<br/>";
            $html .= "2. Haz clic en el botón <b>'Inspect API Categories'</b>.<br/>";
            $html .= "3. Importa al menos 1 producto de cualquier categoría.<br/>";
            $html .= "4. Vuelve a correr este diagnóstico.";
            $html .= "</div>";
            wp_send_json_success( array( 'html' => $html ) );
            return;
        }

        // --- TEST 3: Price & Availability for the test SKU ---
        $html .= "<h4>3. 💰 Price & Availability Response (SKU: " . esc_html( $test_sku ) . ")</h4>";
        $pa_response = $this->get_price_and_availability( array( $test_sku ) );

        if ( is_wp_error( $pa_response ) ) {
            $html .= "<div style='color:red;'>P&A Failed: " . esc_html( $pa_response->get_error_message() ) . "</div>";
        } else {
            $html .= "<strong>HTTP Code:</strong> " . intval( $pa_response['code'] ) . "<br/>";
            
            $pa_body = isset( $pa_response['body'] ) ? $pa_response['body'] : array();
            
            // Detect structure: is it an array of products or keyed under something?
            if ( isset( $pa_body[0] ) ) {
                $html .= "<div style='background:#eff6ff; padding:10px; border-radius:8px; border:1px solid #bfdbfe; margin:10px 0;'>";
                $html .= "📊 <strong>Structure:</strong> Direct array (body[0] exists). Keys in first element: <code>" . esc_html( implode( ', ', array_keys( $pa_body[0] ) ) ) . "</code>";
                $html .= "</div>";
            } elseif ( isset( $pa_body['results'] ) ) {
                $html .= "<div style='background:#eff6ff; padding:10px; border-radius:8px; border:1px solid #bfdbfe; margin:10px 0;'>";
                $html .= "📊 <strong>Structure:</strong> Nested under 'results' key.";
                if ( ! empty( $pa_body['results'][0] ) ) {
                    $html .= " Keys: <code>" . esc_html( implode( ', ', array_keys( $pa_body['results'][0] ) ) ) . "</code>";
                }
                $html .= "</div>";
            } else {
                $html .= "<div style='background:#fef3c7; padding:10px; border-radius:8px; border:1px solid #fde68a; margin:10px 0;'>";
                $html .= "⚠️ <strong>Structure:</strong> Top-level keys: <code>" . esc_html( implode( ', ', array_keys( $pa_body ) ) ) . "</code>";
                $html .= "</div>";
            }
            
            // Show full raw JSON
            $html .= "<details open><summary style='cursor:pointer; font-weight:600; color:#2563eb;'>📄 Full P&A Response JSON</summary>";
            $html .= "<pre style='background:#1e293b; color:#e2e8f0; padding:15px; border-radius:8px; max-height:400px; overflow:auto; font-size:12px;'>" . esc_html( json_encode( $pa_body, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE ) ) . "</pre>";
            $html .= "</details><br/>";
        }

        // --- TEST 4: Product Details (for images) ---
        $html .= "<h4>4. 🖼️ Product Details Response (SKU: " . esc_html( $test_sku ) . ")</h4>";
        $details_response = $this->get_product_details( $test_sku );

        if ( is_wp_error( $details_response ) ) {
            $html .= "<div style='color:red;'>Details Failed: " . esc_html( $details_response->get_error_message() ) . "</div>";
        } else {
            $html .= "<strong>HTTP Code:</strong> " . intval( $details_response['code'] ) . "<br/>";
            
            $details_body = isset( $details_response['body'] ) ? $details_response['body'] : array();
            
            // Highlight image-related keys
            $image_keys = array();
            $this->find_keys_recursive( $details_body, array('image', 'media', 'photo', 'thumb', 'url', 'link'), $image_keys );
            
            if ( ! empty( $image_keys ) ) {
                $html .= "<div style='background:#f0fdf4; padding:10px; border-radius:8px; border:1px solid #bbf7d0; margin:10px 0;'>";
                $html .= "🖼️ <strong>Image-related paths found:</strong><br/>";
                foreach ( $image_keys as $path => $val ) {
                    $display_val = is_string( $val ) ? ( strlen( $val ) > 80 ? substr( $val, 0, 80 ) . '...' : $val ) : gettype( $val );
                    $html .= "<code>" . esc_html( $path ) . "</code> = <span style='color:#059669;'>" . esc_html( $display_val ) . "</span><br/>";
                }
                $html .= "</div>";
            } else {
                $html .= "<div style='background:#fef2f2; padding:10px; border-radius:8px; border:1px solid #fecaca; margin:10px 0;'>";
                $html .= "❌ <strong>No image-related keys found in details response.</strong>";
                $html .= "</div>";
            }
            
            // Top-level keys
            $html .= "<div style='background:#eff6ff; padding:10px; border-radius:8px; border:1px solid #bfdbfe; margin:10px 0;'>";
            $html .= "📋 <strong>Top-level keys:</strong> <code>" . esc_html( implode( ', ', array_keys( $details_body ) ) ) . "</code>";
            $html .= "</div>";
            
            // Show full raw JSON
            $html .= "<details open><summary style='cursor:pointer; font-weight:600; color:#2563eb;'>📄 Full Details Response JSON</summary>";
            $html .= "<pre style='background:#1e293b; color:#e2e8f0; padding:15px; border-radius:8px; max-height:400px; overflow:auto; font-size:12px;'>" . esc_html( json_encode( $details_body, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE ) ) . "</pre>";
            $html .= "</details>";
        }
        // --- TEST 5: Cross-Region Image Discovery ---
        $vendor_pn = '';
        if ( ! empty( $first_product['vendorPartNumber'] ) ) {
            $vendor_pn = $first_product['vendorPartNumber'];
        }

        $html .= "<h4>5. 🌍 Cross-Region Image Discovery (Smart Discovery)</h4>";
        $html .= "<p style='font-size:0.85rem; color:#64748b;'>Probando descubrimiento automático para VPN: <code>" . esc_html( $vendor_pn ) . "</code></p>";

        $sandbox_client_id     = isset( $opts['sandbox_client_id'] ) ? $opts['sandbox_client_id'] : '';
        $sandbox_client_secret = isset( $opts['sandbox_client_secret'] ) ? $opts['sandbox_client_secret'] : '';

        if ( empty( $sandbox_client_id ) || empty( $sandbox_client_secret ) ) {
            $html .= "<div style='background:#fef3c7; padding:12px; border-radius:8px; border:1px solid #fde68a;'>";
            $html .= "⚠️ <strong>Sandbox credentials not configured.</strong> Add them in the Settings tab to enable this feature.";
            $html .= "</div>";
        } else {
            $crid_urls = $this->discover_images_cross_region( $vendor_pn );

            if ( ! empty( $crid_urls ) ) {
                $html .= "<div style='background:#f0fdf4; padding:15px; border-radius:10px; border:1px solid #bbf7d0; margin-top:10px;'>";
                $html .= "<span style='color:#059669; font-weight:700;'>✅ ¡ÉXITO! Se encontraron " . count($crid_urls) . " imágenes en el Sandbox US.</span><br/>";
                
                foreach ( $crid_urls as $u ) {
                    $html .= "<div style='margin-top:10px;'>";
                    $html .= "<img src='" . esc_url( $u ) . "' style='max-width:180px; max-height:180px; border-radius:8px; border:1px solid #059669; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);' />";
                    $html .= "<div style='font-size:0.7rem; color:#64748b; margin-top:4px; word-break: break-all;'>" . esc_html($u) . "</div>";
                    $html .= "</div>";
                    break; // Just show one in diagnostic
                }
                $html .= "</div>";
            } else {
                $html .= "<div style='background:#fef2f2; padding:15px; border-radius:10px; border:1px solid #fecaca; margin-top:10px;'>";
                $html .= "❌ <strong>Fallo en el descubrimiento.</strong> Revisa las credenciales de Sandbox o si el VPN es válido en el catálogo global.";
                $html .= "</div>";
            }
        }

        wp_send_json_success( array( 'html' => $html ) );
    }

    /**
     * Recursively find keys in a nested array that match any of the search terms.
     */
    private function find_keys_recursive( $data, $search_terms, &$results, $prefix = '' ) {
        if ( ! is_array( $data ) ) return;
        foreach ( $data as $key => $value ) {
            $path = $prefix ? $prefix . '.' . $key : $key;
            $key_lower = strtolower( (string) $key );
            
            $matched = false;
            foreach ( $search_terms as $term ) {
                if ( strpos( $key_lower, $term ) !== false ) {
                    $matched = true;
                    break;
                }
            }
            
            if ( $matched && ! is_array( $value ) ) {
                $results[ $path ] = $value;
            }
            
            if ( is_array( $value ) ) {
                $this->find_keys_recursive( $value, $search_terms, $results, $path );
            }
        }
    }

    /**
     * Advanced Category Discovery
     * Scans the catalog to build a category tree, then queries each category
     * individually to get REAL product counts from the Ingram API.
     */
    public function ajax_discover_categories() {
        check_ajax_referer( 'ingram_preview_nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( array( 'message' => 'Unauthorized' ) );

        @set_time_limit(300);

        // Phase 1: Discover unique categories/subcategories from a sample scan
        $max_pages = 5;
        $tree = array();
        $total_api_records = 0;

        for ( $i = 1; $i <= $max_pages; $i++ ) {
            $response = $this->search_products( array(
                'pageNumber' => $i,
                'pageSize'   => 100
            ) );

            if ( is_wp_error( $response ) || 200 !== $response['code'] ) {
                // Production API may require category — try known categories
                break;
            }

            $catalog = isset( $response['body']['catalog'] ) ? $response['body']['catalog'] : array();
            if ( empty( $catalog ) ) break;

            if ( $i === 1 && isset( $response['body']['recordsFound'] ) ) {
                $total_api_records = intval( $response['body']['recordsFound'] );
            }

            foreach ( $catalog as $item ) {
                $cat = isset( $item['category'] ) ? trim( $item['category'] ) : '';
                $sub = isset( $item['subCategory'] ) ? trim( $item['subCategory'] ) : '';

                if ( empty( $cat ) ) continue;

                if ( ! isset( $tree[ $cat ] ) ) {
                    $tree[ $cat ] = array( 'count' => 0, 'subs' => array() );
                }

                if ( ! empty( $sub ) ) {
                    if ( ! isset( $tree[ $cat ]['subs'][ $sub ] ) ) {
                        $tree[ $cat ]['subs'][ $sub ] = 0;
                    }
                    $tree[ $cat ]['subs'][ $sub ]++;
                }
            }
        }

        // If the generic scan failed (404), try with known broad categories
        if ( empty( $tree ) ) {
            $known_cats = array( 'Componentes de Sistema', 'Computadores Servidores y Notebooks', 'Networking', 'Software', 'Almacenamiento', 'Impresion', 'Accesorios' );
            foreach ( $known_cats as $cat_name ) {
                $resp = $this->search_products( array( 'category' => $cat_name, 'pageSize' => 100 ) );
                if ( ! is_wp_error( $resp ) && 200 === $resp['code'] && ! empty( $resp['body']['catalog'] ) ) {
                    $tree[ $cat_name ] = array( 'count' => 0, 'subs' => array() );
                    foreach ( $resp['body']['catalog'] as $item ) {
                        $sub = isset( $item['subCategory'] ) ? trim( $item['subCategory'] ) : '';
                        if ( ! empty( $sub ) ) {
                            if ( ! isset( $tree[ $cat_name ]['subs'][ $sub ] ) ) {
                                $tree[ $cat_name ]['subs'][ $sub ] = 0;
                            }
                            $tree[ $cat_name ]['subs'][ $sub ]++;
                        }
                    }
                }
            }
        }

        if ( empty( $tree ) ) {
            wp_send_json_error( array( 'message' => 'No categories discovered. Check connection or catalog visibility.' ) );
        }

        // Phase 2: Get REAL counts for top-level categories and subcategories (with time safety)
        $phase2_start = time();
        foreach ( $tree as $cat_name => &$cat_data ) {
            // 1. Parent category real count
            $cat_resp = $this->search_products( array( 'category' => $cat_name, 'pageSize' => 1 ) );
            if ( ! is_wp_error( $cat_resp ) && 200 === $cat_resp['code'] && isset( $cat_resp['body']['recordsFound'] ) ) {
                $cat_data['count'] = intval( $cat_resp['body']['recordsFound'] );
            }

            // 2. Subcategory real counts (stop if we exceed 30 seconds to avoid hanging)
            foreach ( $cat_data['subs'] as $sub_name => &$sub_count ) {
                if ( (time() - $phase2_start) > 30 ) break 2;

                $sub_resp = $this->search_products( array(
                    'category'    => $cat_name,
                    'subCategory' => $sub_name,
                    'pageSize'    => 1
                ) );
                if ( ! is_wp_error( $sub_resp ) && 200 === $sub_resp['code'] && isset( $sub_resp['body']['recordsFound'] ) ) {
                    $sub_count = intval( $sub_resp['body']['recordsFound'] );
                }
            }
        }
        unset( $cat_data, $sub_count );

        // Sort alphabetically
        ksort( $tree );
        foreach ( $tree as &$data ) {
            ksort( $data['subs'] );
        }
        unset( $data );

        wp_send_json_success( array( 
            'categories'   => $tree,
            'totalRecords' => $total_api_records
        ) );
    }

    /**
     * Smart Cross-Region Image Discovery (CRID)
     * Uses sandbox credentials to attempt fetching images from the US catalog
     * using the Vendor Part Number (VPN).
     */
    public function discover_images_cross_region( $vendor_pn ) {
        if ( empty( $vendor_pn ) ) return array();

        $opts = get_option( 'ingram_woo_options', array() );
        $client_id     = isset( $opts['sandbox_client_id'] ) ? $opts['sandbox_client_id'] : '';
        $client_secret = isset( $opts['sandbox_client_secret'] ) ? $opts['sandbox_client_secret'] : '';
        $customer_no   = isset( $opts['customer_number'] ) ? $opts['customer_number'] : '';

        if ( empty( $client_id ) || empty( $client_secret ) ) return array();

        // 1. Authenticate with Sandbox
        $auth_resp = wp_remote_post( $this->token_url, array(
            'body' => array(
                'grant_type'    => 'client_credentials',
                'client_id'     => $client_id,
                'client_secret' => $client_secret,
            ),
            'timeout' => 15,
        ) );

        if ( is_wp_error( $auth_resp ) || 200 !== wp_remote_retrieve_response_code( $auth_resp ) ) return array();
        
        $auth_body = json_decode( wp_remote_retrieve_body( $auth_resp ), true );
        $token = $auth_body['access_token'] ?? '';
        if ( empty( $token ) ) return array();

        $sandbox_base = 'https://api.ingrammicro.com/sandbox/resellers/v6';
        
        // 2. Search for the VPN in US Sandbox
        $search_url = $sandbox_base . '/catalog?' . http_build_query( array(
            'vendorPartNumber' => $vendor_pn,
            'pageSize'         => 1,
        ) );

        $search_resp = wp_remote_get( $search_url, array(
            'headers' => array(
                'Authorization'     => 'Bearer ' . $token,
                'IM-CustomerNumber' => '20-213702', // Standard sandbox customer for US
                'IM-CountryCode'    => 'US',
                'IM-CorrelationID'  => substr( str_replace( '-', '', wp_generate_uuid4() ), 0, 32 ),
                'Accept'            => 'application/json',
            ),
            'timeout' => 15,
        ) );

        if ( is_wp_error( $search_resp ) || 200 !== wp_remote_retrieve_response_code( $search_resp ) ) {
            // Re-try with production customer number if different
            if ($customer_no && $customer_no !== '20-213702') {
                 $search_resp = wp_remote_get( $search_url, array(
                    'headers' => array(
                        'Authorization'     => 'Bearer ' . $token,
                        'IM-CustomerNumber' => $customer_no,
                        'IM-CountryCode'    => 'US',
                        'IM-CorrelationID'  => substr( str_replace( '-', '', wp_generate_uuid4() ), 0, 32 ),
                        'Accept'            => 'application/json',
                    ),
                    'timeout' => 15,
                ) );
            }
        }

        if ( is_wp_error( $search_resp ) ) return array();
        $s_body = json_decode( wp_remote_retrieve_body( $search_resp ), true );
        $sandbox_sku = $s_body['catalog'][0]['ingramPartNumber'] ?? '';
        
        if ( empty( $sandbox_sku ) ) return array();

        // 3. Get Details from Sandbox
        $det_url = $sandbox_base . '/catalog/details/' . urlencode( $sandbox_sku );
        $det_resp = wp_remote_get( $det_url, array(
            'headers' => array(
                'Authorization'     => 'Bearer ' . $token,
                'IM-CustomerNumber' => '20-213702',
                'IM-CountryCode'    => 'US',
                'IM-CorrelationID'  => substr( str_replace( '-', '', wp_generate_uuid4() ), 0, 32 ),
                'Accept'            => 'application/json',
            ),
            'timeout' => 15,
        ) );

        if ( is_wp_error( $det_resp ) || 200 !== wp_remote_retrieve_response_code( $det_resp ) ) return array();
        $det_body = json_decode( wp_remote_retrieve_body( $det_resp ), true );

        $images = array();
        $this->find_keys_recursive( $det_body, array('image', 'media', 'photo', 'thumb', 'multimedia'), $images );

        $urls = array();
        foreach ( $images as $val ) {
            if ( is_string( $val ) && strpos( $val, 'http' ) === 0 ) {
                $urls[] = $val;
            }
        }

        return array_unique( $urls );
    }
}

