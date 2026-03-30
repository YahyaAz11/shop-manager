<?php

declare(strict_types=1);

class PurchaseOrderController extends BaseController
{
    /** @var PurchaseOrder */
    private $poModel;
    /** @var PurchaseOrderItem */
    private $itemModel;
    /** @var Product */
    private $productModel;

    public function __construct()
    {
        $this->poModel = new PurchaseOrder();
        $this->itemModel = new PurchaseOrderItem();
        $this->productModel = new Product();
    }

    private function gateAdmin(): void
    {
        if (!$this->isAdmin()) {
            $_SESSION['flash_error'] = 'Accès réservé aux administrateurs.';
            $this->redirect('dashboard');
        }
    }

    /** @return int identifiant fournisseur (table suppliers) lié au compte */
    private function supplierLinkedId(): int
    {
        $this->syncSessionSupplierFromDb();
        $u = $this->currentUser() ?? [];
        if (($u['role'] ?? '') !== 'fournisseur') {
            $_SESSION['flash_error'] = 'Accès réservé aux comptes fournisseur.';
            $this->redirect('dashboard');
        }
        $sid = isset($u['supplier_id']) ? (int) $u['supplier_id'] : 0;
        if ($sid < 1) {
            $_SESSION['flash_error'] = 'Votre compte n’est pas rattaché à une fiche fournisseur. Un administrateur doit l’associer (menu Utilisateurs → modifier votre compte → champ « Entreprise fournisseur »).';
            $this->redirect('supplier_purchase_orders');
        }

        return $sid;
    }

    public function index(): void
    {
        $this->gateAdmin();
        $reqPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $total = $this->poModel->countAll();
        $state = pagination_state($reqPage, $total);
        $orders = $this->poModel->getPage($state['offset'], $state['per_page']);
        $pagination = pagination_for_view('purchase_orders', $state);

        require_once APP_PATH . '/views/purchase_orders/index.php';
    }

    public function create(): void
    {
        $this->gateAdmin();
        $products = $this->productModel->getAll();
        $supplierModel = new Supplier();
        $suppliers = $supplierModel->getAll();
        require_once APP_PATH . '/views/purchase_orders/create.php';
    }

    public function store(): void
    {
        $this->gateAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('purchase_order_create');
        }
        $this->requireCsrf();

        $userId = (int) ($this->currentUser()['id'] ?? 0);
        $supplierId = isset($_POST['supplier_id']) ? (int) $_POST['supplier_id'] : 0;
        $notes = trim((string) ($_POST['notes'] ?? ''));
        $status = trim((string) ($_POST['status'] ?? 'brouillon'));

        if ($supplierId < 1) {
            $_SESSION['flash_error'] = 'Choisissez un fournisseur.';
            $this->redirect('purchase_order_create');
        }
        if (!in_array($status, ['brouillon', 'envoye'], true)) {
            $status = 'brouillon';
        }

        $productIds = $_POST['product_id'] ?? [];
        $quantities = $_POST['quantity'] ?? [];
        $lines = [];
        $n = max(count($productIds), count($quantities));
        for ($i = 0; $i < $n; $i++) {
            $pid = isset($productIds[$i]) ? trim((string) $productIds[$i]) : '';
            if ($pid === '') {
                continue;
            }
            $qty = isset($quantities[$i]) ? (int) $quantities[$i] : 0;
            if ($qty < 1) {
                continue;
            }
            $product = $this->productModel->getById((int) $pid);
            if (!$product) {
                $_SESSION['flash_error'] = 'Produit introuvable.';
                $this->redirect('purchase_order_create');
            }
            if ((int) ($product['supplier_id'] ?? 0) !== $supplierId) {
                $_SESSION['flash_error'] = 'Le produit « ' . $product['name'] . ' » n’appartient pas au fournisseur choisi.';
                $this->redirect('purchase_order_create');
            }
            $lines[] = [
                'product_id' => (int) $pid,
                'quantity'   => $qty,
                'unit_cost'  => (float) $product['price_buy'],
            ];
        }
        if ($lines === []) {
            $_SESSION['flash_error'] = 'Ajoutez au moins une ligne produit.';
            $this->redirect('purchase_order_create');
        }

        $poId = (int) $this->poModel->create([
            'supplier_id' => $supplierId,
            'user_id'     => $userId,
            'notes'       => $notes !== '' ? $notes : null,
            'status'      => $status,
        ]);

        foreach ($lines as $line) {
            $this->itemModel->create([
                'purchase_order_id' => $poId,
                'product_id'        => $line['product_id'],
                'quantity'          => $line['quantity'],
                'unit_cost'         => $line['unit_cost'],
            ]);
        }

