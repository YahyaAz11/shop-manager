<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', BASE_PATH . '/config');

require_once APP_PATH . '/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    /** @var array<string, mixed> $sessionConfig */
    $sessionConfig = require CONFIG_PATH . '/session.php';
    $cookieLifetime = (int) ($sessionConfig['cookie_lifetime'] ?? 0);
    $sameSite = (string) ($sessionConfig['cookie_samesite'] ?? 'Lax');
    if (!in_array($sameSite, ['Lax', 'Strict', 'None'], true)) {
        $sameSite = 'Lax';
    }
    $secure = is_https_request();
    if ($sameSite === 'None' && !$secure) {
        $sameSite = 'Lax';
    }
    $gcLife = (int) ($sessionConfig['gc_maxlifetime_seconds'] ?? 3600);
    $idle = (int) ($sessionConfig['idle_timeout_seconds'] ?? 1800);
    if ($gcLife < $idle) {
        $gcLife = $idle;
    }
    ini_set('session.gc_maxlifetime', (string) $gcLife);

    session_set_cookie_params([
        'lifetime' => $cookieLifetime,
        'path'     => '/',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => $sameSite,
    ]);
    session_start();
}

spl_autoload_register(static function (string $class): void {
    foreach ([
        APP_PATH . '/Controllers/' . $class . '.php',
        APP_PATH . '/Models/' . $class . '.php',
    ] as $file) {
        if (is_file($file)) {
            require_once $file;
            return;
        }
    }
    if ($class === 'Database' && is_file(CONFIG_PATH . '/database.php')) {
        require_once CONFIG_PATH . '/database.php';
    }
});
