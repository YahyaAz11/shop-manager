<?php

class SaleItem
{
    private $conn;
    private $table = "sale_items";

    /** @var bool|null */
    private static $vatRateColumnExists;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
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

    public function create($data)
    {
        $vat = normalize_vat_rate_percent($data['vat_rate'] ?? 20);
        if ($this->hasVatRateColumn()) {
            $sql = "INSERT INTO {$this->table} (sale_id, product_id, quantity, price, vat_rate)
                    VALUES (:sale_id, :product_id, :quantity, :price, :vat_rate)";
            $stmt = $this->conn->prepare($sql);

            return $stmt->execute([
                ':sale_id'    => $data['sale_id'],
                ':product_id' => $data['product_id'],
                ':quantity'   => $data['quantity'],
                ':price'      => $data['price'],
                ':vat_rate'   => $vat,
            ]);
        }

        $sql = "INSERT INTO {$this->table} (sale_id, product_id, quantity, price)
                VALUES (:sale_id, :product_id, :quantity, :price)";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':sale_id'    => $data['sale_id'],
            ':product_id' => $data['product_id'],
            ':quantity'   => $data['quantity'],
            ':price'      => $data['price'],
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

    /**
     * Lignes de plusieurs ventes (liste admin), indexées par sale_id.
     *
     * @param list<int> $saleIds
     * @return array<int, list<array<string, mixed>>>
     */
    public function getGroupedBySaleIds(array $saleIds): array
    {
        $saleIds = array_values(array_filter(array_map('intval', $saleIds), static fn (int $id): bool => $id > 0));
        if ($saleIds === []) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($saleIds), '?'));
        $vatCol = $this->hasVatRateColumn() ? ', si.vat_rate' : '';
        $sql = "SELECT si.sale_id, si.product_id, si.quantity, si.price{$vatCol}, p.name AS product_name
                FROM {$this->table} si
                LEFT JOIN products p ON si.product_id = p.id
                WHERE si.sale_id IN ($placeholders)
                ORDER BY si.sale_id ASC, si.id ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($saleIds);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $bySale = [];
        foreach ($rows as $row) {
            $sid = (int) ($row['sale_id'] ?? 0);
            if ($sid < 1) {
                continue;
            }
            if (!isset($bySale[$sid])) {
                $bySale[$sid] = [];
            }
            $bySale[$sid][] = $row;
        }

        return $bySale;
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