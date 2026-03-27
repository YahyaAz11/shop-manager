<?php

declare(strict_types=1);

class DashboardController extends BaseController
{
    /** @var Product */
    private $productModel;
    /** @var Sale */
    private $saleModel;
    /** @var User */
    private $userModel;

    public function __construct()
    {
        $this->productModel = new Product();
        $this->saleModel = new Sale();
        $this->userModel = new User();
    }

    public function index(): void
    {
        $role = (string) ($this->currentUser()['role'] ?? '');
        if ($role === 'fournisseur') {
            $this->syncSessionSupplierFromDb();
        }
        $isAdmin = $role === 'admin';
        $fournisseurNeedsSupplierLink = false;
        if ($role === 'fournisseur') {
            $cu = $this->currentUser() ?? [];
            $sid = $cu['supplier_id'] ?? null;
            $fournisseurNeedsSupplierLink = $sid === null || $sid === '' || (int) $sid < 1;
        }

        $lowStockProducts = $this->productModel->getLowStockProducts();
        $productCount = $this->productModel->countAll(null);
        $lowStockCount = count($lowStockProducts);

        if ($isAdmin || $role === 'vendeur') {
            $salesCount = $this->saleModel->countAll();
            $revenue = $this->saleModel->getTotalRevenue();
        } else {
            $salesCount = 0;
            $revenue = ['revenue' => null];
        }

        if ($isAdmin) {
            $userCount = $this->userModel->countAll();
        } else {
            $userCount = 0;
        }

        $showSalesStats = $isAdmin || $role === 'vendeur';
        $showUserStat = $isAdmin;

        $showMonthlyRevenue = $isAdmin;
        $currentMonthRevenue = 0.0;
        /** @var list<array<string, mixed>> $monthlyRevenueRows */
        $monthlyRevenueRows = [];
        if ($isAdmin) {
            $currentMonthRevenue = $this->saleModel->getCurrentMonthRevenue();
            $monthlyRevenueRows = array_slice($this->saleModel->getMonthlySales(), 0, 12);
        }

        require_once APP_PATH . '/views/dashboard/index.php';
    }
}
