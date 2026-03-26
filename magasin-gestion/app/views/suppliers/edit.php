<?php
$pageTitle = 'Modifier le fournisseur';
require __DIR__ . '/../partials/header.php';
if (empty($supplier)): ?>
<header class="page-header">
    <h1 class="page-header__title">Fournisseur introuvable</h1>
</header>
<p><a class="link-action" href="index.php?action=suppliers">← Retour</a></p>
<?php require __DIR__ . '/../partials/footer.php'; return; endif; ?>
<header class="page-header">
    <h1 class="page-header__title">Modifier le fournisseur</h1>
</header>
<div class="panel">
    <div class="panel__pad">
        <form method="post" action="index.php?action=supplier_update&id=<?= (int) $supplier['id'] ?>" class="form-grid form-grid--2">
            <?php csrf_field(); ?>
            <div class="form-grid__full">
                <label for="name">Nom</label>
                <input id="name" name="name" required value="<?= htmlspecialchars($supplier['name']) ?>">
            </div>
            <div>
                <label for="contact">Contact</label>
                <input id="contact" name="contact" value="<?= htmlspecialchars($supplier['contact'] ?? '') ?>">
            </div>
            <div>
                <label for="phone">Téléphone</label>
                <input id="phone" name="phone" value="<?= htmlspecialchars($supplier['phone'] ?? '') ?>">
            </div>
            <div class="form-grid__full">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="<?= htmlspecialchars($supplier['email'] ?? '') ?>">
            </div>
            <div class="form-grid__full">
                <label for="address">Adresse</label>
                <textarea id="address" name="address" rows="2"><?= htmlspecialchars($supplier['address'] ?? '') ?></textarea>
            </div>
            <div class="form-grid__full form-actions">
                <button type="submit" class="btn">Mettre à jour</button>
                <a class="btn btn-ghost" href="index.php?action=suppliers">Annuler</a>
            </div>
        </form>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
