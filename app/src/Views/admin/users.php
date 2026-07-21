<?php $title = 'User Management — Musina Gas Admin'; include __DIR__ . '/../shared/header.php'; ?>

<div class="glass-nav" style="max-width: 1200px; margin: 0 auto;">
  <a href="/admin" style="color: var(--text-muted); font-size: 0.9rem;">&larr; Admin Dashboard</a>
  <div style="font-weight: 700;">User Management</div>
  <div></div>
</div>

<div class="app-viewport admin-layout" style="padding: 24px;">

  <h2 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 20px;">System Users & Roles</h2>

  <div class="glass-card">
    <div class="table-responsive">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Full Name</th>
            <th>Phone</th>
            <th>Account Type</th>
            <th>Role</th>
            <th>Change Role</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
            <tr>
              <td><strong><?= htmlspecialchars($u['full_name'] ?? 'User') ?></strong></td>
              <td><?= htmlspecialchars($u['phone'] ?? '-') ?></td>
              <td><span class="badge badge-info"><?= htmlspecialchars(strtoupper($u['account_type'] ?? 'RESIDENTIAL')) ?></span></td>
              <td><span class="badge badge-warning"><?= htmlspecialchars(strtoupper($u['role'] ?? 'CUSTOMER')) ?></span></td>
              <td>
                <form action="/admin/users/<?= $u['id'] ?>/role" method="POST" style="display: flex; gap: 8px;">
                  <select name="role" class="form-control" style="padding: 6px; font-size: 0.85rem;">
                    <option value="customer" <?= ($u['role'] ?? '') === 'customer' ? 'selected' : '' ?>>Customer</option>
                    <option value="driver" <?= ($u['role'] ?? '') === 'driver' ? 'selected' : '' ?>>Driver</option>
                    <option value="admin" <?= ($u['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                  </select>
                  <button type="submit" class="btn btn-secondary btn-sm">Save</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<?php include __DIR__ . '/../shared/footer.php'; ?>
