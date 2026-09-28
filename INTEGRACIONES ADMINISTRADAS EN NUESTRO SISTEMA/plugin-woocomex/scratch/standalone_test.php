<?php
/**
 * Standalone Ingram Micro API Test Script
 * Does not depend on WordPress.
 */

$client_id = 'JGh6j2wNdiekoIWriRzQzsGBETaJRrXf';
$client_secret = 'ZdGldCHtosjknNeW';
$token_url = 'https://api.ingrammicro.com/oauth/oauth30/token';
$base_url = 'https://api.ingrammicro.com/sandbox/resellers/v6';

echo "1. Authenticating...\n";

$ch = curl_init($token_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'grant_type' => 'client_credentials',
    'client_id' => $client_id,
    'client_secret' => $client_secret
]));
curl_setopt($ch, CURLOPT_TIMEOUT, 20);

$token_response = curl_exec($ch);
$token_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($token_code !== 200) {
    echo "FAILED: Auth returned HTTP $token_code\n";
    echo $token_response . "\n";
    exit;
}

$token_data = json_decode($token_response, true);
$token = $token_data['access_token'];
echo "SUCCESS: Token obtained: " . substr($token, 0, 15) . "...\n\n";

echo "2. Fetching Catalog Sample (to get a SKU)...\n";

$search_url = $base_url . '/catalog?pageSize=1';
$ch = curl_init($search_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'IM-CustomerNumber: 20-213702', // Trying common sandbox customer number
    'IM-CountryCode: US',
    'IM-CorrelationID: ' . bin2hex(random_bytes(16)),
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 20);

$search_response = curl_exec($ch);
$search_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($search_code !== 200) {
    echo "FAILED: Catalog Search returned HTTP $search_code\n";
    echo $search_response . "\n";
    exit;
}

$search_data = json_decode($search_response, true);
$sku = $search_data['catalog'][0]['ingramPartNumber'] ?? '';

if (!$sku) {
    echo "FAILED: No products found in sandbox catalog.\n";
    exit;
}

echo "SUCCESS: Found SKU: $sku (" . ($search_data['catalog'][0]['description'] ?? '') . ")\n\n";

echo "3. Fetching Product Details for SKU $sku (Looking for Images)...\n";

$details_url = $base_url . '/catalog/details/' . $sku;
$ch = curl_init($details_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'IM-CustomerNumber: 20-333333',
    'IM-CountryCode: US',
    'IM-CorrelationID: ' . bin2hex(random_bytes(16)),
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 20);

$details_response = curl_exec($ch);
$details_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($details_code !== 200) {
    echo "FAILED: Product Details returned HTTP $details_code\n";
    exit;
}

$details_data = json_decode($details_response, true);

echo "--- DATA MINING FOR IMAGES ---\n";

function find_images($data, &$found, $prefix = '') {
    if (!is_array($data)) return;
    foreach ($data as $key => $value) {
        $path = $prefix ? $prefix . '.' . $key : $key;
        if (is_string($value) && preg_match('/\.(jpg|png|gif|jpeg)$/i', $value)) {
            $found[$path] = $value;
        }
        if (is_array($value)) {
            find_images($value, $found, $path);
        }
    }
}

$found_images = [];
find_images($details_data, $found_images);

if (empty($found_images)) {
    echo "❌ No image URLs found in the JSON response.\n";
    echo "Keys found: " . implode(', ', array_keys($details_data)) . "\n";
} else {
    echo "✅ IMAGES FOUND:\n";
    foreach ($found_images as $path => $url) {
        echo "[$path] => $url\n";
    }
}
