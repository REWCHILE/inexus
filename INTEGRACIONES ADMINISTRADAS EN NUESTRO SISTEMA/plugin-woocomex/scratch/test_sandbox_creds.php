<?php
// Load WordPress
require_once( dirname(__FILE__) . '/../../../../wp-load.php' );

$client_id = 'JGh6j2wNdiekoIWriRzQzsGBETaJRrXf';
$client_secret = 'ZdGldCHtosjknNeW';
$token_url = 'https://api.ingrammicro.com/oauth/oauth30/token';

echo "Testing Sandbox Credentials...\n";
echo "Client ID: $client_id\n";

$response = wp_remote_post( $token_url, array(
    'body' => array(
        'grant_type' => 'client_credentials',
        'client_id' => $client_id,
        'client_secret' => $client_secret,
    ),
    'timeout' => 20,
) );

if ( is_wp_error( $response ) ) {
    echo "Connection Error: " . $response->get_error_message() . "\n";
    exit;
}

$code = wp_remote_retrieve_response_code( $response );
$body = wp_remote_retrieve_body( $response );

echo "HTTP Code: $code\n";
echo "Response Body: $body\n";

$data = json_decode($body, true);
if (isset($data['access_token'])) {
    echo "SUCCESS: Token obtained!\n";
} else {
    echo "FAILURE: Could not obtain token.\n";
}
