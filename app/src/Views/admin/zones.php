<?php $title = 'Zone Manager — Musina Gas Admin'; include __DIR__ . '/../shared/header.php'; ?>

<div class="glass-nav" style="max-width: 1200px; margin: 0 auto;">
  <a href="/admin" style="color: var(--text-muted); font-size: 0.9rem;">&larr; Admin Dashboard</a>
  <div style="font-weight: 700;">Zone & Location Manager</div>
  <div></div>
</div>

<div class="app-viewport admin-layout" style="padding: 24px;">

  <h2 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 20px;">Delivery Zone Fees</h2>

  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 16px;">
    <?php foreach ($zones as $z): ?>
      <div class="glass-card">
        <form action="/admin/pricing/zone/<?= $z['id'] ?>" method="POST">
          <div class="form-group">
            <label class="form-label">Zone Name</label>
            <input type="text" name="zone_name" class="form-control" value="<?= htmlspecialchars($z['zone_name']) ?>" required>
          </div>

          <div class="form-group">
            <label class="form-label">Delivery Fee (ZAR)</label>
            <input type="number" step="5" name="delivery_fee" class="form-control" value="<?= htmlspecialchars($z['delivery_fee']) ?>" required>
          </div>

          <button type="submit" class="btn btn-secondary btn-sm" style="width: 100%;">Save Zone Changes</button>
        </form>
      </div>
    <?php endforeach; ?>
  </div>

</div>

<?php include __DIR__ . '/../shared/footer.php'; ?>
