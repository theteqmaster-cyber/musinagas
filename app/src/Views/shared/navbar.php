<?php
$currentRoute = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$role = $_SESSION['user']['role'] ?? 'customer';
?>

<?php if ($role === 'customer'): ?>
<nav class="bottom-nav">
  <a href="/home" class="nav-item <?= $currentRoute === '/home' ? 'active' : '' ?>">
    <svg viewBox="0 0 24 24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
    <span>Home</span>
  </a>
  <a href="/order/new" class="nav-item <?= strpos($currentRoute, '/order') === 0 ? 'active' : '' ?>">
    <svg viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
    <span>Order</span>
  </a>
  <a href="/orders" class="nav-item <?= $currentRoute === '/orders' ? 'active' : '' ?>">
    <svg viewBox="0 0 24 24"><path d="M13 3c-4.97 0-9 4.03-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42C8.27 19.99 10.51 21 13 21c4.97 0 9-4.03 9-9s-4.03-9-9-9zm-1 5v5l4.28 2.54.72-1.21-3.5-2.08V8H12z"/></svg>
    <span>History</span>
  </a>
  <a href="/addresses" class="nav-item <?= $currentRoute === '/addresses' ? 'active' : '' ?>">
    <svg viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
    <span>Saved</span>
  </a>
  <a href="/profile" class="nav-item <?= $currentRoute === '/profile' ? 'active' : '' ?>">
    <svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
    <span>Profile</span>
  </a>
</nav>
<?php endif; ?>
