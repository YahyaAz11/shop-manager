<?php
$pageTitle = 'Modifier l’utilisateur';
require __DIR__ . '/../partials/header.php';
if (empty($user)): ?>
<header class="page-header">
    <h1 class="page-header__title">Utilisateur introuvable</h1>
</header>
<p><a class="link-action" href="index.php?action=users">← Retour</a></p>
<?php require __DIR__ . '/../partials/footer.php'; return; endif; ?>
<header class="page-header">
    <h1 class="page-header__title">Modifier l’utilisateur</h1>
    <p class="page-header__desc">#<?= (int) $user['id'] ?> — laisser le mot de passe vide pour ne pas le changer.</p>
</header>

<div class="panel">
    <div class="panel__pad">
        <form method="post" action="index.php?action=user_update&id=<?= (int) $user['id'] ?>" class="form-grid form-grid--2">
            <?php csrf_field(); ?>
            <div>
                <label for="name">Nom</label>
                <input id="name" name="name" required value="<?= htmlspecialchars((string) $user['name']) ?>" autocomplete="name">
            </div>
            <div>
                <label for="email">E-mail</label>
                <input id="email" name="email" type="email" required value="<?= htmlspecialchars((string) $user['email']) ?>" autocomplete="email">
            </div>
            <div>
                <label for="password">Nouveau mot de passe</label>
                <input id="password" name="password" type="password" autocomplete="new-password" placeholder="(optionnel)">
            </div>
            <div>
                <label for="role">Rôle</label>
                <select id="role" name="role">
                    <?php foreach (['vendeur', 'fournisseur', 'admin'] as $r): ?>
                        <option value="<?= htmlspecialchars($r) ?>" <?= (string) $user['role'] === $r ? 'selected' : '' ?>><?= htmlspecialchars($r) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-grid__full">
                <label for="supplier_id">Entreprise fournisseur (obligatoire si rôle fournisseur)</label>
                <select id="supplier_id" name="supplier_id">
                    <option value="">—</option>
                    <?php
                    $selSid = (string) ($user['supplier_id'] ?? '');
                    foreach ($suppliers ?? [] as $s):
                    ?>
                        <option value="<?= (int) $s['id'] ?>" <?= $selSid !== '' && (string) $s['id'] === $selSid ? 'selected' : '' ?>><?= htmlspecialchars($s['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-grid__full form-actions">
                <button type="submit" class="btn">Enregistrer</button>
                <a class="btn btn-ghost" href="index.php?action=users">Annuler</a>
            </div>
        </form>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
