$(document).ready(function () {
  // Handle stock update button click
  $('.update-stock').on('click', function () {
    var productId = $(this).data('product-id');
    var variationId = $(this).data('variation-id');
    var currentStock = $(this).data('current-stock');

    $('#modal-product-id').val(productId);
    $('#modal-variation-id').val(variationId);
    $('#modal-current-stock').val(currentStock);
    $('#stock-quantity').val(0);

    $('#stockModal').modal('show');
  });

  // Form validation for stock update
  $('#stock-form').on('submit', function (e) {
    var quantity = parseInt($('#stock-quantity').val());
    var action = $('#stock-action').val();
    var currentStock = parseInt($('#modal-current-stock').val());

    if (quantity < 0) {
      e.preventDefault();
      alert('Quantity cannot be negative.');
      return false;
    }

    if (action === 'subtract' && quantity > currentStock) {
      e.preventDefault();
      alert('Cannot subtract more than current stock.');
      return false;
    }

    return true;
  });
});
