<?php

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

    public function countAll(): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        $stmt = $this->conn->query($sql);

        return (int) $stmt->fetchColumn();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getPage(int $offset, int $limit): array
    {
        $sql = "SELECT s.*, u.name AS seller_name
                FROM {$this->table} s
                LEFT JOIN users u ON s.user_id = u.id
                ORDER BY s.id DESC
                LIMIT :lim OFFSET :off";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
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

    /** Suppression (cascade sur sale_items si FK définie ainsi). */
    public function deleteById(int $id): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = :id");

        return $stmt->execute([':id' => $id]);
    }

    public function create($data)
    {
        $sql = "INSERT INTO {$this->table}
                (user_id, sale_date, total, payment_especes, payment_carte, payment_autre)
                VALUES (:user_id, NOW(), :total, :pe, :pc, :pa)";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':user_id' => $data['user_id'],
            ':total'   => $data['total'],
            ':pe'      => $data['payment_especes'] ?? 0,
            ':pc'      => $data['payment_carte'] ?? 0,
            ':pa'      => $data['payment_autre'] ?? 0,
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

    public function countByUser(int $userId): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':user_id' => $userId]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getPageByUser(int $userId, int $offset, int $limit): array
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE user_id = :user_id
                ORDER BY id DESC
                LIMIT :lim OFFSET :off";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();

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

    /** CA du mois calendaire en cours (dirhams). */
    public function getCurrentMonthRevenue(): float
    {
        $sql = "SELECT COALESCE(SUM(total), 0) AS revenue
                FROM {$this->table}
                WHERE YEAR(sale_date) = YEAR(CURDATE())
                  AND MONTH(sale_date) = MONTH(CURDATE())";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return isset($row['revenue']) ? (float) $row['revenue'] : 0.0;
    }

    public function getTotalRevenue()
    {
        $sql = "SELECT SUM(total) AS revenue FROM {$this->table}";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }
}