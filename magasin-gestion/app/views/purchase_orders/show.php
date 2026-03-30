<?php
$pageTitle = 'Bon de commande';
$statusLabels = [
    'brouillon' => 'Brouillon',
    'envoye'    => 'Envoyé au fournisseur',
    'accepte'   => 'Accepté par le fournisseur',
    'refuse'    => 'Refusé par le fournisseur',
    'recu'      => 'Reçu (stocks maj.)',
    'annule'    => 'Annulé',
];
require __DIR__ . '/../partials/header.php';
if (empty($order)): ?>
<header class="page-header">
    <h1 class="page-header__title">Bon introuvable</h1>
</header>
<p><a class="link-action" href="index.php?action=purchase_orders">← Retour</a></p>
<?php require __DIR__ . '/../partials/footer.php'; return; endif;

$st = (string) ($order['status'] ?? '');
$canMarkSent = $st === 'brouillon';
$canReceive = $st === 'accepte';
$canDelete = $st === 'brouillon';
?>
<header class="page-header">
    <h1 class="page-header__title">Bon #<?= (int) $order['id'] ?></h1>
    <p class="page-header__desc">
        <?= htmlspecialchars((string) ($order['supplier_name'] ?? '')) ?> ·
        statut : <strong><?= htmlspecialchars($statusLabels[$st] ?? $st) ?></strong>
        <?php if (!empty($order['received_at'])): ?>
            · Reçu le <?= htmlspecialchars((string) $order['received_at']) ?>
        <?php endif; ?>
    </p>
</header>

<?php if (!empty($order['notes'])): ?>
<p class="lead-muted"><?= nl2br(htmlspecialchars((string) $order['notes'])) ?></p>
<?php endif; ?>

<?php if ($st === 'refuse' && !empty($order['supplier_reply_note'])): ?>
<div class="flash flash-error" style="margin-bottom:1rem;">
    <strong>Réponse fournisseur</strong> (<?= htmlspecialchars((string) ($order['supplier_replied_at'] ?? '')) ?>) :
    <?= nl2br(htmlspecialchars((string) $order['supplier_reply_note'])) ?>
</div>
<?php endif; ?>
<?php if ($st === 'accepte'): ?>
<p class="flash flash-success" style="margin-bottom:0.5rem;">Le fournisseur a accepté de fournir cette commande<?php if (!empty($order['supplier_replied_at'])): ?> le <?= htmlspecialchars((string) $order['supplier_replied_at']) ?><?php endif; ?>. Vous pouvez <strong>réceptionner</strong> pour mettre à jour les stocks.</p>
<?php endif; ?>
<?php if ($st === 'envoye'): ?>
<p class="text-muted" style="margin-bottom:1rem;">En attente de réponse du fournisseur. La réception n’est possible qu’après son <strong>acceptation</strong> dans « Commandes reçues ».</p>
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

<p class="form-actions" style="margin-top: 1rem;">
    <a class="btn btn-ghost" href="index.php?action=purchase_orders">← Liste</a>
    <?php if ($canMarkSent): ?>
        <form method="post" action="index.php?action=purchase_order_send" class="form-inline-delete">
            <?php csrf_field(); ?>
            <input type="hidden" name="id" value="<?= (int) $order['id'] ?>">
            <button type="submit" class="btn btn-ghost">Marquer comme envoyé</button>
        </form>
    <?php endif; ?>
    <?php if ($canReceive): ?>
        <form method="post" action="index.php?action=purchase_order_receive" class="form-inline-delete" onsubmit="return confirm('Confirmer la réception ? Les stocks seront augmentés.');">
            <?php csrf_field(); ?>
            <input type="hidden" name="id" value="<?= (int) $order['id'] ?>">
            <button type="submit" class="btn">Réceptionner (stocks +)</button>
        </form>
    <?php endif; ?>
    <?php if ($canDelete): ?>
        <form method="post" action="index.php?action=purchase_order_delete" class="form-inline-delete" onsubmit="return confirm('Supprimer définitivement ce brouillon ?');">
            <?php csrf_field(); ?>
            <input type="hidden" name="id" value="<?= (int) $order['id'] ?>">
            <button type="submit" class="btn btn-danger btn-compact">Supprimer</button>
        </form>
    <?php endif; ?>
</p>

<p class="text-muted" style="margin-top:0.5rem">Total estimé : <strong><?= htmlspecialchars(format_mad($sum)) ?></strong> · Créé par <?= htmlspecialchars((string) ($order['user_name'] ?? '—')) ?> le <?= htmlspecialchars((string) ($order['ordered_at'] ?? '')) ?></p>
<?php require __DIR__ . '/../partials/footer.php'; ?>
