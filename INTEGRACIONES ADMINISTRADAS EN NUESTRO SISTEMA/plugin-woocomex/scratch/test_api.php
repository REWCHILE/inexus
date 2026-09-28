<?php
// Load WordPress from two levels up (plugins -> wp-content -> public)
require_once('../../../wp-load.php');
require_once('../includes/class-ingram-api.php');

$api = Ingram_Woo_API::get_instance();

// Try to find a valid SKU from the catalog first
echo "Fetching sample from catalog...\n";
$search = $api->search_products(array('pageSize' => 5));

if (is_wp_error($search)) {
    die("API Error: " . $search->get_error_message());
}

if (empty($search['body']['catalog'])) {
    die("No products found in catalog.");
}

foreach ($search['body']['catalog'] as $item) {
    $sku = $item['ingramPartNumber'];
    echo "\n=========================================\n";
    echo "TESTING SKU: $sku\n";
    
    echo "\n--- PRICE AND AVAILABILITY ---\n";
    $pa = $api->get_price_and_availability(array($sku));
    echo "Response Code: " . $pa['code'] . "\n";
    print_r($pa['body']);
    
    echo "\n--- DETAILS ---\n";
    $details = $api->get_product_details($sku);
    echo "Response Code: " . $details['code'] . "\n";
    // Just print relevant keys to avoid bloat
    if (isset($details['body'])) {
        echo "Keys found: " . implode(', ', array_keys($details['body'])) . "\n";
        if (isset($details['body']['mediaLinks'])) {
            echo "MediaLinks: ";
            print_r($details['body']['mediaLinks']);
        } else {
            echo "NO mediaLinks found in details.\n";
        }
    }
}
