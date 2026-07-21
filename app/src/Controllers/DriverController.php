<?php

namespace App\Controllers;

use App\Middleware\RoleMiddleware;
use App\Services\SupabaseClient;

class DriverController
{
    private SupabaseClient $supabase;

    public function __construct()
    {
        RoleMiddleware::enforce(['driver', 'admin']);
        $this->supabase = new SupabaseClient();
    }

    public function dashboard(): void
    {
        $user = $_SESSION['user'];
        $orders = $this->supabase->select('gas_orders', [], 'created_at.desc');
        
        $myTasks = array_filter($orders, function($o) use ($user) {
            return ($o['assigned_driver'] === $user['id'] || $user['role'] === 'admin') && $o['payment_status'] !== 'completed';
        });

        $completedToday = array_filter($orders, function($o) use ($user) {
            return ($o['assigned_driver'] === $user['id'] || $user['role'] === 'admin') && $o['payment_status'] === 'completed';
        });

        require __DIR__ . '/../Views/driver/dashboard.php';
    }

    public function taskDetail(string $id): void
    {
        $user = $_SESSION['user'];
        $orders = $this->supabase->select('gas_orders', ['id' => "eq.{$id}"]);
        $order = $orders[0] ?? null;

        require __DIR__ . '/../Views/driver/task_detail.php';
    }

    public function markArrived(string $id): void
    {
        $this->supabase->update('gas_orders', $id, [
            'payment_status' => 'arrived'
        ]);
        header("Location: /driver/task/{$id}");
        exit;
    }

    public function showCod(string $id): void
    {
        $orders = $this->supabase->select('gas_orders', ['id' => "eq.{$id}"]);
        $order = $orders[0] ?? null;

        require __DIR__ . '/../Views/driver/cod_confirm.php';
    }

    public function completeCod(string $id): void
    {
        $amountReceived = (float)($_POST['amount_received'] ?? 0.0);
        $this->supabase->update('gas_orders', $id, [
            'payment_status' => 'completed',
            'dispatch_notes' => "COD Collected: R {$amountReceived}"
        ]);
        header("Location: /driver/task/{$id}/complete");
        exit;
    }

    public function completeEft(string $id): void
    {
        $this->supabase->update('gas_orders', $id, [
            'payment_status' => 'completed'
        ]);
        header("Location: /driver/task/{$id}/complete");
        exit;
    }

    public function showComplete(string $id): void
    {
        $orders = $this->supabase->select('gas_orders', ['id' => "eq.{$id}"]);
        $order = $orders[0] ?? null;

        require __DIR__ . '/../Views/driver/complete.php';
    }
}
