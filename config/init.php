<?php
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
;