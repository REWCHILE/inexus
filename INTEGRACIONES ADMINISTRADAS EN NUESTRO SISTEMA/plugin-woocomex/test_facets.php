<?php
define('ABSPATH', dirname(__FILE__) . '/../../../');
require_once ABSPATH . 'wp-load.php';

$api = Ingram_Woo_API::get_instance();
echo "Testing search with includeFacets...\n";
// Using a category we know exists from the user's screenshot
$params = array(
    'category' => 'Cables',
    'pageSize' => 1,
    'includeFacets' => true
);

$result = $api->search_products($params);

if (is_wp_error($result)) {
    echo "Error: " . $result->get_error_message() . "\n";
} else {
    echo "HTTP Code: " . $result['code'] . "\n";
    echo "Body Keys: " . implode(', ', array_keys($result['body'])) . "\n";
    if (isset($result['body']['facets'])) {
        echo "Facets found!\n";
        print_r($result['body']['facets']);
    } else {
        echo "No facets in response.\n";
        // Let's check the whole body structure
        print_r($result['body']);
    }
}
