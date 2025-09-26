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
        'base_path' => '/KRS3/public',
        'timezone' => 'Europe/Prague'
    ],

    'security' => [
        'password_algo' => PASSWORD_DEFAULT,
        'password_cost' => 12
    ],

	'templates' => [
		'dir' => __DIR__ . '/../templates',
		'cache' => __DIR__ . '/../cache/templates' // pro budoucí caching
	]
];