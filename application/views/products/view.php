<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><?= $product['name'] ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= site_url('products') ?>">Products</a></li>
                        <li class="breadcrumb-item active"><?= $product['name'] ?></li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <img src="<?= base_url('assets/images/products/' . $product['image']); ?>" class="img-fluid mb-3" alt="<?= $product['name']; ?>">
                            <h3><?= $product['name']; ?></h3>
                            <h4 class="text-primary">$<?= number_format($product['price'], 2); ?></h4>
                            <?php if($product['featured']): ?>
                                <span class="badge badge-success">Featured</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header">
                            <h3 class="card-title">Description</h3>
                        </div>
                        <div class="card-body">
                            <?= nl2br(htmlspecialchars($product['description'])); ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Product Details</h3>
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
                                        <p>
                                            <?php if(isset($stock) && $stock > 0): ?>
                                                <span class="badge badge-success">In Stock: <?= $stock; ?></span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Out of Stock</span>
                                            <?php endif; ?>
                                        </p>
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

                    <?php if($user && $user['role'] == 'admin'): ?>
                        <div class="card mt-3">
                            <div class="card-header">
                                <h3 class="card-title">Stock Management</h3>
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
                            <div class="card-footer">
                                <a href="<?= site_url('products/edit/' . $product['id']); ?>" class="btn btn-warning">
                                    <i class="fas fa-edit"></i> Edit Product
                                </a>
                                <a href="<?= site_url('products/delete/' . $product['id']); ?>" class="btn btn-danger float-right" onclick="return confirm('Are you sure you want to delete this product?');">
                                    <i class="fas fa-trash"></i> Delete Product
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</div>

<?php if($user && $user['role'] == 'admin'): ?>
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
<?php endif; ?>
