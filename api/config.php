<?php
// api/config.php
// Database configuration settings

error_reporting(0);
ini_set('display_errors', 0);

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'ubiandb');

// Attempt to establish a connection to the database
$link = @mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($link === false) {
    define('DB_CONNECTION_ERROR', mysqli_connect_error());
} else {
    // Ensure the connection is set to UTF-8
    @mysqli_set_charset($link, "utf8mb4");
    define('DB_CONNECTION_ERROR', false);
}

date_default_timezone_set('Asia/Manila');
?>
