<?php $title = 'Sign In — Musina Gas'; include __DIR__ . '/../shared/header.php'; ?>

<div class="app-viewport" style="padding: 24px 20px;">
  <div style="margin-bottom: 24px;">
    <a href="/" style="color: var(--text-muted); font-size: 0.9rem;">&larr; Back</a>
    <h1 style="font-size: 1.75rem; font-weight: 800; margin-top: 16px;">Welcome Back</h1>
    <p style="color: var(--text-muted); font-size: 0.95rem;">Sign in to your Musina Gas account</p>
  </div>

  <?php if (!empty($error)): ?>
    <div class="alert alert-error">
      <span>⚠️ <?= htmlspecialchars($error) ?></span>
    </div>
  <?php endif; ?>

  <div class="glass-card">
    <form action="/login" method="POST">
      <div class="form-group">
        <label class="form-label" for="email">Email Address</label>
        <input type="email" id="email" name="email" class="form-control" placeholder="you@domain.co.za" required>
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
      </div>

      <button type="submit" class="btn btn-primary" style="margin-top: 8px;">SIGN IN</button>
    </form>
  </div>

  <div style="text-align: center; margin-top: 20px; font-size: 0.9rem; color: var(--text-muted);">
    Don't have an account? <a href="/register" style="font-weight: 600;">Register Here</a>
  </div>
</div>

<?php include __DIR__ . '/../shared/footer.php'; ?>
