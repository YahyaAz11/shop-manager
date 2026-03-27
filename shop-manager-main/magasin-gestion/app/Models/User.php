<?php

class User
{
    private $conn;
    private $table = "users";

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function getAll()
    {
        $sql = "SELECT u.id, u.name, u.email, u.role, u.supplier_id, u.created_at, s.name AS supplier_name
                FROM {$this->table} u
                LEFT JOIN suppliers s ON u.supplier_id = s.id
                ORDER BY u.id DESC";
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
        $sql = "SELECT u.id, u.name, u.email, u.role, u.supplier_id, u.created_at, s.name AS supplier_name
                FROM {$this->table} u
                LEFT JOIN suppliers s ON u.supplier_id = s.id
                ORDER BY u.id DESC
                LIMIT :lim OFFSET :off";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function countByRole(string $role): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE role = :r";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':r' => $role]);

        return (int) $stmt->fetchColumn();
    }

    public function getById($id)
    {
        $sql = "SELECT u.id, u.name, u.email, u.role, u.supplier_id, u.created_at, s.name AS supplier_name
                FROM {$this->table} u
                LEFT JOIN suppliers s ON u.supplier_id = s.id
                WHERE u.id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getByEmail($email)
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} (name, email, password, role, supplier_id, created_at)
                VALUES (:name, :email, :password, :role, :supplier_id, NOW())";

        $stmt = $this->conn->prepare($sql);

        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        $supplierId = isset($data['supplier_id']) && $data['supplier_id'] !== '' && $data['supplier_id'] !== null
            ? (int) $data['supplier_id'] : null;
        if ($supplierId !== null && $supplierId < 1) {
            $supplierId = null;
        }

        return $stmt->execute([
            ':name'        => $data['name'],
            ':email'       => $data['email'],
            ':password'    => $hashedPassword,
            ':role'        => $data['role'],
            ':supplier_id' => $supplierId,
        ]);
    }

    public function update($id, $data)
    {
        $hasSupplier = array_key_exists('supplier_id', $data);
        $supplierId = null;
        if ($hasSupplier) {
            $supplierId = $data['supplier_id'] === '' || $data['supplier_id'] === null
                ? null
                : (int) $data['supplier_id'];
            if ($supplierId !== null && $supplierId < 1) {
                $supplierId = null;
            }
        }

        if ($hasSupplier) {
            $sql = "UPDATE {$this->table}
                    SET name = :name, email = :email, role = :role, supplier_id = :supplier_id
                    WHERE id = :id";
            $stmt = $this->conn->prepare($sql);

            return $stmt->execute([
                ':name'        => $data['name'],
                ':email'       => $data['email'],
                ':role'        => $data['role'],
                ':supplier_id' => $supplierId,
                ':id'          => $id
            ]);
        }

        $sql = "UPDATE {$this->table}
                SET name = :name, email = :email, role = :role
                WHERE id = :id";
        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':name'  => $data['name'],
            ':email' => $data['email'],
            ':role'  => $data['role'],
            ':id'    => $id
        ]);
    }

    public function updatePassword($id, $password)
    {
        $sql = "UPDATE {$this->table} SET password = :password WHERE id = :id";
        $stmt = $this->conn->prepare($sql);

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        return $stmt->execute([
            ':password' => $hashedPassword,
            ':id'       => $id
        ]);
    }

    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function login($email, $password)
    {
        $user = $this->getByEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }

        return false;
    }
}