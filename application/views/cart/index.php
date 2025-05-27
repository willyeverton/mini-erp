<div class="container py-5">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Shopping Cart</h4>
                    <a href="#" class="btn btn-sm btn-outline-danger delete-btn"
                        data-delete-url="<?= base_url('cart/clear') ?>"
                        data-confirm-message="Tem certeza que deseja limpar seu carrinho?">
                        <i class="fas fa-trash"></i> Clear Cart
                    </a>
                </div>
                <div class="card-body">
                    <?php if($this->session->flashdata('success')): ?>
                        <div class="alert alert-success">
                            <?= $this->session->flashdata('success'); ?>
                        </div>
                    <?php endif; ?>

                    <?php if($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger">
                            <?= $this->session->flashdata('error'); ?>
                        </div>
                    <?php endif; ?>

                    <?php if(empty($cart_items)): ?>
                        <div class="alert alert-info">
                            Your cart is empty. <a href="<?= base_url('products'); ?>">Continue shopping</a>.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Subtotal</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($cart_items as $item): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="<?= base_url('assets/images/products/' . $item['image']); ?>" alt="<?= $item['product_name']; ?>" class="img-thumbnail mr-3" style="width: 50px;">
                                                    <div>
                                                        <h6 class="mb-0"><?= $item['product_name']; ?></h6>
                                                        <?php if($item['variation_name']): ?>
                                                            <small class="text-muted">Variation: <?= $item['variation_name']; ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>$<?= number_format($item['price'], 2); ?></td>
                                            <td>
                                                <?= form_open('cart/update', ['class' => 'cart-update-form']); ?>
                                                    <input type="hidden" name="cart_id" value="<?= $item['id']; ?>">
                                                    <div class="input-group input-group-sm" style="width: 100px;">
                                                        <div class="input-group-prepend">
                                                            <button type="button" class="btn btn-outline-secondary quantity-decrease">
                                                                <i class="fas fa-minus"></i>
                                                            </button>
                                                        </div>
                                                        <input type="number" name="quantity" class="form-control text-center quantity-input" value="<?= $item['quantity']; ?>" min="1">
                                                        <div class="input-group-append">
                                                            <button type="button" class="btn btn-outline-secondary quantity-increase">
                                                                <i class="fas fa-plus"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                <?= form_close(); ?>
                                            </td>
                                            <td>$<?= number_format($item['price'] * $item['quantity'], 2); ?></td>
                                            <td>
                                                <a href="#" class="btn btn-sm btn-danger delete-btn"
                                                   data-delete-url="<?= site_url('cart/remove/' . $item['id']) ?>"
                                                   data-confirm-message="Tem certeza que deseja remover o item (<?= $item['product_name']; ?>) do carrinho?">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span>$<?= number_format($subtotal, 2); ?></span>
                    </div>

                    <?php if(isset($discount) && $discount > 0): ?>
                        <div class="d-flex justify-content-between mb-2 text-success">
                            <span>Discount:</span>
                            <span>-$<?= number_format($discount, 2); ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Shipping:</span>
                        <span><?= $shipping > 0 ? '$' . number_format($shipping, 2) : 'Free'; ?></span>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between mb-3">
                        <strong>Total:</strong>
                        <strong>$<?= number_format($total, 2); ?></strong>
                    </div>

                    <?php if(!empty($cart_items)): ?>
                        <a href="<?= base_url('orders/checkout'); ?>" class="btn btn-primary btn-block">
                            <i class="fas fa-credit-card"></i> Proceed to Checkout
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Coupon Code</h5>
                </div>
                <div class="card-body">
                    <?php if(isset($coupon)): ?>
                        <div class="alert alert-success">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?= isset($coupon['code']) ? $coupon['code'] : 'Coupon'; ?></strong>
                                    <p class="mb-0 small">
                                        <?php
                                        $coupon_type = isset($coupon['type']) ? $coupon['type'] :
                                                      (isset($coupon['discount_type']) ? $coupon['discount_type'] : 'fixed');
                                        $discount_value = isset($coupon['discount']) ? $coupon['discount'] :
                                                      (isset($coupon['discount_amount']) ? $coupon['discount_amount'] : 0);

                                        echo $coupon_type == 'percentage' ?
                                            $discount_value . '% off' :
                                            number_format($discount_value, 2) . ' off';
                                        ?>
                                    </p>
                                </div>
                                <a href="<?= base_url('cart/remove_coupon'); ?>" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <?= form_open('cart/apply_coupon', ['id' => 'coupon-form']); ?>
                            <div class="input-group">
                                <input type="text" name="coupon_code" class="form-control" placeholder="Enter coupon code">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-outline-secondary">Apply</button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
