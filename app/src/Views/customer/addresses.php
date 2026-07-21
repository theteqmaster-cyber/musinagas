<?php $title = 'Saved Addresses — Musina Gas'; include __DIR__ . '/../shared/header.php'; ?>

<div class="glass-nav">
  <div class="brand-title">🔥 MUSINA GAS</div>
  <div style="font-weight: 700; font-size: 0.9rem;">Saved Addresses</div>
</div>

<div class="app-viewport" style="padding: 20px;">

  <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 16px;">My Locations</h2>

  <?php if (empty($addresses)): ?>
    <div class="glass-card" style="text-align: center; padding: 24px;">
      <div style="font-size: 2rem; margin-bottom: 8px;">📍</div>
      <div style="font-size: 0.9rem; color: var(--text-muted);">No saved addresses yet</div>
    </div>
  <?php else: ?>
    <?php foreach ($addresses as $addr): ?>
      <div class="glass-card">
        <div style="font-size: 1rem; font-weight: 700; margin-bottom: 4px;">📍 <?= htmlspecialchars($addr['label'] ?? 'Saved Address') ?></div>
        <div style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 6px;"><?= htmlspecialchars($addr['digital_address']) ?></div>
        <?php if (!empty($addr['access_notes'])): ?>
          <div style="font-size: 0.8rem; color: var(--text-dim);">Access: <?= htmlspecialchars($addr['access_notes']) ?></div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

  <!-- Add New Address Card -->
  <div class="glass-card" style="margin-top: 20px;">
    <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 12px;">Add New Address</h3>
    <form action="/addresses" method="POST">
      <div class="form-group">
        <label class="form-label" for="label">Label (e.g. Home, Shop, Farm)</label>
        <input type="text" id="label" name="label" class="form-control" placeholder="Home" required>
      </div>

      <div class="form-group">
        <label class="form-label" for="digital_address">Physical Address</label>
        <input type="text" id="digital_address" name="digital_address" class="form-control" placeholder="12 Vhembe Rd, Musina" required>
      </div>

      <div class="form-group">
        <label class="form-label" for="zone_id">Delivery Zone</label>
        <select id="zone_id" name="zone_id" class="form-control">
          <?php foreach ($zones as $zone): ?>
            <option value="<?= htmlspecialchars($zone['id']) ?>"><?= htmlspecialchars($zone['zone_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label" for="access_notes">Access Notes</label>
        <input type="text" id="access_notes" name="access_notes" class="form-control" placeholder="Ring bell twice">
      </div>

      <input type="hidden" name="latitude" value="-22.3562">
      <input type="hidden" name="longitude" value="30.0416">

      <button type="submit" class="btn btn-primary btn-sm" style="width: 100%;">Save Address</button>
    </form>
  </div>

</div>

<?php include __DIR__ . '/../shared/navbar.php'; ?>
<?php include __DIR__ . '/../shared/footer.php'; ?>
