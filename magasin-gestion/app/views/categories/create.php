<?php
$pageTitle = 'Nouvelle catégorie';
require __DIR__ . '/../partials/header.php';
?>
<header class="page-header">
    <h1 class="page-header__title">Nouvelle catégorie</h1>
</header>
<div class="panel">
    <div class="panel__pad">
        <form method="post" action="index.php?action=category_store">
            <?php csrf_field(); ?>
            <label for="name">Nom</label>
            <input id="name" name="name" required>
            <div class="form-actions">
                <button type="submit" class="btn">Enregistrer</button>
                <a class="btn btn-ghost" href="index.php?action=categories">Annuler</a>
            </div>
        </form>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
