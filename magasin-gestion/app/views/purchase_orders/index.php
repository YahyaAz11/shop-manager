<?php
$pageTitle = 'Bons de commande';
$statusLabels = [
    'brouillon' => 'Brouillon',
    'envoye'    => 'Envoyé',
    'accepte'   => 'Accepté (fourn.)',
    'refuse'    => 'Refusé (fourn.)',
    'recu'      => 'Reçu',
    'annule'    => 'Annulé',
];
require __DIR__ . '/../partials/header.php';
?>
<header class="page-header">
    <h1 class="page-header__title">Bons de commande</h1>
    <p class="page-header__desc">Commandes fournisseurs : création, envoi et réception (mise à jour des stocks).</p>
</header>

<div class="page-toolbar">
    <a class="btn" href="index.php?action=purchase_order_create">+ Nouveau bon</a>
</div>

<div class="panel panel--flush">
    <?php if (!empty($orders)): ?>
        <?php require __DIR__ . '/../partials/pagination.php'; ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Fournisseur</th>
                        <th>Statut</th>
                        <th>Créé par</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($orders as $o): ?>
                    <tr>
                        <td class="text-mono"><?= (int) $o['id'] ?></td>
                        <td><?= htmlspecialchars((string) ($o['ordered_at'] ?? '')) ?></td>
                        <td><?= htmlspecialchars((string) ($o['supplier_name'] ?? '')) ?></td>
                        <td><?= htmlspecialchars($statusLabels[$o['status'] ?? ''] ?? (string) ($o['status'] ?? '—')) ?></td>
                        <td><?= htmlspecialchars((string) ($o['user_name'] ?? '—')) ?></td>
                        <td><a class="link-action" href="index.php?action=purchase_order_show&id=<?= (int) $o['id'] ?>">Détail</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <p class="empty-state__title">Aucun bon</p>
            <p class="empty-state__hint">Créez un bon pour réapprovisionner le magasin.</p>
        </div>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
