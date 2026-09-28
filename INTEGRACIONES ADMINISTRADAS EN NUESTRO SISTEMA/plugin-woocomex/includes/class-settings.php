<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Ingram_Woo_Settings {
    private static $instance = null;
    private $options;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );

        // AJAX handler for saving selected categories
        add_action( 'wp_ajax_ingram_ajax_save_selected_categories', array( $this, 'ajax_save_selected_categories' ) );
    }

    public function add_plugin_page() {
        add_menu_page(
            __( 'Ingram Micro', 'ingram-woo' ),
            __( 'Ingram Micro', 'ingram-woo' ),
            'manage_options',
            'ingram-woo-settings',
            array( $this, 'create_admin_page' ),
            'dashicons-cart'
        );
    }

    public function create_admin_page() {
        $this->options = get_option( 'ingram_woo_options' );
        $active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'general';
        ?>
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
            
            /* Tech SaaS Aesthetic CSS */
            .wrap.ingram-woo-wrap {
                font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
                margin: 20px 20px 0 0;
            }
            .ingram-dashboard {
                display: flex;
                background: #f8fafc;
                border-radius: 12px;
                box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.05), 0 4px 6px -4px rgb(0 0 0 / 0.05);
                min-height: 85vh;
                overflow: hidden;
                border: 1px solid #e2e8f0;
            }
            .ingram-sidebar {
                width: 260px;
                background: #ffffff;
                border-right: 1px solid #e2e8f0;
                display: flex;
                flex-direction: column;
            }
            .ingram-sidebar-header {
                padding: 24px;
                font-size: 1.25rem;
                font-weight: 700;
                color: #0f172a;
                border-bottom: 1px solid #e2e8f0;
                display: flex;
                align-items: center;
                gap: 12px;
                background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
            }
            .ingram-nav {
                display: flex;
                flex-direction: column;
                padding: 16px 0;
            }
            .ingram-nav a {
                padding: 14px 24px;
                text-decoration: none;
                color: #64748b;
                font-weight: 500;
                transition: all 0.2s ease;
                border-left: 3px solid transparent;
                display: flex;
                align-items: center;
                gap: 10px;
            }
            .ingram-nav a::before {
                font-family: dashicons;
                font-size: 18px;
            }
            .ingram-nav a[href*="tab=preview"]::before { content: "\f508"; }

            .ingram-nav a:hover, .ingram-nav a:focus {
                background: #f1f5f9;
                color: #0f172a;
                box-shadow: none;
                outline: none;
            }
            .ingram-nav a.active {
                background: #eff6ff;
                color: #2563eb;
                border-left-color: #2563eb;
                font-weight: 600;
            }
            .ingram-content {
                flex: 1;
                padding: 32px 40px;
                background: #f8fafc;
                overflow-y: auto;
            }
            .ingram-card {
                background: #ffffff;
                border-radius: 12px;
                padding: 32px;
                box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.05), 0 1px 2px -1px rgb(0 0 0 / 0.05);
                border: 1px solid #e2e8f0;
            }
            .ingram-content h2.section-header {
                margin-top: 0;
                font-size: 1.5rem;
                color: #0f172a;
                font-weight: 600;
                margin-bottom: 24px;
                padding-bottom: 12px;
                border-bottom: 1px solid #f1f5f9;
            }
            /* Form overrides */
            .ingram-content .form-table th {
                font-weight: 500;
                color: #334155;
            }
            .ingram-content input[type="text"],
            .ingram-content input[type="password"],
            .ingram-content input[type="number"],
            .ingram-content select {
                border-radius: 6px;
                border: 1px solid #cbd5e1;
                padding: 8px 12px;
                box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
                color: #0f172a;
                font-family: inherit;
                transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            }
            .ingram-content input[type="text"]:focus,
            .ingram-content input[type="password"]:focus,
            .ingram-content input[type="number"]:focus,
            .ingram-content select:focus {
                border-color: #3b82f6;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
                outline: none;
            }
            .ingram-content .button-primary {
                background: #2563eb;
                border-color: #2563eb;
                color: white;
                border-radius: 6px;
                padding: 6px 16px;
                font-weight: 500;
                transition: background 0.2s;
            }
            .ingram-content .button-primary:hover {
                background: #1d4ed8;
                border-color: #1d4ed8;
            }
            .ingram-content .button-secondary {
                border-radius: 6px;
                font-weight: 500;
                color: #334155;
            }
            .ingram-content p.description {
                color: #64748b;
                font-style: normal;
                font-size: 0.875rem;
                margin-top: 6px;
            }
            /* Hidden elements wrapper override */
            .ingram-woo-wrap > h1 { display: none; }
            .ingram-preview-actions { display: flex; gap: 12px; margin: 24px 0; }
            .ingram-diagnostic-btn { border-color: #ef4444 !important; color: #ef4444 !important; }
            .ingram-diagnostic-btn:hover { background: #fee2e2 !important; border-color: #ef4444 !important; color: #dc2626 !important; }
        </style>
        <div class="wrap ingram-woo-wrap">
            <div class="ingram-dashboard">
                <div class="ingram-sidebar">
                    <div class="ingram-sidebar-header">
                        <span class="dashicons dashicons-cart" style="color: #2563eb; font-size: 28px; width: 28px; height: 28px;"></span>
                        Ingram Micro
                    </div>
                    <div class="ingram-nav">
                        <a href="?page=ingram-woo-settings&tab=general" class="<?php echo $active_tab == 'general' ? 'active' : ''; ?>"><?php esc_html_e( 'API Credentials', 'ingram-woo' ); ?></a>
                        <a href="?page=ingram-woo-settings&tab=sync" class="<?php echo $active_tab == 'sync' ? 'active' : ''; ?>"><?php esc_html_e( 'Synchronization', 'ingram-woo' ); ?></a>
                        <a href="?page=ingram-woo-settings&tab=pricing" class="<?php echo $active_tab == 'pricing' ? 'active' : ''; ?>"><?php esc_html_e( 'Pricing & Rules', 'ingram-woo' ); ?></a>
                        <a href="?page=ingram-woo-settings&tab=logs" class="<?php echo $active_tab == 'logs' ? 'active' : ''; ?>"><?php esc_html_e( 'System Logs', 'ingram-woo' ); ?></a>
                        <a href="?page=ingram-woo-settings&tab=preview" class="<?php echo $active_tab == 'preview' ? 'active' : ''; ?>"><?php esc_html_e( 'API Explorer', 'ingram-woo' ); ?></a>
                    </div>
                </div>
                <div class="ingram-content">
                    <div class="ingram-card">
                        <form method="post" action="options.php">
                        <?php
                            if ( $active_tab == 'general' ) {
                                echo '<h2 class="section-header">' . esc_html__( 'General API Settings', 'ingram-woo' ) . '</h2>';
                                settings_fields( 'ingram_woo_option_group' );
                                do_settings_sections( 'ingram-woo-settings-general' );
                            } elseif ( $active_tab == 'sync' ) {
                                echo '<h2 class="section-header">' . esc_html__( 'Product Synchronization Dashboard', 'ingram-woo' ) . '</h2>';
                                $this->render_modern_sync_tab();
                            } elseif ( $active_tab == 'pricing' ) {
                                echo '<h2 class="section-header">' . esc_html__( 'Pricing & Inventory Rules', 'ingram-woo' ) . '</h2>';
                                settings_fields( 'ingram_woo_option_group' );
                                do_settings_sections( 'ingram-woo-settings-pricing' );
                            } elseif ( $active_tab == 'logs' ) {
                                echo '<h2 class="section-header">' . esc_html__( 'System Logs', 'ingram-woo' ) . '</h2>';
                                settings_fields( 'ingram_woo_option_group' );
                                do_settings_sections( 'ingram-woo-settings-logs' );
                            } elseif ( $active_tab == 'preview' ) {
                                echo '<h2 class="section-header">' . esc_html__( 'API Data Explorer', 'ingram-woo' ) . '</h2>';
                                $this->render_preview_tab();
                            }
                            
                            if ( $active_tab !== 'logs' && $active_tab !== 'preview' ) {
                                submit_button();
                            }
                        ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function page_init() {
        register_setting(
            'ingram_woo_option_group', // option group
            'ingram_woo_options', // option name
            array( $this, 'sanitize' ) // sanitize callback
        );

        // ==========================================
        // TAB: GENERAL
        // ==========================================
        add_settings_section(
            'setting_section_api',
            __( 'API Credentials', 'ingram-woo' ),
            array( $this, 'print_section_info_api' ),
            'ingram-woo-settings-general'
        );

        add_settings_field(
            'environment',
            __( 'Environment', 'ingram-woo' ),
            array( $this, 'environment_callback' ),
            'ingram-woo-settings-general',
            'setting_section_api'
        );

        add_settings_field(
            'client_id',
            __( 'Client ID', 'ingram-woo' ),
            array( $this, 'client_id_callback' ),
            'ingram-woo-settings-general',
            'setting_section_api'
        );

        add_settings_field(
            'client_secret',
            __( 'Client Secret', 'ingram-woo' ),
            array( $this, 'client_secret_callback' ),
            'ingram-woo-settings-general',
            'setting_section_api'
        );

        add_settings_field(
            'customer_number',
            __( 'Customer Number', 'ingram-woo' ),
            array( $this, 'customer_number_callback' ),
            'ingram-woo-settings-general',
            'setting_section_api'
        );

        add_settings_field(
            'country_code',
            __( 'Country Code', 'ingram-woo' ),
            array( $this, 'country_code_callback' ),
            'ingram-woo-settings-general',
            'setting_section_api'
        );

        // ==========================================
        // TAB: SYNCHRONIZATION
        // ==========================================
        add_settings_section(
            'setting_section_sync',
            __( 'Synchronization Settings', 'ingram-woo' ),
            array( $this, 'print_section_info_sync' ),
            'ingram-woo-settings-sync'
        );

        add_settings_field(
            'cron_schedule',
            __( 'Automatic Sync Schedule', 'ingram-woo' ),
            array( $this, 'cron_schedule_callback' ),
            'ingram-woo-settings-sync',
            'setting_section_sync'
        );

        add_settings_field(
            'manual_sync',
            __( 'Manual Sync', 'ingram-woo' ),
            array( $this, 'manual_sync_callback' ),
            'ingram-woo-settings-sync',
            'setting_section_sync'
        );

        // ==========================================
        // TAB: PRICING & INVENTORY
        // ==========================================
        add_settings_section(
            'setting_section_pricing',
            __( 'Pricing & Inventory Rules', 'ingram-woo' ),
            array( $this, 'print_section_info_pricing' ),
            'ingram-woo-settings-pricing'
        );

        add_settings_field(
            'price_margin',
            __( 'Profit Margin (%)', 'ingram-woo' ),
            array( $this, 'price_margin_callback' ),
            'ingram-woo-settings-pricing',
            'setting_section_pricing'
        );

        add_settings_field(
            'usd_exchange_rate',
            __( 'USD Exchange Rate (CLP)', 'ingram-woo' ),
            array( $this, 'usd_exchange_rate_callback' ),
            'ingram-woo-settings-pricing',
            'setting_section_pricing'
        );

        add_settings_field(
            'out_of_stock_action',
            __( 'Out of Stock Action', 'ingram-woo' ),
            array( $this, 'out_of_stock_action_callback' ),
            'ingram-woo-settings-pricing',
            'setting_section_pricing'
        );

        add_settings_field(
            'default_category',
            __( 'Default Category', 'ingram-woo' ),
            array( $this, 'default_category_callback' ),
            'ingram-woo-settings-pricing',
            'setting_section_pricing'
        );

        // ==========================================
        // TAB: LOGS
        // ==========================================
        add_settings_section(
            'setting_section_logs',
            __( 'System Logs', 'ingram-woo' ),
            array( $this, 'print_section_info_logs' ),
            'ingram-woo-settings-logs'
        );
    }

    public function sanitize( $input ) {
        $existing = get_option( 'ingram_woo_options', array() );
        if ( ! is_array( $existing ) ) $existing = array();

        $new_input = $existing;

        if ( isset( $input['environment'] ) )
            $new_input['environment'] = sanitize_text_field( $input['environment'] );
        if ( isset( $input['client_id'] ) )
            $new_input['client_id'] = sanitize_text_field( $input['client_id'] );
        if ( isset( $input['client_secret'] ) )
            $new_input['client_secret'] = sanitize_text_field( $input['client_secret'] );
        if ( isset( $input['sandbox_client_id'] ) )
            $new_input['sandbox_client_id'] = sanitize_text_field( $input['sandbox_client_id'] );
        if ( isset( $input['sandbox_client_secret'] ) )
            $new_input['sandbox_client_secret'] = sanitize_text_field( $input['sandbox_client_secret'] );
        if ( isset( $input['customer_number'] ) )
            $new_input['customer_number'] = sanitize_text_field( $input['customer_number'] );
        if ( isset( $input['country_code'] ) )
            $new_input['country_code'] = sanitize_text_field( $input['country_code'] );
            
        if ( isset( $input['cron_schedule'] ) )
            $new_input['cron_schedule'] = sanitize_text_field( $input['cron_schedule'] );
            
        if ( isset( $input['price_margin'] ) )
            $new_input['price_margin'] = sanitize_text_field( $input['price_margin'] );
        if ( isset( $input['usd_exchange_rate'] ) )
            $new_input['usd_exchange_rate'] = sanitize_text_field( $input['usd_exchange_rate'] );
        if ( isset( $input['out_of_stock_action'] ) )
            $new_input['out_of_stock_action'] = sanitize_text_field( $input['out_of_stock_action'] );
        if ( isset( $input['default_category'] ) )
            $new_input['default_category'] = sanitize_text_field( $input['default_category'] );
            
        return $new_input;
    }

    // --- Section Callbacks ---

    public function print_section_info_api() {
        _e( 'Enter your primary Ingram Micro API credentials below. These are used for production price and availability checks.', 'ingram-woo' );
    }

    public function print_section_info_sync() {
        print __( 'Configure how and when the product catalog synchronizes with WooCommerce.', 'ingram-woo' );
        if ( isset( $_GET['synced'] ) && $_GET['synced'] == 1 ) {
            echo '<div class="notice notice-success is-dismissible"><p>' . __( 'Manual synchronization triggered successfully.', 'ingram-woo' ) . '</p></div>';
        }
    }

    public function print_section_info_pricing() {
        print __( 'Set up rules for how prices are calculated and how out-of-stock items are handled.', 'ingram-woo' );
    }

    public function print_section_info_logs() {
        print '<p>' . __( 'Recent synchronization activity and API errors will appear here.', 'ingram-woo' ) . '</p>';
        
        $logs = get_option( 'ingram_woo_sync_logs', array() );
        
        if ( empty( $logs ) ) {
            echo '<div class="inside" style="background: #f1f5f9; padding: 15px; border-radius: 6px; color: #64748b;"><p><em>' . __( 'No logs available yet.', 'ingram-woo' ) . '</em></p></div>';
        } else {
            echo '<table class="wp-list-table widefat fixed striped" style="margin-top:15px; border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: none;">';
            echo '<thead style="background: #f8fafc;"><tr><th style="width: 200px; padding: 12px 15px; border-bottom: 1px solid #e2e8f0;">' . __( 'Date & Time', 'ingram-woo' ) . '</th><th style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0;">' . __( 'Event Details', 'ingram-woo' ) . '</th></tr></thead><tbody>';
            foreach ( $logs as $log ) {
                $time = isset( $log['time'] ) ? $log['time'] : '-';
                $message = isset( $log['message'] ) ? $log['message'] : '-';
                echo '<tr><td style="padding: 10px 15px;"><code>' . esc_html( $time ) . '</code></td><td style="padding: 10px 15px;">' . esc_html( $message ) . '</td></tr>';
            }
            echo '</tbody></table>';
        }
    }

    // --- Field Callbacks ---

    public function environment_callback() {
        $env = isset( $this->options['environment'] ) ? $this->options['environment'] : 'sandbox';
        ?>
        <select name="ingram_woo_options[environment]" id="environment">
            <option value="sandbox" <?php selected( $env, 'sandbox' ); ?>><?php esc_html_e( 'Sandbox (Testing)', 'ingram-woo' ); ?></option>
            <option value="production" <?php selected( $env, 'production' ); ?>><?php esc_html_e( 'Production (Live)', 'ingram-woo' ); ?></option>
        </select>
        <?php
    }

    public function client_id_callback() {
        printf(
            '<input type="text" id="client_id" name="ingram_woo_options[client_id]" value="%s" style="width: 350px;" />',
            isset( $this->options['client_id'] ) ? esc_attr( $this->options['client_id'] ) : ''
        );
    }

    public function client_secret_callback() {
        // Leave as password but maybe people want to see it? Password is standard for secrets.
        printf(
            '<input type="password" id="client_secret" name="ingram_woo_options[client_secret]" value="%s" style="width: 350px;" />',
            isset( $this->options['client_secret'] ) ? esc_attr( $this->options['client_secret'] ) : ''
        );
    }

    public function sandbox_client_id_callback() {
        printf(
            '<input type="text" id="sandbox_client_id" name="ingram_woo_options[sandbox_client_id]" value="%s" style="width: 350px;" />',
            isset( $this->options['sandbox_client_id'] ) ? esc_attr( $this->options['sandbox_client_id'] ) : ''
        );
        echo '<p class="description">' . __( 'Used for testing and cross-region image discovery.', 'ingram-woo' ) . '</p>';
    }

    public function sandbox_client_secret_callback() {
        printf(
            '<input type="password" id="sandbox_client_secret" name="ingram_woo_options[sandbox_client_secret]" value="%s" style="width: 350px;" />',
            isset( $this->options['sandbox_client_secret'] ) ? esc_attr( $this->options['sandbox_client_secret'] ) : ''
        );
    }

    public function customer_number_callback() {
        printf(
            '<input type="text" id="customer_number" name="ingram_woo_options[customer_number]" value="%s" />',
            isset( $this->options['customer_number'] ) ? esc_attr( $this->options['customer_number'] ) : ''
        );
    }

    public function country_code_callback() {
        $val = isset( $this->options['country_code'] ) ? esc_attr( $this->options['country_code'] ) : 'CL';
        printf(
            '<input type="text" id="country_code" name="ingram_woo_options[country_code]" value="%s" maxlength="2" placeholder="CL" />',
            $val
        );
        echo '<p class="description">Usa <b>CL</b> para Ingram Micro Chile.</p>';
    }

    public function cron_schedule_callback() {
        $schedule = isset( $this->options['cron_schedule'] ) ? $this->options['cron_schedule'] : 'disabled';
        ?>
        <select name="ingram_woo_options[cron_schedule]" id="cron_schedule">
            <option value="disabled" <?php selected( $schedule, 'disabled' ); ?>><?php esc_html_e( 'Disabled', 'ingram-woo' ); ?></option>
            <option value="twicedaily" <?php selected( $schedule, 'twicedaily' ); ?>><?php esc_html_e( 'Twice Daily (Every 12 hours)', 'ingram-woo' ); ?></option>
            <option value="daily" <?php selected( $schedule, 'daily' ); ?>><?php esc_html_e( 'Once Daily', 'ingram-woo' ); ?></option>
        </select>
        <?php
    }

    public function manual_sync_callback() {
        $sync_url = wp_nonce_url( admin_url( 'admin-post.php?action=ingram_sync_products' ), 'ingram_sync_action' );
        printf(
            '<a href="%s" class="button button-secondary">%s</a>',
            esc_url( $sync_url ),
            esc_html__( 'Synchronize Now', 'ingram-woo' )
        );
        echo '<p class="description"><br/>' . __( 'Click to manually fetch and update the product catalog from Ingram Micro.', 'ingram-woo' ) . '</p>';
    }

    public function price_margin_callback() {
        printf(
            '<input type="number" step="0.01" id="price_margin" name="ingram_woo_options[price_margin]" value="%s" style="width: 100px;" /> %%',
            isset( $this->options['price_margin'] ) ? esc_attr( $this->options['price_margin'] ) : '0'
        );
        echo '<p class="description">' . __( 'Percentage to add to the Ingram Micro wholesale price.', 'ingram-woo' ) . '</p>';
    }

    public function usd_exchange_rate_callback() {
        printf(
            '<input type="number" step="0.01" id="usd_exchange_rate" name="ingram_woo_options[usd_exchange_rate]" value="%s" style="width: 150px;" />',
            isset( $this->options['usd_exchange_rate'] ) ? esc_attr( $this->options['usd_exchange_rate'] ) : '1'
        );
        echo '<p class="description">' . __( 'Conversion factor from USD to CLP. The API price (USD) will be multiplied by this value.', 'ingram-woo' ) . '</p>';
    }

    public function out_of_stock_action_callback() {
        $action = isset( $this->options['out_of_stock_action'] ) ? $this->options['out_of_stock_action'] : 'hide';
        ?>
        <select name="ingram_woo_options[out_of_stock_action]" id="out_of_stock_action">
            <option value="hide" <?php selected( $action, 'hide' ); ?>><?php esc_html_e( 'Hide product in catalog', 'ingram-woo' ); ?></option>
            <option value="draft" <?php selected( $action, 'draft' ); ?>><?php esc_html_e( 'Change status to Draft', 'ingram-woo' ); ?></option>
            <option value="backorder" <?php selected( $action, 'backorder' ); ?>><?php esc_html_e( 'Allow Backorders', 'ingram-woo' ); ?></option>
        </select>
        <?php
    }

    public function default_category_callback() {
        $selected_cat = isset( $this->options['default_category'] ) ? $this->options['default_category'] : 0;
        
        $args = array(
            'taxonomy'     => 'product_cat',
            'name'         => 'ingram_woo_options[default_category]',
            'id'           => 'default_category',
            'selected'     => $selected_cat,
            'show_option_none' => __( 'Select a category (Optional)', 'ingram-woo' ),
            'hide_empty'   => 0,
            'class'        => '',
        );
        
        if ( function_exists( 'wp_dropdown_categories' ) && taxonomy_exists( 'product_cat' ) ) {
            wp_dropdown_categories( $args );
        } else {
            echo '<p>' . __( 'WooCommerce doesn\'t seem to be active or hasn\'t registered categories.', 'ingram-woo' ) . '</p>';
        }
    }

    // --- Custom Tab Renderers ---
    
    public function render_modern_sync_tab() {
        $opts = get_option( 'ingram_woo_options', array() );
        $selected_categories = isset( $opts['selected_categories'] ) ? (array) $opts['selected_categories'] : array();
        $cron_schedule = isset( $opts['cron_schedule'] ) ? $opts['cron_schedule'] : 'daily';
        $preserve_description = isset( $opts['preserve_description'] ) ? intval( $opts['preserve_description'] ) : 1;
        $cron_secret_key = isset( $opts['cron_secret_key'] ) ? $opts['cron_secret_key'] : '';
        if ( empty( $cron_secret_key ) ) {
            $cron_secret_key = get_option( 'ingram_woo_cron_secret', '' );
            if ( empty( $cron_secret_key ) ) {
                $cron_secret_key = md5( ( defined( 'SECURE_AUTH_KEY' ) ? SECURE_AUTH_KEY : 'ingram' ) . 'cron_key' );
                update_option( 'ingram_woo_cron_secret', $cron_secret_key );
            }
            $opts['cron_secret_key'] = $cron_secret_key;
            update_option( 'ingram_woo_options', $opts );
        }
        
        $next_cron_timestamp = wp_next_scheduled( 'ingram_woo_cron_sync_event' );
        if ( $next_cron_timestamp && is_numeric( $next_cron_timestamp ) ) {
            $ts = intval( $next_cron_timestamp );
            $diff = function_exists( 'human_time_diff' ) ? human_time_diff( time(), $ts ) : 'próximamente';
            $cron_status_html = 'Programada (En ' . esc_html( $diff ) . ' — ' . date_i18n( 'H:i d/m', $ts ) . ')';
        } else if ( $cron_schedule !== 'disabled' ) {
            $cron_status_html = '<span style="color:#d97706;">Programada (Reactivando...)</span>';
        } else {
            $cron_status_html = '<span style="color:#94a3b8;">Desactivada</span>';
        }
        $external_cron_url = home_url( '/?ingram_cron_sync=1&key=' . $cron_secret_key );
        ?>
        <div class="ingram-modern-sync">
            <style>
                .sync-step-container { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 32px; }
                .sync-card { background: #fdfdfd; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; transition: all 0.3s ease; height: 100%; box-sizing: border-box; display: flex; flex-direction: column; }
                .sync-card h3 { margin-top: 0; color: #0f172a; font-size: 1.1rem; display: flex; align-items: center; gap: 10px; }
                .sync-card p { color: #64748b; font-size: 0.9rem; line-height: 1.5; margin-bottom: 12px; }
                
                .category-discovery-box { flex-grow: 1; max-height: 350px; overflow-y: auto; border: 1px solid #f1f5f9; border-radius: 8px; padding: 0; background: #fff; margin-top: 15px; }
                .cat-subs { padding: 8px 16px 12px 32px; display: grid; grid-template-columns: 1fr; gap: 6px; }
                .cat-checkbox-label { display: flex; align-items: center; gap: 8px; font-size: 0.85rem; color: #475569; cursor: pointer; }
                
                .catalog-management-list { max-height: 300px; overflow-y: auto; background: #fff; border-radius: 8px; border: 1px solid #f1f5f9; }
                .catalog-item { padding: 10px 16px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; transition: background 0.2s; }
                .catalog-item:hover { background: #f8fafc; }
                .catalog-name { font-weight: 600; font-size: 0.9rem; color: #0f172a; }
                .catalog-count { font-size: 0.75rem; color: #94a3b8; }
                
                /* Modal Styles */
                .ingram-modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 99999; display: none; align-items: center; justify-content: center; }
                .ingram-modal { background: #fff; width: 500px; max-width: 90%; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); padding: 32px; position: relative; }
                .ingram-modal-close { position: absolute; top: 16px; right: 16px; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 50%; border: 1px solid #e2e8f0; background: #fff; cursor: pointer; color: #64748b; }
                .ingram-modal-close:hover { background: #f1f5f9; color: #0f172a; }

                .progress-dashboard-v2 { text-align: center; }
                .progress-bar-outer { width: 100%; height: 14px; background: #e2e8f0; border-radius: 10px; overflow: hidden; margin: 24px 0; }
                .progress-bar-inner { height: 100%; background: linear-gradient(90deg, #3b82f6 0%, #2563eb 100%); width: 0%; transition: width 0.5s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 0 10px rgba(59, 130, 246, 0.3); }
                .status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; margin-bottom: 12px; }
                .status-badge.idle { background: #f1f5f9; color: #64748b; }
                .status-badge.running { background: #dcfce7; color: #166534; animation: pulse 2s infinite; }
                .status-badge.failed { background: #fee2e2; color: #991b1b; }
                
                @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.6; } 100% { opacity: 1; } }
            </style>

            <div class="sync-step-container">
                <!-- Step 1: Discovery -->
                <div class="sync-card">
                    <h3><span class="dashicons dashicons-search"></span> 1. Descubrimiento</h3>
                    <p>Escanea el catálogo de Ingram Micro Chile.</p>
                    <button type="button" id="btn-discover-cats" class="button button-secondary" style="width: 100%; margin-top: 10px; height: 40px;">
                        Escanear Catálogo
                    </button>
                    
                    <div id="category-tree-container" class="category-discovery-box" style="display: <?php echo !empty($selected_categories) ? 'block' : 'none'; ?>;">
                        <div class="category-bulk-actions" style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9; display: flex; gap: 10px; background: #fff; position: sticky; top: 0; z-index: 10;">
                            <button type="button" id="btn-select-all" class="button button-small">Todos</button>
                            <button type="button" id="btn-deselect-all" class="button button-small">Ninguno</button>
                        </div>
                        <div id="discovery-results">
                            <?php if(!empty($selected_categories)): ?>
                                <div class="cat-subs">
                                    <?php foreach($selected_categories as $selection): ?>
                                         <?php 
                                            $catName = is_array($selection) ? $selection['cat'] : $selection;
                                            $subName = is_array($selection) ? $selection['sub'] : '';
                                         ?>
                                        <label class="cat-checkbox-label">
                                            <input type="checkbox" name="ingram_selected_cats[]" value="<?php echo esc_attr($catName); ?>" data-sub="<?php echo esc_attr($subName); ?>" checked>
                                            <?php echo esc_html($catName); ?> <?php echo $subName ? '('.esc_html($subName).')' : ''; ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Controls -->
                <div class="sync-card" style="justify-content: space-between;">
                    <div>
                        <h3><span class="dashicons dashicons-update"></span> 2. Sincronización Automática</h3>
                        <p>Inicia la actualización masiva o configura la sincronización periódica.</p>
                        
                        <div style="margin-top: 15px; background: #ffffff; border: 1px solid #e2e8f0; padding: 14px; border-radius: 8px; display: flex; flex-direction: column; gap: 10px;">
                            <div>
                                <label for="cron_schedule_select" style="font-weight: 600; font-size: 0.85rem; color: #0f172a; display: block; margin-bottom: 4px;">
                                    Frecuencia de Cron Automático:
                                </label>
                                <select id="cron_schedule_select" style="width: 100%; height: 36px; border-radius: 6px;">
                                    <option value="disabled" <?php selected( $cron_schedule, 'disabled' ); ?>>Desactivado</option>
                                    <option value="hourly" <?php selected( $cron_schedule, 'hourly' ); ?>>Cada Hora</option>
                                    <option value="twicedaily" <?php selected( $cron_schedule, 'twicedaily' ); ?>>Dos Veces al Día (Cada 12h)</option>
                                    <option value="daily" <?php selected( $cron_schedule, 'daily' ); ?>>Una Vez al Día (Diario)</option>
                                </select>
                            </div>

                            <div style="margin-top: 4px;">
                                <label style="display: flex; align-items: flex-start; gap: 8px; font-size: 0.85rem; color: #1e293b; cursor: pointer;">
                                    <input type="checkbox" id="chk_preserve_desc" value="1" <?php checked( $preserve_description, 1 ); ?> style="margin-top: 2px;">
                                    <span><strong>Respetar descripción manual de WooCommerce</strong><br/><small style="color: #64748b;">No sobrescribir la descripción si ya fue editada o personalizada en WooCommerce.</small></span>
                                </label>
                            </div>
                        </div>

                        <div style="margin-top: 15px; display: flex; flex-direction: column; gap: 10px;">
                            <button type="button" id="btn-start-massive-sync" class="button button-primary" style="height: 46px; font-size: 1.05rem; width: 100%;">
                                Sincronizar Ahora
                            </button>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                <button type="button" id="btn-save-selection" class="button button-secondary" style="height: 40px;" title="Guarda la selección y la frecuencia del cron">
                                    Guardar Ajustes
                                </button>
                                <button type="button" id="btn-force-stop-sync" class="button" style="color:#ef4444; border-color:#fca5a5; background:#fef2f2; height: 40px;" title="Limpia cualquier estado de sincronización trabado">
                                    Reiniciar
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div id="sync-stats" style="margin-top:15px; padding: 12px; background: #f8fafc; border-radius: 8px; font-size: 0.82rem; color: #475569; border: 1px solid #f1f5f9;">
                        <strong>Selección actual:</strong> <span id="saved-count-text"><?php echo count($selected_categories); ?></span> secciones.<br/>
                        <strong>Estado del Cron WP:</strong> <span style="font-weight:600; color:#166534;"><?php echo $cron_status_html; ?></span>
                        <div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid #e2e8f0; font-size: 0.78rem;">
                            <strong>URL Cron de Servidor (cPanel):</strong>
                            <input type="text" readonly value="<?php echo esc_url( $external_cron_url ); ?>" style="width: 100%; font-size: 0.72rem; font-family: monospace; background: #fff; margin-top: 3px;" onclick="this.select();" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 3: Catalog Management (Separate Card) -->
            <div class="sync-card" style="margin-top: 0; background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <div>
                        <h3 style="margin-bottom: 5px;"><span class="dashicons dashicons-category"></span> 3. Gestión de Catálogo Existente (Woo)</h3>
                        <p style="margin: 0;">Controla la visibilidad o elimina secciones de tu WooCommerce.</p>
                    </div>
                    <div class="catalog-bulk-footer" style="display: flex; gap: 8px;">
                         <button type="button" id="btn-catalog-select-all" class="button button-small">Seleccionar Todos</button>
                         <button type="button" id="btn-catalog-show-all" class="button button-small">Mostrar</button>
                         <button type="button" id="btn-catalog-hide-all" class="button button-small">Ocultar</button>
                         <button type="button" id="btn-catalog-delete-all" class="button button-small" style="color:#ef4444; border-color:#fecaca;">Borrar Selección</button>
                    </div>
                </div>
                <div class="catalog-management-list" id="wocommerce-catalog-list">
                    <p style="padding:20px; text-align:center; color:#94a3b8; font-size:0.8rem;">Cargando catálogo local...</p>
                </div>
            </div>

            <!-- Progress Modal Container -->
            <div id="ingram-sync-modal" class="ingram-modal-overlay">
                <div class="ingram-modal">
                    <button type="button" class="ingram-modal-close" id="btn-close-sync-modal">&times;</button>
                    <div class="progress-dashboard-v2">
                        <div id="sync-status-badge" class="status-badge idle">Inactivo</div>
                        <h2 id="sync-main-status" style="margin: 5px 0 10px 0; color: #0f172a; font-size: 1.5rem;">Preparado</h2>
                        <p id="sync-sub-status" style="color: #64748b; font-size: 0.95rem;">Selecciona categorías para comenzar.</p>
                        
                        <div class="progress-bar-outer">
                            <div id="sync-progress-bar" class="progress-bar-inner"></div>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; font-size: 0.9rem; font-weight: 600; color: #0f172a;">
                            <span id="sync-processed-count">0 / 0 Secciones</span>
                            <span id="sync-percentage">0%</span>
                        </div>

                        <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid #f1f5f9;">
                             <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; margin-bottom: 8px;">Log de Actuación</div>
                             <div id="sync-modal-log" style="font-size: 0.8rem; color: #475569; text-align: left; background: #f8fafc; padding: 12px; border-radius: 8px; height: 80px; overflow-y: auto;">
                                 Esperando inicio...
                             </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script type="text/javascript">
        jQuery(document).ready(function($) {
            function updateUIStatus() {
                $.post(ajaxurl, {
                    action: 'ingram_ajax_get_sync_status',
                    _ajax_nonce: '<?php echo wp_create_nonce("ingram_preview_nonce"); ?>'
                }, function(response) {
                    if(response.success) {
                        const data = response.data;
                        if(data.status === 'processing' || data.pending_batches > 0) {
                            $('#massive-sync-dashboard').fadeIn();
                            $('#btn-start-massive-sync').prop('disabled', true).text('Sync in Progress...');
                            $('#btn-force-stop-sync').show();
                            
                            const total = parseInt(data.total_categories) || 1;
                            const completed = parseInt(data.completed_categories) || 0;
                            const progress = Math.round((completed / total) * 100);
                            
                            $('#sync-progress-bar').css('width', progress + '%').text(progress + '%');
                            $('#sync-percentage').text(progress + '%');
                            $('#sync-main-status').text('Syncing: ' + (data.current_category || 'Initializing...'));
                            $('#sync-sub-status').text('Processing background batches... (' + (data.pending_batches || '0') + ' remaining)');
                            $('#sync-counter').text(completed + ' / ' + total + ' Categories');

                            // Check for stale sync (no heartbeat in 10 minutes)
                            if (data.last_updated && data.server_time) {
                                const diff = data.server_time - data.last_updated;
                                if (diff > 600) { // 10 minutes
                                    $('#sync-main-status').html('<span style="color:#ef4444;">Sync appears stuck</span>');
                                    $('#sync-sub-status').text('No activity for ' + Math.floor(diff/60) + ' minutes. Consider forcing a reset.');
                                }
                            }

                            setTimeout(updateUIStatus, 3000);
                        } else {
                            // Finished or Idle
                            $('#massive-sync-dashboard').fadeOut();
                            $('#btn-start-massive-sync').prop('disabled', false).text('Sincronizar Ahora');
                        }
                    }
                });
            }

            // Initial status check
            updateUIStatus();

            $('#btn-discover-cats').on('click', function() {
                const btn = $(this);
                btn.prop('disabled', true).text('Scanning Catalog...');
                $('#category-tree-container').slideDown();
                $('#discovery-results').html('<p style="padding:20px; text-align:center;"><span class="spinner is-active" style="float:none;"></span> Building category tree...</p>');

                $.post(ajaxurl, {
                    action: 'ingram_ajax_discover_categories',
                    _ajax_nonce: '<?php echo wp_create_nonce("ingram_preview_nonce"); ?>'
                }, function(response) {
                    btn.prop('disabled', false).text('Scan for Categories');
                    if(response.success) {
                        let html = '<div style="padding:15px; background:#f0f9ff; border-radius:8px; margin-bottom:15px; border:1px solid #bae6fd; color:#0369a1; font-weight:600; text-align:center;">';
                        html += '<span class="dashicons dashicons-products" style="vertical-align:middle; margin-right:5px;"></span> ';
                        html += 'Total Products in Ingram Micro Chile: ' + response.data.totalRecords.toLocaleString();
                        html += '</div>';
                        
                        const tree = response.data.categories;
                        for (const cat in tree) {
                            const catData = tree[cat];
                            const safeCode = cat.replace(/[^a-z0-9]/gi, '');
                            html += '<div class="cat-group">';
                            html += '<div class="cat-parent">' + cat + ' <span style="font-weight:400; color:#94a3b8; font-size:0.8rem;">(' + catData.count + ')</span> <input type="checkbox" class="parent-checkbox" data-target="'+safeCode+'"></div>';
                            html += '<div class="cat-subs" id="group-'+safeCode+'">';
                            for (const sub in catData.subs) {
                                const subCount = catData.subs[sub];
                                html += '<label class="cat-checkbox-label"><input type="checkbox" name="ingram_selected_cats[]" value="'+cat+'" data-sub="'+sub+'"> ' + sub + ' <span style="color:#cbd5e1; font-size:0.75rem;">(' + subCount + ')</span></label>';
                            }
                            html += '</div></div>';
                        }
                        $('#discovery-results').html(html);
                    } else {
                        $('#discovery-results').html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
                    }
                });
            });

            $(document).on('change', '.parent-checkbox', function() {
                const checked = $(this).prop('checked');
                const target = $(this).data('target');
                $('#group-' + target + ' input[type="checkbox"]').prop('checked', checked);
            });

            $('#btn-select-all').on('click', function() {
                $('.category-discovery-box input[type="checkbox"]').prop('checked', true);
            });

            $('#btn-deselect-all').on('click', function() {
                $('.category-discovery-box input[type="checkbox"]').prop('checked', false);
            });

            const $modal = $('#ingram-sync-modal');
            const $modalLog = $('#sync-modal-log');

            function addToLog(msg) {
                const time = new Date().toLocaleTimeString();
                $modalLog.append(`<div><small>[${time}]</small> ${msg}</div>`);
                $modalLog.scrollTop($modalLog[0].scrollHeight);
            }

            function updateProgressUI(completed, total, totalProducts) {
                const progress = total > 0 ? (completed / total) * 100 : 0;
                $('#sync-progress-bar').css('width', progress + '%');
                $('#sync-progress-label').text(Math.round(progress) + '%');
                $('#sync-main-status').text(completed + ' / ' + total + ' Secciones');
                $('#sync-sub-status').text(totalProducts + ' productos importados');
            }

            // The core sync loop: calls the server, processes one batch, then loops
            function runSyncLoop() {
                $.post(ajaxurl, {
                    action: 'ingram_ajax_sync_next_batch',
                    _ajax_nonce: '<?php echo wp_create_nonce("ingram_preview_nonce"); ?>'
                }, function(response) {
                    if (!response.success) {
                        addToLog('ERROR: ' + (response.data ? response.data.message : 'Sin respuesta del servidor.'));
                        $('#sync-status-badge').removeClass('running').addClass('failed').text('Error');
                        $('#btn-start-massive-sync').prop('disabled', false).text('Reintentar Sincronización');
                        return;
                    }

                    const d = response.data;

                    if (d.error) {
                        addToLog('⚠️ ' + d.message);
                    } else if (d.imported_this_batch > 0) {
                        const pageInfo = d.page ? ' (pág. ' + d.page + ')' : '';
                        addToLog('✅ ' + d.imported_this_batch + ' productos — ' + (d.current_category || '') + pageInfo);
                    } else {
                        addToLog(d.message || 'Procesando...');
                    }

                    updateProgressUI(
                        parseInt(d.completed) || 0,
                        parseInt(d.total) || 0,
                        parseInt(d.total_imported) || 0
                    );

                    if (d.done) {
                        addToLog('🎉 ¡Sincronización completada exitosamente!');
                        $('#sync-status-badge').removeClass('running').addClass('idle').text('Completado');
                        $('#btn-start-massive-sync').prop('disabled', false).text('Sincronizar Seleccionados');
                        $('#sync-sub-status').text((d.total_imported || 0) + ' productos importados en total.');
                    } else {
                        // Continue the loop after a tiny delay
                        setTimeout(runSyncLoop, 500);
                    }
                }).fail(function(xhr) {
                    addToLog('ERROR de red: ' + xhr.statusText + '. Reintentando en 5s...');
                    setTimeout(runSyncLoop, 5000);
                });
            }

            $('#btn-start-massive-sync').on('click', function() {
                const selected = [];
                $('input[name="ingram_selected_cats[]"]:checked').each(function() {
                    selected.push({
                        cat: $(this).val(),
                        sub: $(this).data('sub') || ''
                    });
                });

                if (selected.length === 0) {
                    alert('Por favor selecciona al menos una categoría.');
                    return;
                }

                if (!confirm('¿Iniciar sincronización masiva de ' + selected.length + ' secciones?')) return;

                // Show modal and reset UI
                $modalLog.html('<div>Iniciando sincronización...</div>');
                $modal.css('display', 'flex');
                $('#sync-status-badge').removeClass('idle failed').addClass('running').text('Sincronizando');
                $(this).prop('disabled', true).text('Procesando...');

                // Start sync on server (saves state)
                $.post(ajaxurl, {
                    action: 'ingram_ajax_start_sync',
                    selections: selected,
                    _ajax_nonce: '<?php echo wp_create_nonce("ingram_preview_nonce"); ?>'
                }, function(response) {
                    if (response.success) {
                        addToLog('Sincronización iniciada. Procesando ' + selected.length + ' secciones...');
                        // Start the browser-driven loop
                        runSyncLoop();
                    } else {
                        addToLog('ERROR: ' + (response.data ? response.data.message : 'Fallo al iniciar.'));
                        $('#btn-start-massive-sync').prop('disabled', false).text('Sincronizar Seleccionados');
                    }
                });
            });

            $('#btn-close-sync-modal').on('click', function() {
                $modal.fadeOut();
            });

            $('#btn-force-stop-sync').on('click', function() {
                if (!confirm('¿Estás seguro de reiniciar el estado? Esto no detendrá tareas que ya estén corriendo en el servidor pero limpiará el panel.')) return;
                
                $.post(ajaxurl, {
                    action: 'ingram_ajax_force_reset_sync',
                    _ajax_nonce: '<?php echo wp_create_nonce("ingram_preview_nonce"); ?>'
                }, function(response) {
                    if (response.success) {
                        location.reload();
                    }
                });
            });

            // --- Catalog Management (Step 3) ---
            function loadLocalCatalog() {
                const container = $('#wocommerce-catalog-list');
                container.html('<p style="padding:20px; text-align:center;"><span class="spinner is-active" style="float:none;"></span> Cargando catálogo...</p>');
                
                $.post(ajaxurl, {
                    action: 'ingram_ajax_get_local_catalog',
                    _ajax_nonce: '<?php echo wp_create_nonce("ingram_preview_nonce"); ?>'
                }, function(response) {
                    if(response.success) {
                        let html = '';
                        if(response.data.length === 0) {
                            html = '<p style="padding:20px; text-align:center; color:#94a3b8;">No se encontraron categorías en WooCommerce.</p>';
                        } else {
                            // Calculate totals
                            let totalProducts = 0, totalStock = 0, totalInStock = 0, totalWithPrice = 0;
                            response.data.forEach(i => { totalProducts += i.count; totalStock += i.total_stock; totalInStock += i.in_stock_count; totalWithPrice += i.with_price; });
                            
                            html += `<div style="padding:12px 16px; background:linear-gradient(135deg,#eff6ff,#f0fdf4); border-bottom:2px solid #e2e8f0; display:flex; gap:20px; font-size:0.8rem; font-weight:600; color:#334155;">
                                <span>📦 ${totalProducts} SKUs</span>
                                <span>📊 Stock Total: ${totalStock.toLocaleString()}</span>
                                <span style="color:#059669;">✅ En Stock: ${totalInStock}</span>
                                <span style="color:#2563eb;">💰 Con Precio: ${totalWithPrice}</span>
                            </div>`;

                            response.data.forEach(function(item) {
                                const stockColor = item.in_stock_count > 0 ? '#059669' : '#ef4444';
                                const priceColor = item.with_price > 0 ? '#2563eb' : '#ef4444';
                                const indent = item.parent > 0 ? 'padding-left:32px;' : '';
                                const nameWeight = item.parent > 0 ? '500' : '700';
                                
                                html += `<div class="catalog-item" data-id="${item.id}" style="${indent}">
                                    <div class="catalog-info" style="flex:1;">
                                        <div class="catalog-name" style="font-weight:${nameWeight};">${item.parent > 0 ? '↳ ' : ''}${item.name}</div>
                                        <div style="display:flex; gap:12px; margin-top:3px;">
                                            <span class="catalog-count">${item.count} productos</span>
                                            <span style="font-size:0.7rem; color:${stockColor};">📊 Stock: ${item.total_stock.toLocaleString()} (${item.in_stock_count} en stock)</span>
                                            <span style="font-size:0.7rem; color:${priceColor};">💰 ${item.with_price}/${item.count} con precio</span>
                                        </div>
                                    </div>
                                    <div class="catalog-actions">
                                        <input type="checkbox" class="catalog-select-item" value="${item.id}">
                                    </div>
                                </div>`;
                            });
                        }
                        container.html(html);
                    }
                });
            }

            loadLocalCatalog();

            function bulkManageCatalog(action) {
                const selected = [];
                $('.catalog-select-item:checked').each(function() {
                    selected.push($(this).val());
                });

                if(selected.length === 0) {
                    alert('Por favor selecciona al menos una categoría local.');
                    return;
                }

                let confirmMsg = '¿Estás seguro?';
                if(action === 'delete') confirmMsg = '¡ATENCIÓN! Esto ELIMINARÁ PERMANENTEMENTE ' + selected.length + ' categorías y TODOS sus productos asociados. Esta acción no se puede deshacer. ¿Proceder?';
                if(action === 'hide') confirmMsg = '¿Ocultar todos los productos en ' + selected.length + ' categorías? (Pasarán a Borrador)';
                if(action === 'show') confirmMsg = '¿Publicar todos los productos en ' + selected.length + ' categorías?';
                
                if(!confirm(confirmMsg)) return;

                const btn = $('#btn-catalog-' + action + '-all');
                const originalText = btn.text();
                btn.prop('disabled', true).text('Procesando...');

                $.post(ajaxurl, {
                    action: 'ingram_ajax_manage_catalog',
                    ids: selected,
                    job_action: action,
                    _ajax_nonce: '<?php echo wp_create_nonce("ingram_preview_nonce"); ?>'
                }, function(response) {
                    btn.prop('disabled', false).text(originalText);
                    if(response.success) {
                        alert('Tarea programada: ' + response.data.message);
                        setTimeout(loadLocalCatalog, 2000);
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                });
            }

            $('#btn-catalog-select-all').on('click', function() {
                const anyUnchecked = $('.catalog-select-item:not(:checked)').length > 0;
                $('.catalog-select-item').prop('checked', anyUnchecked);
                $(this).text(anyUnchecked ? 'Desmarcar Todos' : 'Seleccionar Todos');
            });

            $('#btn-catalog-show-all').on('click', () => bulkManageCatalog('show'));
            $('#btn-catalog-hide-all').on('click', () => bulkManageCatalog('hide'));
            $('#btn-catalog-delete-all').on('click', () => bulkManageCatalog('delete'));

            $('#btn-save-selection').on('click', function() {
                 const btn = $(this);
                 const selected = [];
                 $('input[name="ingram_selected_cats[]"]:checked').each(function() {
                     selected.push({
                         cat: $(this).val(),
                         sub: $(this).data('sub') || ''
                     });
                 });
                 
                 const totalCount = selected.length;
                 const cronSchedule = $('#cron_schedule_select').val() || 'daily';
                 const preserveDesc = $('#chk_preserve_desc').is(':checked') ? 1 : 0;
                 
                 btn.prop('disabled', true).text('Guardando...');
                 
                 $.post(ajaxurl, {
                    action: 'ingram_ajax_save_selected_categories',
                    selections: selected,
                    cron_schedule: cronSchedule,
                    preserve_description: preserveDesc,
                    _ajax_nonce: '<?php echo wp_create_nonce("ingram_preview_nonce"); ?>'
                 }, function(response) {
                    btn.prop('disabled', false).text('Ajustes Guardados');
                    $('#saved-count-text').text(totalCount);
                    alert('Ajustes de sincronización guardados correctamente.');
                    setTimeout(() => {
                        btn.text('Guardar Ajustes');
                        location.reload();
                    }, 1500);
                 });
            });
        });
        </script>
        <?php
    }

    public function render_preview_tab() {
        ?>
        <div class="ingram-preview-container">
            <p style="color: #64748b; margin-top:0; font-size:1.05rem;"><?php esc_html_e( 'Test your API connection and see the raw data returned by Ingram Micro directly in this screen before performing any massive synchronization into WooCommerce.', 'ingram-woo' ); ?></p>
            
            <div class="ingram-preview-actions">
                <button type="button" class="button button-primary" id="btn-preview-products">
                    <?php esc_html_e( 'Preview Latest Products', 'ingram-woo' ); ?>
                </button>
                <button type="button" class="button button-secondary" id="btn-preview-categories">
                    <?php esc_html_e( 'Inspect API Categories', 'ingram-woo' ); ?>
                </button>
                <button type="button" class="button button-secondary ingram-diagnostic-btn" id="btn-diagnostic">
                    <?php esc_html_e( 'Run Connection Diagnostics', 'ingram-woo' ); ?>
                </button>
                <span class="spinner" id="preview-spinner" style="float: none; margin: 4px 10px;"></span>
            </div>

            <div id="preview-results-container" style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 20px; border-radius: 8px; display: none; overflow-x: auto; max-height: 600px; box-shadow: inset 0 2px 4px 0 rgb(0 0 0 / 0.03);">
                <!-- Tables will be injected here via JS -->
            </div>
        </div>

        <script type="text/javascript">
        jQuery(document).ready(function($) {
            function loadPreviewData(actionName) {
                $('#preview-spinner').addClass('is-active');
                $('#preview-results-container').hide().html('');

                $.post(ajaxurl, {
                    action: actionName,
                    _ajax_nonce: '<?php echo wp_create_nonce("ingram_preview_nonce"); ?>'
                }, function(response) {
                    $('#preview-spinner').removeClass('is-active');
                    if(response.success) {
                        $('#preview-results-container').html(response.data.html).fadeIn();
                    } else {
                        var errorMsg = response.data && response.data.message ? response.data.message : 'Unknown error';
                        $('#preview-results-container').html('<div class="notice notice-error"><p><strong>Error:</strong> ' + errorMsg + '</p></div>').fadeIn();
                    }
                }).fail(function() {
                    $('#preview-spinner').removeClass('is-active');
                    $('#preview-results-container').html('<div class="notice notice-error"><p>A critical server error occurred.</p></div>').fadeIn();
                });
            }

            $('#btn-preview-products').on('click', function() {
                loadPreviewData('ingram_ajax_preview_products');
            });

            $('#btn-preview-categories').on('click', function() {
                loadPreviewData('ingram_ajax_preview_categories');
            });

            $('#btn-diagnostic').on('click', function() {
                loadPreviewData('ingram_ajax_run_diagnostic');
            });

            $(document).on('click', '.btn-import-category', function() {
                var btn = $(this);
                var cat = btn.data('cat');
                
                btn.prop('disabled', true).text('Importing...');
                
                $.post(ajaxurl, {
                    action: 'ingram_ajax_test_sync_category',
                    category: cat,
                    _ajax_nonce: '<?php echo wp_create_nonce("ingram_preview_nonce"); ?>'
                }, function(response) {
                    btn.prop('disabled', false).text('Import 5 Samples');
                    if(response.success) {
                        alert(response.data.message);
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                });
            });
        });
        </script>
        <?php
    }

    public function ajax_save_selected_categories() {
        check_ajax_referer( 'ingram_preview_nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( array( 'message' => 'Unauthorized' ) );

        $selections = isset( $_POST['selections'] ) ? (array) $_POST['selections'] : array();
        $opts = get_option( 'ingram_woo_options', array() );
        $opts['selected_categories'] = $selections;

        if ( isset( $_POST['cron_schedule'] ) ) {
            $opts['cron_schedule'] = sanitize_text_field( $_POST['cron_schedule'] );
        }
        if ( isset( $_POST['preserve_description'] ) ) {
            $opts['preserve_description'] = intval( $_POST['preserve_description'] );
        }

        update_option( 'ingram_woo_options', $opts );

        if ( class_exists( 'Ingram_Woo_Product_Sync' ) ) {
            Ingram_Woo_Product_Sync::get_instance()->check_cron_schedule();
        }

        wp_send_json_success( array( 'message' => 'Ajustes guardados correctamente.' ) );
    }
}


