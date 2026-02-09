<?php
// FOR HELIOHOST SETUP
// define('DB_HOST', 'localhost');  // or morty.heliohost.org if localhost doesn't work
// define('DB_USER', 'agaplink_admin');  // Replace with your actual username
// define('DB_PASS', 'JayramGarcia123');
// define('DB_NAME', 'agaplink_db');  // Replace with your actual database name

// OR USING THE ORIGINAL FORMAT
$host = "localhost";
$dbname = "agap_link";
$username ="root";
$password="";

// $host = "morty.heliohost.org";
// $dbname = "agaplink_db";
// $username = "agaplink_admin";
// $password = "JayramGarcia123";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $conn->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}
