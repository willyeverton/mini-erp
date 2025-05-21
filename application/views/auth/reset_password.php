<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card mt-5">
                <div class="card-header">
                    <h4 class="mb-0">Reset Password</h4>
                </div>
                <div class="card-body">
                    <?php if($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger">
                            <?= $this->session->flashdata('error'); ?>
                        </div>
                    <?php endif; ?>

                    <p>Enter your email address and we'll send you a link to reset your password.</p>

                    <?= form_open('auth/reset_password'); ?>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= set_value('email'); ?>" required>
                            <?= form_error('email', '<small class="text-danger">', '</small>'); ?>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>
                        </div>
                    <?= form_close(); ?>

                    <div class="text-center mt-3">
                        <p><a href="<?= base_url('auth'); ?>">Back to Login</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
