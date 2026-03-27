<?php

declare(strict_types=1);

class PurchaseOrderItem
{
    private $conn;
    private string $table = 'purchase_order_items';

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    /** @param array{purchase_order_id: int, product_id: int, quantity: int, unit_cost: float|string} $data */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO {$this->table} (purchase_order_id, product_id, quantity, unit_cost)
                VALUES (:purchase_order_id, :product_id, :quantity, :unit_cost)";
        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':purchase_order_id' => $data['purchase_order_id'],
            ':product_id'        => $data['product_id'],
            ':quantity'          => $data['quantity'],
            ':unit_cost'         => $data['unit_cost'],
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getByPurchaseOrderId(int $purchaseOrderId): array
    {
        $sql = "SELECT poi.*, p.name AS product_name
                FROM {$this->table} poi
                INNER JOIN products p ON poi.product_id = p.id
                WHERE poi.purchase_order_id = :pid
                ORDER BY poi.id ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':pid' => $purchaseOrderId]);

        return $stmt->fetchAll();
    }
}
