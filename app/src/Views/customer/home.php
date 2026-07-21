<?php $title = 'Dashboard — Musina Gas'; include __DIR__ . '/../shared/header.php'; ?>

<div class="glass-nav">
  <div class="brand-title">🔥 MUSINA GAS</div>
  <a href="/logout" style="font-size: 0.85rem; color: var(--text-muted);">Logout</a>
</div>

<div class="app-viewport" style="padding: 20px;">
  
  <!-- Current Gas Price Panel -->
  <div class="glass-card" style="background: linear-gradient(135deg, rgba(255,87,34,0.15) 0%, rgba(17,24,39,0.85) 100%); border-color: rgba(255,87,34,0.3);">
    <div style="font-size: 0.85rem; color: var(--primary-color); font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">Current Gas Price</div>
    <div style="font-size: 2.25rem; font-weight: 800; margin: 8px 0; color: #FFFFFF;">
      R <?= number_format((float)($price['price_per_kg'] ?? 32.50), 2) ?> <span style="font-size: 1rem; font-weight: 500; color: var(--text-muted);">/ kg</span>
    </div>
    <div style="font-size: 0.75rem; color: var(--text-dim);">
      Updated: <?= date('d M Y H:i', strtotime($price['effective_from'] ?? 'now')) ?>
    </div>
  </div>

  <!-- Active Order Banner if exists -->
  <?php if (!empty($activeOrder)): ?>
    <div class="glass-card" style="border-color: var(--accent-cyan);">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
        <span style="font-size: 0.85rem; font-weight: 700; color: var(--accent-cyan);">ACTIVE ORDER</span>
        <span class="badge badge-info"><?= htmlspecialchars(str_replace('_', ' ', strtoupper($activeOrder['payment_status']))) ?></span>
      </div>
      <div style="font-size: 1.1rem; font-weight: 700;">Order #<?= htmlspecialchars($activeOrder['order_ref']) ?></div>
      <div style="font-size: 0.85rem; color: var(--text-muted); margin: 4px 0 12px;">
        <?= htmlspecialchars($activeOrder['cylinder_size_kg']) ?>kg &times; <?= htmlspecialchars($activeOrder['quantity']) ?> &bull; Total R <?= number_format($activeOrder['total_amount'], 2) ?>
      </div>
      <a href="/order/track/<?= $activeOrder['id'] ?>" class="btn btn-secondary btn-sm" style="width: 100%;">Track My Order &rarr;</a>
    </div>
  <?php endif; ?>

  <!-- Action Button -->
  <div style="margin: 20px 0;">
    <a href="/order/new" class="btn btn-primary" style="font-size: 1.1rem; padding: 18px;">
      🛒 ORDER GAS NOW
    </a>
  </div>

  <!-- Quick Info Tiles -->
  <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 10px;">
    <a href="/orders" class="glass-card interactive" style="padding: 16px; margin-bottom: 0;">
      <div style="font-size: 1.5rem; margin-bottom: 4px;">📜</div>
      <div style="font-weight: 700; font-size: 0.95rem;">Order History</div>
      <div style="font-size: 0.75rem; color: var(--text-muted);">Past deliveries & invoices</div>
    </a>

    <a href="/addresses" class="glass-card interactive" style="padding: 16px; margin-bottom: 0;">
      <div style="font-size: 1.5rem; margin-bottom: 4px;">📍</div>
      <div style="font-weight: 700; font-size: 0.95rem;">Addresses</div>
      <div style="font-size: 0.75rem; color: var(--text-muted);">Manage drop-off pins</div>
    </a>
  </div>

</div>

<?php include __DIR__ . '/../shared/navbar.php'; ?>
<?php include __DIR__ . '/../shared/footer.php'; ?>
