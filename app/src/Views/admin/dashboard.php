<?php $title = 'Admin Overview — Musina Gas'; include __DIR__ . '/../shared/header.php'; ?>

<div class="glass-nav" style="max-width: 1200px; margin: 0 auto;">
  <div class="brand-title">🔥 MUSINA GAS ADMIN</div>
  <div style="display: flex; gap: 16px; align-items: center;">
    <a href="/admin/pricing" style="font-size: 0.9rem;">Pricing</a>
    <a href="/admin/orders" style="font-size: 0.9rem;">Dispatch</a>
    <a href="/admin/eft" style="font-size: 0.9rem;">EFT Auditor</a>
    <a href="/admin/zones" style="font-size: 0.9rem;">Zones</a>
    <a href="/admin/inventory" style="font-size: 0.9rem;">Stock</a>
    <a href="/admin/users" style="font-size: 0.9rem;">Users</a>
    <a href="/logout" style="font-size: 0.85rem; color: var(--accent-red);">Logout</a>
  </div>
</div>

<div class="app-viewport admin-layout" style="padding: 24px;">

  <h2 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 20px;">Today at a Glance</h2>

  <!-- Stats Grid -->
  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="glass-card" style="margin-bottom: 0;">
      <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">TOTAL ORDERS</div>
      <div style="font-size: 2rem; font-weight: 800; margin-top: 4px;"><?= $stats['total_orders'] ?></div>
    </div>

    <div class="glass-card" style="margin-bottom: 0; border-color: rgba(245,158,11,0.3);">
      <div style="font-size: 0.8rem; color: #FBBF24; font-weight: 600;">EFT SUBMITTED (PENDING AUDIT)</div>
      <div style="font-size: 2rem; font-weight: 800; color: #FBBF24; margin-top: 4px;"><?= $stats['eft_submitted'] ?></div>
    </div>

    <div class="glass-card" style="margin-bottom: 0; border-color: rgba(16,185,129,0.3);">
      <div style="font-size: 0.8rem; color: #34D399; font-weight: 600;">REVENUE GENERATED</div>
      <div style="font-size: 2rem; font-weight: 800; color: #34D399; margin-top: 4px;">R <?= number_format($stats['total_revenue'], 2) ?></div>
    </div>

    <div class="glass-card" style="margin-bottom: 0; border-color: rgba(255,87,34,0.3);">
      <div style="font-size: 0.8rem; color: var(--primary-color); font-weight: 600;">CURRENT GAS PRICE</div>
      <div style="font-size: 2rem; font-weight: 800; color: var(--primary-color); margin-top: 4px;">R <?= number_format($price['price_per_kg'], 2) ?> / kg</div>
    </div>
  </div>

  <!-- Live Orders Feed -->
  <div class="glass-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
      <h3 style="font-size: 1.1rem; font-weight: 700;">Live Orders Feed</h3>
      <a href="/admin/orders" class="btn btn-secondary btn-sm">View Dispatch Board &rarr;</a>
    </div>

    <div class="table-responsive">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Ref</th>
            <th>Customer</th>
            <th>Cylinder</th>
            <th>Method</th>
            <th>Total</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($orders)): ?>
            <tr><td colspan="7" style="text-align: center; color: var(--text-muted);">No orders in system yet.</td></tr>
          <?php else: ?>
            <?php foreach (array_slice($orders, 0, 10) as $ord): ?>
              <tr>
                <td><strong>#<?= htmlspecialchars($ord['order_ref']) ?></strong></td>
                <td><?= htmlspecialchars($ord['customer_name'] ?? 'Customer') ?><br><small style="color: var(--text-dim);"><?= htmlspecialchars($ord['customer_phone'] ?? '') ?></small></td>
                <td><?= htmlspecialchars($ord['cylinder_size_kg']) ?>kg &times; <?= htmlspecialchars($ord['quantity']) ?></td>
                <td><?= htmlspecialchars($ord['payment_method']) ?></td>
                <td><strong>R <?= number_format($ord['total_amount'], 2) ?></strong></td>
                <td><span class="badge badge-info"><?= htmlspecialchars(strtoupper(str_replace('_', ' ', $ord['payment_status']))) ?></span></td>
                <td>
                  <?php if ($ord['payment_status'] === 'eft_submitted'): ?>
                    <a href="/admin/eft" class="btn btn-primary btn-sm">Audit EFT</a>
                  <?php else: ?>
                    <a href="/admin/orders" class="btn btn-secondary btn-sm">Dispatch</a>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<?php include __DIR__ . '/../shared/footer.php'; ?>
