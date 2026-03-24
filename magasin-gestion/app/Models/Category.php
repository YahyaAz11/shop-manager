<?php

require_once __DIR__ . '/../config/database.php';

class Category
{
    private $conn;
    private $table = "categories";

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function getAll()
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY id DESC";
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
        $sql = "INSERT INTO {$this->table} (name) VALUES (:name)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':name' => $data['name']
        ]);
    }

    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} SET name = :name WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':name' => $data['name'],
            ':id'   => $id
        ]);
    }

    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}