<?php $title = 'Register Account — Musina Gas'; include __DIR__ . '/../shared/header.php'; ?>

<div class="app-viewport" style="padding: 24px 20px;">
  <div style="margin-bottom: 24px;">
    <a href="/" style="color: var(--text-muted); font-size: 0.9rem;">&larr; Back</a>
    <h1 style="font-size: 1.75rem; font-weight: 800; margin-top: 16px;">Create Account</h1>
    <p style="color: var(--text-muted); font-size: 0.95rem;">Join Musina Gas for fast LP Gas deliveries</p>
  </div>

  <?php if (!empty($error)): ?>
    <div class="alert alert-error">
      <span>⚠️ <?= htmlspecialchars($error) ?></span>
    </div>
  <?php endif; ?>

  <div class="glass-card">
    <form action="/register" method="POST">
      <div class="form-group">
        <label class="form-label" for="full_name">Full Name</label>
        <input type="text" id="full_name" name="full_name" class="form-control" placeholder="e.g. John Makhado" required>
      </div>

      <div class="form-group">
        <label class="form-label" for="phone">Phone Number (+27 SA Format)</label>
        <input type="tel" id="phone" name="phone" class="form-control" placeholder="+27 82 123 4567" required>
      </div>

      <div class="form-group">
        <label class="form-label" for="email">Email Address</label>
        <input type="email" id="email" name="email" class="form-control" placeholder="you@domain.co.za" required>
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Password (min 8 chars)</label>
        <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" minlength="8" required>
      </div>

      <div class="form-group">
        <label class="form-label">Account Type</label>
        <div style="display: flex; gap: 16px; margin-top: 8px;">
          <label style="display: flex; align-items: center; gap: 8px; font-size: 0.95rem; cursor: pointer;">
            <input type="radio" name="account_type" value="residential" checked> Residential
          </label>
          <label style="display: flex; align-items: center; gap: 8px; font-size: 0.95rem; cursor: pointer;">
            <input type="radio" name="account_type" value="commercial"> Commercial
          </label>
        </div>
      </div>

      <button type="submit" class="btn btn-primary" style="margin-top: 12px;">CREATE ACCOUNT</button>
    </form>
  </div>

  <div style="text-align: center; margin-top: 20px; font-size: 0.9rem; color: var(--text-muted);">
    Already registered? <a href="/login" style="font-weight: 600;">Sign In</a>
  </div>
</div>

<?php include __DIR__ . '/../shared/footer.php'; ?>
