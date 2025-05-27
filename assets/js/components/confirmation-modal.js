/**
 * Componente de confirmação de exclusão
 * Este componente cria uma modal Bootstrap para confirmação de exclusão
 * e a associa a elementos com a classe 'delete-btn'
 */
(function ($) {
  'use strict';

  // Inicializar o componente
  function init() {
    // Associar evento a todos os botões de exclusão
    $(document).on('click', '.delete-btn', function (e) {
      e.preventDefault();

      const deleteUrl = $(this).data('delete-url');
      const message = $(this).data('confirm-message') || 'Tem certeza que deseja excluir este item?';

      $('#deleteConfirmModalBody').html(message);
      $('#deleteConfirmBtn').attr('href', deleteUrl);

      $('#deleteConfirmModal').modal('show');
    });
  }

  // Inicializar quando o documento estiver pronto
  $(document).ready(init);

})(jQuery);
