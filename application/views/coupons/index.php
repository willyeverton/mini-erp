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
                            <a href="<?= site_url('coupons/create') ?>" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Adicionar Novo Cupom
                            </a>
                        </div>
                        <div class="col-md-6">
                            <form action="<?= site_url('coupons') ?>" method="get" class="form-inline float-right">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Pesquisar..." value="<?= $search ?>">
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
                                    <th>Código</th>
                                    <th>Desconto</th>
                                    <th>Período de Validade</th>
                                    <th>Uso</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($coupons)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">Nenhum cupom encontrado.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($coupons as $coupon): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($coupon['code']) ?></td>
                                            <td>
                                                <?php if ($coupon['type'] == 'percentage'): ?>
                                                    <?= $coupon['discount'] ?>%
                                                    <?php if (isset($coupon['max_discount']) && $coupon['max_discount'] > 0): ?>
                                                        (máx. R$ <?= number_format($coupon['max_discount'], 2, ',', '.') ?>)
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    R$ <?= number_format($coupon['discount'], 2, ',', '.') ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (isset($coupon['start_date']) && $coupon['start_date'] && isset($coupon['expires_at']) && $coupon['expires_at']): ?>
                                                    <?= date('d/m/Y', strtotime($coupon['start_date'])) ?> a <?= date('d/m/Y', strtotime($coupon['expires_at'])) ?>
                                                <?php elseif (isset($coupon['start_date']) && $coupon['start_date']): ?>
                                                    A partir de <?= date('d/m/Y', strtotime($coupon['start_date'])) ?>
                                                <?php elseif (isset($coupon['expires_at']) && $coupon['expires_at']): ?>
                                                    Até <?= date('d/m/Y', strtotime($coupon['expires_at'])) ?>
                                                <?php else: ?>
                                                    Sem data limite
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?= $coupon['usage_count'] ?> /
                                                <?= $coupon['usage_limit'] > 0 ? $coupon['usage_limit'] : '∞' ?>
                                            </td>
                                            <td>
                                                <?php if ($coupon['active']): ?>
                                                    <span class="badge badge-success">Ativo</span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger">Inativo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="<?= site_url('coupons/view/' . $coupon['id']) ?>" class="btn btn-info btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="<?= site_url('coupons/edit/' . $coupon['id']) ?>" class="btn btn-warning btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?= site_url('coupons/toggle_status/' . $coupon['id']) ?>" class="btn <?= $coupon['active'] ? 'btn-success' : 'btn-secondary' ?> btn-sm">
                                                    <i class="fas <?= $coupon['active'] ? 'fa-toggle-on' : 'fa-toggle-off' ?>"></i>
                                                </a>
                                                <a href="<?= site_url('coupons/delete/' . $coupon['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este cupom?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
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
