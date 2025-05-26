// Scripts personalizados para o Mini ERP

// Função para confirmar exclusão
function confirmDelete(url, message) {
  if (confirm(message || 'Are you sure you want to delete this item?')) {
    window.location.href = url;
  }
  return false;
}

// Inicializar tooltips
$(function () {
  $('[data-toggle="tooltip"]').tooltip();
});

// Adicionar campos de variação dinâmicos
$(document).ready(function () {
  // Adicionar nova variação
  $('#add-variation').click(function () {
    var html = '<div class="row variation-row">' +
      '<div class="col-md-6">' +
      '<div class="form-group">' +
      '<input type="text" name="new_variations[]" class="form-control" placeholder="Variation Name">' +
      '</div>' +
      '</div>' +
      '<div class="col-md-4">' +
      '<div class="form-group">' +
      '<input type="number" name="new_stocks[]" class="form-control" placeholder="Stock" min="0">' +
      '</div>' +
      '</div>' +
      '<div class="col-md-2">' +
      '<button type="button" class="btn btn-danger remove-variation">Remove</button>' +
      '</div>' +
      '</div>';
    $('#variations-container').append(html);
  });

  // Remover variação
  $(document).on('click', '.remove-variation', function () {
    $(this).closest('.variation-row').remove();
  });
});
