<?php

declare(strict_types=1);

class CategoryController extends BaseController
{
    /** @var Category */
    private $categoryModel;

    public function __construct()
    {
        $this->categoryModel = new Category();
    }

    public function index(): void
    {
        $categories = $this->categoryModel->getAll();
        require_once APP_PATH . '/views/categories/index.php';
    }

    public function create(): void
    {
        require_once APP_PATH . '/views/categories/create.php';
    }

    public function store(): void
    {
        $this->requireCsrf();
        $data = ['name' => trim((string) ($_POST['name'] ?? ''))];
        $this->categoryModel->create($data);
        $_SESSION['flash_success'] = 'Catégorie créée.';
        $this->redirect('categories');
    }

    public function edit(int $id): void
    {
        $category = $this->categoryModel->getById($id);
        require_once APP_PATH . '/views/categories/edit.php';
    }

    public function update(int $id): void
    {
        $this->requireCsrf();
        $data = ['name' => trim((string) ($_POST['name'] ?? ''))];
        $this->categoryModel->update($id, $data);
        $_SESSION['flash_success'] = 'Catégorie mise à jour.';
        $this->redirect('categories');
    }

    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('categories');
        }
        $this->requireCsrf();
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        if ($id < 1) {
            $this->redirect('categories');
        }
        $this->categoryModel->delete($id);
        $_SESSION['flash_success'] = 'Catégorie supprimée.';
        $this->redirect('categories');
    }
}
