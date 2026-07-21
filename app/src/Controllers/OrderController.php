<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Services\SupabaseClient;
use App\Services\OrderRefService;

class OrderController
{
    private SupabaseClient $supabase;

    public function __construct()
    {
        $this->supabase = new SupabaseClient();
    }

    public function home(): void
    {
        $user = AuthMiddleware::check();
        $price = $this->supabase->getCurrentPrice();
        
        // Fetch active order if any
        $orders = $this->supabase->select('gas_orders', ['customer_id' => "eq.{$user['id']}"]);
        $activeOrder = null;
        foreach ($orders as $ord) {
            if (!in_array($ord['payment_status'], ['completed', 'cancelled'])) {
                $activeOrder = $ord;
                break;
            }
        }

        require __DIR__ . '/../Views/customer/home.php';
    }

    public function showStep1(): void
    {
        $user = AuthMiddleware::check();
        $price = $this->supabase->getCurrentPrice();
        $config = require __DIR__ . '/../../config/config.php';
        $cylinders = $config['cylinder_sizes'];

        require __DIR__ . '/../Views/customer/order_step1.php';
    }

    public function processStep1(): void
    {
        $user = AuthMiddleware::check();
        $cylinderSize = (float)($_POST['cylinder_size_kg'] ?? 9.0);
        $quantity = (int)($_POST['quantity'] ?? 1);

        if ($quantity < 1) $quantity = 1;
        $maxQty = ($user['account_type'] ?? 'residential') === 'commercial' ? 50 : 10;
        if ($quantity > $maxQty) $quantity = $maxQty;

        $_SESSION['order_draft'] = [
            'cylinder_size_kg' => $cylinderSize,
            'quantity' => $quantity,
        ];

        header('Location: /order/location');
        exit;
    }

    public function showStep2(): void
    {
        $user = AuthMiddleware::check();
        if (!isset($_SESSION['order_draft'])) {
            header('Location: /order/new');
            exit;
        }

        $zones = $this->supabase->select('delivery_zones', ['is_active' => 'eq.true']);
        $savedAddresses = $this->supabase->select('delivery_locations', ['customer_id' => "eq.{$user['id']}"]);

        require __DIR__ . '/../Views/customer/order_step2.php';
    }

    public function processStep2(): void
    {
        $user = AuthMiddleware::check();
        if (!isset($_SESSION['order_draft'])) {
            header('Location: /order/new');
            exit;
        }

        $addressId = $_POST['address_id'] ?? null;
        $lat = (float)($_POST['latitude'] ?? -22.3562);
        $lng = (float)($_POST['longitude'] ?? 30.0416);
        $digitalAddress = trim($_POST['digital_address'] ?? 'Musina Location');
        $accessNotes = trim($_POST['access_notes'] ?? '');
        $zoneId = $_POST['zone_id'] ?? 'zone-a';
        $saveAddress = isset($_POST['save_address']);

        if ($saveAddress) {
            $newLoc = $this->supabase->insert('delivery_locations', [
                'customer_id' => $user['id'],
                'label' => 'Saved Address',
                'latitude' => $lat,
                'longitude' => $lng,
                'digital_address' => $digitalAddress,
                'access_notes' => $accessNotes,
                'zone_id' => $zoneId,
                'is_default' => false
            ]);
            $addressId = $newLoc['id'] ?? 'loc-' . time();
        }

        // Look up delivery fee from zone
        $zones = $this->supabase->select('delivery_zones', ['id' => "eq.{$zoneId}"]);
        $deliveryFee = $zones[0]['delivery_fee'] ?? 30.00;

        $_SESSION['order_draft']['location_id'] = $addressId ?: 'loc-temp';
        $_SESSION['order_draft']['latitude'] = $lat;
        $_SESSION['order_draft']['longitude'] = $lng;
        $_SESSION['order_draft']['digital_address'] = $digitalAddress;
        $_SESSION['order_draft']['access_notes'] = $accessNotes;
        $_SESSION['order_draft']['zone_id'] = $zoneId;
        $_SESSION['order_draft']['delivery_fee'] = (float)$deliveryFee;

        header('Location: /order/checkout');
        exit;
    }

    public function showCheckout(): void
    {
        $user = AuthMiddleware::check();
        if (!isset($_SESSION['order_draft']['cylinder_size_kg'])) {
            header('Location: /order/new');
            exit;
        }

        $draft = $_SESSION['order_draft'];
        $price = $this->supabase->getCurrentPrice();
        $pricePerKg = (float)$price['price_per_kg'];
        $gasTotal = $pricePerKg * $draft['cylinder_size_kg'] * $draft['quantity'];
        $grandTotal = $gasTotal + $draft['delivery_fee'];

        $config = require __DIR__ . '/../../config/config.php';
        $codEnabled = $config['cod_settings']['enabled'] && ($grandTotal <= $config['cod_settings']['max_cap']);

        require __DIR__ . '/../Views/customer/checkout.php';
    }

