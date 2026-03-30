<?php

declare(strict_types=1);

class PurchaseOrder
{
    private $conn;
    private string $table = 'purchase_orders';

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function countAll(): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        $stmt = $this->conn->query($sql);

        return (int) $stmt->fetchColumn();
    }

    public function countForSupplier(int $supplierId): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table}
                WHERE supplier_id = :sid AND status <> 'brouillon'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':sid' => $supplierId]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getPageForSupplier(int $supplierId, int $offset, int $limit): array
    {
        $sql = "SELECT po.*, s.name AS supplier_name, u.name AS user_name
                FROM {$this->table} po
                INNER JOIN suppliers s ON po.supplier_id = s.id
                INNER JOIN users u ON po.user_id = u.id
                WHERE po.supplier_id = :sid AND po.status <> 'brouillon'
                ORDER BY po.id DESC
                LIMIT :lim OFFSET :off";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':sid', $supplierId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getPage(int $offset, int $limit): array
    {
        $sql = "SELECT po.*, s.name AS supplier_name, u.name AS user_name
                FROM {$this->table} po
                INNER JOIN suppliers s ON po.supplier_id = s.id
                INNER JOIN users u ON po.user_id = u.id
                ORDER BY po.id DESC
                LIMIT :lim OFFSET :off";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /** @param array{supplier_id: int, user_id: int, notes?: string, status?: string} $data */
    public function create(array $data): string
    {
        $status = $data['status'] ?? 'brouillon';
        $sql = "INSERT INTO {$this->table} (supplier_id, user_id, status, notes)
                VALUES (:supplier_id, :user_id, :status, :notes)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':supplier_id' => $data['supplier_id'],
            ':user_id'     => $data['user_id'],
            ':status'      => $status,
            ':notes'       => $data['notes'] ?? null,
        ]);

        return (string) $this->conn->lastInsertId();
    }

    /** @return array<string, mixed>|false */
    public function getById(int $id)
    {
        $sql = "SELECT po.*, s.name AS supplier_name, u.name AS user_name
                FROM {$this->table} po
                INNER JOIN suppliers s ON po.supplier_id = s.id
                INNER JOIN users u ON po.user_id = u.id
                WHERE po.id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateStatus(int $id, string $status): bool
    {
        $sql = "UPDATE {$this->table} SET status = :st WHERE id = :id";
        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([':st' => $status, ':id' => $id]);
    }

    /** Réponse fournisseur : accepte ou refuse (depuis statut envoye uniquement). */
    public function setSupplierResponse(int $id, bool $accept, ?string $note): bool
    {
        $status = $accept ? 'accepte' : 'refuse';
        $sql = "UPDATE {$this->table}
                SET status = :st,
                    supplier_reply_note = :note,
                    supplier_replied_at = NOW()
                WHERE id = :id AND status = 'envoye'";
        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':st'   => $status,
            ':note' => $note !== null && $note !== '' ? $note : null,
            ':id'   => $id,
        ]) && $stmt->rowCount() > 0;
    }

    public function markReceived(int $id): bool
    {
        $sql = "UPDATE {$this->table}
                SET status = 'recu', received_at = NOW()
                WHERE id = :id";
        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([':id' => $id]);
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([':id' => $id]);
    }
}
