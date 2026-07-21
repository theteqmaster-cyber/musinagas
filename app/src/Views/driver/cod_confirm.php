<?php $title = 'COD Cash Collection — Driver'; include __DIR__ . '/../shared/header.php'; ?>

<div class="glass-nav">
  <a href="/driver/task/<?= $order['id'] ?>" style="color: var(--text-muted); font-size: 0.9rem;">&larr; Back</a>
  <div style="font-weight: 700;">COD Collection</div>
  <div></div>
</div>

<div class="app-viewport" style="padding: 20px;">

  <div class="glass-card" style="text-align: center; padding: 24px; border-color: rgba(16,185,129,0.3);">
    <div style="font-size: 0.85rem; font-weight: 700; color: var(--accent-green);">AMOUNT TO COLLECT</div>
    <div style="font-size: 2.5rem; font-weight: 800; color: var(--accent-green); margin: 8px 0;">
      R <?= number_format($order['total_amount'] ?? 0, 2) ?>
    </div>
    <div style="font-size: 0.85rem; color: var(--text-muted);">Order #<?= htmlspecialchars($order['order_ref'] ?? '') ?></div>
  </div>

  <div class="glass-card">
    <form action="/driver/task/<?= $order['id'] ?>/cod" method="POST">
      <div class="form-group">
        <label class="form-label" for="amount_received">Amount Received from Customer (ZAR)</label>
        <input type="number" step="0.50" id="amount_received" name="amount_received" class="form-control" placeholder="350.00" value="<?= htmlspecialchars($order['total_amount'] ?? 0) ?>" required>
      </div>

      <button type="submit" class="btn btn-primary" style="padding: 16px; background: linear-gradient(135deg, #10B981 0%, #059669 100%);">
        ✅ CONFIRM RECEIPT &amp; COMPLETE
      </button>
    </form>
  </div>

</div>

<?php include __DIR__ . '/../shared/footer.php'; ?>
