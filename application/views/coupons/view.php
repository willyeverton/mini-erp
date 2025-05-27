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
                        <li class="breadcrumb-item"><a href="<?= site_url('coupons') ?>">Cupons</a></li>
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
                    <h3 class="card-title">Informações do Cupom</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h3 class="card-title">Informações Básicas</h3>
                                </div>
                                <div class="card-body">
                                    <dl class="row">
                                        <dt class="col-sm-4">Código:</dt>
                                        <dd class="col-sm-8"><strong><?= htmlspecialchars($coupon['code']) ?></strong></dd>

                                        <dt class="col-sm-4">Descrição:</dt>
                                        <dd class="col-sm-8"><?= htmlspecialchars($coupon['description']) ?: '<i>Sem descrição</i>' ?></dd>

                                        <dt class="col-sm-4">Status:</dt>
                                        <dd class="col-sm-8">
                                            <?php if ($coupon['active']): ?>
                                                <span class="badge badge-success">Ativo</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Inativo</span>
                                            <?php endif; ?>
                                        </dd>

                                        <dt class="col-sm-4">Criado em:</dt>
                                        <dd class="col-sm-8"><?= date('d/m/Y H:i', strtotime($coupon['created_at'])) ?></dd>
                                    </dl>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h3 class="card-title">Desconto</h3>
                                </div>
                                <div class="card-body">
                                    <dl class="row">
                                        <dt class="col-sm-4">Tipo:</dt>
                                        <dd class="col-sm-8">
                                            <?php if ($coupon['type'] == 'percentage'): ?>
                                                <span class="badge badge-info">Porcentagem</span>
                                            <?php else: ?>
                                                <span class="badge badge-info">Valor Fixo</span>
                                            <?php endif; ?>
                                        </dd>

                                        <dt class="col-sm-4">Valor:</dt>
                                        <dd class="col-sm-8">
                                            <?php if ($coupon['type'] == 'percentage'): ?>
                                                <strong><?= $coupon['discount'] ?>%</strong>
                                            <?php else: ?>
                                                <strong>R$ <?= number_format($coupon['discount'], 2, ',', '.') ?></strong>
                                            <?php endif; ?>
                                        </dd>

                                        <?php if ($coupon['type'] == 'percentage' && isset($coupon['max_discount']) && $coupon['max_discount'] > 0): ?>
                                            <dt class="col-sm-4">Máximo:</dt>
                                            <dd class="col-sm-8">R$ <?= number_format($coupon['max_discount'], 2, ',', '.') ?></dd>
                                        <?php endif; ?>

                                        <?php if (isset($coupon['minimum_value']) && $coupon['minimum_value'] > 0): ?>
                                            <dt class="col-sm-4">Mínimo:</dt>
                                            <dd class="col-sm-8">R$ <?= number_format($coupon['minimum_value'], 2, ',', '.') ?></dd>
                                        <?php endif; ?>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h3 class="card-title">Validade</h3>
                                </div>
                                <div class="card-body">
                                    <dl class="row">
                                        <dt class="col-sm-4">Início:</dt>
                                        <dd class="col-sm-8">
                                            <?= $coupon['start_date'] ? date('d/m/Y', strtotime($coupon['start_date'])) : '<i>Sem data definida</i>' ?>
                                        </dd>

                                        <dt class="col-sm-4">Término:</dt>
                                        <dd class="col-sm-8">
                                            <?= $coupon['expires_at'] ? date('d/m/Y', strtotime($coupon['expires_at'])) : '<i>Sem data definida</i>' ?>
                                        </dd>

                                        <?php
                                        $today = date('Y-m-d');
                                        $expired = (isset($coupon['expires_at']) && $coupon['expires_at'] && date('Y-m-d', strtotime($coupon['expires_at'])) < $today);
                                        $notStarted = (isset($coupon['start_date']) && $coupon['start_date'] && $coupon['start_date'] > $today);
                                        ?>

                                        <dt class="col-sm-4">Situação:</dt>
                                        <dd class="col-sm-8">
                                            <?php if ($expired): ?>
                                                <span class="badge badge-danger">Expirado</span>
                                            <?php elseif ($notStarted): ?>
                                                <span class="badge badge-warning">Não iniciado</span>
                                            <?php else: ?>
                                                <span class="badge badge-success">No período</span>
                                            <?php endif; ?>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h3 class="card-title">Utilização</h3>
                                </div>
                                <div class="card-body">
                                    <dl class="row">
                                        <dt class="col-sm-4">Usos:</dt>
                                        <dd class="col-sm-8"><?= $coupon['usage_count'] ?></dd>

                                        <dt class="col-sm-4">Limite:</dt>
                                        <dd class="col-sm-8">
                                            <?= $coupon['usage_limit'] > 0 ? $coupon['usage_limit'] : '<i>Ilimitado</i>' ?>
                                        </dd>

                                        <?php if ($coupon['usage_limit'] > 0): ?>
                                            <dt class="col-sm-4">Restantes:</dt>
                                            <dd class="col-sm-8">
                                                <?php $remaining = $coupon['usage_limit'] - $coupon['usage_count']; ?>
                                                <?php if ($remaining <= 0): ?>
                                                    <span class="badge badge-danger">Esgotado</span>
                                                <?php elseif ($remaining <= 5): ?>
                                                    <span class="badge badge-warning"><?= $remaining ?></span>
                                                <?php else: ?>
                                                    <span class="badge badge-info"><?= $remaining ?></span>
                                                <?php endif; ?>
                                            </dd>
                                        <?php endif; ?>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="<?= site_url('coupons/edit/' . $coupon['id']) ?>" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <a href="<?= site_url('coupons') ?>" class="btn btn-default">Voltar</a>
                    <a href="#" class="btn btn-danger float-right delete-btn"
                        data-delete-url="<?= site_url('coupons/delete/' . $coupon['id']) ?>"
                        data-confirm-message="Tem certeza que deseja excluir este cupom?">
                        <i class="fas fa-trash"></i> Excluir
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>
