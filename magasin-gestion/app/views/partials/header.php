<?php
$shopBrand = (string) (shop_config()['trade_name'] ?? 'Omnix Market');
$pageTitle = $pageTitle ?? $shopBrand;
$brandLogo = brand_logo_web_path();
$brandShowName = (bool) (shop_config()['brand_logo_show_name'] ?? true);
/** Compte connecté (ne pas réutiliser la variable $user : réservée aux vues qui affichent un autre enregistrement, ex. édition utilisateur). */
$currentUser = isset($_SESSION['user']) && is_array($_SESSION['user']) ? $_SESSION['user'] : null;
$navAction = $_GET['action'] ?? '';
$na = function (array $actions) use ($navAction): string {
    return 'nav-link' . (in_array($navAction, $actions, true) ? ' is-active' : '');
};
$productsActions = ['products', 'product_create', 'product_store', 'product_edit', 'product_update', 'product_delete', 'product_search', 'low_stock'];
$categoriesActions = ['categories', 'category_create', 'category_store', 'category_edit', 'category_update', 'category_delete'];
$suppliersActions = ['suppliers', 'supplier_create', 'supplier_store', 'supplier_edit', 'supplier_update', 'supplier_delete'];
$salesActions = ['sales', 'sale_create', 'sale_store', 'sale_show', 'sale_ticket'];
$mySalesActions = ['my_sales'];
$usersActions = ['users', 'user_create', 'user_store', 'user_edit', 'user_update', 'user_delete'];
$poActions = ['purchase_orders', 'purchase_order_create', 'purchase_order_store', 'purchase_order_show', 'purchase_order_send', 'purchase_order_receive', 'purchase_order_delete'];
$supplierPoActions = ['supplier_purchase_orders', 'supplier_purchase_order_show', 'supplier_purchase_order_respond'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400..700;1,9..40,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body>
<header class="navbar" id="site-navbar">
    <div class="navbar-inner">
        <a class="navbar-brand<?= $brandLogo ? ' navbar-brand--logo' : '' ?>" href="index.php?action=dashboard">
            <?php if ($brandLogo !== null): ?>
                <img class="navbar-brand-logo" src="<?= htmlspecialchars($brandLogo, ENT_QUOTES, 'UTF-8') ?>" width="40" height="40" alt="" decoding="async">
                
            <?php else: ?>
                <span class="navbar-brand-icon" aria-hidden="true"></span>
                <span class="navbar-brand-text"><?= htmlspecialchars($shopBrand, ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </a>
        <button type="button" class="navbar-toggle" id="navbar-toggle" aria-controls="navbar-menu" aria-expanded="false" aria-label="Ouvrir le menu">
            <span class="navbar-toggle-bar"></span>
            <span class="navbar-toggle-bar"></span>
            <span class="navbar-toggle-bar"></span>
        </button>
        <div class="navbar-drawer" id="navbar-menu">
            <nav class="navbar-links" aria-label="Navigation principale">
                <?php if (nav_can($currentUser, 'products')): ?>
                    <a href="index.php?action=products" class="<?= htmlspecialchars($na($productsActions)) ?>">Produits</a>
                <?php endif; ?>
                <?php if (nav_can($currentUser, 'categories')): ?>
                    <a href="index.php?action=categories" class="<?= htmlspecialchars($na($categoriesActions)) ?>">Catégories</a>
                <?php endif; ?>
                <?php if (nav_can($currentUser, 'suppliers')): ?>
                    <a href="index.php?action=suppliers" class="<?= htmlspecialchars($na($suppliersActions)) ?>">Fournisseurs</a>
                <?php endif; ?>
                <?php if (nav_can($currentUser, 'purchase_orders')): ?>
                    <a href="index.php?action=purchase_orders" class="<?= htmlspecialchars($na($poActions)) ?>">Bons de commande</a>
                <?php endif; ?>
                <?php if (nav_can($currentUser, 'supplier_purchase_orders')): ?>
                    <a href="index.php?action=supplier_purchase_orders" class="<?= htmlspecialchars($na($supplierPoActions)) ?>">Commandes reçues</a>
                <?php endif; ?>
                <?php if (nav_can($currentUser, 'sales')): ?>
                    <a href="index.php?action=sales" class="<?= htmlspecialchars($na($salesActions)) ?>">Ventes</a>
                <?php endif; ?>
                <?php if (nav_can($currentUser, 'my_sales')): ?>
                    <a href="index.php?action=my_sales" class="<?= htmlspecialchars($na($mySalesActions)) ?>">Mes ventes</a>
                <?php endif; ?>
                <?php if (nav_can($currentUser, 'low_stock')): ?>
                    <a href="index.php?action=low_stock" class="<?= htmlspecialchars($na(['low_stock'])) ?>">Stock faible</a>
                <?php endif; ?>
                <?php if (nav_can($currentUser, 'users')): ?>
                    <a href="index.php?action=users" class="<?= htmlspecialchars($na($usersActions)) ?>">Utilisateurs</a>
                <?php endif; ?>
            </nav>
            <?php if ($currentUser): ?>
                <div class="navbar-aside">
                    <span class="navbar-user" title="<?= htmlspecialchars((string) ($currentUser['email'] ?? '')) ?>"><?= htmlspecialchars((string) ($currentUser['name'] ?? '')) ?></span>
                    <span class="navbar-role"><?= htmlspecialchars((string) ($currentUser['role'] ?? '')) ?></span>
                    <a href="index.php?action=logout" class="nav-link nav-link-logout">Déconnexion</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</header>
<main class="main-content">
<?php require __DIR__ . '/flash.php'; ?>
