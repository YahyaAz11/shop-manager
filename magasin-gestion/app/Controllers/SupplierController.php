<?php

declare(strict_types=1);

class SupplierController extends BaseController
{
    /** @var Supplier */
    private $supplierModel;

    public function __construct()
    {
        $this->supplierModel = new Supplier();
    }

    public function index(): void
    {
        $suppliers = $this->supplierModel->getAll();
        $canManage = $this->isAdmin();
        require_once APP_PATH . '/views/suppliers/index.php';
    }

    public function create(): void
    {
        require_once APP_PATH . '/views/suppliers/create.php';
    }

    public function store(): void
    {
        $this->requireCsrf();
        $data = [
            'name'    => trim((string) ($_POST['name'] ?? '')),
            'contact' => '',
            'phone'   => trim((string) ($_POST['phone'] ?? '')),
            'email'   => trim((string) ($_POST['email'] ?? '')),
            'address' => trim((string) ($_POST['address'] ?? '')),
        ];
        $this->supplierModel->create($data);
        $_SESSION['flash_success'] = 'Fournisseur créé.';
        $this->redirect('suppliers');
    }

    public function edit(int $id): void
    {
        $supplier = $this->supplierModel->getById($id);
        require_once APP_PATH . '/views/suppliers/edit.php';
    }

    public function update(int $id): void
    {
        $this->requireCsrf();
        $data = [
            'name'    => trim((string) ($_POST['name'] ?? '')),
            'contact' => '',
            'phone'   => trim((string) ($_POST['phone'] ?? '')),
            'email'   => trim((string) ($_POST['email'] ?? '')),
            'address' => trim((string) ($_POST['address'] ?? '')),
        ];
        $this->supplierModel->update($id, $data);
        $_SESSION['flash_success'] = 'Fournisseur mis à jour.';
        $this->redirect('suppliers');
    }

    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('suppliers');
        }
        $this->requireCsrf();
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        if ($id < 1) {
            $this->redirect('suppliers');
        }
        $this->supplierModel->delete($id);
        $_SESSION['flash_success'] = 'Fournisseur supprimé.';
        $this->redirect('suppliers');
    }
}
