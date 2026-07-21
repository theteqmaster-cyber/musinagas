<?php $title = 'Order Tracking — Musina Gas'; include __DIR__ . '/../shared/header.php'; ?>

<div class="glass-nav">
  <a href="/orders" style="color: var(--text-muted); font-size: 0.9rem;">&larr; Back</a>
  <div style="font-weight: 700;">Order Tracking</div>
  <div></div>
</div>

<div class="app-viewport" id="trackingContainer" data-order-id="<?= htmlspecialchars($order['id'] ?? '') ?>" style="padding: 20px;">

  <?php
  $status = $order['payment_status'] ?? 'pending';
  $statusMap = [
    'pending' => ['step' => 1, 'title' => 'Order Placed', 'desc' => 'Waiting for processing', 'badge' => 'badge-info'],
    'awaiting_eft' => ['step' => 1, 'title' => 'Awaiting EFT Proof', 'desc' => 'Please upload bank proof', 'badge' => 'badge-warning'],
    'eft_submitted' => ['step' => 2, 'title' => 'EFT Proof Submitted', 'desc' => 'Under admin review', 'badge' => 'badge-info'],
    'eft_rejected' => ['step' => 1, 'title' => 'EFT Rejected', 'desc' => 'Please re-upload proof', 'badge' => 'badge-danger'],
    'verified' => ['step' => 2, 'title' => 'Payment Verified', 'desc' => 'Assigning driver', 'badge' => 'badge-success'],
    'dispatched' => ['step' => 3, 'title' => 'Dispatched', 'desc' => 'Driver en route to your address', 'badge' => 'badge-warning'],
    'arrived' => ['step' => 4, 'title' => 'Driver Arrived', 'desc' => 'Driver at your gate', 'badge' => 'badge-success'],
    'completed' => ['step' => 5, 'title' => 'Delivered & Complete', 'desc' => 'Cylinder delivered safely', 'badge' => 'badge-success'],
    'cancelled' => ['step' => 0, 'title' => 'Cancelled', 'desc' => 'Order was cancelled', 'badge' => 'badge-danger'],
  ];
  $curInfo = $statusMap[$status] ?? $statusMap['pending'];
  ?>

  <div class="glass-card" style="text-align: center; padding: 24px;">
    <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">ORDER REFERENCE</div>
    <div style="font-size: 1.75rem; font-weight: 800; color: var(--primary-color); margin: 4px 0 12px;">
      #<?= htmlspecialchars($order['order_ref'] ?? 'MG-00124') ?>
    </div>
    
    <span class="badge <?= $curInfo['badge'] ?>" id="statusBadge" style="font-size: 0.9rem; padding: 6px 16px;">
      <?= htmlspecialchars(strtoupper(str_replace('_', ' ', $status))) ?>
    </span>

    <div style="font-size: 1rem; font-weight: 700; margin-top: 14px;"><?= $curInfo['title'] ?></div>
    <div style="font-size: 0.85rem; color: var(--text-muted);"><?= $curInfo['desc'] ?></div>
  </div>

  <?php if ($status === 'eft_rejected' || $status === 'awaiting_eft'): ?>
    <div class="alert alert-error">
      <span>⚠️ Payment verification pending.</span>
      <a href="/order/<?= $order['id'] ?>/eft-upload" class="btn btn-primary btn-sm" style="margin-left: auto;">Upload Proof</a>
    </div>
  <?php endif; ?>

  <div class="glass-card">
    <h4 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 12px;">DELIVERY DETAILS</h4>
    <div style="font-size: 0.9rem; margin-bottom: 6px;">📍 <strong>Address:</strong> <?= htmlspecialchars($order['digital_address'] ?? 'Musina Location') ?></div>
    <div style="font-size: 0.9rem; margin-bottom: 6px;">📦 <strong>Item:</strong> <?= htmlspecialchars($order['cylinder_size_kg'] ?? 9) ?>kg Cylinder &times; <?= htmlspecialchars($order['quantity'] ?? 1) ?></div>
    <div style="font-size: 0.9rem;">💳 <strong>Payment:</strong> <?= htmlspecialchars($order['payment_method'] ?? 'EFT') ?> (R <?= number_format($order['total_amount'] ?? 0, 2) ?>)</div>
  </div>

</div>

<?php include __DIR__ . '/../shared/navbar.php'; ?>
<?php include __DIR__ . '/../shared/footer.php'; ?>
