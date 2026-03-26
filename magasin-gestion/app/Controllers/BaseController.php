<?php

declare(strict_types=1);

class BaseController
{
    /** @return array<string, mixed>|null */
    protected function currentUser(): ?array
    {
        $u = $_SESSION['user'] ?? null;

        return is_array($u) ? $u : null;
    }

    protected function isAdmin(): bool
    {
        return ($this->currentUser()['role'] ?? '') === 'admin';
    }

    /** Met à jour supplier_id en session depuis la BDD (après modification par un admin, sans se reconnecter). */
    protected function syncSessionSupplierFromDb(): void
    {
        $u = $this->currentUser();
        if (!$u || ($u['role'] ?? '') !== 'fournisseur' || !isset($_SESSION['user'])) {
            return;
        }
        $uid = (int) ($u['id'] ?? 0);
        if ($uid < 1) {
            return;
        }
        $fresh = (new User())->getById($uid);
        if (!$fresh) {
            return;
        }
        $sid = $fresh['supplier_id'] ?? null;
        if ($sid !== null && $sid !== '' && (int) $sid > 0) {
            $_SESSION['user']['supplier_id'] = (int) $sid;
        } else {
            $_SESSION['user']['supplier_id'] = null;
        }
    }

    protected function redirect(string $action, array $query = []): void
    {
        $query['action'] = $action;
        header('Location: index.php?' . http_build_query($query));
        exit;
    }

    protected function requireCsrf(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            $_SESSION['flash_error'] = 'Session expirée ou formulaire invalide. Réessayez.';
            $this->redirect('dashboard');
        }
    }
}
