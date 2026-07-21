<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Services\SupabaseClient;

class LocationController
{
    private SupabaseClient $supabase;

    public function __construct()
    {
        $this->supabase = new SupabaseClient();
    }

    public function listAddresses(): void
    {
        $user = AuthMiddleware::check();
        $addresses = $this->supabase->select('delivery_locations', ['customer_id' => "eq.{$user['id']}"]);
        $zones = $this->supabase->select('delivery_zones', ['is_active' => 'eq.true']);

        require __DIR__ . '/../Views/customer/addresses.php';
    }

    public function addAddress(): void
    {
        $user = AuthMiddleware::check();
        $label = trim($_POST['label'] ?? 'Home');
        $lat = (float)($_POST['latitude'] ?? -22.3562);
        $lng = (float)($_POST['longitude'] ?? 30.0416);
        $digitalAddress = trim($_POST['digital_address'] ?? '');
        $accessNotes = trim($_POST['access_notes'] ?? '');
        $zoneId = $_POST['zone_id'] ?? 'zone-a';

        $this->supabase->insert('delivery_locations', [
            'customer_id' => $user['id'],
            'label' => $label,
            'latitude' => $lat,
            'longitude' => $lng,
            'digital_address' => $digitalAddress,
            'access_notes' => $accessNotes,
            'zone_id' => $zoneId,
            'is_default' => false
        ]);

        header('Location: /addresses');
        exit;
    }
}
