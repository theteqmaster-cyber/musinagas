<?php $title = 'Order History — Musina Gas'; include __DIR__ . '/../shared/header.php'; ?>

<div class="glass-nav">
  <div class="brand-title">🔥 MUSINA GAS</div>
  <div style="font-weight: 700; font-size: 0.9rem;">Order History</div>
</div>

<div class="app-viewport" style="padding: 20px;">

  <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 16px;">Your Orders</h2>

  <?php if (empty($orders)): ?>
    <div class="glass-card" style="text-align: center; padding: 32px 20px;">
      <div style="font-size: 2.5rem; margin-bottom: 10px;">📦</div>
      <div style="font-weight: 700;">No past orders yet</div>
      <div style="font-size: 0.85rem; color: var(--text-muted); margin: 8px 0 20px;">Place your first LP gas order today</div>
      <a href="/order/new" class="btn btn-primary">ORDER GAS NOW</a>
    </div>
  <?php else: ?>
    <?php foreach ($orders as $ord): ?>
      <div class="glass-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
          <span style="font-size: 1.1rem; font-weight: 800; color: var(--primary-color);">#<?= htmlspecialchars($ord['order_ref']) ?></span>
          <span class="badge badge-info"><?= htmlspecialchars(strtoupper(str_replace('_', ' ', $ord['payment_status']))) ?></span>
        </div>
        
        <div style="font-size: 0.9rem; margin-bottom: 4px;">
          <?= htmlspecialchars($ord['cylinder_size_kg']) ?>kg Cylinder &times; <?= htmlspecialchars($ord['quantity']) ?>
        </div>

        <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 12px;">
          <?= date('d M Y, H:i', strtotime($ord['created_at'])) ?> &bull; R <?= number_format($ord['total_amount'], 2) ?> (<?= htmlspecialchars($ord['payment_method']) ?>)
        </div>

        <a href="/order/track/<?= $ord['id'] ?>" class="btn btn-secondary btn-sm" style="width: 100%;">View Details &rarr;</a>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

</div>

<?php include __DIR__ . '/../shared/navbar.php'; ?>
<?php include __DIR__ . '/../shared/footer.php'; ?>
