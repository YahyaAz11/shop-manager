<?php
$pageTitle = 'Catégories';
require __DIR__ . '/../partials/header.php';
?>
<header class="page-header">
    <h1 class="page-header__title">Catégories</h1>
    <p class="page-header__desc">Classez vos produits pour un catalogue plus lisible.</p>
</header>

<div class="page-toolbar">
    <a class="btn" href="index.php?action=category_create">+ Nouvelle catégorie</a>
</div>

<div class="panel panel--flush">
    <?php if (!empty($categories)): ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($categories as $c): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($c['name']) ?></strong></td>
                        <td class="table-actions">
                            <a class="link-action" href="index.php?action=category_edit&id=<?= (int) $c['id'] ?>">Modifier</a>
                            <form method="post" action="index.php?action=category_delete" class="form-inline-delete" onsubmit="return confirm('Supprimer cette catégorie ?');">
                                <?php csrf_field(); ?>
                                <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-compact">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <p class="empty-state__title">Aucune catégorie</p>
            <p class="empty-state__hint">Créez une catégorie pour organiser vos produits.</p>
        </div>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
