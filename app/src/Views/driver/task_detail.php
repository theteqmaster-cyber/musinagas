<?php $title = 'Delivery Detail — Driver'; include __DIR__ . '/../shared/header.php'; ?>

<div class="glass-nav">
  <a href="/driver" style="color: var(--text-muted); font-size: 0.9rem;">&larr; Back to Tasks</a>
  <div style="font-weight: 700;">Task #<?= htmlspecialchars($order['order_ref'] ?? '') ?></div>
  <div></div>
</div>

<div class="app-viewport" style="padding: 20px;">

  <div class="glass-card">
    <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 700;">CUSTOMER INFO</div>
    <div style="font-size: 1.25rem; font-weight: 800; margin: 4px 0;"><?= htmlspecialchars($order['customer_name'] ?? 'John Customer') ?></div>
    <div style="font-size: 1rem; color: var(--accent-cyan);">📞 <a href="tel:<?= htmlspecialchars($order['customer_phone'] ?? '') ?>"><?= htmlspecialchars($order['customer_phone'] ?? '+27 83 555 1234') ?></a></div>
  </div>

  <div class="glass-card">
    <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 700;">DELIVERY ADDRESS</div>
    <div style="font-size: 1rem; font-weight: 700; margin-top: 4px;">📍 <?= htmlspecialchars($order['digital_address'] ?? '12 Vhembe Rd, Musina CBD') ?></div>
  </div>

  <div class="glass-card">
    <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 700;">ORDER DETAILS</div>
    <div style="font-size: 1rem; font-weight: 700; margin-top: 4px;">📦 <?= htmlspecialchars($order['cylinder_size_kg'] ?? 9) ?>kg &times; <?= htmlspecialchars($order['quantity'] ?? 1) ?></div>
    <div style="font-size: 1.1rem; font-weight: 800; color: var(--primary-color); margin-top: 6px;">Total Due: R <?= number_format($order['total_amount'] ?? 0, 2) ?></div>
  </div>

  <?php if (($order['payment_status'] ?? '') !== 'arrived'): ?>
    <form action="/driver/task/<?= $order['id'] ?>/arrive" method="POST" style="margin-top: 20px;">
      <button type="submit" class="btn btn-primary" style="padding: 16px;">📍 MARK ARRIVED AT LOCATION</button>
    </form>
  <?php else: ?>
    <?php if (($order['payment_method'] ?? 'EFT') === 'COD'): ?>
      <a href="/driver/task/<?= $order['id'] ?>/cod" class="btn btn-primary" style="margin-top: 20px; padding: 16px; background: linear-gradient(135deg, #10B981 0%, #059669 100%);">
        💵 COLLECT COD CASH &amp; COMPLETE
      </a>
    <?php else: ?>
      <form action="/driver/task/<?= $order['id'] ?>/complete" method="POST" style="margin-top: 20px;">
        <button type="submit" class="btn btn-primary" style="padding: 16px; background: linear-gradient(135deg, #10B981 0%, #059669 100%);">
          ✅ CONFIRM DELIVERY COMPLETE
        </button>
      </form>
    <?php endif; ?>
  <?php endif; ?>

</div>

<?php include __DIR__ . '/../shared/footer.php'; ?>
