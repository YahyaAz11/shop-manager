<?php

require_once __DIR__ . '/../models/Supplier.php';

class SupplierController
{
    private $supplierModel;

    public function __construct()
    {
        $this->supplierModel = new Supplier();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function index()
    {
        $suppliers = $this->supplierModel->getAll();
        require_once __DIR__ . '/../views/suppliers/index.php';
    }

    public function create()
    {
        require_once __DIR__ . '/../views/suppliers/create.php';
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name'    => trim($_POST['name']),
                'contact' => trim($_POST['contact']),
                'phone'   => trim($_POST['phone']),
                'email'   => trim($_POST['email']),
                'address' => trim($_POST['address'])
            ];

            $this->supplierModel->create($data);

            header('Location: index.php?action=suppliers');
            exit;
        }
    }

    public function edit($id)
    {
        $supplier = $this->supplierModel->getById($id);
        require_once __DIR__ . '/../views/suppliers/edit.php';
    }

    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name'    => trim($_POST['name']),
                'contact' => trim($_POST['contact']),
                'phone'   => trim($_POST['phone']),
                'email'   => trim($_POST['email']),
                'address' => trim($_POST['address'])
            ];

            $this->supplierModel->update($id, $data);

            header('Location: index.php?action=suppliers');
            exit;
        }
    }

    public function delete($id)
    {
        $this->supplierModel->delete($id);
        header('Location: index.php?action=suppliers');
        exit;
    }
}