<?php
$pageTitle = 'Nouveau produit';
require __DIR__ . '/../partials/header.php';
?>
<header class="page-header">
    <h1 class="page-header__title">Nouveau produit</h1>
    <p class="page-header__desc">Renseignez les informations et le stock initial.</p>
</header>

<div class="panel">
    <div class="panel__pad">
        <form method="post" action="index.php?action=product_store" class="form-grid form-grid--2">
            <?php csrf_field(); ?>
            <div class="form-grid__full">
                <label for="name">Nom</label>
                <input id="name" name="name" required>
            </div>
            <div class="form-grid__full">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="3"></textarea>
            </div>
            <div>
                <label for="price_buy">Prix d’achat (DH)</label>
                <input id="price_buy" name="price_buy" type="number" step="0.01" min="0" required>
            </div>
            <div>
                <label for="price_sell">Prix de vente (DH)</label>
                <input id="price_sell" name="price_sell" type="number" step="0.01" min="0" required>
            </div>
            <div>
                <label for="stock">Stock</label>
                <input id="stock" name="stock" type="number" min="0" value="0" required>
            </div>
            <div>
                <label for="stock_alert_threshold">Seuil d’alerte stock faible</label>
                <input id="stock_alert_threshold" name="stock_alert_threshold" type="number" min="0" value="5" title="Alerte lorsque stock ≤ ce nombre">
            </div>
            <div>
                <label for="category_id">Catégorie</label>
                <select id="category_id" name="category_id">
                    <option value="">—</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= (int) $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-grid__full">
                <label for="supplier_id">Fournisseur</label>
                <select id="supplier_id" name="supplier_id">
                    <option value="">—</option>
                    <?php foreach ($suppliers as $s): ?>
                        <option value="<?= (int) $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-grid__full form-actions">
                <button type="submit" class="btn">Enregistrer</button>
                <a class="btn btn-ghost" href="index.php?action=products">Annuler</a>
            </div>
        </form>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
