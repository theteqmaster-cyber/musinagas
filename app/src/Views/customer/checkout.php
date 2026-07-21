<?php $title = 'Checkout — Musina Gas'; include __DIR__ . '/../shared/header.php'; ?>

<div class="glass-nav">
  <a href="/order/location" style="color: var(--text-muted); font-size: 0.9rem;">&larr; Back</a>
  <div style="font-weight: 700;">Checkout (Step 3 of 3)</div>
  <div></div>
</div>

<div class="app-viewport" style="padding: 20px;">

  <div class="progress-bar-wrap">
    <div class="progress-track">
      <div class="progress-fill" style="width: 100%;"></div>
    </div>
  </div>

  <div class="glass-card">
    <h3 style="font-size: 1.1rem; font-weight: 700; border-bottom: 1px solid var(--border-glass); padding-bottom: 10px; margin-bottom: 12px;">ORDER SUMMARY</h3>
    
    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
      <span style="color: var(--text-muted);"><?= htmlspecialchars($draft['cylinder_size_kg']) ?>kg Cylinder &times; <?= htmlspecialchars($draft['quantity']) ?></span>
      <span>R <?= number_format($gasTotal, 2) ?></span>
    </div>

    <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
      <span style="color: var(--text-muted);">Delivery Fee</span>
      <span>R <?= number_format($draft['delivery_fee'], 2) ?></span>
    </div>

    <div style="display: flex; justify-content: space-between; border-top: 1px dashed var(--border-glass); padding-top: 12px; font-size: 1.25rem; font-weight: 800; color: var(--primary-color);">
      <span>TOTAL DUE</span>
      <span>R <?= number_format($grandTotal, 2) ?></span>
    </div>
  </div>

  <div class="glass-card" style="margin-bottom: 20px;">
    <div style="font-size: 0.85rem; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">DELIVERING TO:</div>
    <div style="font-size: 0.95rem; font-weight: 600;"><?= htmlspecialchars($draft['digital_address']) ?></div>
    <?php if (!empty($draft['access_notes'])): ?>
      <div style="font-size: 0.8rem; color: var(--text-dim); margin-top: 2px;">Note: <?= htmlspecialchars($draft['access_notes']) ?></div>
    <?php endif; ?>
  </div>

  <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 12px;">SELECT PAYMENT METHOD</h3>

  <form action="/order/place" method="POST">
    
    <!-- Card Payment (Disabled Placeholder) -->
    <div class="glass-card" style="opacity: 0.5; display: flex; align-items: center; justify-content: space-between;">
      <div style="display: flex; align-items: center; gap: 12px;">
        <span style="font-size: 1.5rem;">💳</span>
        <div>
          <div style="font-weight: 700;">Credit / Debit Card</div>
          <div style="font-size: 0.75rem; color: var(--text-dim);">Visa, Mastercard</div>
        </div>
      </div>
      <span class="badge badge-warning">COMING SOON</span>
    </div>

    <!-- EFT Option -->
    <div class="glass-card interactive selected" style="display: flex; align-items: center; justify-content: space-between;">
      <label style="display: flex; align-items: center; gap: 12px; width: 100%; cursor: pointer;">
        <input type="radio" name="payment_method" value="EFT" checked style="accent-color: var(--primary-color);">
        <span style="font-size: 1.5rem;">🏦</span>
        <div>
          <div style="font-weight: 700;">EFT / Bank Transfer</div>
          <div style="font-size: 0.75rem; color: var(--text-muted);">Upload proof of payment after placing</div>
        </div>
      </label>
    </div>

    <!-- Cash on Delivery Option -->
    <?php if ($codEnabled): ?>
      <div class="glass-card interactive" style="display: flex; align-items: center; justify-content: space-between;">
        <label style="display: flex; align-items: center; gap: 12px; width: 100%; cursor: pointer;">
          <input type="radio" name="payment_method" value="COD" style="accent-color: var(--primary-color);">
          <span style="font-size: 1.5rem;">💵</span>
          <div>
            <div style="font-weight: 700;">Cash on Delivery (COD)</div>
            <div style="font-size: 0.75rem; color: var(--text-muted);">Pay driver cash upon drop-off</div>
          </div>
        </label>
      </div>
    <?php endif; ?>

    <button type="submit" class="btn btn-primary" style="margin-top: 14px;">PLACE ORDER &rarr;</button>
  </form>

</div>

<?php include __DIR__ . '/../shared/footer.php'; ?>
