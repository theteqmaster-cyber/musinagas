<?php

namespace App\Controllers;

use App\Middleware\RoleMiddleware;
use App\Services\SupabaseClient;

class AdminController
{
    private SupabaseClient $supabase;

    public function __construct()
    {
        RoleMiddleware::enforce(['admin']);
        $this->supabase = new SupabaseClient();
    }

    public function dashboard(): void
    {
        $orders = $this->supabase->select('gas_orders', [], 'created_at.desc');
        $price = $this->supabase->getCurrentPrice();

        $stats = [
            'total_orders' => count($orders),
            'pending_eft' => 0,
            'eft_submitted' => 0,
            'total_revenue' => 0.0
        ];

        foreach ($orders as $ord) {
            if ($ord['payment_status'] === 'awaiting_eft') $stats['pending_eft']++;
            if ($ord['payment_status'] === 'eft_submitted') $stats['eft_submitted']++;
            if (in_array($ord['payment_status'], ['completed', 'verified', 'dispatched'])) {
                $stats['total_revenue'] += (float)$ord['total_amount'];
            }
        }

        require __DIR__ . '/../Views/admin/dashboard.php';
    }

    public function orders(): void
    {
        $orders = $this->supabase->select('gas_orders', [], 'created_at.desc');
        $profiles = $this->supabase->select('profiles');
        $drivers = array_filter($profiles, fn($p) => ($p['role'] ?? '') === 'driver');

        require __DIR__ . '/../Views/admin/orders.php';
    }

    public function assignDriver(string $id): void
    {
        $driverId = $_POST['driver_id'] ?? null;
        if ($driverId) {
            $this->supabase->update('gas_orders', $id, [
                'assigned_driver' => $driverId,
                'payment_status' => 'dispatched'
            ]);
        }
        header('Location: /admin/orders');
        exit;
    }

    public function eftAuditor(): void
    {
        $orders = $this->supabase->select('gas_orders', [], 'created_at.desc');
        $eftOrders = array_filter($orders, fn($o) => in_array($o['payment_status'], ['eft_submitted', 'awaiting_eft', 'eft_rejected']));

        require __DIR__ . '/../Views/admin/eft.php';
    }

    public function approveEft(string $id): void
    {
        $this->supabase->update('gas_orders', $id, [
            'payment_status' => 'verified'
        ]);
        header('Location: /admin/eft');
        exit;
    }

    public function rejectEft(string $id): void
    {
        $this->supabase->update('gas_orders', $id, [
            'payment_status' => 'eft_rejected'
        ]);
        header('Location: /admin/eft');
        exit;
    }

    public function zones(): void
    {
        $zones = $this->supabase->select('delivery_zones');
        require __DIR__ . '/../Views/admin/zones.php';
    }

    public function updateZone(string $id): void
    {
        $fee = (float)($_POST['delivery_fee'] ?? 30);
        $name = trim($_POST['zone_name'] ?? '');
        $this->supabase->update('delivery_zones', $id, [
            'delivery_fee' => $fee,
            'zone_name' => $name
        ]);
        header('Location: /admin/zones');
        exit;
    }

    public function inventory(): void
    {
        $inventory = $this->supabase->select('inventory');
        require __DIR__ . '/../Views/admin/inventory.php';
    }

    public function updateStock(string $id): void
    {
        $count = (int)($_POST['stock_count'] ?? 0);
        $this->supabase->update('inventory', $id, [
            'stock_count' => $count,
            'last_inspected' => date('Y-m-d')
        ]);
        header('Location: /admin/inventory');
        exit;
    }

    public function users(): void
    {
        $users = $this->supabase->select('profiles');
        require __DIR__ . '/../Views/admin/users.php';
    }

    public function updateRole(string $id): void
    {
        $role = $_POST['role'] ?? 'customer';
        if (in_array($role, ['customer', 'driver', 'admin'])) {
            $this->supabase->update('profiles', $id, ['role' => $role]);
        }
        header('Location: /admin/users');
        exit;
    }
}
