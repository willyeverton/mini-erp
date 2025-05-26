$(document).ready(function () {
  // Quantity increase button
  $('.quantity-increase').on('click', function () {
    var input = $(this).closest('.input-group').find('.quantity-input');
    var currentValue = parseInt(input.val());
    input.val(currentValue + 1);
    $(this).closest('form').submit();
  });

  // Quantity decrease button
  $('.quantity-decrease').on('click', function () {
    var input = $(this).closest('.input-group').find('.quantity-input');
    var currentValue = parseInt(input.val());
    if (currentValue > 1) {
      input.val(currentValue - 1);
      $(this).closest('form').submit();
    }
  });

  // Submit form when quantity changes
  $('.quantity-input').on('change', function () {
    $(this).closest('form').submit();
  });
});
