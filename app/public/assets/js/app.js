// Musina Gas v2 Interactive Scripts

document.addEventListener('DOMContentLoaded', () => {
  // Stepper logic for order cylinder quantity
  const stepperVal = document.getElementById('qtyVal');
  const qtyInput = document.getElementById('qtyInput');
  const btnMinus = document.getElementById('btnMinus');
  const btnPlus = document.getElementById('btnPlus');
  const summaryQty = document.getElementById('summaryQty');
  const summaryTotal = document.getElementById('summaryTotal');

  if (stepperVal && qtyInput) {
    let currentQty = parseInt(qtyInput.value) || 1;
    const maxQty = parseInt(qtyInput.getAttribute('max')) || 10;
    const sizeKg = parseFloat(qtyInput.getAttribute('data-size-kg')) || 9;
    const pricePerKg = parseFloat(qtyInput.getAttribute('data-price-per-kg')) || 32.50;

    function updateSummary() {
      stepperVal.textContent = currentQty;
      qtyInput.value = currentQty;
      if (summaryQty) summaryQty.textContent = currentQty;
      if (summaryTotal) {
        const total = (sizeKg * pricePerKg * currentQty).toFixed(2);
        summaryTotal.textContent = `R ${total}`;
      }
    }

    if (btnMinus) {
      btnMinus.addEventListener('click', () => {
        if (currentQty > 1) {
          currentQty--;
          updateSummary();
        }
      });
    }

    if (btnPlus) {
      btnPlus.addEventListener('click', () => {
        if (currentQty < maxQty) {
          currentQty++;
          updateSummary();
        }
      });
    }
  }

  // Cylinder Selection Card Toggle
  const cylinderCards = document.querySelectorAll('.cylinder-card');
  cylinderCards.forEach(card => {
    card.addEventListener('click', () => {
      cylinderCards.forEach(c => c.classList.remove('selected'));
      card.classList.add('selected');
      const radio = card.querySelector('input[type="radio"]');
      if (radio) radio.checked = true;
    });
  });

  // Auto Polling for Order Tracking Screen
  const trackingContainer = document.getElementById('trackingContainer');
  if (trackingContainer) {
    const orderId = trackingContainer.getAttribute('data-order-id');
    if (orderId) {
      setInterval(async () => {
        try {
          const res = await fetch(`/api/order-status/${orderId}`);
          if (res.ok) {
            const data = await res.json();
            if (data.status) {
              const statusBadge = document.getElementById('statusBadge');
              if (statusBadge && statusBadge.textContent !== data.status) {
                location.reload();
              }
            }
          }
        } catch (e) {
          console.error('Polling error', e);
        }
      }, 15000); // Poll every 15 seconds
    }
  }
});
