<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$action = isset($_GET['action']) ? (string) $_GET['action'] : 'login';
$user = isset($_SESSION['user']) && is_array($_SESSION['user']) ? $_SESSION['user'] : null;

$publicActions = ['login', 'do_login', 'logout'];
if (!in_array($action, $publicActions, true)) {
    if ($user === null) {
        header('Location: index.php?action=login');
        exit;
    }
    if (!user_can_access($user, $action)) {
        $_SESSION['flash_error'] = 'Vous n\'avez pas accès à cette page.';
        header('Location: index.php?action=dashboard');
        exit;
    }
}

/** @var array<string, array{0: class-string, 1: string, 2?: true}> $routes */
$routes = [
    'login'           => [AuthController::class, 'showLogin'],
    'do_login'        => [AuthController::class, 'login'],
    'logout'          => [AuthController::class, 'logout'],
    'dashboard'       => [DashboardController::class, 'index'],
    'products'        => [ProductController::class, 'index'],
    'product_create'  => [ProductController::class, 'create'],
    'product_store'   => [ProductController::class, 'store'],
    'product_edit'    => [ProductController::class, 'edit', true],
    'product_update'  => [ProductController::class, 'update', true],
    'product_delete'  => [ProductController::class, 'delete'],
    'product_search'  => [ProductController::class, 'search'],
    'low_stock'       => [ProductController::class, 'lowStock'],
    'categories'      => [CategoryController::class, 'index'],
    'category_create' => [CategoryController::class, 'create'],
    'category_store'  => [CategoryController::class, 'store'],
    'category_edit'   => [CategoryController::class, 'edit', true],
    'category_update' => [CategoryController::class, 'update', true],
    'category_delete' => [CategoryController::class, 'delete'],
    'suppliers'       => [SupplierController::class, 'index'],
    'supplier_create' => [SupplierController::class, 'create'],
    'supplier_store'  => [SupplierController::class, 'store'],
    'supplier_edit'   => [SupplierController::class, 'edit', true],
    'supplier_update' => [SupplierController::class, 'update', true],
    'supplier_delete' => [SupplierController::class, 'delete'],
    'sales'           => [SaleController::class, 'index'],
    'sale_create'     => [SaleController::class, 'create'],
    'sale_store'      => [SaleController::class, 'store'],
    'sale_show'       => [SaleController::class, 'show', true],
    'sale_ticket'     => [SaleController::class, 'ticket', true],
    'my_sales'        => [SaleController::class, 'mySales'],
    'users'           => [UserController::class, 'index'],
    'user_create'     => [UserController::class, 'create'],
    'user_store'      => [UserController::class, 'store'],
    'user_edit'       => [UserController::class, 'edit', true],
    'user_update'     => [UserController::class, 'update', true],
    'user_delete'     => [UserController::class, 'delete'],
    'purchase_orders'      => [PurchaseOrderController::class, 'index'],
    'purchase_order_create'=> [PurchaseOrderController::class, 'create'],
    'purchase_order_store' => [PurchaseOrderController::class, 'store'],
    'purchase_order_show'  => [PurchaseOrderController::class, 'show', true],
    'purchase_order_send'  => [PurchaseOrderController::class, 'markSent'],
    'purchase_order_receive'=> [PurchaseOrderController::class, 'receive'],
    'purchase_order_delete' => [PurchaseOrderController::class, 'delete'],
    'supplier_purchase_orders'       => [PurchaseOrderController::class, 'supplierIndex'],
    'supplier_purchase_order_show'   => [PurchaseOrderController::class, 'supplierShow', true],
    'supplier_purchase_order_respond'=> [PurchaseOrderController::class, 'supplierRespond'],
];

if (!isset($routes[$action])) {
    http_response_code(404);
    echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><title>Introuvable</title></head><body><p>Page introuvable.</p><p><a href="index.php?action=dashboard">Accueil</a></p></body></html>';
    exit;
}

$route = $routes[$action];
$class = $route[0];
$method = $route[1];
$needsId = $route[2] ?? false;

$controller = new $class();

if ($needsId) {
    $id = request_id();
    if ($id === null) {
        $_SESSION['flash_error'] = 'Lien invalide.';
        header('Location: index.php?action=dashboard');
        exit;
    }
    $controller->{$method}($id);
    exit;
}

$controller->{$method}();
