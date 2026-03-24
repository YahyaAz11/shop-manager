<?php

require_once __DIR__ . '/../models/Category.php';

class CategoryController
{
    private $categoryModel;

    public function __construct()
    {
        $this->categoryModel = new Category();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function index()
    {
        $categories = $this->categoryModel->getAll();
        require_once __DIR__ . '/../views/categories/index.php';
    }

    public function create()
    {
        require_once __DIR__ . '/../views/categories/create.php';
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => trim($_POST['name'])
            ];

            $this->categoryModel->create($data);

            header('Location: index.php?action=categories');
            exit;
        }
    }

    public function edit($id)
    {
        $category = $this->categoryModel->getById($id);
        require_once __DIR__ . '/../views/categories/edit.php';
    }

    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => trim($_POST['name'])
            ];

            $this->categoryModel->update($id, $data);

            header('Location: index.php?action=categories');
            exit;
        }
    }

    public function delete($id)
    {
        $this->categoryModel->delete($id);
        header('Location: index.php?action=categories');
        exit;
    }
}