<?php
/**
 * Test script for Winpy Scraper
 */

// Load WordPress
define('WP_USE_THEMES', false);
require_once('../../../../wp-load.php');

$sku = '100-100000457BOX'; // Ryzen 5 5500
$url = "https://www.winpy.cl/buscador?q=" . urlencode($sku);

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

$args = array(
    'timeout'    => 30,
    'user-agent' => $user_agents[ array_rand($user_agents) ],
    'headers'    => $headers,
);

echo "Fetching: $url\n";
$response = wp_remote_get( $url, $args );

if ( is_wp_error( $response ) ) {
    echo "Error: " . $response->get_error_message() . "\n";
} else {
    $code = wp_remote_retrieve_response_code( $response );
    echo "HTTP Code: $code\n";
    $body = wp_remote_retrieve_body( $response );
    
    if (strpos($body, 'Ryzen 5 5500') !== false) {
        echo "Success: Found 'Ryzen 5 5500' in response\n";
        // Check for images
        if (preg_match_all('/<img[^>]+src="([^">]+)"/i', $body, $matches)) {
            echo "Found " . count($matches[1]) . " images\n";
            foreach($matches[1] as $img) {
                if (strpos($img, 'winpy.cl') !== false) {
                    echo "Potential Image: $img\n";
                }
            }
        }
    } else {
        echo "Failure: 'Ryzen 5 5500' not found in body\n";
        file_put_contents('winpy_debug.html', $body);
    }
}
