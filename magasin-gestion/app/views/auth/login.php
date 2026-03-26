<?php
$shopBrand = (string) (shop_config()['trade_name'] ?? 'Omnix Market');
$brandLogo = brand_logo_web_path();
$brandShowName = (bool) (shop_config()['brand_logo_show_name'] ?? true);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — <?= htmlspecialchars($shopBrand, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400..700;1,9..40,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="page-login">
<div class="login-shell">
    <aside class="login-aside" aria-hidden="false">
        <div class="login-aside__inner">
            <?php if ($brandLogo !== null): ?>
                <p class="login-aside__logo-wrap">
                    <img class="login-aside__logo" src="<?= htmlspecialchars($brandLogo, ENT_QUOTES, 'UTF-8') ?>" width="120" height="120" alt="" decoding="async">
                </p>
                <?php if ($brandShowName): ?>
                    <p class="login-aside__brand"><?= htmlspecialchars($shopBrand, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
            <?php else: ?>
                <p class="login-aside__brand"><?= htmlspecialchars($shopBrand, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
            <p class="login-aside__tagline">Stocks, ventes et catalogue réunis sur un tableau de bord clair. Connectez-vous pour continuer.</p>
        </div>
    </aside>
    <div class="login-panel">
        <div class="box">
            <h1>Connexion</h1>
            <p class="login-subtitle">Entrez vos identifiants</p>
            <?php if (!empty($error)): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="post" action="index.php?action=do_login">
                <?php csrf_field(); ?>
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required autocomplete="username">

                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">

                <button type="submit" class="btn btn-block">Se connecter</button>
            </form>
        </div>
    </div>
</div>
<script src="assets/js/app.js" defer></script>
</body>
</html>
