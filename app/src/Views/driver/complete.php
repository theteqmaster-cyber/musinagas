<?php $title = 'Delivery Complete — Driver'; include __DIR__ . '/../shared/header.php'; ?>

<div class="app-viewport" style="padding: 40px 20px; text-align: center;">

  <div style="font-size: 4rem; margin-bottom: 12px;">🎉</div>
  <h1 style="font-size: 1.75rem; font-weight: 800;">Delivery Complete!</h1>
  <p style="color: var(--text-muted); font-size: 0.95rem; margin: 8px 0 24px;">
    Order #<?= htmlspecialchars($order['order_ref'] ?? '') ?> has been marked completed successfully.
  </p>

  <a href="/driver" class="btn btn-primary" style="padding: 16px;">
    &larr; RETURN TO TASK LIST
  </a>

</div>

<?php include __DIR__ . '/../shared/footer.php'; ?>
