<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><?= $title ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= site_url('products') ?>">Products</a></li>
                        <li class="breadcrumb-item active"><?= $title ?></li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Product Form</h3>
                </div>
                <form action="<?= site_url('products/create') ?>" method="post" enctype="multipart/form-data">
                    <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                    <div class="card-body">
                        <?php if (validation_errors()): ?>
                            <div class="alert alert-danger alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                <?= validation_errors() ?>
                            </div>
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="name">Product Name *</label>
                                    <input type="text" name="name" id="name" class="form-control" required value="<?= set_value('name') ?>">
                                </div>

                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea name="description" id="description" class="form-control" rows="5"><?= set_value('description') ?></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="price">Price *</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">$</span>
                                        </div>
                                        <input type="number" name="price" id="price" class="form-control" step="0.01" min="0" required value="<?= set_value('price') ?>">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="featured" name="featured" value="1" <?= set_checkbox('featured', '1') ?>>
                                        <label class="custom-control-label" for="featured">Featured Product</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="image">Product Image</label>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="image" name="image">
                                            <label class="custom-file-label" for="image">Choose file</label>
                                        </div>
                                    </div>
                                    <small class="text-muted">Recommended size: 800x800px. Max file size: 2MB.</small>
                                    <div class="mt-3">
                                        <img id="image-preview" class="img-fluid img-thumbnail">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card card-outline card-info mt-4">
                            <div class="card-header">
                                <h3 class="card-title">
                                    Variations & Stock
                                    <button type="button" class="btn btn-sm btn-outline-primary ml-2" id="add-variation">
                                        <i class="fas fa-plus"></i> Add Variation
                                    </button>
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <p class="mb-0">If your product has variations (like sizes or colors), add them here. Otherwise, just specify the stock for the main product below.</p>
                                </div>

                                <div id="variations-container">
                                    <!-- Variations will be added here dynamically -->
                                </div>

                                <div class="form-group" id="main-stock-group">
                                    <label for="stock">Stock Quantity</label>
                                    <input type="number" name="stock" id="stock" class="form-control" min="0" value="<?= set_value('stock', '0') ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Save Product</button>
                        <a href="<?= site_url('products') ?>" class="btn btn-default">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
