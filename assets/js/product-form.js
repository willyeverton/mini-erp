document.addEventListener('DOMContentLoaded', function () {
  // Image preview
  const imageInput = document.getElementById('image');
  if (imageInput) {
    imageInput.addEventListener('change', function () {
      const file = this.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
          document.getElementById('image-preview').src = e.target.result;
        }
        reader.readAsDataURL(file);
        document.querySelector('.custom-file-label').textContent = file.name;
      }
    });
  }

  // Add variation
  const addVariationBtn = document.getElementById('add-variation');
  if (addVariationBtn) {
    let variationIndex = document.querySelectorAll('.variation-item').length;

    addVariationBtn.addEventListener('click', function () {
      const container = document.getElementById('variations-container');
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

      container.insertAdjacentHTML('beforeend', html);
      variationIndex++;

      // Hide main stock field when variations are added
      document.getElementById('main-stock-group').style.display = 'none';

      // Add event listener to remove button
      addRemoveEventListeners();
    });

    // Initial setup for existing remove buttons
    addRemoveEventListeners();
  }

  function addRemoveEventListeners() {
    const removeButtons = document.querySelectorAll('.remove-variation');
    removeButtons.forEach(function (button) {
      // Remove existing event listeners to prevent duplicates
      button.removeEventListener('click', removeVariationHandler);
      // Add new event listener
      button.addEventListener('click', removeVariationHandler);
    });
  }

  function removeVariationHandler() {
    this.closest('.variation-item').remove();

    // If no variations left, show main stock field again
    if (document.querySelectorAll('.variation-item').length === 0) {
      document.getElementById('main-stock-group').style.display = 'block';
    }
  }
});
