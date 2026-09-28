<?php
define('ABSPATH', dirname(__FILE__) . '/../../../');
require_once ABSPATH . 'wp-load.php';

$api = Ingram_Woo_API::get_instance();
echo "Running test search...\n";
$result = $api->search_products(array('keyword' => 'test'));
echo "Result code: " . (is_wp_error($result) ? $result->get_error_code() : 'Success') . "\n";
if (is_wp_error($result)) {
    echo "Message: " . $result->get_error_message() . "\n";
    print_r($result->get_error_data());
} else {
    print_r($result);
}