        $_SESSION['flash_success'] = 'Bon de commande enregistré.';
        $this->redirect('purchase_order_show', ['id' => $poId]);
    }

    public function show(int $id): void
    {
        $this->gateAdmin();
        $order = $this->poModel->getById($id);
        $items = $order ? $this->itemModel->getByPurchaseOrderId($id) : [];
        require_once APP_PATH . '/views/purchase_orders/show.php';
    }

    public function markSent(): void
    {
        $this->gateAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('purchase_orders');
        }
        $this->requireCsrf();
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        if ($id < 1) {
            $this->redirect('purchase_orders');
        }
        $order = $this->poModel->getById($id);
        if (!$order || $order['status'] !== 'brouillon') {
            $_SESSION['flash_error'] = 'Action impossible pour ce bon.';
            $this->redirect('purchase_order_show', ['id' => $id]);
        }
        $this->poModel->updateStatus($id, 'envoye');
        $_SESSION['flash_success'] = 'Bon marqué comme envoyé.';
        $this->redirect('purchase_order_show', ['id' => $id]);
    }

    public function receive(): void
    {
        $this->gateAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('purchase_orders');
        }
        $this->requireCsrf();
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        if ($id < 1) {
            $this->redirect('purchase_orders');
        }
        $order = $this->poModel->getById($id);
        if (!$order) {
            $_SESSION['flash_error'] = 'Bon introuvable.';
            $this->redirect('purchase_orders');
        }
        if (($order['status'] ?? '') !== 'accepte') {
            $_SESSION['flash_error'] = 'Réception possible uniquement après acceptation du fournisseur (statut « Accepté par le fournisseur »).';
            $this->redirect('purchase_order_show', ['id' => $id]);
        }

        $items = $this->itemModel->getByPurchaseOrderId($id);
        foreach ($items as $it) {
            $this->productModel->increaseStock((int) $it['product_id'], (int) $it['quantity']);
        }
        $this->poModel->markReceived($id);
        $_SESSION['flash_success'] = 'Réception enregistrée : stocks mis à jour.';
        $this->redirect('purchase_order_show', ['id' => $id]);
    }

    public function delete(): void
    {
        $this->gateAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('purchase_orders');
        }
        $this->requireCsrf();
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        if ($id < 1) {
            $this->redirect('purchase_orders');
        }
        $order = $this->poModel->getById($id);
        if (!$order) {
            $_SESSION['flash_error'] = 'Bon introuvable.';
            $this->redirect('purchase_orders');
        }
        if ($order['status'] !== 'brouillon') {
            $_SESSION['flash_error'] = 'Seuls les bons en brouillon peuvent être supprimés.';
            $this->redirect('purchase_order_show', ['id' => $id]);
        }
        $this->poModel->delete($id);
        $_SESSION['flash_success'] = 'Bon supprimé.';
        $this->redirect('purchase_orders');
    }

    public function supplierIndex(): void
    {
        $this->syncSessionSupplierFromDb();
        $u = $this->currentUser() ?? [];
        if (($u['role'] ?? '') !== 'fournisseur') {
            $_SESSION['flash_error'] = 'Accès réservé aux comptes fournisseur.';
            $this->redirect('dashboard');
        }
        $linkedId = isset($u['supplier_id']) ? (int) $u['supplier_id'] : 0;
        if ($linkedId < 1) {
            $supplierNotLinked = true;
            $orders = [];
            $pagination = pagination_for_view('supplier_purchase_orders', pagination_state(1, 0));
            require_once APP_PATH . '/views/purchase_orders/supplier_index.php';

            return;
        }

        $supplierNotLinked = false;
        $reqPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $total = $this->poModel->countForSupplier($linkedId);
        $state = pagination_state($reqPage, $total);
        $orders = $this->poModel->getPageForSupplier($linkedId, $state['offset'], $state['per_page']);
        $pagination = pagination_for_view('supplier_purchase_orders', $state);

        require_once APP_PATH . '/views/purchase_orders/supplier_index.php';
    }

    public function supplierShow(int $id): void
    {
        $linkedId = $this->supplierLinkedId();
        $order = $this->poModel->getById($id);
        if (!$order || (int) $order['supplier_id'] !== $linkedId) {
            $_SESSION['flash_error'] = 'Bon introuvable ou non destiné à votre entreprise.';
            $this->redirect('supplier_purchase_orders');
        }
        $items = $this->itemModel->getByPurchaseOrderId($id);
        require_once APP_PATH . '/views/purchase_orders/supplier_show.php';
    }

    public function supplierRespond(): void
    {
        $linkedId = $this->supplierLinkedId();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('supplier_purchase_orders');
        }
        $this->requireCsrf();

        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        if ($id < 1) {
            $this->redirect('supplier_purchase_orders');
        }
        $order = $this->poModel->getById($id);
        if (!$order || (int) $order['supplier_id'] !== $linkedId || $order['status'] !== 'envoye') {
            $_SESSION['flash_error'] = 'Vous ne pouvez pas répondre à ce bon.';
            $this->redirect('supplier_purchase_order_show', ['id' => $id]);
        }

        $decision = trim((string) ($_POST['decision'] ?? ''));
        $note = trim((string) ($_POST['supplier_note'] ?? ''));
        if (!in_array($decision, ['accept', 'refuse'], true)) {
            $_SESSION['flash_error'] = 'Choix invalide.';
            $this->redirect('supplier_purchase_order_show', ['id' => $id]);
        }
        if ($decision === 'refuse' && $note === '') {
            $_SESSION['flash_error'] = 'Indiquez un court motif si vous refusez la commande.';
            $this->redirect('supplier_purchase_order_show', ['id' => $id]);
        }

        $ok = $this->poModel->setSupplierResponse($id, $decision === 'accept', $note !== '' ? $note : null);
        if (!$ok) {
            $_SESSION['flash_error'] = 'Impossible d’enregistrer la réponse (le bon n’est peut‑être plus en attente).';
            $this->redirect('supplier_purchase_order_show', ['id' => $id]);
        }

        $_SESSION['flash_success'] = $decision === 'accept'
            ? 'Vous avez accepté de fournir cette commande.'
            : 'Vous avez indiqué ne pas pouvoir fournir cette commande.';
        $this->redirect('supplier_purchase_order_show', ['id' => $id]);
    }
}
