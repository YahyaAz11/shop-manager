<?php

require_once __DIR__ . '/../models/Sale.php';
require_once __DIR__ . '/../models/SaleItem.php';
require_once __DIR__ . '/../models/Product.php';

class SaleController
{
    private $saleModel;
    private $saleItemModel;
    private $productModel;

    public function __construct()
    {
        $this->saleModel = new Sale();
        $this->saleItemModel = new SaleItem();
        $this->productModel = new Product();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function index()
    {
        $sales = $this->saleModel->getAll();
        require_once __DIR__ . '/../views/sales/index.php';
    }

    public function create()
    {
        $products = $this->productModel->getAll();
        require_once __DIR__ . '/../views/sales/create.php';
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user']['id'];

            $productIds = $_POST['product_id'];
            $quantities = $_POST['quantity'];
            $prices = $_POST['price'];

            $total = 0;

            for ($i = 0; $i < count($productIds); $i++) {
                $total += $quantities[$i] * $prices[$i];
            }

            $saleId = $this->saleModel->create([
                'user_id' => $userId,
                'total'   => $total
            ]);

            for ($i = 0; $i < count($productIds); $i++) {
                $this->saleItemModel->create([
                    'sale_id'    => $saleId,
                    'product_id' => $productIds[$i],
                    'quantity'   => $quantities[$i],
                    'price'      => $prices[$i]
                ]);

                $this->productModel->decreaseStock($productIds[$i], $quantities[$i]);
            }

            header('Location: index.php?action=sales');
            exit;
        }
    }

    public function show($id)
    {
        $sale = $this->saleModel->getById($id);
        $items = $this->saleItemModel->getBySaleId($id);

        require_once __DIR__ . '/../views/sales/show.php';
    }

    public function mySales()
    {
        $userId = $_SESSION['user']['id'];
        $sales = $this->saleModel->getSalesByUser($userId);

        require_once __DIR__ . '/../views/sales/my_sales.php';
    }
}