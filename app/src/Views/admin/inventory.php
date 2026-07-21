<?php $title = 'Inventory — Musina Gas Admin'; include __DIR__ . '/../shared/header.php'; ?>

<div class="glass-nav" style="max-width: 1200px; margin: 0 auto;">
  <a href="/admin" style="color: var(--text-muted); font-size: 0.9rem;">&larr; Admin Dashboard</a>
  <div style="font-weight: 700;">Cylinder Inventory</div>
  <div></div>
</div>

<div class="app-viewport admin-layout" style="padding: 24px;">

  <h2 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 20px;">Cylinder Stock Levels</h2>

  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
    <?php foreach ($inventory as $inv): ?>
      <div class="glass-card">
        <div style="font-size: 1.5rem; font-weight: 800; color: var(--primary-color); margin-bottom: 8px;">
          <?= htmlspecialchars($inv['cylinder_size']) ?> kg Cylinders
        </div>

        <form action="/admin/inventory/<?= $inv['id'] ?>" method="POST">
          <div class="form-group">
            <label class="form-label">Available Stock Count</label>
            <input type="number" name="stock_count" class="form-control" value="<?= htmlspecialchars($inv['stock_count']) ?>" required>
          </div>

          <div style="font-size: 0.75rem; color: var(--text-dim); margin-bottom: 12px;">
            Last Inspected: <?= htmlspecialchars($inv['last_inspected'] ?? date('Y-m-d')) ?>
          </div>

          <button type="submit" class="btn btn-secondary btn-sm" style="width: 100%;">Update Stock</button>
        </form>
      </div>
    <?php endforeach; ?>
  </div>

</div>

<?php include __DIR__ . '/../shared/footer.php'; ?>
