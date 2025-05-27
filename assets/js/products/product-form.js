$(document).ready(function () {
  // Image preview
  const imageInput = $('#image');
  if (imageInput.length) {
    imageInput.on('change', function () {
      const file = this.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
          $('#image-preview').attr('src', e.target.result);
        }
        reader.readAsDataURL(file);
        $('#image-preview').attr('src', e.target.result);
        $('.custom-file-label').text(file.name);
      }
    });
  }

  // Add variation
  const addVariationBtn = $('#add-variation');
  if (addVariationBtn.length) {
    let variationIndex = $('.variation-item').length;

    addVariationBtn.on('click', function () {
      const container = $('#variations-container');
      const html = `
                <div class="variation-item card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">Variation #${variationIndex + 1}</h6>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-variation">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="form-group">
                            <label>Variation Name</label>
                            <input type="text" name="new_variations[${variationIndex}]" class="form-control" placeholder="e.g. Small, Red, etc.">
                        </div>
                        <div class="form-group mb-0">
                            <label>Stock Quantity</label>
                            <input type="number" name="new_stocks[${variationIndex}]" class="form-control" min="0" value="0">
                        </div>
                    </div>
                </div>
            `;

      container.append(html);
      variationIndex++;

      // Hide main stock field when variations are added
      $('#main-stock-group').hide();

      // Add event listener to remove button
      addRemoveEventListeners();
    });

    // Initial setup for existing remove buttons
    addRemoveEventListeners();
  }

  function addRemoveEventListeners() {
    const removeButtons = $('.remove-variation');
    removeButtons.each(function () {
      // Remove existing event listeners to prevent duplicates
      $(this).off('click').on('click', removeVariationHandler);
    });
  }

  function removeVariationHandler() {
    $(this).closest('.variation-item').remove();

    // If no variations left, show main stock field again
    if ($('.variation-item').length === 0) {
      $('#main-stock-group').show();
    }
  }
});
