<?php
$pageTitle = 'Nouvel utilisateur';
require __DIR__ . '/../partials/header.php';
?>
<header class="page-header">
    <h1 class="page-header__title">Nouvel utilisateur</h1>
    <p class="page-header__desc">Mot de passe : minimum recommandé 8 caractères.</p>
</header>

<div class="panel">
    <div class="panel__pad">
        <form method="post" action="index.php?action=user_store" class="form-grid form-grid--2">
            <?php csrf_field(); ?>
            <div>
                <label for="name">Nom</label>
                <input id="name" name="name" required autocomplete="name">
            </div>
            <div>
                <label for="email">E-mail</label>
                <input id="email" name="email" type="email" required autocomplete="email">
            </div>
            <div>
                <label for="password">Mot de passe</label>
                <input id="password" name="password" type="password" required autocomplete="new-password">
            </div>
            <div>
                <label for="role">Rôle</label>
                <select id="role" name="role">
                    <option value="vendeur">vendeur</option>
                    <option value="fournisseur">fournisseur</option>
                    <option value="admin">admin</option>
                </select>
            </div>
            <div class="form-grid__full">
                <label for="supplier_id">Entreprise fournisseur (obligatoire si rôle fournisseur)</label>
                <select id="supplier_id" name="supplier_id">
                    <option value="">—</option>
                    <?php foreach ($suppliers ?? [] as $s): ?>
                        <option value="<?= (int) $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="text-muted" style="margin:0.35rem 0 0;font-size:0.88rem">Permet au fournisseur de voir les bons de commande envoyés à cette entreprise.</p>
            </div>
            <div class="form-grid__full form-actions">
                <button type="submit" class="btn">Créer</button>
                <a class="btn btn-ghost" href="index.php?action=users">Annuler</a>
            </div>
        </form>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
