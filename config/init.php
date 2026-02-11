<?php
// START SESSION ONCE
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// PROJECT ROOT
define('ROOT_PATH', dirname(__DIR__));

// BASE URL (BROWSER PATH FOR REDIRECTS AND LINKS)
define('BASE_URL', dirname($_SERVER['SCRIPT_NAME'], 2));


// MVC FOLDERS
define('MODEL_PATH', ROOT_PATH . '/model/');
define('VIEW_PATH', ROOT_PATH . '/view/');
define('CONTROLLER_PATH', ROOT_PATH . '/controller/');
define("ASSET_PATH", ROOT_PATH . "/assets/");
define("UPLOAD_PATH", ROOT_PATH . "/uploads/");