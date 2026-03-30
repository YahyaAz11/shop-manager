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

    /** Fournisseur : id entreprise liée (>0), sinon 0. Autres rôles : 0. */
    private function catalogSupplierAccountId(): int
    {
        $this->syncSessionSupplierFromDb();
        $u = $this->currentUser() ?? [];
        if (($u['role'] ?? '') !== 'fournisseur') {
            return 0;
        }
        $sid = isset($u['supplier_id']) ? (int) $u['supplier_id'] : 0;

        return $sid > 0 ? $sid : 0;
    }

    /**
     * Liste / recherche : null = tout le catalogue (admin), int = filtre fournisseur.
     *
     * @return array{filter: ?int, not_linked: bool}
     */
    private function catalogListContext(): array
    {
        $this->syncSessionSupplierFromDb();
        $u = $this->currentUser() ?? [];
        if (($u['role'] ?? '') !== 'fournisseur') {
            return ['filter' => null, 'not_linked' => false];
        }
        $sid = isset($u['supplier_id']) ? (int) $u['supplier_id'] : 0;
        if ($sid < 1) {
            return ['filter' => null, 'not_linked' => true];
        }

        return ['filter' => $sid, 'not_linked' => false];
    }

    /** Fournisseur sans lien : message + redirection. Retourne l'id fournisseur (>0). */
    private function requireSupplierLinkedForWrite(): int
    {
        $sid = $this->catalogSupplierAccountId();
        if (($this->currentUser()['role'] ?? '') === 'fournisseur' && $sid < 1) {
            $_SESSION['flash_error'] = 'Votre compte doit être rattaché à une entreprise fournisseur pour ajouter des produits.';
            $this->redirect('supplier_purchase_orders');
        }

        return $sid;
    }

    public function index(): void
    {
        $ctx = $this->catalogListContext();
        $canManage = $this->isAdmin();
        $supplierAccountId = $this->catalogSupplierAccountId();
        $isSupplierCatalog = $supplierAccountId > 0;
        $supplierNotLinked = $ctx['not_linked'];
        $canAddProduct = $canManage || $isSupplierCatalog;

        if ($supplierNotLinked) {
            $products = [];
            $state = pagination_state(1, 0);
            $pagination = pagination_for_view('products', $state);
            require_once APP_PATH . '/views/products/index.php';

            return;
        }

        $supplierFilter = $ctx['filter'];
        $reqPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $total = $this->productModel->countAll(null, $supplierFilter);
        $state = pagination_state($reqPage, $total);
        $products = $this->productModel->getPage($state['offset'], $state['per_page'], null, $supplierFilter);
        $pagination = pagination_for_view('products', $state);

        require_once APP_PATH . '/views/products/index.php';
    }

    public function create(): void
    {
        $role = (string) ($this->currentUser()['role'] ?? '');
        if ($role === 'fournisseur') {
            $sid = $this->requireSupplierLinkedForWrite();
            $sup = $this->supplierModel->getById($sid);
            $supplierCatalogLock = [
                'id'   => $sid,
                'name' => $sup ? (string) $sup['name'] : ('#' . $sid),
            ];
            $suppliers = [];
        } else {
            $supplierCatalogLock = null;
            $suppliers = $this->supplierModel->getAll();
        }

        $categories = $this->categoryModel->getAll();
        require_once APP_PATH . '/views/products/create.php';
    }

    public function store(): void
    {
        $this->requireCsrf();
        $role = (string) ($this->currentUser()['role'] ?? '');
        if ($role === 'fournisseur') {
            $sid = $this->requireSupplierLinkedForWrite();
        } else {
            $sid = 0;
        }

        $data = [
            'name'                   => trim((string) ($_POST['name'] ?? '')),
            'description'            => trim((string) ($_POST['description'] ?? '')),
            'price_buy'              => $_POST['price_buy'] ?? '',
            'price_sell'             => $_POST['price_sell'] ?? '',
            'vat_rate'               => $_POST['vat_rate'] ?? 20,
            'stock'                  => $_POST['stock'] ?? '',
            'stock_alert_threshold'  => $_POST['stock_alert_threshold'] ?? 5,
            'category_id'            => $_POST['category_id'] ?? '',
            'supplier_id'            => $role === 'fournisseur' ? (string) $sid : ($_POST['supplier_id'] ?? ''),
        ];
        $this->productModel->create($data);
        $_SESSION['flash_success'] = 'Produit créé. Il pourra être commandé par le magasin sur un bon de commande.';
        $this->redirect('products');
    }

    public function edit(int $id): void
    {
        $product = $this->productModel->getById($id);
        $categories = $this->categoryModel->getAll();
        $role = (string) ($this->currentUser()['role'] ?? '');

        if ($role === 'fournisseur') {
            $sid = $this->catalogSupplierAccountId();
            if ($sid < 1 || !$product || (int) ($product['supplier_id'] ?? 0) !== $sid) {
                $_SESSION['flash_error'] = 'Vous ne pouvez modifier que vos propres produits.';
                $this->redirect('products');
            }
            $sup = $this->supplierModel->getById($sid);
            $supplierCatalogLock = [
                'id'   => $sid,
                'name' => $sup ? (string) $sup['name'] : ('#' . $sid),
            ];
            $suppliers = [];
        } else {
            $supplierCatalogLock = null;
            $suppliers = $this->supplierModel->getAll();
        }

        require_once APP_PATH . '/views/products/edit.php';
    }

    public function update(int $id): void
    {
        $this->requireCsrf();
        $product = $this->productModel->getById($id);
        if (!$product) {
            $_SESSION['flash_error'] = 'Produit introuvable.';
            $this->redirect('products');
        }

        $role = (string) ($this->currentUser()['role'] ?? '');
        if ($role === 'fournisseur') {
            $sid = $this->catalogSupplierAccountId();
            if ($sid < 1 || (int) ($product['supplier_id'] ?? 0) !== $sid) {
                $_SESSION['flash_error'] = 'Action non autorisée.';
                $this->redirect('products');
            }
        } else {
            $sid = 0;
        }

        $data = [
            'name'                   => trim((string) ($_POST['name'] ?? '')),
            'description'            => trim((string) ($_POST['description'] ?? '')),
            'price_buy'              => $_POST['price_buy'] ?? '',
            'price_sell'             => $_POST['price_sell'] ?? '',
            'vat_rate'               => $_POST['vat_rate'] ?? 20,
            'stock'                  => $_POST['stock'] ?? '',
            'stock_alert_threshold'  => $_POST['stock_alert_threshold'] ?? 5,
            'category_id'            => $_POST['category_id'] ?? '',
            'supplier_id'            => $role === 'fournisseur' ? (string) $sid : ($_POST['supplier_id'] ?? ''),
        ];
        $this->productModel->update($id, $data);
        $_SESSION['flash_success'] = 'Produit mis à jour.';
        $this->redirect('products');
    }

    public function delete(): void
    {
        if (($this->currentUser()['role'] ?? '') === 'fournisseur') {
            $_SESSION['flash_error'] = 'Seul un administrateur peut supprimer un produit.';
            $this->redirect('products');
        }
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
        $ctx = $this->catalogListContext();
        $canManage = $this->isAdmin();
        $supplierAccountId = $this->catalogSupplierAccountId();
        $isSupplierCatalog = $supplierAccountId > 0;
        $supplierNotLinked = $ctx['not_linked'];
        $canAddProduct = $canManage || $isSupplierCatalog;

        if ($supplierNotLinked) {
            $products = [];
            $state = pagination_state(1, 0);
            $pagination = pagination_for_view('product_search', $state, $keyword !== '' ? ['keyword' => $keyword] : []);
            require_once APP_PATH . '/views/products/index.php';

            return;
        }

        $supplierFilter = $ctx['filter'];
        $reqPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $total = $this->productModel->countAll($keyword, $supplierFilter);
        $state = pagination_state($reqPage, $total);
        $products = $this->productModel->getPage($state['offset'], $state['per_page'], $keyword, $supplierFilter);
        $extra = $keyword !== '' ? ['keyword' => $keyword] : [];
        $pagination = pagination_for_view('product_search', $state, $extra);

        require_once APP_PATH . '/views/products/index.php';
    }

    public function lowStock(): void
    {
        $canManage = $this->isAdmin();
        $supplierAccountId = $this->catalogSupplierAccountId();
        $sidFilter = $supplierAccountId > 0 ? $supplierAccountId : null;
        if (($this->currentUser()['role'] ?? '') === 'fournisseur' && $supplierAccountId < 1) {
            $products = [];
        } else {
            $products = $this->productModel->getLowStockProducts($sidFilter);
        }

        require_once APP_PATH . '/views/products/low_stock.php';
    }
}

