<?php
$db_name = 'local';
$db_user = 'root';
$db_pass = 'root';
$db_host = '127.0.0.1'; // Use IP to avoid socket issues on CLI

$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}

$result = $mysqli->query("SELECT option_value FROM wp_options WHERE option_name = 'ingram_woo_options'");
if ($result) {
    $row = $result->fetch_assoc();
    $options = unserialize($row['option_value']);
    echo "INGRAM OPTIONS:\n";
    print_r($options);
} else {
    echo "Option not found or query failed.\n";
}

$mysqli->close();
