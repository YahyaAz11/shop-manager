<?php

require_once __DIR__ . '/../config/database.php';

class SaleItem
{
    private $conn;
    private $table = "sale_items";

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} (sale_id, product_id, quantity, price)
                VALUES (:sale_id, :product_id, :quantity, :price)";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':sale_id'    => $data['sale_id'],
            ':product_id' => $data['product_id'],
            ':quantity'   => $data['quantity'],
            ':price'      => $data['price']
        ]);
    }

    public function getBySaleId($saleId)
    {
        $sql = "SELECT si.*, p.name AS product_name
                FROM {$this->table} si
                LEFT JOIN products p ON si.product_id = p.id
                WHERE si.sale_id = :sale_id";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':sale_id' => $saleId
        ]);

        return $stmt->fetchAll();
    }

    public function deleteBySaleId($saleId)
    {
        $sql = "DELETE FROM {$this->table} WHERE sale_id = :sale_id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':sale_id' => $saleId
        ]);
    }
}