<?php
// We can't easily include wp-load.php from here without knowing the exact path relative to this file
// But we can just use pure PHP for the test if we mock the request part or just use file_get_contents if allowed (usually wp_remote_get is better)
// Actually, I can just use curl to test the regex.

function fetch($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
        'Accept-Language: es-ES,es;q=0.9,en;q=0.8',
        'Cache-Control: no-cache',
        'Pragma: no-cache'
    ));
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

$sku = 'GV-N4060GAMING-8GD';
$url = "https://www.spdigital.cl/search/?q=" . urlencode( $sku );

echo "Searching for SKU: $sku at $url\n";
$body = fetch($url);

// Look for product link
if ( preg_match( '/class="Fractal-ProductCard--image"[^>]*href="([^">]+)"/is', $body, $matches ) ) {
    echo "Product Link: " . $matches[1] . "\n";
    $product_url = "https://www.spdigital.cl" . $matches[1];
    
    echo "Fetching product page: $product_url\n";
    $body2 = fetch($product_url);
    
    // Look for image
    if ( preg_match( '/Fractal-ProductImage.*?src="([^">]+)"/is', $body2, $img_matches ) ) {
        echo "Image URL: " . $img_matches[1] . "\n";
    } else {
        echo "Image not found on product page.\n";
    }
    
    // Look for description
    // Based on subagent, it might be product-detail-module--description--
    if ( preg_match( '/class="[^"]*description[^"]*"[^>]*>(.*?)<\/div>/is', $body2, $desc_matches ) ) {
         echo "Description found (first 100 chars): " . substr(strip_tags($desc_matches[1]), 0, 100) . "...\n";
    } else {
        echo "Description not found.\n";
    }
} else {
    echo "No product link found in search results.\n";
    echo "Body snippet (first 2000 chars): " . substr($body, 0, 2000) . "\n";
    // Search for any href that looks like a product
    if ( preg_match_all( '/href="(\/p\/[^">]+)"/is', $body, $matches ) ) {
        echo "Found potential product links:\n";
        print_r(array_unique($matches[1]));
    }
}
