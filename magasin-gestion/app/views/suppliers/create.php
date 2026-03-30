<?php
$pageTitle = 'Nouveau fournisseur';
require __DIR__ . '/../partials/header.php';
?>
<header class="page-header">
    <h1 class="page-header__title">Nouveau fournisseur</h1>
    <p class="page-header__desc">Coordonnées du partenaire fournisseur.</p>
</header>
<div class="panel">
    <div class="panel__pad">
        <form method="post" action="index.php?action=supplier_store" class="form-grid form-grid--2">
            <?php csrf_field(); ?>
            <div class="form-grid__full">
                <label for="name">Nom</label>
                <input id="name" name="name" required>
            </div>
            
            <div>
                <label for="phone">Téléphone</label>
                <input id="phone" name="phone">
            </div>
            <div class="form-grid__full">
                <label for="email">Email</label>
                <input id="email" name="email" type="email">
            </div>
            <div class="form-grid__full">
                <label for="address">Adresse</label>
                <textarea id="address" name="address" rows="2"></textarea>
            </div>
            <div class="form-grid__full form-actions">
                <button type="submit" class="btn">Enregistrer</button>
                <a class="btn btn-ghost" href="index.php?action=suppliers">Annuler</a>
            </div>
        </form>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
