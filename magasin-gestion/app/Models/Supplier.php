<?php

class Supplier
{
    private $conn;
    private $table = "suppliers";

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
        $sql = "INSERT INTO {$this->table} (name, contact, phone, email, address)
                VALUES (:name, :contact, :phone, :email, :address)";
        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':name'    => $data['name'],
            ':contact' => $data['contact'],
            ':phone'   => $data['phone'],
            ':email'   => $data['email'],
            ':address' => $data['address']
        ]);
    }

    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table}
                SET name = :name, contact = :contact, phone = :phone, email = :email, address = :address
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':name'    => $data['name'],
            ':contact' => $data['contact'],
            ':phone'   => $data['phone'],
            ':email'   => $data['email'],
            ':address' => $data['address'],
            ':id'      => $id
        ]);
    }

    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}