    public function placeOrder(): void
    {
        $user = AuthMiddleware::check();
        if (!isset($_SESSION['order_draft']['cylinder_size_kg'])) {
            header('Location: /order/new');
            exit;
        }

        $paymentMethod = $_POST['payment_method'] ?? 'EFT';
        if (!in_array($paymentMethod, ['EFT', 'COD'])) {
            $paymentMethod = 'EFT';
        }

        $draft = $_SESSION['order_draft'];
        $price = $this->supabase->getCurrentPrice();
        $pricePerKg = (float)$price['price_per_kg'];
        $gasTotal = $pricePerKg * $draft['cylinder_size_kg'] * $draft['quantity'];
        $grandTotal = $gasTotal + $draft['delivery_fee'];

        $orderRef = OrderRefService::generate();
        $status = ($paymentMethod === 'EFT') ? 'awaiting_eft' : 'pending';

        $order = $this->supabase->insert('gas_orders', [
            'order_ref' => $orderRef,
            'customer_id' => $user['id'],
            'customer_name' => $user['full_name'] ?? 'Customer',
            'customer_phone' => $user['phone'] ?? '',
            'location_id' => $draft['location_id'],
            'digital_address' => $draft['digital_address'],
            'cylinder_size_kg' => $draft['cylinder_size_kg'],
            'quantity' => $draft['quantity'],
            'price_per_kg' => $pricePerKg,
            'delivery_fee' => $draft['delivery_fee'],
            'total_amount' => $grandTotal,
            'payment_method' => $paymentMethod,
            'payment_status' => $status,
            'created_at' => date('c'),
            'updated_at' => date('c')
        ]);

        unset($_SESSION['order_draft']);
        header("Location: /order/confirm/" . ($order['id'] ?? 'ord-101'));
        exit;
    }

    public function confirm(string $id): void
    {
        $user = AuthMiddleware::check();
        $orders = $this->supabase->select('gas_orders', ['id' => "eq.{$id}"]);
        $order = $orders[0] ?? null;

        require __DIR__ . '/../Views/customer/order_confirm.php';
    }

    public function track(string $id): void
    {
        $user = AuthMiddleware::check();
        $orders = $this->supabase->select('gas_orders', ['id' => "eq.{$id}"]);
        $order = $orders[0] ?? null;

        require __DIR__ . '/../Views/customer/order_track.php';
    }

    public function listOrders(): void
    {
        $user = AuthMiddleware::check();
        $orders = $this->supabase->select('gas_orders', ['customer_id' => "eq.{$user['id']}"], 'created_at.desc');

        require __DIR__ . '/../Views/customer/order_history.php';
    }

    public function showEftUpload(string $id): void
    {
        $user = AuthMiddleware::check();
        $orders = $this->supabase->select('gas_orders', ['id' => "eq.{$id}"]);
        $order = $orders[0] ?? null;
        $bankList = $this->supabase->select('bank_config', ['is_active' => 'eq.true']);
        $bank = $bankList[0] ?? ['bank_name' => 'FNB', 'account_name' => 'Musina Gas Pty Ltd', 'account_no' => '62891048291', 'branch_code' => '250655'];

        require __DIR__ . '/../Views/customer/eft_upload.php';
    }

    public function processEftUpload(string $id): void
    {
        $user = AuthMiddleware::check();

        if (isset($_FILES['proof_file']) && $_FILES['proof_file']['error'] === UPLOAD_ERR_OK) {
            $tmpPath = $_FILES['proof_file']['tmp_name'];
            $fileName = time() . '_' . basename($_FILES['proof_file']['name']);
            $targetDir = __DIR__ . '/../../public/uploads/';
            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

            $targetUrl = '/uploads/' . $fileName;
            move_uploaded_file($tmpPath, $targetDir . $fileName);

            $this->supabase->update('gas_orders', $id, [
                'eft_proof_url' => $targetUrl,
                'payment_status' => 'eft_submitted'
            ]);
        }

        header("Location: /order/track/{$id}");
        exit;
    }

    public function apiStatus(string $id): void
    {
        header('Content-Type: application/json');
        $orders = $this->supabase->select('gas_orders', ['id' => "eq.{$id}"]);
        if (!empty($orders)) {
            echo json_encode([
                'status' => $orders[0]['payment_status'],
                'updated_at' => $orders[0]['updated_at'] ?? date('c')
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Order not found']);
        }
        exit;
    }
}
