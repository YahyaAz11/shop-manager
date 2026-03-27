<?php

class Product
{
    private $conn;
    private $table = "products";

    /** @var bool|null */
    private static $stockAlertColumnExists;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    /** Seuil global si la colonne stock_alert_threshold n’existe pas encore en base. */
    private const LEGACY_LOW_STOCK_THRESHOLD = 5;

    private function hasStockAlertThresholdColumn(): bool
    {
        if (self::$stockAlertColumnExists !== null) {
            return self::$stockAlertColumnExists;
        }
        try {
            $stmt = $this->conn->query(
                "SHOW COLUMNS FROM {$this->table} LIKE " . $this->conn->quote('stock_alert_threshold')
            );
            self::$stockAlertColumnExists = $stmt !== false && $stmt->fetch(PDO::FETCH_ASSOC) !== false;
        } catch (Throwable $e) {
            self::$stockAlertColumnExists = false;
        }

        return self::$stockAlertColumnExists;
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

    public function countAll(?string $keyword = null): int
    {
        if ($keyword !== null && trim($keyword) !== '') {
            $pat = '%' . $keyword . '%';
            $sql = "SELECT COUNT(*) FROM {$this->table} p
                    LEFT JOIN categories c ON p.category_id = c.id
                    LEFT JOIN suppliers s ON p.supplier_id = s.id
                    WHERE p.name LIKE :k_name OR c.name LIKE :k_cat OR s.name LIKE :k_sup";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':k_name', $pat);
            $stmt->bindValue(':k_cat', $pat);
            $stmt->bindValue(':k_sup', $pat);
            $stmt->execute();

            return (int) $stmt->fetchColumn();
        }
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        $stmt = $this->conn->query($sql);

        return (int) $stmt->fetchColumn();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getPage(int $offset, int $limit, ?string $keyword = null): array
    {
        if ($keyword !== null && trim($keyword) !== '') {
            $pat = '%' . $keyword . '%';
            $sql = "SELECT p.*, c.name AS category_name, s.name AS supplier_name
                    FROM {$this->table} p
                    LEFT JOIN categories c ON p.category_id = c.id
                    LEFT JOIN suppliers s ON p.supplier_id = s.id
                    WHERE p.name LIKE :k_name OR c.name LIKE :k_cat OR s.name LIKE :k_sup
                    ORDER BY p.id DESC
                    LIMIT :lim OFFSET :off";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':k_name', $pat);
            $stmt->bindValue(':k_cat', $pat);
            $stmt->bindValue(':k_sup', $pat);
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll();
        }

        $sql = "SELECT p.*, c.name AS category_name, s.name AS supplier_name
                FROM {$this->table} p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN suppliers s ON p.supplier_id = s.id
                ORDER BY p.id DESC
                LIMIT :lim OFFSET :off";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function create($data)
    {
        $categoryId = $data['category_id'] === '' || $data['category_id'] === null ? null : $data['category_id'];
        $supplierId = $data['supplier_id'] === '' || $data['supplier_id'] === null ? null : $data['supplier_id'];
        $threshold = isset($data['stock_alert_threshold']) ? (int) $data['stock_alert_threshold'] : 5;
        if ($threshold < 0) {
            $threshold = 0;
        }

        if ($this->hasStockAlertThresholdColumn()) {
            $sql = "INSERT INTO {$this->table}
                    (name, description, price_buy, price_sell, stock, stock_alert_threshold, category_id, supplier_id)
                    VALUES
                    (:name, :description, :price_buy, :price_sell, :stock, :stock_alert_threshold, :category_id, :supplier_id)";
            $stmt = $this->conn->prepare($sql);

            return $stmt->execute([
                ':name'                   => $data['name'],
                ':description'            => $data['description'],
                ':price_buy'              => $data['price_buy'],
                ':price_sell'             => $data['price_sell'],
                ':stock'                  => $data['stock'],
                ':stock_alert_threshold'  => $threshold,
                ':category_id'            => $categoryId,
                ':supplier_id'            => $supplierId
            ]);
        }

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
            ':category_id' => $categoryId,
            ':supplier_id' => $supplierId
        ]);
    }

    public function update($id, $data)
    {
        $categoryId = $data['category_id'] === '' || $data['category_id'] === null ? null : $data['category_id'];
        $supplierId = $data['supplier_id'] === '' || $data['supplier_id'] === null ? null : $data['supplier_id'];
        $threshold = isset($data['stock_alert_threshold']) ? (int) $data['stock_alert_threshold'] : 5;
        if ($threshold < 0) {
            $threshold = 0;
        }

        if ($this->hasStockAlertThresholdColumn()) {
            $sql = "UPDATE {$this->table}
                    SET name = :name,
                        description = :description,
                        price_buy = :price_buy,
                        price_sell = :price_sell,
                        stock = :stock,
                        stock_alert_threshold = :stock_alert_threshold,
                        category_id = :category_id,
                        supplier_id = :supplier_id
                    WHERE id = :id";
            $stmt = $this->conn->prepare($sql);

            return $stmt->execute([
                ':name'                   => $data['name'],
                ':description'            => $data['description'],
                ':price_buy'              => $data['price_buy'],
                ':price_sell'             => $data['price_sell'],
                ':stock'                  => $data['stock'],
                ':stock_alert_threshold'  => $threshold,
                ':category_id'            => $categoryId,
                ':supplier_id'            => $supplierId,
                ':id'                     => $id
            ]);
        }

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
            ':category_id' => $categoryId,
            ':supplier_id' => $supplierId,
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
        $pat = '%' . $keyword . '%';
        $sql = "SELECT p.*, c.name AS category_name, s.name AS supplier_name
                FROM {$this->table} p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN suppliers s ON p.supplier_id = s.id
                WHERE p.name LIKE :k_name OR c.name LIKE :k_cat OR s.name LIKE :k_sup
                ORDER BY p.id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':k_name', $pat);
        $stmt->bindValue(':k_cat', $pat);
        $stmt->bindValue(':k_sup', $pat);
        $stmt->execute();

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

    public function increaseStock($id, $quantity)
    {
        $sql = "UPDATE {$this->table} SET stock = stock + :quantity WHERE id = :id";
        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':quantity' => $quantity,
            ':id'       => $id
        ]);
    }

    /**
     * Stock faible : comparé au seuil propre à chaque produit (colonne stock_alert_threshold).
     *
     * @return list<array<string, mixed>>
     */
    public function getLowStockProducts()
    {
        $hasCol = $this->hasStockAlertThresholdColumn();
        if ($hasCol) {
            $sql = "SELECT * FROM {$this->table} WHERE stock <= stock_alert_threshold ORDER BY stock ASC, id ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
        } else {
            $sql = "SELECT * FROM {$this->table} WHERE stock <= :legacy_thr ORDER BY stock ASC, id ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':legacy_thr', self::LEGACY_LOW_STOCK_THRESHOLD, PDO::PARAM_INT);
            $stmt->execute();
        }

        return $stmt->fetchAll();
    }
}