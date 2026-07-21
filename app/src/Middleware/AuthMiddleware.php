<?php

namespace App\Middleware;

class AuthMiddleware
{
    /**
     * Check if user is logged in
     */
    public static function check(): ?array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        return $_SESSION['user'];
    }
}
