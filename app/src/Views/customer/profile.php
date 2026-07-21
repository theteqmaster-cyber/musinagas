<?php $title = 'Profile — Musina Gas'; include __DIR__ . '/../shared/header.php'; ?>

<div class="glass-nav">
  <div class="brand-title">🔥 MUSINA GAS</div>
  <div style="font-weight: 700; font-size: 0.9rem;">Profile</div>
</div>

<div class="app-viewport" style="padding: 20px;">

  <div class="glass-card" style="text-align: center; padding: 24px;">
    <div style="font-size: 3rem; margin-bottom: 8px;">👤</div>
    <h2 style="font-size: 1.25rem; font-weight: 800;"><?= htmlspecialchars($_SESSION['user']['full_name'] ?? 'Customer') ?></h2>
    <div style="font-size: 0.85rem; color: var(--text-muted);"><?= htmlspecialchars($_SESSION['user']['email'] ?? '') ?></div>
    <div style="font-size: 0.85rem; color: var(--primary-color); margin-top: 4px; font-weight: 600;">
      <?= htmlspecialchars($_SESSION['user']['phone'] ?? '') ?>
    </div>
    <div style="margin-top: 10px;">
      <span class="badge badge-info"><?= htmlspecialchars(strtoupper($_SESSION['user']['account_type'] ?? 'RESIDENTIAL')) ?> ACCOUNT</span>
    </div>
  </div>

  <div class="glass-card">
    <h4 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 12px;">ACCOUNT INFO</h4>
    <div style="display: flex; justify-content: space-between; font-size: 0.9rem; padding: 8px 0; border-bottom: 1px solid var(--border-glass);">
      <span style="color: var(--text-muted);">Role</span>
      <span><?= htmlspecialchars(ucfirst($_SESSION['user']['role'] ?? 'customer')) ?></span>
    </div>
    <div style="display: flex; justify-content: space-between; font-size: 0.9rem; padding: 8px 0;">
      <span style="color: var(--text-muted);">Region</span>
      <span>Musina, Limpopo</span>
    </div>
  </div>

  <a href="/logout" class="btn btn-secondary" style="margin-top: 20px; border-color: rgba(239,68,68,0.3); color: #FCA5A5;">
    🚪 SIGN OUT
  </a>

</div>

<?php include __DIR__ . '/../shared/navbar.php'; ?>
<?php include __DIR__ . '/../shared/footer.php'; ?>
