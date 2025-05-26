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
                        <li class="breadcrumb-item"><a href="<?= site_url('users') ?>">Usuários</a></li>
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
                    <h3 class="card-title">Formulário de Edição</h3>
                </div>
                <form action="<?= site_url('users/edit/' . $user_data['id']) ?>" method="post">
                    <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                    <div class="card-body">
                        <?php if (validation_errors()): ?>
                            <div class="alert alert-danger alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                <?= validation_errors() ?>
                            </div>
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="name">Nome Completo*</label>
                            <input type="text" name="name" id="name" class="form-control" required value="<?= set_value('name', $user_data['name']) ?>">
                        </div>

                        <div class="form-group">
                            <label for="email">Email*</label>
                            <input type="email" name="email" id="email" class="form-control" required value="<?= set_value('email', $user_data['email']) ?>">
                        </div>

                        <div class="form-group">
                            <label for="role">Função*</label>
                            <select name="role" id="role" class="form-control" required>
                                <option value="">Selecione...</option>
                                <option value="admin" <?= set_select('role', 'admin', $user_data['role'] == 'admin') ?>>Administrador</option>
                                <option value="customer" <?= set_select('role', 'customer', $user_data['role'] == 'customer') ?>>Cliente</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="password">Senha (deixe em branco para manter a mesma)</label>
                            <input type="password" name="password" id="password" class="form-control">
                            <small class="text-muted">Preencha apenas se deseja alterar a senha.</small>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirmar Senha</label>
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control">
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Atualizar</button>
                        <a href="<?= site_url('users') ?>" class="btn btn-default">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
