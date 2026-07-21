<?php $title = 'Upload Payment Proof — Musina Gas'; include __DIR__ . '/../shared/header.php'; ?>

<div class="glass-nav">
  <a href="/order/confirm/<?= $order['id'] ?>" style="color: var(--text-muted); font-size: 0.9rem;">&larr; Back</a>
  <div style="font-weight: 700;">EFT Proof Upload</div>
  <div></div>
</div>

<div class="app-viewport" style="padding: 20px;">

  <div class="glass-card" style="border-color: rgba(255,152,0,0.3);">
    <h3 style="font-size: 1rem; font-weight: 700; color: #FF9800; margin-bottom: 8px;">BANK DETAILS FOR EFT</h3>
    <div style="font-size: 0.9rem; line-height: 1.6;">
      <div><strong>Bank:</strong> <?= htmlspecialchars($bank['bank_name']) ?></div>
      <div><strong>Account Name:</strong> <?= htmlspecialchars($bank['account_name']) ?></div>
      <div><strong>Account No:</strong> <?= htmlspecialchars($bank['account_no']) ?></div>
      <div><strong>Branch Code:</strong> <?= htmlspecialchars($bank['branch_code']) ?></div>
      <div style="margin-top: 8px; font-weight: 700; color: var(--primary-color);">
        Reference to use: <?= htmlspecialchars($order['order_ref'] ?? 'MG-00124') ?>
      </div>
      <div><strong>Amount:</strong> R <?= number_format($order['total_amount'] ?? 0, 2) ?></div>
    </div>
  </div>

  <div class="glass-card">
    <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 12px;">UPLOAD PAYMENT SCREENSHOT</h3>

    <form action="/order/<?= $order['id'] ?>/eft-upload" method="POST" enctype="multipart/form-data">
      <div class="form-group">
        <label class="form-label" for="proof_file">Select Image / PDF (Max 5MB)</label>
        <input type="file" id="proof_file" name="proof_file" class="form-control" accept="image/jpeg,image/png,application/pdf" required style="padding: 10px;">
      </div>

      <button type="submit" class="btn btn-primary" style="margin-top: 10px;">SUBMIT PROOF OF PAYMENT</button>
    </form>
  </div>

  <div style="font-size: 0.8rem; color: var(--text-muted); text-align: center;">
    ⚡ Admin verifies EFT payments within 2 business hours.
  </div>

</div>

<?php include __DIR__ . '/../shared/footer.php'; ?>
