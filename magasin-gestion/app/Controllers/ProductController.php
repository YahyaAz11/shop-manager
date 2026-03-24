<?php

require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/Supplier.php';

class ProductController
{
    private $productModel;
    private $categoryModel;
    private $supplierModel;

    public function __construct()
    {
        $this->productModel = new Product();
        $this->categoryModel = new Category();
        $this->supplierModel = new Supplier();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function index()
    {
        $products = $this->productModel->getAll();
        require_once __DIR__ . '/../views/products/index.php';
    }

    public function create()
    {
        $categories = $this->categoryModel->getAll();
        $suppliers = $this->supplierModel->getAll();

        require_once __DIR__ . '/../views/products/create.php';
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name'        => trim($_POST['name']),
                'description' => trim($_POST['description']),
                'price_buy'   => $_POST['price_buy'],
                'price_sell'  => $_POST['price_sell'],
                'stock'       => $_POST['stock'],
                'category_id' => $_POST['category_id'],
                'supplier_id' => $_POST['supplier_id']
            ];

            $this->productModel->create($data);

            header('Location: index.php?action=products');
            exit;
        }
    }

    public function edit($id)
    {
        $product = $this->productModel->getById($id);
        $categories = $this->categoryModel->getAll();
        $suppliers = $this->supplierModel->getAll();

        require_once __DIR__ . '/../views/products/edit.php';
    }

    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name'        => trim($_POST['name']),
                'description' => trim($_POST['description']),
                'price_buy'   => $_POST['price_buy'],
                'price_sell'  => $_POST['price_sell'],
                'stock'       => $_POST['stock'],
                'category_id' => $_POST['category_id'],
                'supplier_id' => $_POST['supplier_id']
            ];

            $this->productModel->update($id, $data);

            header('Location: index.php?action=products');
            exit;
        }
    }

    public function delete($id)
    {
        $this->productModel->delete($id);
        header('Location: index.php?action=products');
        exit;
    }

    public function search()
    {
        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
        $products = $this->productModel->search($keyword);

        require_once __DIR__ . '/../views/products/index.php';
    }

    public function lowStock()
    {
        $products = $this->productModel->getLowStockProducts();
        require_once __DIR__ . '/../views/products/low_stock.php';
    }
}