<?php

namespace App\Middleware;

class RoleMiddleware
{
    /**
     * Ensure current user has one of the allowed roles
     */
    public static function enforce(array $allowedRoles): void
    {
        $user = AuthMiddleware::check();

        if (!in_array($user['role'] ?? 'customer', $allowedRoles)) {
            // Redirect based on role without showing unauthorized errors
            $role = $user['role'] ?? 'customer';
            if ($role === 'admin') {
                header('Location: /admin');
            } elseif ($role === 'driver') {
                header('Location: /driver');
            } else {
                header('Location: /home');
            }
            exit;
        }
    }
}
