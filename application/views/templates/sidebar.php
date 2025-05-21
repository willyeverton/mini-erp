<div class="container-fluid">
    <div class="row">
        <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
            <div class="sidebar-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?= $this->router->fetch_class() == 'dashboard' ? 'active' : ''; ?>" href="<?= base_url('dashboard'); ?>">
                            <i class="fas fa-tachometer-alt"></i>
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $this->router->fetch_class() == 'products' ? 'active' : ''; ?>" href="<?= base_url('products'); ?>">
                            <i class="fas fa-box"></i>
                            Products
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $this->router->fetch_class() == 'orders' ? 'active' : ''; ?>" href="<?= base_url('orders'); ?>">
                            <i class="fas fa-shopping-cart"></i>
                            Orders
                        </a>
                    </li>
                    <?php if ($this->user && $this->user['role'] == 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $this->router->fetch_class() == 'users' ? 'active' : ''; ?>" href="<?= base_url('users'); ?>">
                                <i class="fas fa-users"></i>
                                Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $this->router->fetch_class() == 'coupons' ? 'active' : ''; ?>" href="<?= base_url('coupons'); ?>">
                                <i class="fas fa-tag"></i>
                                Coupons
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>

                <?php if ($this->user && $this->user['role'] == 'admin'): ?>
                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                        <span>Reports</span>
                    </h6>
                    <ul class="nav flex-column mb-2">
                        <li class="nav-item">
                            <a class="nav-link <?= $this->router->fetch_class() == 'dashboard' && $this->router->fetch_method() == 'reports' ? 'active' : ''; ?>" href="<?= base_url('dashboard/reports'); ?>">
                                <i class="fas fa-file-alt"></i>
                                Sales Reports
                            </a>
                        </li>
                    </ul>
                <?php endif; ?>
            </div>
        </nav>

        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
            <!-- O conteúdo principal será carregado aqui -->
