<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Product</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= base_url('products'); ?>" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Products
        </a>
    </div>
</div>

<?php if($this->session->flashdata('error')): ?>
    <div class="alert alert-danger">
        <?= $this->session->flashdata('error'); ?>
    </div>
<?php endif; ?>

<?= form_open_multipart('products/edit/' . $product['id']); ?>
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Product Information</h5>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">Product Name *</label>
                        <input type="text" name="name" class="form-control" value="<?= set_value('name', $product['name']); ?>" required>
                        <?= form_error('name', '<small class="text-danger">', '</small>'); ?>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea name="description" class="form-control" rows="5"><?= set_value('description', $product['description']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="price">Price *</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">$</span>
                            </div>
                            <input type="number" name="price" class="form-control" step="0.01" min="0" value="<?= set_value('price', $product['price']); ?>" required>
                        </div>
                        <?= form_error('price', '<small class="text-danger">', '</small>'); ?>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="featured" name="featured" value="1" <?= $product['featured'] ? 'checked' : ''; ?>>
                            <label class="custom-control-label" for="featured">Featured Product</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Variations</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-variation">
                        <i class="fas fa-plus"></i> Add Variation
                    </button>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <p class="mb-0">If your product has variations (like sizes or colors), add them here. Otherwise, just specify the stock for the main product below.</p>
                    </div>

                    <div id="variations-container">
                        <?php if(!empty($variations)): ?>
                            <?php foreach($variations as $index => $variation): ?>
                                <div class="variation-item card mb-3">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="mb-0">Variation #<?= $index + 1; ?></h6>
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-variation">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                        <input type="hidden" name="variation_ids[]" value="<?= $variation['id']; ?>">
                                        <div class="form-group">
                                            <label>Variation Name</label>
                                            <input type="text" name="variation_names[]" class="form-control" value="<?= $variation['name']; ?>">
                                        </div>
                                        <div class="form-group mb-0">
                                            <label>Stock Quantity</label>
                                            <input type="number" name="variation_stocks[]" class="form-control" min="0" value="<?= $variation['stock']; ?>">
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div class="form-group" id="main-stock-group" <?= !empty($variations) ? 'style="display:none;"' : ''; ?>>
                        <label for="stock">Stock Quantity</label>
                        <input type="number" name="stock" class="form-control" min="0" value="<?= isset($stock) ? $stock : '0'; ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Product Image</h5>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="image" name="image">
                            <label class="custom-file-label" for="image">Choose file</label>
                        </div>
                        <small class="form-text text-muted">Recommended size: 800x800px. Max file size: 2MB.</small>
                    </div>

                    <div class="mt-3">
                        <img id="image-preview" src="<?= base_url('assets/images/products/' . $product['image']); ?>" class="img-fluid img-thumbnail" alt="Product Image">
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-save"></i> Update Product
                    </button>
                    <a href="<?= base_url('products'); ?>" class="btn btn-outline-secondary btn-block mt-2">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </div>
        </div>
    </div>
<?= form_close(); ?>
