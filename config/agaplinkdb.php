<?php
$db_config = [
    'local' => [
        'host' => 'localhost',
        'dbname' => 'agap_link',
        'username' => 'root',
        'password' => ''
    ],
    'heliohost' => [
        'host' => 'localhost',  // or morty.heliohost.org // Heliohost also supports localhost
        'dbname' => 'agaplink_db',
        'username' => 'agaplink_admin',
        'password' => 'JayramGarcia123'
    ]
];

// CONDITIONAL STATEMENT TO SWITCH BETWEEN LOCAL AND CLOUD SERVER CONFIGURATION
$isHelioHost = strpos($_SERVER['HTTP_HOST'], 'helioho.st') !== false || strpos($_SERVER['HTTP_HOST'], 'heliohost.org') !== false;
$config = $isHelioHost ? $db_config['heliohost'] : $db_config['local'];


// PDO
try {
    $conn = new PDO(
        "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8",
        $config['username'],
        $config['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
        ]
    );
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}
