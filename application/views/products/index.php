<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Products</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= base_url('products/create'); ?>" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-plus"></i> Add New Product
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

<div class="table-responsive">
    <?php if(empty($products)): ?>
        <div class="alert alert-info">No products found. <a href="<?= base_url('products/create'); ?>">Add a new product</a>.</div>
    <?php else: ?>
        <table class="table table-striped table-sm">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Featured</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($products as $product): ?>
                    <tr>
                        <td><?= $product['id']; ?></td>
                        <td>
                            <img src="<?= base_url('assets/images/products/' . $product['image']); ?>" alt="<?= $product['name']; ?>" class="img-thumbnail" style="width: 50px;">
                        </td>
                        <td><?= $product['name']; ?></td>
                        <td>$<?= number_format($product['price'], 2); ?></td>
                        <td>
                            <?php if($product['featured']): ?>
                                <span class="badge badge-success">Yes</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">No</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?= base_url('products/view/' . $product['id']); ?>" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?= base_url('products/edit/' . $product['id']); ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="<?= base_url('products/delete/' . $product['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this product?');">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
