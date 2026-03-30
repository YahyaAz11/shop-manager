<?php
$pageTitle = 'Modifier le produit';
require __DIR__ . '/../partials/header.php';
if (empty($product)): ?>
<header class="page-header">
    <h1 class="page-header__title">Produit introuvable</h1>
</header>
<p><a class="link-action" href="index.php?action=products">← Retour aux produits</a></p>
<?php
require __DIR__ . '/../partials/footer.php';
return;
endif;
?>
<header class="page-header">
    <h1 class="page-header__title">Modifier le produit</h1>
    <p class="page-header__desc">Identifiant #<?= (int) $product['id'] ?></p>
</header>

<div class="panel">
    <div class="panel__pad">
        <form method="post" action="index.php?action=product_update&id=<?= (int) $product['id'] ?>" class="form-grid form-grid--2">
            <?php csrf_field(); ?>
            <div class="form-grid__full">
                <label for="name">Nom</label>
                <input id="name" name="name" required value="<?= htmlspecialchars($product['name']) ?>">
            </div>
            <div class="form-grid__full">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="3"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
            </div>
            <div>
                <label for="price_buy">Prix d’achat (DH)</label>
                <input id="price_buy" name="price_buy" type="number" step="0.01" min="0" required value="<?= htmlspecialchars((string) $product['price_buy']) ?>">
            </div>
            <div>
                <label for="price_sell">Prix de vente TTC (DH)</label>
                <input id="price_sell" name="price_sell" type="number" step="0.01" min="0" required value="<?= htmlspecialchars((string) $product['price_sell']) ?>">
            </div>
            <div>
                <label for="vat_rate">TVA (%)</label>
                <input id="vat_rate" name="vat_rate" type="number" step="0.01" min="0" max="100" value="<?= htmlspecialchars((string) ($product['vat_rate'] ?? 20)) ?>" title="Ex. 20 pour la TVA normale, 0 pour exonéré">
            </div>
            <div>
                <label for="stock">Stock</label>
                <input id="stock" name="stock" type="number" min="0" required value="<?= (int) $product['stock'] ?>">
            </div>
            <div>
                <label for="stock_alert_threshold">Seuil d’alerte stock faible</label>
                <input id="stock_alert_threshold" name="stock_alert_threshold" type="number" min="0" value="<?= (int) ($product['stock_alert_threshold'] ?? 5) ?>">
            </div>
            <div>
                <label for="category_id">Catégorie</label>
                <select id="category_id" name="category_id">
                    <option value="">—</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= (int) $c['id'] ?>" <?= (string) $product['category_id'] === (string) $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if (!empty($supplierCatalogLock)): ?>
            <div class="form-grid__full">
                <label>Fournisseur</label>
                <input type="hidden" name="supplier_id" value="<?= (int) $supplierCatalogLock['id'] ?>">
                <p class="text-muted" style="margin:0;font-size:0.92rem"><?= htmlspecialchars($supplierCatalogLock['name']) ?></p>
            </div>
            <?php else: ?>
            <div class="form-grid__full">
                <label for="supplier_id">Fournisseur</label>
                <select id="supplier_id" name="supplier_id">
                    <option value="">—</option>
                    <?php foreach ($suppliers as $s): ?>
                        <option value="<?= (int) $s['id'] ?>" <?= (string) $product['supplier_id'] === (string) $s['id'] ? 'selected' : '' ?>><?= htmlspecialchars($s['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="form-grid__full form-actions">
                <button type="submit" class="btn">Mettre à jour</button>
                <a class="btn btn-ghost" href="index.php?action=products">Annuler</a>
            </div>
        </form>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
