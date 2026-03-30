<?php
declare(strict_types=1);
/** @var array<string, mixed>|null $pagination */
if (empty($pagination) || (int) ($pagination['total_pages'] ?? 1) <= 1) {
    return;
}
$action = (string) $pagination['action'];
$page = (int) $pagination['page'];
$totalPages = (int) $pagination['total_pages'];
$totalItems = (int) ($pagination['total'] ?? 0);
$perPage = (int) ($pagination['per_page'] ?? shop_page_size());
$extra = isset($pagination['extra']) && is_array($pagination['extra']) ? $pagination['extra'] : [];
$from = $totalItems === 0 ? 0 : (($page - 1) * $perPage + 1);
$to = min($totalItems, $page * $perPage);

$buildUrl = static function (int $p) use ($action, $extra): string {
    $q = array_merge(['action' => $action, 'page' => $p], $extra);

    return 'index.php?' . http_build_query($q);
};
?>
<nav class="pagination" aria-label="Pagination">
    <p class="pagination__meta">
        <?= (int) $from ?>–<?= (int) $to ?> sur <?= (int) $totalItems ?>
    </p>
    <div class="pagination__links">
        <?php if ($page > 1): ?>
            <a class="btn btn-ghost btn-compact" href="<?= htmlspecialchars($buildUrl($page - 1)) ?>">Précédent</a>
        <?php endif; ?>
        <span class="pagination__current">Page <?= $page ?> / <?= $totalPages ?></span>
        <?php if ($page < $totalPages): ?>
            <a class="btn btn-ghost btn-compact" href="<?= htmlspecialchars($buildUrl($page + 1)) ?>">Suivant</a>
        <?php endif; ?>
    </div>
</nav>
