<?php
declare(strict_types=1);
/** @var array<string, mixed>|false|null $sale */
/** @var list<array<string, mixed>> $items */
/** @var array<string, mixed>|false|null $seller */
$shop = shop_config();
if (empty($sale)) {
    http_response_code(404);
    echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><title>Ticket</title></head><body><p>Vente introuvable.</p></body></html>';
    return;
}
$pe = (float) ($sale['payment_especes'] ?? 0);
$pc = (float) ($sale['payment_carte'] ?? 0);
$pa = (float) ($sale['payment_autre'] ?? 0);
$identLines = [];
foreach (
    [
        'ice'     => 'ICE',
        'if_num'  => 'IF',
        'rc'      => 'RC',
        'patente' => 'Patente',
    ] as $key => $label
) {
    $v = trim((string) ($shop[$key] ?? ''));
    if ($v !== '') {
        $identLines[] = $label . ' : ' . $v;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #<?= (int) $sale['id'] ?></title>
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="ticket-page">
<div class="ticket-shell">
    <header class="ticket-header">
        <h1 class="ticket-brand"><?= htmlspecialchars((string) ($shop['trade_name'] ?? 'Magasin')) ?></h1>
        <p class="ticket-legal"><?= htmlspecialchars((string) ($shop['legal_name'] ?? '')) ?></p>
        <p class="ticket-meta"><?= nl2br(htmlspecialchars((string) ($shop['address'] ?? ''))) ?></p>
        <p class="ticket-meta">Tél. <?= htmlspecialchars((string) ($shop['phone'] ?? '')) ?><?php if (!empty($shop['email'])): ?><br>E-mail : <?= htmlspecialchars((string) $shop['email']) ?><?php endif; ?></p>
        <?php if ($identLines !== []): ?>
            <p class="ticket-meta ticket-ident"><?= htmlspecialchars(implode(' · ', $identLines)) ?></p>
        <?php endif; ?>
    </header>

    <section class="ticket-block">
        <h2 class="ticket-h">Ticket de caisse</h2>
        <p class="ticket-line"><span>N°</span><strong>#<?= (int) $sale['id'] ?></strong></p>
        <p class="ticket-line"><span>Date</span><strong><?= htmlspecialchars((string) ($sale['sale_date'] ?? '')) ?></strong></p>
        <?php if (is_array($seller) && !empty($seller['name'])): ?>
            <p class="ticket-line"><span>Vendeur</span><strong><?= htmlspecialchars((string) $seller['name']) ?></strong></p>
        <?php endif; ?>
    </section>

    <table class="ticket-table">
        <thead>
            <tr>
                <th>Article</th>
                <th>Qté</th>
                <th>PU TTC</th>
                <th>TVA</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $tSumHt = 0.0;
        $tSumVat = 0.0;
        foreach ($items as $it):
            $lineTtc = round((float) $it['quantity'] * (float) $it['price'], 2);
            $tVr = normalize_vat_rate_percent($it['vat_rate'] ?? 20);
            $tBr = sale_line_ht_vat_from_ttc($lineTtc, $tVr);
            $tSumHt += $tBr['ht'];
            $tSumVat += $tBr['vat'];
            ?>
            <tr>
                <td><?= htmlspecialchars((string) ($it['product_name'] ?? ('#' . $it['product_id']))) ?></td>
                <td class="num"><?= (int) $it['quantity'] ?></td>
                <td class="num"><?= htmlspecialchars(format_mad($it['price'])) ?></td>
                <td class="num"><?= htmlspecialchars(format_vat_percent($tVr)) ?></td>
                <td class="num"><?= htmlspecialchars(format_mad($lineTtc)) ?></td>
            </tr>
        <?php endforeach;
        $tSumHt = round($tSumHt, 2);
        $tSumVat = round($tSumVat, 2);
        ?>
        </tbody>
    </table>

    <section class="ticket-block ticket-total">
        <p class="ticket-line"><span>Total HT</span><strong><?= htmlspecialchars(format_mad($tSumHt)) ?></strong></p>
        <p class="ticket-line"><span>TVA</span><strong><?= htmlspecialchars(format_mad($tSumVat)) ?></strong></p>
        <p class="ticket-line ticket-line--big"><span>Total TTC (DH)</span><strong><?= htmlspecialchars(format_mad($sale['total'])) ?></strong></p>
        <p class="ticket-line"><span>Espèces</span><strong><?= htmlspecialchars(format_mad($pe)) ?></strong></p>
        <p class="ticket-line"><span>Carte</span><strong><?= htmlspecialchars(format_mad($pc)) ?></strong></p>
        <?php if ($pa > 0.001): ?>
            <p class="ticket-line"><span>Autre</span><strong><?= htmlspecialchars(format_mad($pa)) ?></strong></p>
        <?php endif; ?>
    </section>

    <footer class="ticket-footer">
        <?php foreach ($shop['ticket_footer_lines'] ?? [] as $line): ?>
            <p><?= htmlspecialchars((string) $line) ?></p>
        <?php endforeach; ?>
    </footer>

    <p class="ticket-actions no-print">
        <button type="button" class="btn" onclick="window.print()">Imprimer</button>
        <a class="btn btn-ghost" href="index.php?action=sale_show&id=<?= (int) $sale['id'] ?>">Retour</a>
    </p>
</div>
</body>
</html>
