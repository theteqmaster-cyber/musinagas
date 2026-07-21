<?php $title = 'Delivery Location — Musina Gas'; include __DIR__ . '/../shared/header.php'; ?>

<div class="glass-nav">
  <a href="/order/new" style="color: var(--text-muted); font-size: 0.9rem;">&larr; Back</a>
  <div style="font-weight: 700;">New Order (Step 2 of 3)</div>
  <div></div>
</div>

<div class="app-viewport" style="padding: 20px;">

  <div class="progress-bar-wrap">
    <div class="progress-track">
      <div class="progress-fill" style="width: 66%;"></div>
    </div>
  </div>

  <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 14px;">Set Delivery Location</h2>

  <form action="/order/location" method="POST">
    
    <!-- Map Container -->
    <div class="glass-card" style="padding: 10px; margin-bottom: 14px;">
      <div id="leafletMap" style="width: 100%; height: 220px; border-radius: 12px; z-index: 1;"></div>
      <button type="button" id="btnUseGps" class="btn btn-secondary btn-sm" style="margin-top: 10px; width: 100%;">
        📍 Use My Current GPS
      </button>
    </div>

    <!-- Location Inputs -->
    <div class="glass-card">
      <div class="form-group">
        <label class="form-label" for="digital_address">Street Address / Digital Location</label>
        <input type="text" id="digital_address" name="digital_address" class="form-control" placeholder="e.g. 12 Vhembe Rd, Musina CBD" required>
      </div>

      <div class="form-group">
        <label class="form-label" for="zone_id">Delivery Zone</label>
        <select id="zone_id" name="zone_id" class="form-control">
          <?php foreach ($zones as $zone): ?>
            <option value="<?= htmlspecialchars($zone['id']) ?>">
              <?= htmlspecialchars($zone['zone_name']) ?> (+R <?= number_format($zone['delivery_fee'], 2) ?> delivery)
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label" for="access_notes">Access Instructions (Optional)</label>
        <input type="text" id="access_notes" name="access_notes" class="form-control" placeholder="e.g. Blue gate, ring bell twice">
      </div>

      <label style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem; color: var(--text-muted); cursor: pointer;">
        <input type="checkbox" name="save_address" value="1" checked style="accent-color: var(--primary-color);"> Save to my address book
      </label>

      <input type="hidden" id="latitude" name="latitude" value="-22.3562">
      <input type="hidden" id="longitude" name="longitude" value="30.0416">
    </div>

    <button type="submit" class="btn btn-primary">NEXT: CHECKOUT &rarr;</button>
  </form>

</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    initMusinaMap('latitude', 'longitude', 'digital_address');
  });
</script>

<?php include __DIR__ . '/../shared/footer.php'; ?>
