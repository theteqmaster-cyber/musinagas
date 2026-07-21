<?php

namespace App\Controllers;

use App\Middleware\RoleMiddleware;
use App\Services\SupabaseClient;

class PricingController
{
    private SupabaseClient $supabase;

    public function __construct()
    {
        RoleMiddleware::enforce(['admin']);
        $this->supabase = new SupabaseClient();
    }

    public function showPricing(): void
    {
        $currentPrice = $this->supabase->getCurrentPrice();
        $priceHistory = $this->supabase->select('price_config', [], 'effective_from.desc', 20);
        $zones = $this->supabase->select('delivery_zones');
        $config = require __DIR__ . '/../../config/config.php';

        require __DIR__ . '/../Views/admin/pricing.php';
    }

    public function updatePrice(): void
    {
        $newPrice = (float)($_POST['price_per_kg'] ?? 0.0);
        $notes = trim($_POST['notes'] ?? 'Updated via Pricing Panel');

        if ($newPrice >= 1.0) {
            $user = $_SESSION['user'];
            $this->supabase->updatePrice($newPrice, $user['id'], $notes);
        }

        header('Location: /admin/pricing');
        exit;
    }
}
