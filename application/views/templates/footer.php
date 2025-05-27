</div> <!-- Fim da row -->

<div class="row">
    <div class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
        <footer class="bg-dark text-white py-4 mt-5">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Mini ERP</h5>
                        <p>A simple ERP system with e-commerce capabilities.</p>
                    </div>
                    <div class="col-md-3">
                        <h5>Links</h5>
                        <ul class="list-unstyled">
                            <li><a href="<?= base_url(); ?>" class="text-white">Home</a></li>
                            <li><a href="<?= base_url('products'); ?>" class="text-white">Products</a></li>
                            <li><a href="<?= base_url('orders'); ?>" class="text-white">Orders</a></li>
                        </ul>
                    </div>
                    <div class="col-md-3">
                        <h5>Contact</h5>
                        <address>
                            <p>123 Main Street<br>City, State 12345</p>
                            <p>Email: info@minierp.com<br>Phone: (123) 456-7890</p>
                        </address>
                    </div>
                </div>
                <hr>
                <div class="text-center">
                    <p>&copy; <?= date('Y'); ?> Mini ERP. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</div>

    <!-- jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- Utility scripts -->
    <script src="<?= base_url('assets/js/utils/alerts.js'); ?>"></script>

    <?php
    // Renderizar HTML dos componentes
    echo render_components();

    // Carregar scripts registrados dinamicamente
    echo get_registered_js();
    ?>
</body>
</html>
