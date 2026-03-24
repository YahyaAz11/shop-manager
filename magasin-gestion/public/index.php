<?php

require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/ProductController.php';
require_once __DIR__ . '/../controllers/CategoryController.php';
require_once __DIR__ . '/../controllers/SupplierController.php';
require_once __DIR__ . '/../controllers/SaleController.php';
require_once __DIR__ . '/../controllers/DashboardController.php';

$action = $_GET['action'] ?? 'login';
$id = $_GET['id'] ?? null;

switch ($action) {
    case 'login':
        (new AuthController())->showLogin();
        break;

    case 'do_login':
        (new AuthController())->login();
        break;

    case 'logout':
        (new AuthController())->logout();
        break;

    case 'dashboard':
        (new DashboardController())->index();
        break;

    case 'products':
        (new ProductController())->index();
        break;

    case 'product_create':
        (new ProductController())->create();
        break;

    case 'product_store':
        (new ProductController())->store();
        break;

    case 'product_edit':
        (new ProductController())->edit($id);
        break;

    case 'product_update':
        (new ProductController())->update($id);
        break;

    case 'product_delete':
        (new ProductController())->delete($id);
        break;

    case 'product_search':
        (new ProductController())->search();
        break;

    case 'low_stock':
        (new ProductController())->lowStock();
        break;

    case 'categories':
        (new CategoryController())->index();
        break;

    case 'category_create':
        (new CategoryController())->create();
        break;

    case 'category_store':
        (new CategoryController())->store();
        break;

    case 'category_edit':
        (new CategoryController())->edit($id);
        break;

    case 'category_update':
        (new CategoryController())->update($id);
        break;

    case 'category_delete':
        (new CategoryController())->delete($id);
        break;

    case 'suppliers':
        (new SupplierController())->index();
        break;

    case 'supplier_create':
        (new SupplierController())->create();
        break;

    case 'supplier_store':
        (new SupplierController())->store();
        break;

    case 'supplier_edit':
        (new SupplierController())->edit($id);
        break;

    case 'supplier_update':
        (new SupplierController())->update($id);
        break;

    case 'supplier_delete':
        (new SupplierController())->delete($id);
        break;

    case 'sales':
        (new SaleController())->index();
        break;

    case 'sale_create':
        (new SaleController())->create();
        break;

    case 'sale_store':
        (new SaleController())->store();
        break;

    case 'sale_show':
        (new SaleController())->show($id);
        break;

    case 'my_sales':
        (new SaleController())->mySales();
        break;

    default:
        echo "Action introuvable.";
        break;
}