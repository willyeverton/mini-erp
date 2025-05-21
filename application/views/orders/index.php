<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?= $this->user['role'] == 'admin' ? 'All Orders' : 'My Orders'; ?></h1>
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

<div class="card">
    <div class="card-body">
        <?php if(empty($orders)): ?>
            <div class="alert alert-info">
                No orders found.
                <?php if($this->user['role'] != 'admin'): ?>
                    <a href="<?= base_url('products'); ?>">Start shopping</a>.
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <?php if($this->user['role'] == 'admin'): ?>
                                <th>Customer</th>
                            <?php endif; ?>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($orders as $order): ?>
                            <tr>
                                <td><?= $order['id']; ?></td>
                                <?php if($this->user['role'] == 'admin'): ?>
                                    <td><?= $order['user_name']; ?></td>
                                <?php endif; ?>
                                <td><?= date('M d, Y', strtotime($order['created_at'])); ?></td>
                                <td>$<?= number_format($order['total'], 2); ?></td>
                                <td>
                                    <?php
                                    $status_class = '';
                                    switch($order['status']) {
                                        case 'pending':
                                            $status_class = 'badge-warning';
                                            break;
                                        case 'processing':
                                            $status_class = 'badge-info';
                                            break;
                                        case 'shipped':
                                            $status_class = 'badge-primary';
                                            break;
                                        case 'delivered':
                                            $status_class = 'badge-success';
                                            break;
                                        case 'canceled':
                                            $status_class = 'badge-danger';
                                            break;
                                    }
                                    ?>
                                    <span class="badge <?= $status_class; ?>"><?= ucfirst($order['status']); ?></span>
                                </td>
                                <td>
                                    <a href="<?= base_url('orders/view/' . $order['id']); ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> View
                                    </a>

                                    <?php if($order['status'] == 'pending'): ?>
                                        <a href="<?= base_url('orders/cancel/' . $order['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to cancel this order?');">
                                            <i class="fas fa-times"></i> Cancel
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
