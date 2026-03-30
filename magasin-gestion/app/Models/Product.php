<?php

class Product
{
    private $conn;
    private $table = "products";

    /** @var bool|null */
    private static $stockAlertColumnExists;

    /** @var bool|null */
    private static $vatRateColumnExists;

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
            $t = str_replace('`', '``', $this->table);
            $stmt = $this->conn->query(
                "SHOW COLUMNS FROM `{$t}` LIKE " . $this->conn->quote('stock_alert_threshold')
            );
            self::$stockAlertColumnExists = $stmt !== false && $stmt->fetch(PDO::FETCH_ASSOC) !== false;
        } catch (\Throwable $e) {
            self::$stockAlertColumnExists = false;
        }

        return self::$stockAlertColumnExists;
    }

    private function hasVatRateColumn(): bool
    {
        if (self::$vatRateColumnExists !== null) {
            return self::$vatRateColumnExists;
        }
        try {
            $t = str_replace('`', '``', $this->table);
            $stmt = $this->conn->query(
                "SHOW COLUMNS FROM `{$t}` LIKE " . $this->conn->quote('vat_rate')
            );
            self::$vatRateColumnExists = $stmt !== false && $stmt->fetch(PDO::FETCH_ASSOC) !== false;
        } catch (\Throwable $e) {
            self::$vatRateColumnExists = false;
        }

        return self::$vatRateColumnExists;
    }

    /**
     * @param list<int> $ids
     * @return array<int, float> id => taux %
     */
    public function getVatRatesByProductIds(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), static function ($i): bool {
            return (int) $i > 0;
        })));
        if ($ids === [] || !$this->hasVatRateColumn()) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT id, vat_rate FROM {$this->table} WHERE id IN ($placeholders)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($ids);
        $map = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $map[(int) $row['id']] = normalize_vat_rate_percent($row['vat_rate'] ?? 20);
        }

        return $map;
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

    public function countAll(?string $keyword = null, ?int $supplierId = null): int
    {
        $sidFilter = $supplierId !== null && $supplierId > 0;

        if ($keyword !== null && trim($keyword) !== '') {
            $pat = '%' . trim($keyword) . '%';
            $sql = "SELECT COUNT(*) FROM {$this->table} p
                    LEFT JOIN categories c ON p.category_id = c.id
                    LEFT JOIN suppliers s ON p.supplier_id = s.id
                    WHERE (p.name LIKE :k_name OR c.name LIKE :k_cat OR s.name LIKE :k_sup)";
            if ($sidFilter) {
                $sql .= ' AND p.supplier_id = :sid';
            }
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':k_name', $pat);
            $stmt->bindValue(':k_cat', $pat);
            $stmt->bindValue(':k_sup', $pat);
            if ($sidFilter) {
                $stmt->bindValue(':sid', $supplierId, PDO::PARAM_INT);
            }
            $stmt->execute();

            return (int) $stmt->fetchColumn();
        }

        if ($sidFilter) {
            $sql = "SELECT COUNT(*) FROM {$this->table} WHERE supplier_id = :sid";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':sid', $supplierId, PDO::PARAM_INT);
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
    public function getPage(int $offset, int $limit, ?string $keyword = null, ?int $supplierId = null): array
    {
        $sidFilter = $supplierId !== null && $supplierId > 0;

        if ($keyword !== null && trim($keyword) !== '') {
            $pat = '%' . trim($keyword) . '%';
            $sql = "SELECT p.*, c.name AS category_name, s.name AS supplier_name
                    FROM {$this->table} p
                    LEFT JOIN categories c ON p.category_id = c.id
                    LEFT JOIN suppliers s ON p.supplier_id = s.id
                    WHERE (p.name LIKE :k_name OR c.name LIKE :k_cat OR s.name LIKE :k_sup)";
            if ($sidFilter) {
                $sql .= ' AND p.supplier_id = :sid';
            }
            $sql .= ' ORDER BY p.id DESC LIMIT :lim OFFSET :off';
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':k_name', $pat);
            $stmt->bindValue(':k_cat', $pat);
            $stmt->bindValue(':k_sup', $pat);
            if ($sidFilter) {
                $stmt->bindValue(':sid', $supplierId, PDO::PARAM_INT);
            }
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll();
        }

        $sql = "SELECT p.*, c.name AS category_name, s.name AS supplier_name
                FROM {$this->table} p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN suppliers s ON p.supplier_id = s.id";
        if ($sidFilter) {
            $sql .= ' WHERE p.supplier_id = :sid';
        }
        $sql .= ' ORDER BY p.id DESC LIMIT :lim OFFSET :off';
        $stmt = $this->conn->prepare($sql);
        if ($sidFilter) {
            $stmt->bindValue(':sid', $supplierId, PDO::PARAM_INT);
        }
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
        $vatRate = normalize_vat_rate_percent($data['vat_rate'] ?? 20);

        if ($this->hasStockAlertThresholdColumn() && $this->hasVatRateColumn()) {
            $sql = "INSERT INTO {$this->table}
                    (name, description, price_buy, price_sell, vat_rate, stock, stock_alert_threshold, category_id, supplier_id)
                    VALUES
                    (:name, :description, :price_buy, :price_sell, :vat_rate, :stock, :stock_alert_threshold, :category_id, :supplier_id)";
            $stmt = $this->conn->prepare($sql);

            return $stmt->execute([
                ':name'                   => $data['name'],
                ':description'            => $data['description'],
                ':price_buy'              => $data['price_buy'],
                ':price_sell'             => $data['price_sell'],
                ':vat_rate'               => $vatRate,
                ':stock'                  => $data['stock'],
                ':stock_alert_threshold'  => $threshold,
                ':category_id'            => $categoryId,
                ':supplier_id'            => $supplierId
            ]);
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
        $vatRate = normalize_vat_rate_percent($data['vat_rate'] ?? 20);

        if ($this->hasStockAlertThresholdColumn() && $this->hasVatRateColumn()) {
            $sql = "UPDATE {$this->table}
                    SET name = :name,
                        description = :description,
                        price_buy = :price_buy,
                        price_sell = :price_sell,
                        vat_rate = :vat_rate,
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
                ':vat_rate'               => $vatRate,
                ':stock'                  => $data['stock'],
                ':stock_alert_threshold'  => $threshold,
                ':category_id'            => $categoryId,
                ':supplier_id'            => $supplierId,
                ':id'                     => $id
            ]);
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
    /**
     * @return list<array<string, mixed>>
     */
    public function getLowStockProducts(?int $supplierId = null): array
    {
        $sidFilter = $supplierId !== null && $supplierId > 0;
        $hasCol = $this->hasStockAlertThresholdColumn();
        $supplierClause = $sidFilter ? ' AND supplier_id = :sid' : '';
        if ($hasCol) {
            $sql = "SELECT * FROM {$this->table}
                    WHERE stock <= stock_alert_threshold{$supplierClause}
                    ORDER BY stock ASC, id ASC";
            $stmt = $this->conn->prepare($sql);
            if ($sidFilter) {
                $stmt->bindValue(':sid', $supplierId, PDO::PARAM_INT);
            }
            $stmt->execute();
        } else {
            $sql = "SELECT * FROM {$this->table}
                    WHERE stock <= :legacy_thr{$supplierClause}
                    ORDER BY stock ASC, id ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':legacy_thr', self::LEGACY_LOW_STOCK_THRESHOLD, PDO::PARAM_INT);
            if ($sidFilter) {
                $stmt->bindValue(':sid', $supplierId, PDO::PARAM_INT);
            }
            $stmt->execute();
        }

        return $stmt->fetchAll();
    }
}