<?php
$pageTitle = 'Fournisseurs';
require __DIR__ . '/../partials/header.php';
?>
<header class="page-header">
    <h1 class="page-header__title">Fournisseurs</h1>
    <p class="page-header__desc">Contacts et coordonnées de vos partenaires d’approvisionnement.</p>
</header>

<?php if (!empty($canManage)): ?>
    <div class="page-toolbar">
        <a class="btn" href="index.php?action=supplier_create">+ Ajouter un fournisseur</a>
    </div>
<?php endif; ?>

<div class="panel panel--flush">
    <?php if (!empty($suppliers)): ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Téléphone</th>
                        <th>Email</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($suppliers as $s): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($s['name']) ?></strong></td>
                        <td class="text-mono"><?= htmlspecialchars($s['phone'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($s['email'] ?? '—') ?></td>
                        <td class="table-actions">
                            <?php if (!empty($canManage)): ?>
                                <a class="link-action" href="index.php?action=supplier_edit&id=<?= (int) $s['id'] ?>">Modifier</a>
                                <form method="post" action="index.php?action=supplier_delete" class="form-inline-delete" onsubmit="return confirm('Supprimer ce fournisseur ?');">
                                    <?php csrf_field(); ?>
                                    <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-compact">Supprimer</button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <p class="empty-state__title">Aucun fournisseur</p>
            <p class="empty-state__hint">Les comptes administrateur peuvent en ajouter depuis ce menu.</p>
        </div>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
