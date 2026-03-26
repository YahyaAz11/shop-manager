<?php
$pageTitle = 'Commande #' . (int) ($order['id'] ?? 0);
$statusLabels = [
    'envoye'    => 'En attente de votre réponse',
    'accepte'   => 'Acceptée — vous fournissez la commande',
    'refuse'    => 'Refusée',
    'recu'      => 'Réceptionnée par le magasin',
    'annule'    => 'Annulée',
];
$st = (string) ($order['status'] ?? '');
$canRespond = $st === 'envoye';
require __DIR__ . '/../partials/header.php';
if (empty($order)): ?>
<header class="page-header">
    <h1 class="page-header__title">Commande introuvable</h1>
</header>
<p><a class="link-action" href="index.php?action=supplier_purchase_orders">← Retour</a></p>
<?php require __DIR__ . '/../partials/footer.php'; return; endif; ?>
<header class="page-header">
    <h1 class="page-header__title">Commande #<?= (int) $order['id'] ?></h1>
    <p class="page-header__desc">
        <?= htmlspecialchars((string) ($order['supplier_name'] ?? '')) ?> ·
        <strong><?= htmlspecialchars($statusLabels[$st] ?? $st) ?></strong>
    </p>
</header>

<?php if (!empty($order['notes'])): ?>
<p class="lead-muted">Message du magasin :<br><?= nl2br(htmlspecialchars((string) $order['notes'])) ?></p>
<?php endif; ?>

<?php if ($st === 'refuse' && !empty($order['supplier_reply_note'])): ?>
<div class="flash flash-error" style="margin-bottom:1rem;">
    <strong>Votre motif :</strong> <?= nl2br(htmlspecialchars((string) $order['supplier_reply_note'])) ?>
    <?php if (!empty($order['supplier_replied_at'])): ?>
        <br><span class="text-muted" style="font-size:0.88rem">Le <?= htmlspecialchars((string) $order['supplier_replied_at']) ?></span>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if ($st === 'accepte'): ?>
<div class="flash flash-success" style="margin-bottom:1rem;">
    Vous avez accepté de fournir cette commande.
    <?php if (!empty($order['supplier_replied_at'])): ?>
        <span class="text-muted"> — <?= htmlspecialchars((string) $order['supplier_replied_at']) ?></span>
    <?php endif; ?>
</div>
<?php endif; ?>

<div class="panel panel--flush">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Produit</th>
                    <th>Qté</th>
                    <th>Coût unit. (DH)</th>
                    <th>Sous-total (DH)</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $sum = 0.0;
            foreach ($items as $it):
                $sub = (float) $it['quantity'] * (float) $it['unit_cost'];
                $sum += $sub;
            ?>
                <tr>
                    <td><?= htmlspecialchars((string) ($it['product_name'] ?? ('#' . $it['product_id']))) ?></td>
                    <td class="text-mono"><?= (int) $it['quantity'] ?></td>
                    <td class="cell-num"><?= htmlspecialchars(format_mad($it['unit_cost'])) ?></td>
                    <td class="cell-num"><strong><?= htmlspecialchars(format_mad($sub)) ?></strong></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<p class="text-muted">Total estimé : <strong><?= htmlspecialchars(format_mad($sum)) ?></strong> · Demandé par <?= htmlspecialchars((string) ($order['user_name'] ?? '—')) ?> le <?= htmlspecialchars((string) ($order['ordered_at'] ?? '')) ?></p>

<?php if ($canRespond): ?>
<div class="panel" style="margin-top:1.25rem;">
    <div class="panel__pad">
        <h2 class="subheading" style="margin-top:0;">Votre réponse</h2>
        <p class="lead-muted">Indiquez si vous pouvez fournir les articles demandés. En cas de refus, précisez le motif (obligatoire).</p>
        <div style="display:flex;flex-wrap:wrap;gap:1.5rem;align-items:flex-start;">
            <form method="post" action="index.php?action=supplier_purchase_order_respond" class="form-inline-delete" style="flex-direction:column;align-items:stretch;gap:0.5rem;" onsubmit="return confirm('Confirmer que vous acceptez de fournir cette commande ?');">
                <?php csrf_field(); ?>
                <input type="hidden" name="id" value="<?= (int) $order['id'] ?>">
                <input type="hidden" name="decision" value="accept">
                <button type="submit" class="btn">J’accepte de fournir</button>
            </form>
            <form method="post" action="index.php?action=supplier_purchase_order_respond" class="form-grid form-grid--2" style="flex:1;min-width:220px;max-width:420px;">
                <?php csrf_field(); ?>
                <input type="hidden" name="id" value="<?= (int) $order['id'] ?>">
                <input type="hidden" name="decision" value="refuse">
                <div class="form-grid__full">
                    <label for="supplier_note">Motif du refus</label>
                    <textarea id="supplier_note" name="supplier_note" rows="2" required placeholder="Ex. : rupture, délai trop court…"></textarea>
                </div>
                <div class="form-grid__full">
                    <button type="submit" class="btn btn-danger">Je ne peux pas fournir</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<p class="form-actions" style="margin-top:1rem;">
    <a class="btn btn-ghost" href="index.php?action=supplier_purchase_orders">← Liste</a>
</p>
<?php require __DIR__ . '/../partials/footer.php'; ?>
