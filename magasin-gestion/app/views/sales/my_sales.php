<?php
$pageTitle = 'Mes ventes';
require __DIR__ . '/../partials/header.php';
?>
<header class="page-header">
    <h1 class="page-header__title">Mes ventes</h1>
    <p class="page-header__desc">Historique limité à vos ventes (enregistrées avec ce compte).</p>
</header>

<?php if (nav_can($currentUser ?? null, 'sale_create')): ?>
<div class="page-toolbar">
    <a class="btn" href="index.php?action=sale_create">+ Nouvelle vente</a>
</div>
<?php endif; ?>

<div class="panel panel--flush">
    <?php if (!empty($sales)): ?>
        <?php require __DIR__ . '/../partials/pagination.php'; ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Total (DH)</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($sales as $s): ?>
                    <tr>
                        <td class="text-mono"><?= (int) $s['id'] ?></td>
                        <td><?= htmlspecialchars($s['sale_date'] ?? '') ?></td>
                        <td class="cell-num"><strong><?= htmlspecialchars(format_mad($s['total'])) ?></strong></td>
                        <td><a class="link-action" href="index.php?action=sale_show&id=<?= (int) $s['id'] ?>">Détails</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <p class="empty-state__title">Aucune vente</p>
            <p class="empty-state__hint">Vos ventes apparaîtront ici après enregistrement.</p>
        </div>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
