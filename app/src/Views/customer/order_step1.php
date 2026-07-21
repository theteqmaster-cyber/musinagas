<?php $title = 'Select Cylinder — Musina Gas'; include __DIR__ . '/../shared/header.php'; ?>

<div class="glass-nav">
  <a href="/home" style="color: var(--text-muted); font-size: 0.9rem;">&larr; Back</a>
  <div style="font-weight: 700;">New Order (Step 1 of 3)</div>
  <div></div>
</div>

<div class="app-viewport" style="padding: 20px;">

  <div class="progress-bar-wrap">
    <div class="progress-track">
      <div class="progress-fill" style="width: 33%;"></div>
    </div>
  </div>

  <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 16px;">Select Cylinder Size</h2>

  <form action="/order/new" method="POST">
    <?php 
    $pricePerKg = (float)($price['price_per_kg'] ?? 32.50);
    $first = true;
    foreach ($cylinders as $key => $cyl): 
      $sizeKg = $cyl['size_kg'];
      $calculatedPrice = number_format($sizeKg * $pricePerKg, 2);
    ?>
      <div class="glass-card interactive cylinder-card <?= $first ? 'selected' : '' ?>" style="display: flex; align-items: center; justify-content: space-between; padding: 18px;">
        <div style="display: flex; align-items: center; gap: 14px;">
          <input type="radio" name="cylinder_size_kg" value="<?= $sizeKg ?>" <?= $first ? 'checked' : '' ?> style="accent-color: var(--primary-color);">
          <div>
            <div style="font-size: 1.1rem; font-weight: 700;"><?= htmlspecialchars($cyl['name']) ?></div>
            <div style="font-size: 0.8rem; color: var(--text-muted);"><?= htmlspecialchars($cyl['description']) ?></div>
          </div>
        </div>
        <div style="text-align: right;">
          <div style="font-size: 1.25rem; font-weight: 800; color: var(--primary-color);">R <?= $calculatedPrice ?></div>
          <div style="font-size: 0.75rem; color: var(--text-dim);">@ R <?= $pricePerKg ?>/kg</div>
        </div>
      </div>
    <?php 
      $first = false;
    endforeach; 
    ?>

    <div class="glass-card" style="text-align: center; padding: 20px; margin-top: 20px;">
      <div class="form-label" style="margin-bottom: 12px;">Quantity</div>
      <div class="stepper">
        <button type="button" class="stepper-btn" id="btnMinus">&minus;</button>
        <span class="stepper-val" id="qtyVal">1</span>
        <button type="button" class="stepper-btn" id="btnPlus">&plus;</button>
      </div>
      <input type="hidden" id="qtyInput" name="quantity" value="1" max="<?= ($user['account_type'] ?? 'residential') === 'commercial' ? 50 : 10 ?>" data-size-kg="9" data-price-per-kg="<?= $pricePerKg ?>">
    </div>

    <button type="submit" class="btn btn-primary" style="margin-top: 10px;">NEXT: LOCATION &rarr;</button>
  </form>

</div>

<?php include __DIR__ . '/../shared/footer.php'; ?>
