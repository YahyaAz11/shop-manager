<?php
$pageTitle = 'Stock faible';
$supplierAccountId = isset($supplierAccountId) ? (int) $supplierAccountId : 0;
require __DIR__ . '/../partials/header.php';
?>
<header class="page-header">
    <h1 class="page-header__title">Stock faible</h1>
    <p class="page-header__desc">Articles dont le stock est au ou en dessous du seuil défini par produit.</p>
</header>

<div class="panel panel--flush">
    <?php if (!empty($products)): ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Stock</th>
                        <th>Seuil</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($products as $p): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($p['name']) ?></strong></td>
                        <td><span class="badge-stock badge-stock--low"><?= (int) $p['stock'] ?></span></td>
                        <td class="text-mono"><?= (int) ($p['stock_alert_threshold'] ?? 5) ?></td>
                        <td class="table-actions">
                            <?php
                            $own = $supplierAccountId > 0 && (int) ($p['supplier_id'] ?? 0) === $supplierAccountId;
                            $canEditRow = !empty($canManage) || $own;
                            ?>
                            <?php if ($canEditRow): ?>
                                <a class="link-action" href="index.php?action=product_edit&id=<?= (int) $p['id'] ?>">Modifier</a>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <p class="empty-state__title">Aucune alerte</p>
            <p class="empty-state__hint">Tous vos stocks sont au-dessus du seuil.</p>
        </div>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
