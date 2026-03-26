<?php
$pageTitle = 'Ventes';
require __DIR__ . '/../partials/header.php';
?>
<header class="page-header">
    <h1 class="page-header__title">Ventes</h1>
    <p class="page-header__desc">Historique des ventes et accès au détail de chaque ticket.</p>
</header>

<div class="page-toolbar">
    <a class="btn" href="index.php?action=sale_create">+ Nouvelle vente</a>
</div>

<div class="panel panel--flush">
    <?php if (!empty($sales)): ?>
        <?php require __DIR__ . '/../partials/pagination.php'; ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Vendeur</th>
                        <th>Total (DH)</th>
                        <th>Paiement</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($sales as $s): ?>
                    <?php
                    $pe = (float) ($s['payment_especes'] ?? 0);
                    $pc = (float) ($s['payment_carte'] ?? 0);
                    $pa = (float) ($s['payment_autre'] ?? 0);
                    $bits = [];
                    if ($pe > 0.001) $bits[] = 'Esp. ' . format_mad($pe, 0);
                    if ($pc > 0.001) $bits[] = 'CB ' . format_mad($pc, 0);
                    if ($pa > 0.001) $bits[] = 'Autre ' . format_mad($pa, 0);
                    $payLabel = $bits !== [] ? implode(' · ', $bits) : '—';
                    ?>
                    <tr>
                        <td class="text-mono"><?= (int) $s['id'] ?></td>
                        <td><?= htmlspecialchars($s['sale_date'] ?? '') ?></td>
                        <td><?= htmlspecialchars($s['seller_name'] ?? '—') ?></td>
                        <td class="cell-num"><strong><?= htmlspecialchars(format_mad($s['total'])) ?></strong></td>
                        <td class="text-muted" style="font-size:0.85rem"><?= htmlspecialchars($payLabel) ?></td>
                        <td><a class="link-action" href="index.php?action=sale_show&id=<?= (int) $s['id'] ?>">Détails</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <p class="empty-state__title">Aucune vente</p>
            <p class="empty-state__hint">Enregistrez une première vente pour l’historique.</p>
        </div>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
