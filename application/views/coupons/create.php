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
                    <h3 class="card-title">Formulário de Cadastro</h3>
                </div>
                <form action="<?= site_url('coupons/create') ?>" method="post">
                    <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                    <div class="card-body">
                        <?php if (validation_errors()): ?>
                            <div class="alert alert-danger alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                <?= validation_errors() ?>
                            </div>
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="code">Código do Cupom*</label>
                                    <input type="text" name="code" id="code" class="form-control" required value="<?= set_value('code') ?>" placeholder="Ex: DESCONTO20">
                                    <small class="text-muted">Código único para o cupom (sem espaços).</small>
                                </div>

                                <div class="form-group">
                                    <label for="description">Descrição</label>
                                    <textarea name="description" id="description" class="form-control" rows="3"><?= set_value('description') ?></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="discount_type">Tipo de Desconto*</label>
                                    <select name="discount_type" id="discount_type" class="form-control" required>
                                        <option value="percentage" <?= set_select('discount_type', 'percentage', TRUE) ?>>Porcentagem (%)</option>
                                        <option value="fixed" <?= set_select('discount_type', 'fixed') ?>>Valor Fixo (R$)</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="discount_amount">Valor do Desconto*</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend discount-symbol" id="discount-symbol-percentage">
                                            <span class="input-group-text">%</span>
                                        </div>
                                        <div class="input-group-prepend discount-symbol" id="discount-symbol-fixed" style="display: none;">
                                            <span class="input-group-text">R$</span>
                                        </div>
                                        <input type="number" name="discount_amount" id="discount_amount" class="form-control" required value="<?= set_value('discount_amount') ?>" step="0.01" min="0.01">
                                    </div>
                                </div>

                                <div class="form-group" id="max-discount-container">
                                    <label for="max_discount">Desconto Máximo (R$)</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">R$</span>
                                        </div>
                                        <input type="number" name="max_discount" id="max_discount" class="form-control" value="<?= set_value('max_discount', '0') ?>" step="0.01" min="0">
                                    </div>
                                    <small class="text-muted">Deixe 0 para sem limite.</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="min_purchase">Valor Mínimo de Compra (R$)</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">R$</span>
                                        </div>
                                        <input type="number" name="min_purchase" id="min_purchase" class="form-control" value="<?= set_value('min_purchase', '0') ?>" step="0.01" min="0">
                                    </div>
                                    <small class="text-muted">Deixe 0 para sem mínimo.</small>
                                </div>

                                <div class="form-group">
                                    <label for="usage_limit">Limite de Uso</label>
                                    <input type="number" name="usage_limit" id="usage_limit" class="form-control" value="<?= set_value('usage_limit', '0') ?>" min="0">
                                    <small class="text-muted">Deixe 0 para uso ilimitado.</small>
                                </div>

                                <div class="form-group">
                                    <label for="start_date">Data de Início</label>
                                    <input type="date" name="start_date" id="start_date" class="form-control" value="<?= set_value('start_date') ?>">
                                </div>

                                <div class="form-group">
                                    <label for="end_date">Data de Término</label>
                                    <input type="date" name="end_date" id="end_date" class="form-control" value="<?= set_value('end_date') ?>">
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="active" name="active" value="1" <?= set_checkbox('active', '1', TRUE) ?>>
                                        <label class="custom-control-label" for="active">Cupom Ativo</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Salvar</button>
                        <a href="<?= site_url('coupons') ?>" class="btn btn-default">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
