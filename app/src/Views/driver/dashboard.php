<?php $title = 'Driver Tasks — Musina Gas'; include __DIR__ . '/../shared/header.php'; ?>

<div class="glass-nav">
  <div class="brand-title">🚚 DRIVER PORTAL</div>
  <a href="/logout" style="font-size: 0.85rem; color: var(--accent-red);">Logout</a>
</div>

<div class="app-viewport" style="padding: 20px;">

  <div class="glass-card" style="background: linear-gradient(135deg, rgba(6,182,212,0.15) 0%, rgba(17,24,39,0.85) 100%);">
    <div style="font-size: 0.85rem; font-weight: 700; color: var(--accent-cyan);">TODAY'S DELIVERIES</div>
    <div style="font-size: 2.25rem; font-weight: 800; margin-top: 4px; color: #FFFFFF;"><?= count($myTasks) ?> Pending</div>
  </div>

  <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 12px;">Assigned Task List</h3>

  <?php if (empty($myTasks)): ?>
    <div class="glass-card" style="text-align: center; padding: 24px;">
      <div style="font-size: 2rem; margin-bottom: 8px;">🎉</div>
      <div style="font-weight: 700;">No pending deliveries assigned!</div>
    </div>
  <?php else: ?>
    <?php foreach ($myTasks as $t): ?>
      <div class="glass-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
          <span style="font-size: 1.1rem; font-weight: 800; color: var(--primary-color);">#<?= htmlspecialchars($t['order_ref']) ?></span>
          <span class="badge badge-info"><?= htmlspecialchars(strtoupper(str_replace('_', ' ', $t['payment_status']))) ?></span>
        </div>

        <div style="font-size: 0.95rem; font-weight: 700; margin-bottom: 4px;">
          📦 <?= htmlspecialchars($t['cylinder_size_kg']) ?>kg &times; <?= htmlspecialchars($t['quantity']) ?>
        </div>

        <div style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 6px;">
          📍 <?= htmlspecialchars($t['digital_address'] ?? 'Musina Location') ?>
        </div>

        <div style="font-size: 0.85rem; font-weight: 700; color: var(--accent-green); margin-bottom: 14px;">
          Payment: <?= htmlspecialchars($t['payment_method']) ?> (R <?= number_format($t['total_amount'], 2) ?>)
        </div>

        <a href="/driver/task/<?= $t['id'] ?>" class="btn btn-primary btn-sm" style="width: 100%;">START / VIEW DELIVERY &rarr;</a>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

  <?php if (!empty($completedToday)): ?>
    <h3 style="font-size: 1rem; font-weight: 700; margin: 24px 0 12px; color: var(--text-muted);">Completed Today (<?= count($completedToday) ?>)</h3>
    <?php foreach ($completedToday as $c): ?>
      <div class="glass-card" style="opacity: 0.7;">
        <div style="display: flex; justify-content: space-between;">
          <span style="font-weight: 700;">#<?= htmlspecialchars($c['order_ref']) ?></span>
          <span class="badge badge-success">COMPLETED</span>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

</div>

<?php include __DIR__ . '/../shared/footer.php'; ?>
