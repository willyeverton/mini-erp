<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?= $product['name']; ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <?php if($this->session->userdata('role') == 'admin'): ?>
            <a href="<?= base_url('products/edit/' . $product['id']); ?>" class="btn btn-sm btn-outline-primary mr-2">
                <i class="fas fa-edit"></i> Edit
            </a>
        <?php endif; ?>
        <a href="<?= base_url('products'); ?>" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Products
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <img src="<?= base_url('assets/images/products/' . $product['image']); ?>" class="card-img-top" alt="<?= $product['name']; ?>">
            <div class="card-body">
                <h5 class="card-title"><?= $product['name']; ?></h5>
                <h6 class="card-subtitle mb-2 text-muted">$<?= number_format($product['price'], 2); ?></h6>

                <?php if($product['featured']): ?>
                    <span class="badge badge-success">Featured</span>
                <?php endif; ?>

                <p class="card-text mt-3"><?= nl2br($product['description']); ?></p>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Product Details</h5>
            </div>
            <div class="card-body">
                <?= form_open('cart/add'); ?>
                    <input type="hidden" name="product_id" value="<?= $product['id']; ?>">

                    <?php if(!empty($variations)): ?>
                        <div class="form-group">
                            <label for="variation_id">Select Variation</label>
                            <select name="variation_id" class="form-control" required>
                                <option value="">-- Select --</option>
                                <?php foreach($variations as $variation): ?>
                                    <option value="<?= $variation['id']; ?>">
                                        <?= $variation['name']; ?>
                                        (<?php if($variation['stock'] <= 0): ?>Out of Stock<?php else: ?>In Stock: <?= $variation['stock']; ?><?php endif; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php else: ?>
                        <div class="form-group">
                            <label>Stock</label>
                            <p><?php if(isset($stock) && $stock > 0): ?><span class="badge badge-success">In Stock: <?= $stock; ?></span><?php else: ?><span class="badge badge-danger">Out of Stock</span><?php endif; ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="quantity">Quantity</label>
                        <input type="number" name="quantity" class="form-control" min="1" value="1" max="<?= isset($stock) ? $stock : ''; ?>">
                    </div>

                    <button type="submit" class="btn btn-primary" <?= (isset($stock) && $stock <= 0) || (empty($stock) && empty($variations)) ? 'disabled' : ''; ?>>
                        <i class="fas fa-cart-plus"></i> Add to Cart
                    </button>
                <?= form_close(); ?>
            </div>
        </div>

        <?php if($this->session->userdata('role') == 'admin'): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Stock Management</h5>
                </div>
                <div class="card-body">
                    <?php if(!empty($variations)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Variation</th>
                                        <th>Stock</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($variations as $variation): ?>
                                        <tr>
                                            <td><?= $variation['name']; ?></td>
                                            <td>
                                                <?php if($variation['stock'] <= 0): ?>
                                                    <span class="badge badge-danger">Out of Stock</span>
                                                <?php else: ?>
                                                    <span class="badge badge-success"><?= $variation['stock']; ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-primary update-stock"
                                                        data-product-id="<?= $product['id']; ?>"
                                                        data-variation-id="<?= $variation['id']; ?>"
                                                        data-current-stock="<?= $variation['stock']; ?>">
                                                    <i class="fas fa-sync-alt"></i> Update Stock
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="form-group">
                            <label>Current Stock</label>
                            <p>
                                <?php if(isset($stock) && $stock > 0): ?>
                                    <span class="badge badge-success"><?= $stock; ?></span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Out of Stock</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <button type="button" class="btn btn-primary update-stock"
                                data-product-id="<?= $product['id']; ?>"
                                data-variation-id=""
                                data-current-stock="<?= isset($stock) ? $stock : 0; ?>">
                            <i class="fas fa-sync-alt"></i> Update Stock
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Stock Update Modal -->
<div class="modal fade" id="stockModal" tabindex="-1" role="dialog" aria-labelledby="stockModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="stockModalLabel">Update Stock</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?= form_open('stock/update', ['id' => 'stock-form']); ?>
                <div class="modal-body">
                    <input type="hidden" name="product_id" id="modal-product-id">
                    <input type="hidden" name="variation_id" id="modal-variation-id">

                    <div class="form-group">
                        <label for="current_stock">Current Stock</label>
                        <input type="text" class="form-control" id="modal-current-stock" readonly>
                    </div>

                    <div class="form-group">
                        <label for="stock_action">Action</label>
                        <select name="stock_action" id="stock-action" class="form-control">
                            <option value="set">Set to specific value</option>
                            <option value="add">Add to current stock</option>
                            <option value="subtract">Subtract from current stock</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="stock_quantity">Quantity</label>
                        <input type="number" name="stock_quantity" id="stock-quantity" class="form-control" min="0" value="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Stock</button>
                </div>
            <?= form_close(); ?>
        </div>
    </div>
</div>
