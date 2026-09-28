<?php

require __DIR__ . '/vendor/autoload.php';

function testDDG($query) {
    // 1. Get token from DDG
    $ch = curl_init("https://duckduckgo.com/?q=" . urlencode($query));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    $html = curl_exec($ch);
    curl_close($ch);

    if (preg_match('/vqd=([0-9-]+)/', $html, $m)) {
        $vqd = $m[1];
        $apiUrl = "https://duckduckgo.com/i.js?l=es-es&o=json&q=" . urlencode($query) . "&vqd=" . $vqd;
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        $json = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($json, true);
        if (!empty($data['results'])) {
            echo "DDG Image Results for '$query':\n";
            foreach (array_slice($data['results'], 0, 3) as $r) {
                echo "Image: " . $r['image'] . "\n";
                echo "Title: " . $r['title'] . "\n";
            }
            return true;
        }
    }
    echo "DDG failed for '$query'\n";
    return false;
}

testDDG('Lenovo ThinkPad E14 Gen 5');
testDDG('Kingston KC3000 SSD');
testDDG('Logitech MX Master 3S');
