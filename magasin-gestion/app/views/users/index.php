<?php
$pageTitle = 'Utilisateurs';
require __DIR__ . '/../partials/header.php';
?>
<header class="page-header">
    <h1 class="page-header__title">Utilisateurs</h1>
    <p class="page-header__desc">Comptes, rôles et accès au logiciel.</p>
</header>

<div class="page-toolbar">
    <a class="btn" href="index.php?action=user_create">+ Nouvel utilisateur</a>
</div>

<div class="panel panel--flush">
    <?php if (!empty($users)): ?>
        <?php require __DIR__ . '/../partials/pagination.php'; ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>E-mail</th>
                        <th>Rôle</th>
                        <th>Fournisseur lié</th>
                        <th>Créé le</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars((string) $u['name']) ?></strong></td>
                        <td><?= htmlspecialchars((string) $u['email']) ?></td>
                        <td><span class="navbar-role" style="text-transform:none"><?= htmlspecialchars((string) $u['role']) ?></span></td>
                        <td class="text-muted"><?= (string) ($u['role'] ?? '') === 'fournisseur' ? htmlspecialchars((string) ($u['supplier_name'] ?? '—')) : '—' ?></td>
                        <td class="text-muted"><?= htmlspecialchars((string) ($u['created_at'] ?? '—')) ?></td>
                        <td class="table-actions">
                            <a class="link-action" href="index.php?action=user_edit&id=<?= (int) $u['id'] ?>">Modifier</a>
                            <?php if ((int) $u['id'] !== (int) ($_SESSION['user']['id'] ?? 0)): ?>
                                <form method="post" action="index.php?action=user_delete" class="form-inline-delete" onsubmit="return confirm('Supprimer cet utilisateur ?');">
                                    <?php csrf_field(); ?>
                                    <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-compact">Supprimer</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <p class="empty-state__title">Aucun utilisateur</p>
            <p class="empty-state__hint">Créez un compte pour commencer.</p>
        </div>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
