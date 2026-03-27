<?php

declare(strict_types=1);

class AuthController extends BaseController
{
    /** @var User */
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function showLogin(): void
    {
        if ($this->currentUser() !== null) {
            $this->redirect('dashboard');
        }
        require_once APP_PATH . '/views/auth/login.php';
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('login');
        }
        if (!csrf_verify()) {
            $error = 'Session expirée. Rechargez la page et réessayez.';
            require_once APP_PATH . '/views/auth/login.php';
            return;
        }

        $email = trim((string) ($_POST['email'] ?? ''));
        $password = trim((string) ($_POST['password'] ?? ''));

        $user = $this->userModel->login($email, $password);

        if ($user) {
            session_regenerate_id(true);
            $sid = $user['supplier_id'] ?? null;
            $_SESSION['user'] = [
                'id'          => $user['id'],
                'name'        => $user['name'],
                'email'       => $user['email'],
                'role'        => $user['role'],
                'supplier_id' => $sid !== null && $sid !== '' ? (int) $sid : null,
            ];
            $this->redirect('dashboard');
        }

        $error = 'Email ou mot de passe incorrect.';
        require_once APP_PATH . '/views/auth/login.php';
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], (bool) $p['secure'], (bool) $p['httponly']);
        }
        session_destroy();
        header('Location: index.php?action=login');
        exit;
    }
}
