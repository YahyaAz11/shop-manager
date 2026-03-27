<?php

declare(strict_types=1);

class UserController extends BaseController
{
    /** @var User */
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    private function gateAdmin(): void
    {
        if (!$this->isAdmin()) {
            $_SESSION['flash_error'] = 'Accès réservé aux administrateurs.';
            $this->redirect('dashboard');
        }
    }

    public function index(): void
    {
        $this->gateAdmin();
        $reqPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $total = $this->userModel->countAll();
        $state = pagination_state($reqPage, $total);
        $users = $this->userModel->getPage($state['offset'], $state['per_page']);
        $pagination = pagination_for_view('users', $state);

        require_once APP_PATH . '/views/users/index.php';
    }

    public function create(): void
    {
        $this->gateAdmin();
        $suppliers = (new Supplier())->getAll();
        require_once APP_PATH . '/views/users/create.php';
    }

    public function store(): void
    {
        $this->gateAdmin();
        $this->requireCsrf();

        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $role = trim((string) ($_POST['role'] ?? 'vendeur'));

        if ($name === '' || $email === '' || $password === '') {
            $_SESSION['flash_error'] = 'Nom, e-mail et mot de passe sont obligatoires.';
            $this->redirect('user_create');
        }
        if (!in_array($role, ['admin', 'vendeur', 'fournisseur'], true)) {
            $role = 'vendeur';
        }
        if ($this->userModel->getByEmail($email)) {
            $_SESSION['flash_error'] = 'Cet e-mail est déjà utilisé.';
            $this->redirect('user_create');
        }

        $supplierIdRaw = $_POST['supplier_id'] ?? '';
        if ($role === 'fournisseur') {
            $sid = (int) $supplierIdRaw;
            if ($sid < 1) {
                $_SESSION['flash_error'] = 'Pour un compte fournisseur, choisissez l’entreprise (fiche fournisseur) à laquelle il est rattaché.';
                $this->redirect('user_create');
            }
        }

        $this->userModel->create([
            'name'        => $name,
            'email'       => $email,
            'password'    => $password,
            'role'        => $role,
            'supplier_id' => $role === 'fournisseur' ? (int) $supplierIdRaw : null,
        ]);
        $_SESSION['flash_success'] = 'Utilisateur créé.';
        $this->redirect('users');
    }

    public function edit(int $id): void
    {
        $this->gateAdmin();
        $user = $this->userModel->getById($id);
        $suppliers = (new Supplier())->getAll();
        require_once APP_PATH . '/views/users/edit.php';
    }

    public function update(int $id): void
    {
        $this->gateAdmin();
        $this->requireCsrf();

        $existing = $this->userModel->getById($id);
        if (!$existing) {
            $_SESSION['flash_error'] = 'Utilisateur introuvable.';
            $this->redirect('users');
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $role = trim((string) ($_POST['role'] ?? 'vendeur'));
        $password = trim((string) ($_POST['password'] ?? ''));

        if ($name === '' || $email === '') {
            $_SESSION['flash_error'] = 'Nom et e-mail obligatoires.';
            $this->redirect('user_edit', ['id' => $id]);
        }
        if (!in_array($role, ['admin', 'vendeur', 'fournisseur'], true)) {
            $role = 'vendeur';
        }

        $other = $this->userModel->getByEmail($email);
        if ($other && (int) $other['id'] !== $id) {
            $_SESSION['flash_error'] = 'Cet e-mail est déjà utilisé.';
            $this->redirect('user_edit', ['id' => $id]);
        }

        if ($existing['role'] === 'admin' && $role !== 'admin' && $this->userModel->countByRole('admin') <= 1) {
            $_SESSION['flash_error'] = 'Impossible de retirer le dernier compte administrateur.';
            $this->redirect('user_edit', ['id' => $id]);
        }

        $supplierIdRaw = $_POST['supplier_id'] ?? '';
        if ($role === 'fournisseur') {
            $sid = (int) $supplierIdRaw;
            if ($sid < 1) {
                $_SESSION['flash_error'] = 'Pour un compte fournisseur, sélectionnez l’entreprise fournisseur liée.';
                $this->redirect('user_edit', ['id' => $id]);
            }
        }

        $this->userModel->update($id, [
            'name'        => $name,
            'email'       => $email,
            'role'        => $role,
            'supplier_id' => $role === 'fournisseur' ? (int) $supplierIdRaw : null,
        ]);
        if ($password !== '') {
            $this->userModel->updatePassword($id, $password);
        }

        $_SESSION['flash_success'] = 'Utilisateur mis à jour.';
        $this->redirect('users');
    }

    public function delete(): void
    {
        $this->gateAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('users');
        }
        $this->requireCsrf();

        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        if ($id < 1) {
            $this->redirect('users');
        }

        $me = (int) ($this->currentUser()['id'] ?? 0);
        if ($id === $me) {
            $_SESSION['flash_error'] = 'Vous ne pouvez pas supprimer votre propre compte.';
            $this->redirect('users');
        }

        $target = $this->userModel->getById($id);
        if ($target && $target['role'] === 'admin' && $this->userModel->countByRole('admin') <= 1) {
            $_SESSION['flash_error'] = 'Impossible de supprimer le dernier administrateur.';
            $this->redirect('users');
        }

        $this->userModel->delete($id);
        $_SESSION['flash_success'] = 'Utilisateur supprimé.';
        $this->redirect('users');
    }
}
