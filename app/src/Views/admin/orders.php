<?php $title = 'Dispatch Board — Musina Gas Admin'; include __DIR__ . '/../shared/header.php'; ?>

<div class="glass-nav" style="max-width: 1200px; margin: 0 auto;">
  <a href="/admin" style="color: var(--text-muted); font-size: 0.9rem;">&larr; Admin Dashboard</a>
  <div style="font-weight: 700;">Order & Dispatch Board</div>
  <div></div>
</div>

<div class="app-viewport admin-layout" style="padding: 24px;">

  <h2 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 20px;">Active Dispatch Queue</h2>

  <div class="glass-card">
    <div class="table-responsive">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Order Ref</th>
            <th>Customer & Contact</th>
            <th>Delivery Address</th>
            <th>Cylinder</th>
            <th>Total & Method</th>
            <th>Status</th>
            <th>Assigned Driver</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($orders)): ?>
            <tr><td colspan="8" style="text-align: center; color: var(--text-muted);">No active orders.</td></tr>
          <?php else: ?>
            <?php foreach ($orders as $ord): ?>
              <tr>
                <td><strong>#<?= htmlspecialchars($ord['order_ref']) ?></strong></td>
                <td>
                  <strong><?= htmlspecialchars($ord['customer_name'] ?? 'John Customer') ?></strong><br>
                  <small style="color: var(--text-dim);"><?= htmlspecialchars($ord['customer_phone'] ?? '+27 83 555 1234') ?></small>
                </td>
                <td><?= htmlspecialchars($ord['digital_address'] ?? 'Musina CBD') ?></td>
                <td><?= htmlspecialchars($ord['cylinder_size_kg']) ?>kg &times; <?= htmlspecialchars($ord['quantity']) ?></td>
                <td>
                  <strong>R <?= number_format($ord['total_amount'], 2) ?></strong><br>
                  <small style="color: var(--primary-color);"><?= htmlspecialchars($ord['payment_method']) ?></small>
                </td>
                <td><span class="badge badge-info"><?= htmlspecialchars(strtoupper(str_replace('_', ' ', $ord['payment_status']))) ?></span></td>
                <td>
                  <form action="/admin/orders/<?= $ord['id'] ?>/assign" method="POST" style="display: flex; gap: 6px;">
                    <select name="driver_id" class="form-control" style="padding: 6px 10px; font-size: 0.85rem;">
                      <option value="">-- Select Driver --</option>
                      <?php foreach ($drivers as $drv): ?>
                        <option value="<?= $drv['id'] ?>" <?= ($ord['assigned_driver'] ?? '') === $drv['id'] ? 'selected' : '' ?>>
                          <?= htmlspecialchars($drv['full_name']) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm">Dispatch</button>
                  </form>
                </td>
                <td>
                  <?php if (!empty($ord['eft_proof_url'])): ?>
                    <a href="<?= htmlspecialchars($ord['eft_proof_url']) ?>" target="_blank" class="btn btn-secondary btn-sm">View Proof</a>
                  <?php else: ?>
                    <span style="font-size: 0.8rem; color: var(--text-dim);">-</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<?php include __DIR__ . '/../shared/footer.php'; ?>
