<?php
$pageTitle = 'Tableau de bord';
$rev = isset($revenue['revenue']) && $revenue['revenue'] !== null ? (float) $revenue['revenue'] : 0;
require __DIR__ . '/../partials/header.php';
?>
<?php if (!empty($fournisseurNeedsSupplierLink)): ?>
<div class="flash flash-info" role="status">
    <strong>Configuration du compte fournisseur</strong> — Votre compte n’est pas encore relié à une fiche « fournisseur » dans l’application.
    Un administrateur doit ouvrir <strong>Utilisateurs</strong>, modifier votre compte et choisir l’<strong>entreprise fournisseur</strong> correspondante.
    Ensuite, rechargez cette page : les commandes vous seront visibles dans <strong>Commandes reçues</strong>.
</div>
<?php endif; ?>
<header class="page-header">
    <h1 class="page-header__title">Tableau de bord</h1>
    <p class="page-header__desc">Vue d’ensemble du magasin : stocks, alertes et activité commerciale.</p>
</header>

<div class="stat-grid">
    <article class="stat-card">
        <div class="stat-card__eyebrow">Catalogue</div>
        <div class="stat-card__value"><?= (int) $productCount ?></div>
        <div class="stat-card__label">Produits référencés</div>
    </article>
    <article class="stat-card stat-card--alert">
        <div class="stat-card__eyebrow">Alertes</div>
        <div class="stat-card__value"><?= (int) $lowStockCount ?></div>
        <div class="stat-card__label">Articles en stock faible</div>
    </article>
    <?php if (!empty($showSalesStats)): ?>
        <article class="stat-card">
            <div class="stat-card__eyebrow">Activité</div>
            <div class="stat-card__value"><?= (int) $salesCount ?></div>
            <div class="stat-card__label">Ventes enregistrées</div>
        </article>
        <article class="stat-card stat-card--revenue">
            <div class="stat-card__eyebrow">Finances</div>
            <div class="stat-card__value text-mono"><?= htmlspecialchars(format_mad($rev, 0)) ?></div>
            <div class="stat-card__label">Chiffre d’affaires cumulé (DH)</div>
        </article>
        <?php if (!empty($showMonthlyRevenue)): ?>
            <article class="stat-card stat-card--revenue-month">
                <div class="stat-card__eyebrow">Ce mois-ci</div>
                <div class="stat-card__value text-mono"><?= htmlspecialchars(format_mad($currentMonthRevenue ?? 0, 0)) ?></div>
                <div class="stat-card__label">Chiffre d’affaires du mois (DH)</div>
            </article>
        <?php endif; ?>
    <?php endif; ?>
    <?php if (!empty($showUserStat)): ?>
        <article class="stat-card">
            <div class="stat-card__eyebrow">Équipe</div>
            <div class="stat-card__value"><?= (int) $userCount ?></div>
            <div class="stat-card__label">Comptes utilisateurs</div>
        </article>
    <?php endif; ?>
</div>

<?php if (!empty($showMonthlyRevenue) && !empty($monthlyRevenueRows)): ?>
    <?php
    $monthsFr = [1 => 'janvier', 2 => 'février', 3 => 'mars', 4 => 'avril', 5 => 'mai', 6 => 'juin', 7 => 'juillet', 8 => 'août', 9 => 'septembre', 10 => 'octobre', 11 => 'novembre', 12 => 'décembre'];
    ?>
    <section class="panel panel--flush dashboard-monthly-ca" aria-label="Chiffre d’affaires par mois">
        <div class="panel__pad" style="padding-bottom: 1.25rem;">
            <h2 class="subheading" style="margin-top: 0;">Chiffre d’affaires par mois</h2>
            <p class="lead-muted" style="margin-top: 0.25rem;">12 derniers mois avec au moins une vente (les plus récents en premier).</p>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Mois</th>
                        <th>CA (DH)</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($monthlyRevenueRows as $row): ?>
                    <?php
                    $ym = (string) ($row['month'] ?? '');
                    $parts = explode('-', $ym);
                    $y = $parts[0] ?? '';
                    $m = isset($parts[1]) ? (int) $parts[1] : 0;
                    $label = ($m >= 1 && $m <= 12 ? $monthsFr[$m] : $ym) . ' ' . $y;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars(ucfirst($label)) ?></td>
                        <td class="cell-num"><strong><?= htmlspecialchars(format_mad($row['total_sales'] ?? 0)) ?></strong></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php elseif (!empty($showMonthlyRevenue)): ?>
    <section class="panel">
        <div class="panel__pad">
            <h2 class="subheading" style="margin-top: 0;">Chiffre d’affaires par mois</h2>
            <p class="text-muted">Aucune vente enregistrée pour l’instant.</p>
        </div>
    </section>
<?php endif; ?>

<?php
$du = $_SESSION['user'] ?? null;
$quick = [];
if (nav_can($du, 'product_create')) {
    $quick[] = '<a href="index.php?action=product_create">Nouveau produit</a>';
}
if (nav_can($du, 'sale_create')) {
    $quick[] = '<a href="index.php?action=sale_create">Nouvelle vente</a>';
}
?>
<?php if (count($quick) > 0): ?>
    <section class="quick-actions" aria-label="Raccourcis">
        <h2 class="quick-actions__title">Accès rapide</h2>
        <div class="quick-actions__links"><?= implode('', $quick) ?></div>
    </section>
<?php endif; ?>
<?php require __DIR__ . '/../partials/footer.php'; ?>
