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
                    <h3 class="card-title">Informações do Usuário</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-4">ID:</dt>
                                <dd class="col-sm-8"><?= $user_data['id'] ?></dd>

                                <dt class="col-sm-4">Nome:</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($user_data['name']) ?></dd>

                                <dt class="col-sm-4">Email:</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($user_data['email']) ?></dd>

                                <dt class="col-sm-4">Função:</dt>
                                <dd class="col-sm-8">
                                    <span class="badge <?= $user_data['role'] == 'admin' ? 'badge-danger' : 'badge-primary' ?>">
                                        <?= ucfirst($user_data['role']) ?>
                                    </span>
                                </dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-4">Data de Criação:</dt>
                                <dd class="col-sm-8"><?= date('d/m/Y H:i', strtotime($user_data['created_at'])) ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="<?= site_url('users/edit/' . $user_data['id']) ?>" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <a href="<?= site_url('users') ?>" class="btn btn-default">Voltar</a>
                    <?php if ($user_data['id'] != $user['id']): ?>
                        <a href="#" class="btn btn-danger float-right delete-btn"
                            data-delete-url="<?= site_url('users/delete/' . $user_data['id']) ?>"
                            data-confirm-message="Tem certeza que deseja excluir este usuário?">
                            <i class="fas fa-trash"></i> Excluir
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</div>
