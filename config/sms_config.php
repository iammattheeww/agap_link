<?php
// Ensure ROOT_PATH is defined and Dotenv is loaded if not already
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}
require_once ROOT_PATH . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(ROOT_PATH);
$dotenv->safeLoad();

// ─── PHILSMS API CONFIGURATION ────────────────────────────────────────────────
define('PHILSMS_API_KEY',   $_ENV['PHILSMS_API_KEY']);
define('PHILSMS_API_URL',   $_ENV['PHILSMS_API_URL']);
define('PHILSMS_SENDER_ID', $_ENV['PHILSMS_SENDER_ID']);
