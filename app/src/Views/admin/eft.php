<?php $title = 'EFT Auditor — Musina Gas Admin'; include __DIR__ . '/../shared/header.php'; ?>

<div class="glass-nav" style="max-width: 1200px; margin: 0 auto;">
  <a href="/admin" style="color: var(--text-muted); font-size: 0.9rem;">&larr; Admin Dashboard</a>
  <div style="font-weight: 700;">EFT Payment Auditor</div>
  <div></div>
</div>

<div class="app-viewport admin-layout" style="padding: 24px;">

  <h2 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 20px;">EFT Proof Verification Queue</h2>

  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 18px;">
    <?php if (empty($eftOrders)): ?>
      <div class="glass-card" style="grid-column: 1 / -1; text-align: center; padding: 32px;">
        <div style="font-size: 2rem; margin-bottom: 8px;">✅</div>
        <div style="font-size: 1.1rem; font-weight: 700;">No pending EFT proofs to audit</div>
      </div>
    <?php else: ?>
      <?php foreach ($eftOrders as $ord): ?>
        <div class="glass-card">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <span style="font-size: 1.25rem; font-weight: 800; color: var(--primary-color);">#<?= htmlspecialchars($ord['order_ref']) ?></span>
            <span class="badge badge-warning"><?= htmlspecialchars(strtoupper(str_replace('_', ' ', $ord['payment_status']))) ?></span>
          </div>

          <div style="font-size: 0.9rem; line-height: 1.6; margin-bottom: 14px;">
            <div><strong>Customer:</strong> <?= htmlspecialchars($ord['customer_name'] ?? 'Sarah L.') ?></div>
            <div><strong>Expected Amount:</strong> <span style="font-size: 1.1rem; font-weight: 800; color: var(--accent-green);">R <?= number_format($ord['total_amount'], 2) ?></span></div>
            <div><strong>Cylinder:</strong> <?= htmlspecialchars($ord['cylinder_size_kg']) ?>kg &times; <?= htmlspecialchars($ord['quantity']) ?></div>
            <div><strong>Submitted:</strong> <?= date('d M H:i', strtotime($ord['created_at'])) ?></div>
          </div>

          <?php if (!empty($ord['eft_proof_url'])): ?>
            <div style="margin-bottom: 14px; text-align: center; background: rgba(0,0,0,0.4); border-radius: 8px; padding: 10px;">
              <a href="<?= htmlspecialchars($ord['eft_proof_url']) ?>" target="_blank" style="color: var(--accent-cyan); font-weight: 600; font-size: 0.9rem;">
                🖼️ Open Proof Image / PDF
              </a>
            </div>
          <?php endif; ?>

          <div style="display: flex; gap: 10px;">
            <form action="/admin/eft/<?= $ord['id'] ?>/approve" method="POST" style="flex: 1;">
              <button type="submit" class="btn btn-primary btn-sm" style="width: 100%; background: linear-gradient(135deg, #10B981 0%, #059669 100%);">
                ✅ APPROVE
              </button>
            </form>
            <form action="/admin/eft/<?= $ord['id'] ?>/reject" method="POST" style="flex: 1;">
              <button type="submit" class="btn btn-secondary btn-sm" style="width: 100%; border-color: rgba(239,68,68,0.4); color: #FCA5A5;">
                ❌ REJECT
              </button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

</div>

<?php include __DIR__ . '/../shared/footer.php'; ?>
