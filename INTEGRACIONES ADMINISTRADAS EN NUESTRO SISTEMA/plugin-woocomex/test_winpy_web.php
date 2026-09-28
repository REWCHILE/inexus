<?php
/**
 * Test script for Winpy Scraper (Web Access)
 */

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
    'DNT'                       => '1',
);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_USERAGENT, $user_agents[array_rand($user_agents)]);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
    'Accept-Language: es-CL,es;q=0.9,en;q=0.8',
));
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$body = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<h1>Winpy Scraper Test</h1>";
echo "<p>URL: $url</p>";
echo "<p>HTTP Code: $code</p>";

if ($code == 200) {
    echo "<h2>Success!</h2>";
    if (strpos($body, 'Ryzen 5 5500') !== false) {
        echo "<p>Found 'Ryzen 5 5500' in body.</p>";
        
        // Find product links
        if (preg_match_all('/href="(\/venta\/[^"]+)"/i', $body, $matches)) {
            echo "<h3>Product Links found:</h3><ul>";
            foreach(array_unique($matches[1]) as $link) {
                echo "<li><a href='https://www.winpy.cl$link'>https://www.winpy.cl$link</a></li>";
            }
            echo "</ul>";
        }

        // Find images
        if (preg_match_all('/src="([^"]+winpy\.cl\/[^"]+\.(?:jpg|png|gif|jpeg)[^"]*)"/i', $body, $matches)) {
            echo "<h3>Images found:</h3>";
            foreach(array_unique($matches[1]) as $img) {
                echo "<div style='display:inline-block; margin:5px;'><img src='$img' style='width:100px;'><br><small>$img</small></div>";
            }
        }
    } else {
        echo "<p>Could not find product name in body. Winpy might be showing a 'no results' page or a block page.</p>";
        echo "<pre>" . htmlspecialchars(substr($body, 0, 1000)) . "</pre>";
    }
} else {
    echo "<h2>Failure</h2>";
    echo "<p>Response body snippet:</p>";
    echo "<pre>" . htmlspecialchars(substr($body, 0, 1000)) . "</pre>";
}
