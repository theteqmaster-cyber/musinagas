<?php

namespace App\Controllers;

use App\Services\SupabaseClient;

class AuthController
{
    private SupabaseClient $supabase;

    public function __construct()
    {
        $this->supabase = new SupabaseClient();
    }

    public function showLogin(): void
    {
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['error']);
        require __DIR__ . '/../Views/customer/login.php';
    }

    public function processLogin(): void
    {
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($email) || empty($password)) {
            $_SESSION['error'] = 'Please fill in both email and password.';
            header('Location: /login');
            exit;
        }

        $res = $this->supabase->signIn($email, $password);
        if ($res['success']) {
            $_SESSION['user'] = $res['user'];
            $role = $res['user']['role'] ?? 'customer';

            if ($role === 'admin') {
                header('Location: /admin');
            } elseif ($role === 'driver') {
                header('Location: /driver');
            } else {
                header('Location: /home');
            }
            exit;
        } else {
            $_SESSION['error'] = $res['error'] ?? 'Login failed.';
            header('Location: /login');
            exit;
        }
    }

    public function showRegister(): void
    {
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['error']);
        require __DIR__ . '/../Views/customer/register.php';
    }

    public function processRegister(): void
    {
        $fullName = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $accountType = trim($_POST['account_type'] ?? 'residential');

        if (empty($fullName) || empty($phone) || empty($email) || strlen($password) < 8) {
            $_SESSION['error'] = 'Please fill in all fields correctly (Password min 8 chars).';
            header('Location: /register');
            exit;
        }

        $res = $this->supabase->signUp($fullName, $phone, $email, $password, $accountType);
        if ($res['success']) {
            $_SESSION['user'] = $res['user'];
            header('Location: /home');
            exit;
        } else {
            $_SESSION['error'] = $res['error'] ?? 'Registration failed.';
            header('Location: /register');
            exit;
        }
    }

    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        session_destroy();
        header('Location: /login');
        exit;
    }
}
