$(document).ready(function () {
  // CEP lookup functionality
  $('#zipcode').on('blur', function () {
    var zipcode = $(this).val().replace(/\D/g, '');

    if (zipcode.length === 8) {
      $.getJSON('https://viacep.com.br/ws/' + zipcode + '/json/', function (data) {
        if (!data.erro) {
          $('#address').val(data.logradouro);
          $('#district').val(data.bairro);
          $('#city').val(data.localidade);
          $('#state').val(data.uf);

          // Focus on number field if address was found
          if (data.logradouro) {
            $('#number').focus();
          }
        }
      });
    }
  });

  // Form validation
  $('#checkout-form').on('submit', function (e) {
    var isValid = true;

    // Basic validation
    $(this).find('input[required], select[required]').each(function () {
      if ($(this).val() === '') {
        isValid = false;
        $(this).addClass('is-invalid');
      } else {
        $(this).removeClass('is-invalid');
      }
    });

    if (!isValid) {
      e.preventDefault();
      $('html, body').animate({
        scrollTop: $('.is-invalid:first').offset().top - 100
      }, 500);
    }
  });
});
