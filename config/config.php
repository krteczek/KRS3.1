<?php
// config/config.php - JEDINÝ konfigurační soubor
declare(strict_types=1);

return [
    'database' => [
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'name' => $_ENV['DB_NAME'] ?? 'krs',
        'user' => $_ENV['DB_USER'] ?? 'root',
        'pass' => $_ENV['DB_PASS'] ?? '',
        'charset' => 'utf8mb4',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    ],

    'site' => [
        'name' => 'KRS Redakční systém',
        'url' => 'http://localhost/KRS3',
        'base_path' => '/KRS3/public/',
        'timezone' => 'Europe/Prague'
    ],

    'security' => [
        'password_algo' => PASSWORD_DEFAULT,
        'password_cost' => 12
    ],

    'csrf' => [
        'token_expire' => 3600,        // 1 hodina v sekundách
        'max_tokens' => 10,            // Maximální počet současných tokenů
        'validate_origin' => true,     // Validovat HTTP origin
        'validate_referer' => true,    // Validovat HTTP referer
        'token_name' => 'csrf_token'   // Název tokenu v formulářích
    ],

    'templates' => [
        'dir' => __DIR__ . '/../templates',
        'cache' => __DIR__ . '/../cache/templates' // pro budoucí caching
    ],

    'logs' => [
        'level' => 'DEBUG',        // DEBUG, INFO, WARNING, ERROR, CRITICAL, EXCEPTION, NONE
        'echo' => true,           // Vypnout výpis na obrazovku v produkci
        'file' => true,            // Zapnout zápis do souboru
        'rotation' => 'daily',     // daily, hourly, size, none
        'max_size' => 10240,    // 10MB (pro size rotaci)
        'max_files' => 30,         // Počet souborů k uchování
        'dir' => __DIR__ . '/../logs/',
        'file' => 'app.log'
    ],

    // Nové sekce pro kompatibilitu s existujícím kódem
    'app' => [
        'debug' => true,
        'env' => $_ENV['APP_ENV'] ?? 'development'
    ],

    'session' => [
        'name' => 'krs3_session',
        'lifetime' => 7200, // 2 hodiny
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => true
    ],
	'uploads' => [
	    'gallery' => [
	        'path' => __DIR__ . '/../public/uploads/gallery',
	        'url' => '/uploads/gallery/',
	        'max_file_size' => 10 * 1024 * 1024, // 10MB
	        'allowed_types' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
	        'thumb_width' => 300,
	        'thumb_height' => 200
	    ]
	],
];