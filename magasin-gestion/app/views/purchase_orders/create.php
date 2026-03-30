<?php
$pageTitle = 'Nouveau bon de commande';
ob_start();
?>
<div class="sale-row po-row">
    <div>
        <label>Produit</label>
        <select name="product_id[]" class="po-product-select" required>
            <option value="">— Choisir —</option>
            <?php foreach ($products as $p): ?>
                <option value="<?= (int) $p['id'] ?>"
                    data-supplier-id="<?= (int) ($p['supplier_id'] ?? 0) ?>"
                    data-price-buy="<?= htmlspecialchars((string) $p['price_buy']) ?>">
                    <?= htmlspecialchars($p['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label>Quantité</label>
        <input name="quantity[]" type="number" min="1" value="1" required>
    </div>
    <div>
        <label>Prix achat catalogue (DH)</label>
        <span class="po-price-hint cell-num text-muted">—</span>
    </div>
</div>
<?php
$rowTpl = ob_get_clean();
require __DIR__ . '/../partials/header.php';
?>
<header class="page-header">
    <h1 class="page-header__title">Nouveau bon de commande</h1>
    <p class="page-header__desc">Choisissez d’abord le <strong>fournisseur</strong> : seuls les produits rattachés à ce fournisseur (prix d’achat définis en catalogue) sont proposés. Le coût unitaire du bon est celui du catalogue, pas modifiable ici. Ensuite le fournisseur pourra accepter ou refuser si le bon est <strong>envoyé</strong>.</p>
</header>

<div class="panel">
    <div class="panel__pad">
        <form method="post" action="index.php?action=purchase_order_store" id="po-form">
            <?php csrf_field(); ?>
            <div class="form-grid form-grid--2">
                <div class="form-grid__full">
                    <label for="supplier_id">Fournisseur</label>
                    <select id="supplier_id" name="supplier_id" required>
                        <option value="">—</option>
                        <?php foreach ($suppliers as $s): ?>
                            <option value="<?= (int) $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-grid__full">
                    <label for="notes">Notes (interne)</label>
                    <textarea id="notes" name="notes" rows="2"></textarea>
                </div>
                <div>
                    <label for="status">Statut initial</label>
                    <select id="status" name="status">
                        <option value="brouillon">Brouillon</option>
                        <option value="envoye">Déjà envoyé au fournisseur</option>
                    </select>
                </div>
            </div>
            <h2 class="subheading" style="margin-top: 1.5rem;">Lignes</h2>
            <div id="po-rows"><?= $rowTpl ?></div>
            <div class="form-actions" style="margin-top: 0.5rem;">
                <button type="button" class="btn btn-ghost" id="po-add-line">+ Ligne</button>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn">Enregistrer le bon</button>
                <a class="btn btn-ghost" href="index.php?action=purchase_orders">Annuler</a>
            </div>
        </form>
    </div>
</div>
<template id="po-row-template"><?= $rowTpl ?></template>
<?php require __DIR__ . '/../partials/footer.php'; ?>
