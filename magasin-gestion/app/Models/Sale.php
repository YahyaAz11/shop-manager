<?php

require_once __DIR__ . '/../config/database.php';

class Sale
{
    private $conn;
    private $table = "sales";

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function getAll()
    {
        $sql = "SELECT s.*, u.name AS seller_name
                FROM {$this->table} s
                LEFT JOIN users u ON s.user_id = u.id
                ORDER BY s.id DESC";

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
        $sql = "INSERT INTO {$this->table} (user_id, sale_date, total)
                VALUES (:user_id, NOW(), :total)";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':user_id' => $data['user_id'],
            ':total'   => $data['total']
        ]);

        return $this->conn->lastInsertId();
    }

    public function getSalesByUser($userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = :user_id ORDER BY id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId
        ]);
        return $stmt->fetchAll();
    }

    public function getDailySales()
    {
        $sql = "SELECT DATE(sale_date) AS day, SUM(total) AS total_sales
                FROM {$this->table}
                GROUP BY DATE(sale_date)
                ORDER BY day DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getMonthlySales()
    {
        $sql = "SELECT DATE_FORMAT(sale_date, '%Y-%m') AS month, SUM(total) AS total_sales
                FROM {$this->table}
                GROUP BY DATE_FORMAT(sale_date, '%Y-%m')
                ORDER BY month DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTotalRevenue()
    {
        $sql = "SELECT SUM(total) AS revenue FROM {$this->table}";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }
}