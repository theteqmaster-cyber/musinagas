<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simple Autoloader for App namespace
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// Serve static assets if index.php is invoked directly
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Simple Express-style Router
use App\Controllers\AuthController;
use App\Controllers\OrderController;
use App\Controllers\LocationController;
use App\Controllers\AdminController;
use App\Controllers\PricingController;
use App\Controllers\DriverController;

if ($uri === '/' && $method === 'GET') {
    if (isset($_SESSION['user'])) {
        $role = $_SESSION['user']['role'] ?? 'customer';
        if ($role === 'admin') header('Location: /admin');
        elseif ($role === 'driver') header('Location: /driver');
        else header('Location: /home');
        exit;
    }
    require __DIR__ . '/../src/Views/customer/landing.php';
    exit;
}

// Auth Routes
if ($uri === '/login') {
    $auth = new AuthController();
    if ($method === 'GET') $auth->showLogin();
    elseif ($method === 'POST') $auth->processLogin();
    exit;
}

if ($uri === '/register') {
    $auth = new AuthController();
    if ($method === 'GET') $auth->showRegister();
    elseif ($method === 'POST') $auth->processRegister();
    exit;
}

if ($uri === '/logout') {
    (new AuthController())->logout();
    exit;
}

// Customer Routes
if ($uri === '/home' && $method === 'GET') {
    (new OrderController())->home();
    exit;
}

if ($uri === '/order/new') {
    $order = new OrderController();
    if ($method === 'GET') $order->showStep1();
    elseif ($method === 'POST') $order->processStep1();
    exit;
}

if ($uri === '/order/location') {
    $order = new OrderController();
    if ($method === 'GET') $order->showStep2();
    elseif ($method === 'POST') $order->processStep2();
    exit;
}

if ($uri === '/order/checkout' && $method === 'GET') {
    (new OrderController())->showCheckout();
    exit;
}

if ($uri === '/order/place' && $method === 'POST') {
    (new OrderController())->placeOrder();
    exit;
}

if (preg_match('#^/order/confirm/([^/]+)$#', $uri, $matches)) {
    (new OrderController())->confirm($matches[1]);
    exit;
}

if (preg_match('#^/order/track/([^/]+)$#', $uri, $matches)) {
    (new OrderController())->track($matches[1]);
    exit;
}

if ($uri === '/orders' && $method === 'GET') {
    (new OrderController())->listOrders();
    exit;
}

if ($uri === '/addresses') {
    $loc = new LocationController();
    if ($method === 'GET') $loc->listAddresses();
    elseif ($method === 'POST') $loc->addAddress();
    exit;
}

if ($uri === '/profile' && $method === 'GET') {
    $user = \App\Middleware\AuthMiddleware::check();
    require __DIR__ . '/../src/Views/customer/profile.php';
    exit;
}

if (preg_match('#^/order/([^/]+)/eft-upload$#', $uri, $matches)) {
    $order = new OrderController();
    if ($method === 'GET') $order->showEftUpload($matches[1]);
    elseif ($method === 'POST') $order->processEftUpload($matches[1]);
    exit;
}

// Admin Routes
if ($uri === '/admin' && $method === 'GET') {
    (new AdminController())->dashboard();
    exit;
}

if ($uri === '/admin/pricing') {
    $pricing = new PricingController();
    if ($method === 'GET') $pricing->showPricing();
    exit;
}

if ($uri === '/admin/pricing/price' && $method === 'POST') {
    (new PricingController())->updatePrice();
    exit;
}

if (preg_match('#^/admin/pricing/zone/([^/]+)$#', $uri, $matches) && $method === 'POST') {
    (new AdminController())->updateZone($matches[1]);
    exit;
}

if ($uri === '/admin/orders' && $method === 'GET') {
    (new AdminController())->orders();
    exit;
}

if (preg_match('#^/admin/orders/([^/]+)/assign$#', $uri, $matches) && $method === 'POST') {
    (new AdminController())->assignDriver($matches[1]);
    exit;
}

if ($uri === '/admin/eft' && $method === 'GET') {
    (new AdminController())->eftAuditor();
    exit;
}

if (preg_match('#^/admin/eft/([^/]+)/approve$#', $uri, $matches) && $method === 'POST') {
    (new AdminController())->approveEft($matches[1]);
    exit;
}

if (preg_match('#^/admin/eft/([^/]+)/reject$#', $uri, $matches) && $method === 'POST') {
    (new AdminController())->rejectEft($matches[1]);
    exit;
}

if ($uri === '/admin/zones' && $method === 'GET') {
    (new AdminController())->zones();
    exit;
}

if ($uri === '/admin/inventory' && $method === 'GET') {
    (new AdminController())->inventory();
    exit;
}

if (preg_match('#^/admin/inventory/([^/]+)$#', $uri, $matches) && $method === 'POST') {
    (new AdminController())->updateStock($matches[1]);
    exit;
}

if ($uri === '/admin/users' && $method === 'GET') {
    (new AdminController())->users();
    exit;
}

if (preg_match('#^/admin/users/([^/]+)/role$#', $uri, $matches) && $method === 'POST') {
    (new AdminController())->updateRole($matches[1]);
    exit;
}

// Driver Routes
if (($uri === '/driver' || $uri === '/driver/tasks') && $method === 'GET') {
    (new DriverController())->dashboard();
    exit;
}

if (preg_match('#^/driver/task/([^/]+)$#', $uri, $matches) && $method === 'GET') {
    (new DriverController())->taskDetail($matches[1]);
    exit;
}

if (preg_match('#^/driver/task/([^/]+)/arrive$#', $uri, $matches) && $method === 'POST') {
    (new DriverController())->markArrived($matches[1]);
    exit;
}

if (preg_match('#^/driver/task/([^/]+)/cod$#', $uri, $matches)) {
    $driver = new DriverController();
    if ($method === 'GET') $driver->showCod($matches[1]);
    elseif ($method === 'POST') $driver->completeCod($matches[1]);
    exit;
}

if (preg_match('#^/driver/task/([^/]+)/complete$#', $uri, $matches)) {
    $driver = new DriverController();
    if ($method === 'POST') $driver->completeEft($matches[1]);
    elseif ($method === 'GET') $driver->showComplete($matches[1]);
    exit;
}

// API Routes
if (preg_match('#^/api/order-status/([^/]+)$#', $uri, $matches) && $method === 'GET') {
    (new OrderController())->apiStatus($matches[1]);
    exit;
}

// 404 Fallback
http_response_code(404);
echo "<h1 style='font-family:sans-serif; text-align:center; margin-top:50px;'>404 — Page Not Found</h1>";
