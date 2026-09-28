<?php
// Load WordPress
require_once( dirname(__FILE__) . '/../../../../wp-load.php' );

$client_id = 'JGh6j2wNdiekoIWriRzQzsGBETaJRrXf';
$client_secret = 'ZdGldCHtosjknNeW';

echo "Updating Ingram Micro Sandbox Credentials...\n";

$opts = get_option( 'ingram_woo_options', array() );
$opts['sandbox_client_id'] = $client_id;
$opts['sandbox_client_secret'] = $client_secret;

// Also set environment to sandbox for initial testing if not set
if (empty($opts['environment'])) {
    $opts['environment'] = 'sandbox';
}

update_option( 'ingram_woo_options', $opts );

echo "Successfully updated options.\n";
print_r(get_option('ingram_woo_options'));
