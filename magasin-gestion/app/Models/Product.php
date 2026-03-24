<?php

require_once __DIR__ . '/../config/database.php';

class Product
{
    private $conn;
    private $table = "products";

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function getAll()
    {
        $sql = "SELECT p.*, c.name AS category_name, s.name AS supplier_name
                FROM {$this->table} p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN suppliers s ON p.supplier_id = s.id
                ORDER BY p.id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function create($data)
    {
        $sql = "INSERT INTO {$this->table}
                (name, description, price_buy, price_sell, stock, category_id, supplier_id)
                VALUES
                (:name, :description, :price_buy, :price_sell, :stock, :category_id, :supplier_id)";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':name'        => $data['name'],
            ':description' => $data['description'],
            ':price_buy'   => $data['price_buy'],
            ':price_sell'  => $data['price_sell'],
            ':stock'       => $data['stock'],
            ':category_id' => $data['category_id'],
            ':supplier_id' => $data['supplier_id']
        ]);
    }

    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table}
                SET name = :name,
                    description = :description,
                    price_buy = :price_buy,
                    price_sell = :price_sell,
                    stock = :stock,
                    category_id = :category_id,
                    supplier_id = :supplier_id
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':name'        => $data['name'],
            ':description' => $data['description'],
            ':price_buy'   => $data['price_buy'],
            ':price_sell'  => $data['price_sell'],
            ':stock'       => $data['stock'],
            ':category_id' => $data['category_id'],
            ':supplier_id' => $data['supplier_id'],
            ':id'          => $id
        ]);
    }

    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function search($keyword)
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE name LIKE :keyword OR description LIKE :keyword
                ORDER BY id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':keyword' => '%' . $keyword . '%'
        ]);

        return $stmt->fetchAll();
    }

    public function filterByCategory($categoryId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE category_id = :category_id ORDER BY id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':category_id' => $categoryId
        ]);
        return $stmt->fetchAll();
    }

    public function filterBySupplier($supplierId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE supplier_id = :supplier_id ORDER BY id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':supplier_id' => $supplierId
        ]);
        return $stmt->fetchAll();
    }

    public function updateStock($id, $newStock)
    {
        $sql = "UPDATE {$this->table} SET stock = :stock WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':stock' => $newStock,
            ':id'    => $id
        ]);
    }

    public function decreaseStock($id, $quantity)
    {
        $sql = "UPDATE {$this->table}
                SET stock = stock - :quantity
                WHERE id = :id AND stock >= :quantity";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':quantity' => $quantity,
            ':id'       => $id
        ]);
    }

    public function getLowStockProducts($threshold = 5)
    {
        $sql = "SELECT * FROM {$this->table} WHERE stock <= :threshold ORDER BY stock ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':threshold', $threshold, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}