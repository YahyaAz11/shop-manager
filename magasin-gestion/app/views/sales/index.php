<?php
$pageTitle = 'Ventes';
require __DIR__ . '/../partials/header.php';
$saleLinesBySaleId = $saleLinesBySaleId ?? [];
?>
<header class="page-header">
    <h1 class="page-header__title">Ventes</h1>
    <p class="page-header__desc">Toutes les ventes du magasin : articles, montants, vendeur et modes de paiement.</p>
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
                        <th>Vendeur</th>
                        <th>Articles</th>
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
                    $sid = (int) $s['id'];
                    $lines = $saleLinesBySaleId[$sid] ?? [];
                    ?>
                    <tr>
                        <td class="text-mono"><?= $sid ?></td>
                        <td><?= htmlspecialchars($s['sale_date'] ?? '') ?></td>
                        <td><?= htmlspecialchars($s['seller_name'] ?? '—') ?></td>
                        <td class="sale-row-articles">
                            <?php if ($lines !== []): ?>
                                <ul class="sale-lines-inline">
                                    <?php foreach ($lines as $ln): ?>
                                        <?php
                                        $qty = (int) ($ln['quantity'] ?? 0);
                                        $pu = (float) ($ln['price'] ?? 0);
                                        $lineTot = round($qty * $pu, 2);
                                        $pname = (string) ($ln['product_name'] ?? ('#' . ($ln['product_id'] ?? '')));
                                        ?>
                                        <li><strong><?= htmlspecialchars($pname) ?></strong><span class="text-muted"> — <?= $qty ?> × <?= htmlspecialchars(format_mad($pu)) ?> = <?= htmlspecialchars(format_mad($lineTot)) ?></span></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="cell-num"><strong><?= htmlspecialchars(format_mad($s['total'])) ?></strong></td>
                        <td class="text-muted" style="font-size:0.85rem"><?= htmlspecialchars($payLabel) ?></td>
                        <td><a class="link-action" href="index.php?action=sale_show&id=<?= $sid ?>">Détails</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <p class="empty-state__title">Aucune vente</p>
            <?php if (nav_can($currentUser ?? null, 'sale_create')): ?>
            <p class="empty-state__hint">Enregistrez une première vente pour l’historique.</p>
            <?php else: ?>
            <p class="empty-state__hint">L’historique apparaîtra ici dès qu’un vendeur aura enregistré une vente.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
