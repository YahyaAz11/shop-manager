<?php
$pageTitle = 'Commandes reçues';
$statusLabels = [
    'envoye'    => 'En attente de votre réponse',
    'accepte'   => 'Acceptée (fourniture)',
    'refuse'    => 'Refusée',
    'recu'      => 'Réceptionnée par le magasin',
    'annule'    => 'Annulée',
];
require __DIR__ . '/../partials/header.php';
?>
<header class="page-header">
    <h1 class="page-header__title">Commandes reçues</h1>
    <p class="page-header__desc">Bons de commande qui vous sont adressés (après envoi par le magasin). Répondez pour indiquer si vous pouvez fournir.</p>
</header>

<?php if (!empty($supplierNotLinked)): ?>
<div class="flash flash-info" role="status" style="margin-bottom:1.25rem;">
    <strong>Compte non relié à une entreprise</strong> — Pour recevoir les bons de commande, un administrateur doit associer votre compte à une fiche fournisseur :
    menu <strong>Utilisateurs</strong> → <strong>Modifier</strong> votre compte → champ <strong>Entreprise fournisseur</strong> (obligatoire pour le rôle fournisseur).
    Après enregistrement, actualisez cette page.
</div>
<?php endif; ?>

<div class="panel panel--flush">
    <?php if (!empty($orders)): ?>
        <?php require __DIR__ . '/../partials/pagination.php'; ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
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
                        <td><?= htmlspecialchars($statusLabels[$o['status'] ?? ''] ?? (string) ($o['status'] ?? '—')) ?></td>
                        <td><?= htmlspecialchars((string) ($o['user_name'] ?? '—')) ?></td>
                        <td><a class="link-action" href="index.php?action=supplier_purchase_order_show&id=<?= (int) $o['id'] ?>">Voir / répondre</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <p class="empty-state__title"><?= !empty($supplierNotLinked) ? 'Aucune commande pour l’instant' : 'Aucune commande' ?></p>
            <p class="empty-state__hint"><?= !empty($supplierNotLinked)
                ? 'Une fois votre compte relié à une entreprise, les bons « Envoyé » par le magasin s’afficheront ici.'
                : 'Les bons marqués « Envoyé » par le magasin apparaîtront ici.' ?></p>
        </div>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
