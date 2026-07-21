<?php $title = 'Order Confirmed — Musina Gas'; include __DIR__ . '/../shared/header.php'; ?>

<div class="app-viewport" style="padding: 30px 20px; text-align: center;">

  <div style="font-size: 4rem; margin-bottom: 10px; animation: bounce 1s infinite;">✅</div>
  
  <h1 style="font-size: 1.75rem; font-weight: 800;">Order Placed!</h1>
  <div style="font-size: 1.1rem; font-weight: 700; color: var(--primary-color); margin: 6px 0 20px;">
    Order #<?= htmlspecialchars($order['order_ref'] ?? 'MG-00124') ?>
  </div>

  <div class="glass-card" style="text-align: left; padding: 20px;">
    <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 8px;">What happens next:</h3>

    <?php if (($order['payment_method'] ?? 'EFT') === 'EFT'): ?>
      <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 16px;">
        Please upload your proof of payment screenshot so our admin can verify and dispatch your gas cylinder.
      </p>
      <a href="/order/<?= $order['id'] ?>/eft-upload" class="btn btn-primary" style="margin-bottom: 10px;">
        📤 UPLOAD EFT PROOF
      </a>
    <?php else: ?>
      <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 16px;">
        Your COD order has been received and sent to dispatch. Please have <strong>R <?= number_format($order['total_amount'] ?? 0, 2) ?></strong> cash ready for the driver.
      </p>
    <?php endif; ?>

    <a href="/order/track/<?= $order['id'] ?>" class="btn btn-secondary" style="width: 100%;">
      📍 TRACK MY ORDER
    </a>
  </div>

  <a href="/home" style="font-size: 0.9rem; color: var(--text-dim); display: inline-block; margin-top: 14px;">
    &larr; Back to Home
  </a>

</div>

<?php include __DIR__ . '/../shared/footer.php'; ?>
