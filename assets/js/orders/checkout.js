$(document).ready(function () {
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
