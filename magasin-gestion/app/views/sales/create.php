<?php
$pageTitle = 'Nouvelle vente';
ob_start();
?>
<div class="sale-row">
    <div>
        <label>Produit</label>
        <select name="product_id[]" required>
            <option value="">— Choisir —</option>
            <?php foreach ($products as $p): ?>
                <option value="<?= (int) $p['id'] ?>" data-price="<?= htmlspecialchars((string) $p['price_sell']) ?>">
                    <?= htmlspecialchars($p['name']) ?> (stock <?= (int) $p['stock'] ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label>Quantité</label>
        <input name="quantity[]" type="number" min="1" value="1" required>
    </div>
    <div>
        <label>Prix unitaire TTC (DH)</label>
        <input name="price[]" type="number" step="0.01" min="0" required class="price-field">
    </div>
</div>
<?php
$rowTpl = ob_get_clean();
require __DIR__ . '/../partials/header.php';
$saleCancelAction = sales_list_action_for_user($currentUser ?? null);
?>
<header class="page-header">
    <h1 class="page-header__title">Nouvelle vente</h1>
    <p class="page-header__desc">Ajoutez des lignes, vérifiez les prix puis enregistrez.</p>
</header>

<div class="panel">
    <div class="panel__pad">
        <form method="post" action="index.php?action=sale_store" id="sale-form">
            <?php csrf_field(); ?>
            <div id="rows"><?= $rowTpl ?></div>
            <div class="form-actions" style="margin-top: 0.5rem;">
                <button type="button" class="btn btn-ghost" id="add-line">+ Ligne</button>
            </div>

            <h2 class="subheading" style="margin-top: 1.5rem;">Paiement</h2>
            <p class="lead-muted">Total vente : <strong id="sale-total-display">0,00 DH</strong>. Répartissez le montant entre espèces, carte et autre (chèque, virement…) ; la somme doit égaler le total.</p>
            <div class="form-grid form-grid--2" id="sale-payment-fields">
                <div>
                    <label for="payment_especes">Espèces (DH)</label>
                    <input id="payment_especes" name="payment_especes" type="number" step="0.01" min="0" value="0" class="sale-pay-field">
                </div>
                <div>
                    <label for="payment_carte">Carte (DH)</label>
                    <input id="payment_carte" name="payment_carte" type="number" step="0.01" min="0" value="0" class="sale-pay-field">
                </div>
                <div class="form-grid__full">
                    <label for="payment_autre">Autre (DH)</label>
                    <input id="payment_autre" name="payment_autre" type="number" step="0.01" min="0" value="0" class="sale-pay-field">
                </div>
            </div>
            <p id="sale-pay-diff" class="text-muted" style="font-size:0.9rem" aria-live="polite"></p>

            <div class="form-actions">
                <button type="submit" class="btn">Enregistrer la vente</button>
                <a class="btn btn-ghost" href="index.php?action=<?= htmlspecialchars($saleCancelAction) ?>">Annuler</a>
            </div>
        </form>
    </div>
</div>
<template id="row-template"><?= $rowTpl ?></template>
<?php require __DIR__ . '/../partials/footer.php'; ?>
