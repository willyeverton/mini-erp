<div class="container py-5">
    <div class="text-center mb-5">
        <div class="mb-4">
            <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
        </div>
        <h1>Thank You for Your Order!</h1>
        <p class="lead">Your order has been placed successfully.</p>
        <p>Order #<?= $order['id']; ?></p>
    </div>

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Order Details</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Shipping Information</h6>
                            <p class="mb-0"><?= $order['address']; ?>, <?= $order['number']; ?></p>
                            <?php if($order['complement']): ?>
                                <p class="mb-0"><?= $order['complement']; ?></p>
                            <?php endif; ?>
                            <p class="mb-0"><?= $order['district']; ?></p>
                            <p class="mb-0"><?= $order['city']; ?> - <?= $order['state']; ?></p>
                            <p class="mb-0">CEP: <?= $order['zipcode']; ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Order Information</h6>
                            <p class="mb-0">Date: <?= date('M d, Y', strtotime($order['created_at'])); ?></p>
                            <p class="mb-0">Status: <span class="badge badge-primary"><?= ucfirst($order['status']); ?></span></p>
                            <p class="mb-0">Payment Method: Credit Card</p>
                        </div>
                    </div>

                    <h6>Order Items</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th class="text-center">Price</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($items as $item): ?>
                                    <tr>
                                        <td>
                                            <?= $item['product_name']; ?>
                                            <?php if($item['variation_name']): ?>
                                                <br><small class="text-muted">Variation: <?= $item['variation_name']; ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">$<?= number_format($item['price'], 2); ?></td>
                                        <td class="text-center"><?= $item['quantity']; ?></td>
                                        <td class="text-right">$<?= number_format($item['subtotal'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-right">Subtotal</td>
                                    <td class="text-right">$<?= number_format($order['subtotal'], 2); ?></td>
                                </tr>
                                <?php if($order['discount'] > 0): ?>
                                    <tr>
                                        <td colspan="3" class="text-right text-success">Discount</td>
                                        <td class="text-right text-success">-$<?= number_format($order['discount'], 2); ?></td>
                                    </tr>
                                <?php endif; ?>
                                <tr>
                                    <td colspan="3" class="text-right">Shipping</td>
                                    <td class="text-right"><?= $order['shipping'] > 0 ? '$' . number_format($order['shipping'], 2) : 'Free'; ?></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-right"><strong>Total</strong></td>
                                    <td class="text-right"><strong>$<?= number_format($order['total'], 2); ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="<?= base_url('orders'); ?>" class="btn btn-primary">
                    <i class="fas fa-list"></i> View All Orders
                </a>
                <a href="<?= base_url('products'); ?>" class="btn btn-outline-secondary ml-2">
                    <i class="fas fa-shopping-cart"></i> Continue Shopping
                </a>
            </div>
        </div>
    </div>
</div>
