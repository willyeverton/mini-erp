/**
 * Product Stock Management JavaScript
 */
$(document).ready(function () {
  $('.update-stock').click(function () {
    var productId = $(this).data('product-id');
    var variationId = $(this).data('variation-id');
    var currentStock = $(this).data('current-stock');

    $('#modal-product-id').val(productId);
    $('#modal-variation-id').val(variationId);
    $('#modal-current-stock').val(currentStock);
    $('#stockModal').modal('show');
  });
});
