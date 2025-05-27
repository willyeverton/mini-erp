<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Order #<?= $order['id']; ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= base_url('orders'); ?>" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Orders
        </a>
    </div>
</div>

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

<div class="row">
    <div class="col-md-8">
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
                        <p class="mb-0">Status: <span class="badge badge-<?= $order['status'] == 'pending' ? 'warning' : ($order['status'] == 'canceled' ? 'danger' : 'success'); ?>"><?= ucfirst($order['status']); ?></span></p>
                        <p class="mb-0">Payment Method: Credit Card</p>

                        <?php if($user['role'] == 'admin'): ?>
                            <h6 class="mt-3">Customer Information</h6>
                            <p class="mb-0">Name: <?= $order['user_name']; ?></p>
                            <p class="mb-0">Email: <?= $order['user_email']; ?></p>
                        <?php endif; ?>
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
    </div>

    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Order Actions</h5>
            </div>
            <div class="card-body">
                <?php if($order['status'] == 'pending'): ?>
                    <a href="#" class="btn btn-danger btn-block delete-btn"
                        data-delete-url="<?= site_url('orders/cancel/' . $order['id']) ?>"
                        data-confirm-message="Tem certeza que deseja cancelar este pedido?">
                        <i class="fas fa-times"></i> Cancel Order
                    </a>
                <?php endif; ?>

                <?php if($user['role'] == 'admin' && $order['status'] != 'canceled'): ?>
                    <?= form_open('orders/update_status/' . $order['id'], ['class' => 'mt-3']); ?>
                        <div class="form-group">
                            <label for="status">Update Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="processing" <?= $order['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                <option value="shipped" <?= $order['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                <option value="delivered" <?= $order['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                <option value="canceled" <?= $order['status'] == 'canceled' ? 'selected' : ''; ?>>Canceled</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-save"></i> Update Status
                        </button>
                    <?= form_close(); ?>
                <?php endif; ?>
            </div>
        </div>

        <?php if($order['status'] == 'shipped'): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Tracking Information</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">Tracking Number: <?= isset($order['tracking_number']) ? $order['tracking_number'] : 'Not available'; ?></p>
                    <p class="mb-0">Carrier: <?= isset($order['carrier']) ? $order['carrier'] : 'Not available'; ?></p>

                    <?php if($user['role'] == 'admin'): ?>
                        <?= form_open('orders/update_tracking/' . $order['id'], ['class' => 'mt-3']); ?>
                            <div class="form-group">
                                <label for="tracking_number">Tracking Number</label>
                                <input type="text" name="tracking_number" id="tracking_number" class="form-control" value="<?= isset($order['tracking_number']) ? $order['tracking_number'] : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label for="carrier">Carrier</label>
                                <input type="text" name="carrier" id="carrier" class="form-control" value="<?= isset($order['carrier']) ? $order['carrier'] : ''; ?>">
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-save"></i> Update Tracking
                            </button>
                        <?= form_close(); ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
