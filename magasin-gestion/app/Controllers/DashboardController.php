<?php

require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Sale.php';
require_once __DIR__ . '/../models/User.php';

class DashboardController
{
    private $productModel;
    private $saleModel;
    private $userModel;

    public function __construct()
    {
        $this->productModel = new Product();
        $this->saleModel = new Sale();
        $this->userModel = new User();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function index()
    {
        $products = $this->productModel->getAll();
        $lowStockProducts = $this->productModel->getLowStockProducts();
        $sales = $this->saleModel->getAll();
        $users = $this->userModel->getAll();
        $revenue = $this->saleModel->getTotalRevenue();

        $productCount = count($products);
        $lowStockCount = count($lowStockProducts);
        $salesCount = count($sales);
        $userCount = count($users);

        require_once __DIR__ . '/../views/dashboard/index.php';
    }
}