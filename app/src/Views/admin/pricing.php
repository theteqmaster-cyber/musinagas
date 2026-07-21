<?php $title = 'Pricing Control Panel — Musina Gas Admin'; include __DIR__ . '/../shared/header.php'; ?>

<div class="glass-nav" style="max-width: 1200px; margin: 0 auto;">
  <a href="/admin" style="color: var(--text-muted); font-size: 0.9rem;">&larr; Admin Dashboard</a>
  <div style="font-weight: 700;">Pricing Control Panel</div>
  <div></div>
</div>

<div class="app-viewport admin-layout" style="padding: 24px;">

  <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
    
    <!-- Update Price Per KG -->
    <div class="glass-card">
      <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 14px;">Set Base Gas Price (per KG)</h3>
      
      <div style="background: rgba(255,87,34,0.1); border: 1px solid rgba(255,87,34,0.3); border-radius: 12px; padding: 16px; margin-bottom: 16px;">
        <div style="font-size: 0.8rem; color: var(--text-muted);">CURRENT ACTIVE PRICE</div>
        <div style="font-size: 2.25rem; font-weight: 800; color: var(--primary-color);">R <?= number_format($currentPrice['price_per_kg'], 2) ?> / kg</div>
      </div>

      <form action="/admin/pricing/price" method="POST">
        <div class="form-group">
          <label class="form-label" for="price_per_kg">New Price per KG (ZAR)</label>
          <input type="number" step="0.50" min="1" id="price_per_kg" name="price_per_kg" class="form-control" value="<?= htmlspecialchars($currentPrice['price_per_kg']) ?>" required>
        </div>

        <div class="form-group">
          <label class="form-label" for="notes">Reason / Notes</label>
          <input type="text" id="notes" name="notes" class="form-control" placeholder="e.g. July price adjustment">
        </div>

        <button type="submit" class="btn btn-primary">UPDATE GAS PRICE IMMEDIATELY</button>
      </form>
    </div>

    <!-- Price History Audit Log -->
    <div class="glass-card">
      <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 14px;">Price History Log</h3>
      
      <div class="table-responsive">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Price/KG</th>
              <th>Notes</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($priceHistory as $ph): ?>
              <tr>
                <td><?= date('d M Y, H:i', strtotime($ph['effective_from'])) ?></td>
                <td><strong>R <?= number_format($ph['price_per_kg'], 2) ?></strong></td>
                <td><?= htmlspecialchars($ph['notes'] ?? 'Updated') ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>

</div>

<?php include __DIR__ . '/../shared/footer.php'; ?>
