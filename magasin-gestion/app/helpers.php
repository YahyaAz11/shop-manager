<?php

declare(strict_types=1);

/** Requête en HTTPS (directe ou derrière un reverse proxy). */
function is_https_request(): bool
{
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        return true;
    }
    $fwd = strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
    if ($fwd === 'https') {
        return true;
    }

    return false;
}

/**
 * Délai d’inactivité : met à jour l’horodatage ou déconnecte et redirige vers la connexion.
 *
 * @param list<string> $publicActions
 */
function session_enforce_idle_timeout(string $action, array $publicActions): void
{
    if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
        return;
    }

    /** @var array<string, mixed> $cfg */
    $cfg = require CONFIG_PATH . '/session.php';
    $maxIdle = (int) ($cfg['idle_timeout_seconds'] ?? 1800);
    if ($maxIdle < 120) {
        $maxIdle = 120;
    }

    $now = time();
    $last = (int) ($_SESSION['_session_last_activity'] ?? $now);

    if (($now - $last) > $maxIdle) {
        unset($_SESSION['user'], $_SESSION['_session_last_activity']);
        if (!in_array($action, $publicActions, true)) {
            $_SESSION['flash_error'] = 'Votre session a expiré (inactivité). Veuillez vous reconnecter.';
            header('Location: index.php?action=login');
            exit;
        }

        return;
    }

    $_SESSION['_session_last_activity'] = $now;
}

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

    /** @var array<string, list<string>> $map */
    $map = require CONFIG_PATH . '/permissions.php';

    $supplierPoActions = [
        'supplier_purchase_orders',
        'supplier_purchase_order_show',
        'supplier_purchase_order_respond',
    ];
    if (in_array($action, $supplierPoActions, true)) {
        $allowedFournisseur = $map['fournisseur'] ?? [];

        return $role === 'fournisseur' && in_array($action, $allowedFournisseur, true);
    }

    // Création de vente et « Mes ventes » : réservés au vendeur. Consultation (liste, détail, ticket) : admin inclus.
    $vendeurExclusiveSalesActions = [
        'sale_create',
        'sale_store',
        'my_sales',
    ];
    if (in_array($action, $vendeurExclusiveSalesActions, true)) {
        $allowedVendeur = $map['vendeur'] ?? [];

        return $role === 'vendeur' && in_array($action, $allowedVendeur, true);
    }

    if ($role === 'admin') {
        return true;
    }
    $allowed = $map[$role] ?? [];

    return in_array($action, $allowed, true);
}

/** @param array<string, mixed>|null $user */
function nav_can(?array $user, string $action): bool
{
    return user_can_access($user, $action);
}

/** Liste des ventes dans l’UI : toutes (admin) ou seulement les siennes (vendeur). */
function sales_list_action_for_user(?array $user): string
{
    if ($user !== null && (($user['role'] ?? '') === 'vendeur')) {
        return 'my_sales';
    }

    return 'sales';
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
 * Taux de TVA en pourcentage (0–100), défaut 20.
 *
 * @param float|int|string|null $value
 */
function normalize_vat_rate_percent($value): float
{
    if ($value === null || $value === '') {
        return 20.0;
    }
    $n = (float) $value;
    if ($n < 0) {
        return 0.0;
    }
    if ($n > 100) {
        return 100.0;
    }

    return round($n, 2);
}

/**
 * Décompose un sous-total TTC (prix unit. TTC × qté) en HT et montant de TVA.
 * Le prix catalogue / caisse est considéré TTC.
 *
 * @return array{ht: float, vat: float, ttc: float}
 */
function sale_line_ht_vat_from_ttc(float $lineTtc, float $vatRatePercent): array
{
    $ttc = round($lineTtc, 2);
    $r = $vatRatePercent / 100.0;
    if ($r <= 0) {
        return ['ht' => $ttc, 'vat' => 0.0, 'ttc' => $ttc];
    }
    $ht = round($ttc / (1 + $r), 2);
    $vat = round($ttc - $ht, 2);

    return ['ht' => $ht, 'vat' => $vat, 'ttc' => $ttc];
}

/**
 * @param float|int|string|null $pct
 */
function format_vat_percent($pct): string
{
    $n = normalize_vat_rate_percent($pct);

    return rtrim(rtrim(number_format($n, 2, ',', ' '), '0'), ',') . ' %';
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
