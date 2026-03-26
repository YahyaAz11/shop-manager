<?php
$pageTitle = 'Modifier la catégorie';
require __DIR__ . '/../partials/header.php';
if (empty($category)): ?>
<header class="page-header">
    <h1 class="page-header__title">Catégorie introuvable</h1>
</header>
<p><a class="link-action" href="index.php?action=categories">← Retour</a></p>
<?php require __DIR__ . '/../partials/footer.php'; return; endif; ?>
<header class="page-header">
    <h1 class="page-header__title">Modifier la catégorie</h1>
</header>
<div class="panel">
    <div class="panel__pad">
        <form method="post" action="index.php?action=category_update&id=<?= (int) $category['id'] ?>">
            <?php csrf_field(); ?>
            <label for="name">Nom</label>
            <input id="name" name="name" required value="<?= htmlspecialchars($category['name']) ?>">
            <div class="form-actions">
                <button type="submit" class="btn">Mettre à jour</button>
                <a class="btn btn-ghost" href="index.php?action=categories">Annuler</a>
            </div>
        </form>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
