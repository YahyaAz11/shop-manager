<?php
$pageTitle = 'Détail de la vente';
require __DIR__ . '/../partials/header.php';
$backSalesList = sales_list_action_for_user($currentUser ?? null);
$backSalesLabel = (($currentUser['role'] ?? '') === 'vendeur') ? 'mes ventes' : 'les ventes';
if (empty($sale)): ?>
<header class="page-header">
    <h1 class="page-header__title">Vente introuvable</h1>
</header>
<p><a class="link-action" href="index.php?action=<?= htmlspecialchars($backSalesList) ?>">← Retour à <?= htmlspecialchars($backSalesLabel) ?></a></p>
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
                    <th>PU TTC</th>
                    <th>TVA</th>
                    <th>Sous-total TTC</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $sumHt = 0.0;
            $sumVatAmt = 0.0;
            foreach ($items as $it):
                $lineTtc = round((float) $it['quantity'] * (float) $it['price'], 2);
                $vr = normalize_vat_rate_percent($it['vat_rate'] ?? 20);
                $br = sale_line_ht_vat_from_ttc($lineTtc, $vr);
                $sumHt += $br['ht'];
                $sumVatAmt += $br['vat'];
                ?>
                <tr>
                    <td><?= htmlspecialchars($it['product_name'] ?? ('#' . $it['product_id'])) ?></td>
                    <td class="text-mono"><?= (int) $it['quantity'] ?></td>
                    <td class="cell-num"><?= htmlspecialchars(format_mad($it['price'])) ?></td>
                    <td class="cell-num"><?= htmlspecialchars(format_vat_percent($vr)) ?></td>
                    <td class="cell-num"><strong><?= htmlspecialchars(format_mad($lineTtc)) ?></strong></td>
                </tr>
            <?php endforeach;
            $sumHt = round($sumHt, 2);
            $sumVatAmt = round($sumVatAmt, 2);
            ?>
            </tbody>
        </table>
    </div>
    <div class="panel__pad" style="border-top: 1px solid var(--border, #e5e5e5);">
        <p class="text-muted" style="margin:0 0 0.35rem;font-size:0.9rem">Détail TVA (prix unitaires TTC, taux au moment de la vente)</p>
        <ul class="sale-vat-summary" style="list-style:none;margin:0;padding:0;font-size:0.95rem;line-height:1.6">
            <li><span class="text-muted">Total HT</span> <strong><?= htmlspecialchars(format_mad($sumHt)) ?></strong></li>
            <li><span class="text-muted">Montant TVA</span> <strong><?= htmlspecialchars(format_mad($sumVatAmt)) ?></strong></li>
            <li><span class="text-muted">Total TTC</span> <strong><?= htmlspecialchars(format_mad($sale['total'])) ?></strong></li>
        </ul>
    </div>
</div>
<p class="form-actions no-print" style="margin-top: 1rem;">
    <a class="btn" href="index.php?action=sale_ticket&id=<?= (int) $sale['id'] ?>" target="_blank" rel="noopener">Imprimer le ticket</a>
    <a class="btn btn-ghost" href="index.php?action=<?= htmlspecialchars($backSalesList) ?>">Retour à <?= htmlspecialchars($backSalesLabel) ?></a>
</p>
<?php require __DIR__ . '/../partials/footer.php'; ?>
