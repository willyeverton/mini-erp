/**
 * Coupon Form JavaScript
 */
document.addEventListener('DOMContentLoaded', function () {
  const discountType = document.getElementById('discount_type');
  const percentageSymbol = document.getElementById('discount-symbol-percentage');
  const fixedSymbol = document.getElementById('discount-symbol-fixed');
  const maxDiscountContainer = document.getElementById('max-discount-container');

  function updateDiscountType() {
    if (discountType.value === 'percentage') {
      percentageSymbol.style.display = 'flex';
      fixedSymbol.style.display = 'none';
      maxDiscountContainer.style.display = 'block';
    } else {
      percentageSymbol.style.display = 'none';
      fixedSymbol.style.display = 'flex';
      maxDiscountContainer.style.display = 'none';
    }
  }

  // Inicializar
  updateDiscountType();

  // Evento de mudança
  discountType.addEventListener('change', updateDiscountType);
});
