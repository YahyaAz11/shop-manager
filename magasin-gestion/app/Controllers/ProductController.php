<?php

declare(strict_types=1);

class ProductController extends BaseController
{
    /** @var Product */
    private $productModel;
    /** @var Category */
    private $categoryModel;
    /** @var Supplier */
    private $supplierModel;

    public function __construct()
    {
        $this->productModel = new Product();
        $this->categoryModel = new Category();
        $this->supplierModel = new Supplier();
    }

    public function index(): void
    {
        $reqPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $total = $this->productModel->countAll(null);
        $state = pagination_state($reqPage, $total);
        $products = $this->productModel->getPage($state['offset'], $state['per_page'], null);
        $pagination = pagination_for_view('products', $state);
        $canManage = $this->isAdmin();
        require_once APP_PATH . '/views/products/index.php';
    }

    public function create(): void
    {
        $categories = $this->categoryModel->getAll();
        $suppliers = $this->supplierModel->getAll();
        require_once APP_PATH . '/views/products/create.php';
    }

    public function store(): void
    {
        $this->requireCsrf();
        $data = [
            'name'                   => trim((string) ($_POST['name'] ?? '')),
            'description'            => trim((string) ($_POST['description'] ?? '')),
            'price_buy'              => $_POST['price_buy'] ?? '',
            'price_sell'             => $_POST['price_sell'] ?? '',
            'stock'                  => $_POST['stock'] ?? '',
            'stock_alert_threshold'  => $_POST['stock_alert_threshold'] ?? 5,
            'category_id'            => $_POST['category_id'] ?? '',
            'supplier_id'            => $_POST['supplier_id'] ?? '',
        ];
        $this->productModel->create($data);
        $_SESSION['flash_success'] = 'Produit créé.';
        $this->redirect('products');
    }

    public function edit(int $id): void
    {
        $product = $this->productModel->getById($id);
        $categories = $this->categoryModel->getAll();
        $suppliers = $this->supplierModel->getAll();
        require_once APP_PATH . '/views/products/edit.php';
    }

    public function update(int $id): void
    {
        $this->requireCsrf();
        $data = [
            'name'                   => trim((string) ($_POST['name'] ?? '')),
            'description'            => trim((string) ($_POST['description'] ?? '')),
            'price_buy'              => $_POST['price_buy'] ?? '',
            'price_sell'             => $_POST['price_sell'] ?? '',
            'stock'                  => $_POST['stock'] ?? '',
            'stock_alert_threshold'  => $_POST['stock_alert_threshold'] ?? 5,
            'category_id'            => $_POST['category_id'] ?? '',
            'supplier_id'            => $_POST['supplier_id'] ?? '',
        ];
        $this->productModel->update($id, $data);
        $_SESSION['flash_success'] = 'Produit mis à jour.';
        $this->redirect('products');
    }

    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('products');
        }
        $this->requireCsrf();
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        if ($id < 1) {
            $this->redirect('products');
        }
        $this->productModel->delete($id);
        $_SESSION['flash_success'] = 'Produit supprimé.';
        $this->redirect('products');
    }

    public function search(): void
    {
        $keyword = isset($_GET['keyword']) ? trim((string) $_GET['keyword']) : '';
        $reqPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $total = $this->productModel->countAll($keyword);
        $state = pagination_state($reqPage, $total);
        $products = $this->productModel->getPage($state['offset'], $state['per_page'], $keyword);
        $extra = $keyword !== '' ? ['keyword' => $keyword] : [];
        $pagination = pagination_for_view('product_search', $state, $extra);
        $canManage = $this->isAdmin();
        require_once APP_PATH . '/views/products/index.php';
    }

    public function lowStock(): void
    {
        $products = $this->productModel->getLowStockProducts();
        $canManage = $this->isAdmin();
        require_once APP_PATH . '/views/products/low_stock.php';
    }
}
