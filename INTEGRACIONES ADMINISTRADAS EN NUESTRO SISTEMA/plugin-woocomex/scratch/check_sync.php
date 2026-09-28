<?php
define('WP_USE_THEMES', false);
// Try to find wp-load.php
$path = __DIR__;
while ($path !== DIRECTORY_SEPARATOR && !file_exists($path . DIRECTORY_SEPARATOR . 'wp-load.php')) {
    $path = dirname($path);
}
if (file_exists($path . DIRECTORY_SEPARATOR . 'wp-load.php')) {
    require_once($path . DIRECTORY_SEPARATOR . 'wp-load.php');
} else {
    die("Could not find wp-load.php\n");
}

$logs = get_option('ingram_woo_sync_logs', []);
$status = get_option('ingram_woo_active_sync', []);

echo "SYNC STATUS:\n";
print_r($status);
echo "\n\nRECENT LOGS:\n";
print_r(array_slice($logs, 0, 10));

if (function_exists('as_get_scheduled_actions')) {
    $pending = as_get_scheduled_actions(['group' => 'ingram_sync', 'status' => 'pending']);
    echo "\n\nPENDING AS ACTIONS: " . count($pending) . "\n";
    if (!empty($pending)) {
        print_r(array_slice($pending, 0, 3));
    }
}
