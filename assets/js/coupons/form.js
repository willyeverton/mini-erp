/**
 * Coupon Form JavaScript
 */
$(document).ready(function () {

  const discountType = $('#discount_type');
  const percentageSymbol = $('#discount-symbol-percentage');
  const fixedSymbol = $('#discount-symbol-fixed');
  const maxDiscountContainer = $('#max-discount-container');


  function updateDiscountType() {
    if (discountType.val() === 'percentage') {
      percentageSymbol.show();
      fixedSymbol.hide();
      maxDiscountContainer.show();
    } else {
      percentageSymbol.hide();
      fixedSymbol.show();
      maxDiscountContainer.hide();
    }
  }

  // Inicializar
  updateDiscountType();

  // Evento de mudança
  discountType.on('change', updateDiscountType);
});
