<?php

declare(strict_types=1);

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }

    return (string) $_SESSION['_csrf'];
}

function csrf_field(): void
{
    echo '<input type="hidden" name="_csrf" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function csrf_verify(): bool
{
    $sent = $_POST['_csrf'] ?? '';
    if (!is_string($sent) || $sent === '' || empty($_SESSION['_csrf'])) {
        return false;
    }

    return hash_equals((string) $_SESSION['_csrf'], $sent);
}

function request_id(): ?int
{
    $v = $_GET['id'] ?? $_POST['id'] ?? null;
    if ($v === null || $v === '') {
        return null;
    }
    $i = (int) $v;

    return $i > 0 ? $i : null;
}

/** @param array<string, mixed>|null $user */
function user_can_access(?array $user, string $action): bool
{
    $public = ['login', 'do_login', 'logout'];
    if (in_array($action, $public, true)) {
        return true;
    }
    if (empty($user['role'])) {
        return false;
    }
    $role = (string) $user['role'];
    if ($role === 'admin') {
        return true;
    }
    /** @var array<string, list<string>> $map */
    $map = require CONFIG_PATH . '/permissions.php';
    $allowed = $map[$role] ?? [];

    return in_array($action, $allowed, true);
}

/** @param array<string, mixed>|null $user */
function nav_can(?array $user, string $action): bool
{
    return user_can_access($user, $action);
}

function flash_take(string $key): ?string
{
    if (empty($_SESSION[$key]) || !is_string($_SESSION[$key])) {
        return null;
    }
    $m = $_SESSION[$key];
    unset($_SESSION[$key]);

    return $m;
}

/**
 * Affichage d’un montant en dirhams marocains (MAD / DH).
 *
 * @param float|int|string|null $amount
 */
function format_mad($amount, int $decimals = 2): string
{
    if ($amount === null || $amount === '') {
        $n = 0.0;
    } elseif (is_numeric($amount)) {
        $n = (float) $amount;
    } else {
        $n = 0.0;
    }

    return number_format($n, $decimals, ',', ' ') . ' DH';
}

function shop_config(): array
{
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }
    $file = CONFIG_PATH . '/shop.php';
    $c = is_file($file) ? require $file : [];
    $cached = is_array($c) ? $c : [];

    return $cached;
}

function shop_page_size(): int
{
    return 20;
}

/**
 * Premier fichier logo trouvé sous public/assets/images/ (chemin relatif pour &lt;img src&gt;).
 */
function brand_logo_web_path(): ?string
{
    $dir = BASE_PATH . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images';
    if (!is_dir($dir)) {
        return null;
    }
    $candidates = [
        '1212.png',
        'omnix-logo.png',
        'Omnix-logo.png',
        'Dynamic Omnix Logo with Geometric Loop.png',
    ];
    foreach ($candidates as $file) {
        if (is_file($dir . DIRECTORY_SEPARATOR . $file)) {
            return 'assets/images/' . rawurlencode($file);
        }
    }

    return null;
}

/**
 * @return array{page: int, per_page: int, offset: int, total_pages: int, total: int}
 */
function pagination_state(int $requestedPage, int $totalItems, ?int $perPage = null): array
{
    $perPage = max(1, $perPage ?? shop_page_size());
    $totalPages = max(1, (int) ceil($totalItems / $perPage));
    $page = max(1, min($requestedPage, $totalPages));

    return [
        'page'         => $page,
        'per_page'     => $perPage,
        'offset'       => ($page - 1) * $perPage,
        'total_pages'  => $totalPages,
        'total'        => $totalItems,
    ];
}

/**
 * @param array{page:int,per_page:int,offset:int,total_pages:int,total:int} $state
 * @param array<string, string|int|float|bool> $extraQuery
 */
function pagination_for_view(string $action, array $state, array $extraQuery = []): array
{
    return [
        'action'      => $action,
        'page'        => $state['page'],
        'total_pages' => $state['total_pages'],
        'total'       => $state['total'],
        'per_page'    => $state['per_page'],
        'extra'       => $extraQuery,
    ];
}
