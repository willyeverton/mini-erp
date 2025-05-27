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
                    <div class="row">
                        <div class="col-md-6">
                            <?php if ($user['role'] == 'admin'): ?>
                            <a href="<?= site_url('products/create') ?>" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add New Product
                            </a>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <form action="<?= site_url('products') ?>" method="get" class="form-inline float-right">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Search..." value="<?= $search ?>">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-default">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            <?= $this->session->flashdata('success') ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            <?= $this->session->flashdata('error') ?>
                        </div>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
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
                                <?php if (empty($products)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No products found.</td>
                                    </tr>
                                <?php else: ?>
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
                                                <a href="<?= site_url('products/view/' . $product['id']); ?>" class="btn btn-info btn-sm">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                <?php if ($user['role'] == 'admin'): ?>
                                                    <a href="<?= site_url('products/edit/' . $product['id']); ?>" class="btn btn-warning btn-sm">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                    <a href="#" class="btn btn-sm btn-danger delete-btn"
                                                       data-delete-url="<?= base_url('products/delete/' . $product['id']); ?>"
                                                       data-confirm-message="Tem certeza que deseja excluir o produto '<?= htmlspecialchars($product['name']); ?>'?">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer clearfix">
                    <div class="float-right">
                        <?= $pagination ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
