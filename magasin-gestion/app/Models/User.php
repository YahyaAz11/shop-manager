<?php

require_once __DIR__ . '/../config/database.php';

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
        $sql = "SELECT id, name, email, role, created_at FROM {$this->table} ORDER BY id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById($id)
    {
        $sql = "SELECT id, name, email, role, created_at FROM {$this->table} WHERE id = :id";
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
        $sql = "INSERT INTO {$this->table} (name, email, password, role, created_at)
                VALUES (:name, :email, :password, :role, NOW())";

        $stmt = $this->conn->prepare($sql);

        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        return $stmt->execute([
            ':name'     => $data['name'],
            ':email'    => $data['email'],
            ':password' => $hashedPassword,
            ':role'     => $data['role']
        ]);
    }

    public function update($id, $data)
    {
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