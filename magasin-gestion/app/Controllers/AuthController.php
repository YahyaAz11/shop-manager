<?php

require_once __DIR__ . '/../models/User.php';

class AuthController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function showLogin()
    {
        require_once __DIR__ . '/../views/auth/login.php';
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);

            $user = $this->userModel->login($email, $password);

            if ($user) {
                $_SESSION['user'] = [
                    'id'    => $user['id'],
                    'name'  => $user['name'],
                    'email' => $user['email'],
                    'role'  => $user['role']
                ];

                header('Location: index.php?action=dashboard');
                exit;
            } else {
                $error = "Email ou mot de passe incorrect.";
                require_once __DIR__ . '/../views/auth/login.php';
            }
        }
    }

    public function logout()
    {
        session_destroy();
        header('Location: index.php?action=login');
        exit;
    }
}