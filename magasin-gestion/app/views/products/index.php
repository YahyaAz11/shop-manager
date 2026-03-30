<?php
$pageTitle = 'Produits';
$keyword = isset($_GET['keyword']) ? trim((string) $_GET['keyword']) : '';
$canAddProduct = $canAddProduct ?? false;
$supplierNotLinked = $supplierNotLinked ?? false;
$supplierAccountId = isset($supplierAccountId) ? (int) $supplierAccountId : 0;
require __DIR__ . '/../partials/header.php';
?>
<header class="page-header">
    <h1 class="page-header__title">Produits</h1>
    <p class="page-header__desc">
        <?php if (!empty($supplierAccountId) && ($currentUser['role'] ?? '') === 'fournisseur'): ?>
            Vos articles rattachés à votre entreprise — le magasin peut les inclure dans les bons de commande.
        <?php else: ?>
            Catalogue, prix et stocks. Recherche par nom de produit, nom de catégorie ou nom de fournisseur.
        <?php endif; ?>
    </p>
</header>

<?php if (!empty($supplierNotLinked)): ?>
<div class="flash flash-info" role="status">
    <strong>Compte fournisseur</strong> — Associez d’abord votre utilisateur à une fiche fournisseur (administration) pour référencer des produits.
</div>
<?php endif; ?>

<div class="page-toolbar">
    <?php if (!empty($canAddProduct)): ?>
        <a class="btn" href="index.php?action=product_create">+ Ajouter un produit</a>
    <?php endif; ?>
    <form method="get" action="index.php" class="toolbar-form">
        <input type="hidden" name="action" value="product_search">
        <input type="search" name="keyword" class="search-input" value="<?= htmlspecialchars($keyword) ?>" placeholder="Produit, catégorie ou fournisseur…" aria-label="Rechercher par nom de produit, catégorie ou fournisseur">
        <button type="submit" class="btn btn-ghost">Rechercher</button>
    </form>
</div>

<div class="panel panel--flush">
    <?php if (!empty($products)): ?>
        <?php require __DIR__ . '/../partials/pagination.php'; ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Catégorie</th>
                        <th>Fournisseur</th>
                        <th>Prix achat (DH)</th>
                        <th>Prix vente TTC (DH)</th>
                        <th>TVA</th>
                        <th>Stock</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($products as $p): ?>
                    <?php
                    $stock = (int) $p['stock'];
                    $thr = isset($p['stock_alert_threshold']) ? (int) $p['stock_alert_threshold'] : 5;
                    $low = $stock <= $thr;
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($p['name']) ?></strong></td>
                        <td><?= htmlspecialchars($p['category_name'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($p['supplier_name'] ?? '—') ?></td>
                        <td class="cell-num"><?= htmlspecialchars(format_mad($p['price_buy'])) ?></td>
                        <td class="cell-num"><?= htmlspecialchars(format_mad($p['price_sell'])) ?></td>
                        <td class="cell-num"><?= htmlspecialchars(format_vat_percent($p['vat_rate'] ?? 20)) ?></td>
                        <td>
                            <span class="badge-stock<?= $low ? ' badge-stock--low' : '' ?>" title="Seuil d’alerte : <?= $thr ?>"><?= $stock ?></span>
                        </td>
                        <td class="table-actions">
                            <?php
                            $own = $supplierAccountId > 0 && (int) ($p['supplier_id'] ?? 0) === $supplierAccountId;
                            $canEditRow = !empty($canManage) || $own;
                            ?>
                            <?php if ($canEditRow): ?>
                                <a class="link-action" href="index.php?action=product_edit&id=<?= (int) $p['id'] ?>">Modifier</a>
                            <?php endif; ?>
                            <?php if (!empty($canManage)): ?>
                                <form method="post" action="index.php?action=product_delete" class="form-inline-delete" onsubmit="return confirm('Supprimer ce produit ?');">
                                    <?php csrf_field(); ?>
                                    <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-compact">Supprimer</button>
                                </form>
                            <?php endif; ?>
                            <?php if (!$canEditRow && empty($canManage)): ?>
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
            <p class="empty-state__title">Aucun produit</p>
            <p class="empty-state__hint">Ajoutez un premier article ou modifiez votre recherche.</p>
        </div>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
