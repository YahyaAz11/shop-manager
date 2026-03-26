<?php
$pageTitle = 'Détail de la vente';
require __DIR__ . '/../partials/header.php';
if (empty($sale)): ?>
<header class="page-header">
    <h1 class="page-header__title">Vente introuvable</h1>
</header>
<p><a class="link-action" href="index.php?action=sales">← Retour aux ventes</a></p>
<?php require __DIR__ . '/../partials/footer.php'; return; endif; ?>
<header class="page-header">
    <h1 class="page-header__title">Vente #<?= (int) $sale['id'] ?></h1>
    <p class="page-header__desc text-muted">Date : <?= htmlspecialchars($sale['sale_date'] ?? '') ?> · Total : <strong><?= htmlspecialchars(format_mad($sale['total'])) ?></strong></p>
    <?php
    $pe = (float) ($sale['payment_especes'] ?? 0);
    $pc = (float) ($sale['payment_carte'] ?? 0);
    $pa = (float) ($sale['payment_autre'] ?? 0);
    ?>
    <p class="lead-muted no-print">
        Paiement :
        Espèces <?= htmlspecialchars(format_mad($pe)) ?>
        · Carte <?= htmlspecialchars(format_mad($pc)) ?>
        <?php if ($pa > 0.001): ?>
            · Autre <?= htmlspecialchars(format_mad($pa)) ?>
        <?php endif; ?>
    </p>
</header>

<div class="panel panel--flush">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Produit</th>
                    <th>Qté</th>
                    <th>Prix unit. (DH)</th>
                    <th>Sous-total (DH)</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $it): ?>
                <tr>
                    <td><?= htmlspecialchars($it['product_name'] ?? ('#' . $it['product_id'])) ?></td>
                    <td class="text-mono"><?= (int) $it['quantity'] ?></td>
                    <td class="cell-num"><?= htmlspecialchars(format_mad($it['price'])) ?></td>
                    <td class="cell-num"><strong><?= htmlspecialchars(format_mad((float) $it['quantity'] * (float) $it['price'])) ?></strong></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<p class="form-actions no-print" style="margin-top: 1rem;">
    <a class="btn" href="index.php?action=sale_ticket&id=<?= (int) $sale['id'] ?>" target="_blank" rel="noopener">Imprimer le ticket</a>
    <a class="btn btn-ghost" href="index.php?action=sales">Retour aux ventes</a>
</p>
<?php require __DIR__ . '/../partials/footer.php'; ?>
