<?php

date_default_timezone_set('Asia/Manila');
// START SESSION ONCE
// if (session_status() === PHP_SESSION_NONE) {
//     session_start();
// }

// // PROJECT ROOT (File system path - for require/include statements)
// define('ROOT_PATH', dirname(__DIR__));

// // BASE URL (Web-accessible path - for browser URLs)
// // This determines the web path to your application
// // Example: if your app is at http://localhost/agap_link/, this will be '/agap_link'
// define('BASE_URL', dirname($_SERVER['SCRIPT_NAME'], 2));

// // Alternative manual configuration (uncomment if automatic detection fails):
// // define('BASE_URL', '/agap_link');

// // MVC FOLDERS (File system paths - for require/include)
// define('MODEL_PATH', ROOT_PATH . '/model');
// define('VIEW_PATH', ROOT_PATH . '/view');
// define('CONTROLLER_PATH', ROOT_PATH . '/controller');
// define('CONFIG_PATH', ROOT_PATH . '/config');

// // ASSET AND UPLOAD URLS (Web-accessible paths - for browser URLs)
// define('ASSET_URL', value: BASE_URL . '/assets');
// define('UPLOAD_URL', BASE_URL . '/uploads');

// // UPLOAD PATH (File system - for file operations like move_uploaded_file)
// define('UPLOAD_PATH', ROOT_PATH . '/uploads/')
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// FILE SYSTEM ROOT
define('ROOT_PATH', dirname(__DIR__));

// AUTO BASE URL (Localhost + Live)
$host = $_SERVER['HTTP_HOST'];

if ($host === 'localhost' || str_contains($host, '127.0.0.1')) {
    define('BASE_URL', '/agap_link');
} else {
    define('BASE_URL', '');
}

// MVC PATHS (SERVER SIDE)
define('MODEL_PATH', ROOT_PATH . '/model/');
define('VIEW_PATH', ROOT_PATH . '/view/');
define('CONTROLLER_PATH', ROOT_PATH . '/controller/');
define('CONFIG_PATH', ROOT_PATH . '/config/');

// WEB PATHS
define('ASSET_URL', BASE_URL . '/assets');
define('UPLOAD_URL', BASE_URL . '/uploads');

// FILE SYSTEM UPLOAD PATH
define('UPLOAD_PATH', ROOT_PATH . '/uploads/');

require_once __DIR__ . '/sms_config.php';