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
                            <a href="<?= site_url('users/create') ?>" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Adicionar Novo Usuário
                            </a>
                        </div>
                        <div class="col-md-6">
                            <form action="<?= site_url('users') ?>" method="get" class="form-inline float-right">
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
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>Email</th>
                                    <th>Função</th>
                                    <th>Data de Criação</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($users)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">Nenhum usuário encontrado.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($users as $user_item): ?>
                                        <tr>
                                            <td><?= $user_item['id'] ?></td>
                                            <td><?= htmlspecialchars($user_item['name']) ?></td>
                                            <td><?= htmlspecialchars($user_item['email']) ?></td>
                                            <td>
                                                <span class="badge <?= $user_item['role'] == 'admin' ? 'badge-danger' : 'badge-primary' ?>">
                                                    <?= ucfirst($user_item['role']) ?>
                                                </span>
                                            </td>
                                            <td><?= date('d/m/Y H:i', strtotime($user_item['created_at'])) ?></td>
                                            <td>
                                                <a href="<?= site_url('users/view/' . $user_item['id']) ?>" class="btn btn-info btn-sm">
                                                    <i class="fas fa-eye"></i> Ver
                                                </a>
                                                <a href="<?= site_url('users/edit/' . $user_item['id']) ?>" class="btn btn-warning btn-sm">
                                                    <i class="fas fa-edit"></i> Editar
                                                </a>
                                                <?php if ($user_item['id'] != $user['id']): ?>
                                                    <a href="<?= site_url('users/delete/' . $user_item['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este usuário?')">
                                                        <i class="fas fa-trash"></i> Excluir
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
