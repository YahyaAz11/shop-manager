<?php

declare(strict_types=1);

class SaleController extends BaseController
{
    /** @var Sale */
    private $saleModel;
    /** @var SaleItem */
    private $saleItemModel;
    /** @var Product */
    private $productModel;

    public function __construct()
    {
        $this->saleModel = new Sale();
        $this->saleItemModel = new SaleItem();
        $this->productModel = new Product();
    }

    public function index(): void
    {
        $reqPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $total = $this->saleModel->countAll();
        $state = pagination_state($reqPage, $total);
        $sales = $this->saleModel->getPage($state['offset'], $state['per_page']);
        $pagination = pagination_for_view('sales', $state);

        require_once APP_PATH . '/views/sales/index.php';
    }

    public function create(): void
    {
        $products = $this->productModel->getAll();
        require_once APP_PATH . '/views/sales/create.php';
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('sale_create');
        }
        $this->requireCsrf();

        $userId = (int) ($this->currentUser()['id'] ?? 0);
        if ($userId < 1) {
            $this->redirect('login');
        }

        $productIds = $_POST['product_id'] ?? [];
        $quantities = $_POST['quantity'] ?? [];
        $prices = $_POST['price'] ?? [];

        $lines = [];
        $n = max(count($productIds), count($quantities), count($prices));
        for ($i = 0; $i < $n; $i++) {
            $pid = isset($productIds[$i]) ? trim((string) $productIds[$i]) : '';
            if ($pid === '') {
                continue;
            }
            $qty = isset($quantities[$i]) ? (int) $quantities[$i] : 0;
            $price = isset($prices[$i]) ? (float) $prices[$i] : 0;
            if ($qty < 1 || $price < 0) {
                continue;
            }
            $lines[] = ['product_id' => $pid, 'quantity' => $qty, 'price' => $price];
        }

        if ($lines === []) {
            $this->redirect('sale_create');
        }

        $total = 0.0;
        foreach ($lines as $line) {
            $total += $line['quantity'] * $line['price'];
        }
        $total = round($total, 2);

        $pe = round((float) ($_POST['payment_especes'] ?? 0), 2);
        $pc = round((float) ($_POST['payment_carte'] ?? 0), 2);
        $pa = round((float) ($_POST['payment_autre'] ?? 0), 2);
        if ($pe < 0 || $pc < 0 || $pa < 0) {
            $_SESSION['flash_error'] = 'Les montants de paiement ne peuvent pas être négatifs.';
            $this->redirect('sale_create');
        }
        $paySum = round($pe + $pc + $pa, 2);
        if ($paySum < 0.01 && $total > 0) {
            $pe = $total;
            $paySum = $total;
        }
        if (abs($paySum - $total) > 0.02) {
            $_SESSION['flash_error'] = 'La somme des paiements doit égaler le total de la vente (' . format_mad($total) . ').';
            $this->redirect('sale_create');
        }

        $saleId = $this->saleModel->create([
            'user_id'         => $userId,
            'total'           => $total,
            'payment_especes' => $pe,
            'payment_carte'   => $pc,
            'payment_autre'   => $pa,
        ]);

        foreach ($lines as $line) {
            $this->saleItemModel->create([
                'sale_id'    => $saleId,
                'product_id' => $line['product_id'],
                'quantity'   => $line['quantity'],
                'price'      => $line['price'],
            ]);
            $this->productModel->decreaseStock($line['product_id'], $line['quantity']);
        }

        $_SESSION['flash_success'] = 'Vente enregistrée.';
        $this->redirect('sales');
    }

    public function show(int $id): void
    {
        $sale = $this->saleModel->getById($id);
        $items = $this->saleItemModel->getBySaleId($id);
        require_once APP_PATH . '/views/sales/show.php';
    }

    public function ticket(int $id): void
    {
        $sale = $this->saleModel->getById($id);
        $items = $this->saleItemModel->getBySaleId($id);
        $seller = null;
        if ($sale && !empty($sale['user_id'])) {
            $u = new User();
            $seller = $u->getById((int) $sale['user_id']);
        }
        require_once APP_PATH . '/views/sales/ticket.php';
    }

    public function mySales(): void
    {
        $userId = (int) ($this->currentUser()['id'] ?? 0);
        $reqPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $total = $this->saleModel->countByUser($userId);
        $state = pagination_state($reqPage, $total);
        $sales = $this->saleModel->getPageByUser($userId, $state['offset'], $state['per_page']);
        $pagination = pagination_for_view('my_sales', $state);

        require_once APP_PATH . '/views/sales/my_sales.php';
    }
}
