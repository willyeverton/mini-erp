<div class="container py-5">
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="mb-0">Checkout</h4>
                </div>
                <div class="card-body">
                    <?php if($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger">
                            <?= $this->session->flashdata('error'); ?>
                        </div>
                    <?php endif; ?>

                    <?= form_open('orders/checkout', ['id' => 'checkout-form']); ?>
                        <h5 class="mb-3">Shipping Information</h5>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="zipcode">Zipcode *</label>
                                <input type="text" name="zipcode" id="zipcode" class="form-control" value="<?= set_value('zipcode'); ?>" required>
                                <?= form_error('zipcode', '<small class="text-danger">', '</small>'); ?>
                            </div>
                            <div class="form-group col-md-8">
                                <label for="address">Address *</label>
                                <input type="text" name="address" id="address" class="form-control" value="<?= set_value('address'); ?>" required>
                                <?= form_error('address', '<small class="text-danger">', '</small>'); ?>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-2">
                                <label for="number">Number *</label>
                                <input type="text" name="number" id="number" class="form-control" value="<?= set_value('number'); ?>" required>
                                <?= form_error('number', '<small class="text-danger">', '</small>'); ?>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="complement">Complement</label>
                                <input type="text" name="complement" id="complement" class="form-control" value="<?= set_value('complement'); ?>">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="district">District *</label>
                                <input type="text" name="district" id="district" class="form-control" value="<?= set_value('district'); ?>" required>
                                <?= form_error('district', '<small class="text-danger">', '</small>'); ?>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-8">
                                <label for="city">City *</label>
                                <input type="text" name="city" id="city" class="form-control" value="<?= set_value('city'); ?>" required>
                                <?= form_error('city', '<small class="text-danger">', '</small>'); ?>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="state">State *</label>
                                <select name="state" id="state" class="form-control" required>
                                    <option value="">Select...</option>
                                    <option value="AC" <?= set_select('state', 'AC'); ?>>Acre</option>
                                    <option value="AL" <?= set_select('state', 'AL'); ?>>Alagoas</option>
                                    <option value="AP" <?= set_select('state', 'AP'); ?>>Amapá</option>
                                    <option value="AM" <?= set_select('state', 'AM'); ?>>Amazonas</option>
                                    <option value="BA" <?= set_select('state', 'BA'); ?>>Bahia</option>
                                    <option value="CE" <?= set_select('state', 'CE'); ?>>Ceará</option>
                                    <option value="DF" <?= set_select('state', 'DF'); ?>>Distrito Federal</option>
                                    <option value="ES" <?= set_select('state', 'ES'); ?>>Espírito Santo</option>
                                    <option value="GO" <?= set_select('state', 'GO'); ?>>Goiás</option>
                                    <option value="MA" <?= set_select('state', 'MA'); ?>>Maranhão</option>
                                    <option value="MT" <?= set_select('state', 'MT'); ?>>Mato Grosso</option>
                                    <option value="MS" <?= set_select('state', 'MS'); ?>>Mato Grosso do Sul</option>
                                    <option value="MG" <?= set_select('state', 'MG'); ?>>Minas Gerais</option>
                                    <option value="PA" <?= set_select('state', 'PA'); ?>>Pará</option>
                                    <option value="PB" <?= set_select('state', 'PB'); ?>>Paraíba</option>
                                    <option value="PR" <?= set_select('state', 'PR'); ?>>Paraná</option>
                                    <option value="PE" <?= set_select('state', 'PE'); ?>>Pernambuco</option>
                                    <option value="PI" <?= set_select('state', 'PI'); ?>>Piauí</option>
                                    <option value="RJ" <?= set_select('state', 'RJ'); ?>>Rio de Janeiro</option>
                                    <option value="RN" <?= set_select('state', 'RN'); ?>>Rio Grande do Norte</option>
                                    <option value="RS" <?= set_select('state', 'RS'); ?>>Rio Grande do Sul</option>
                                    <option value="RO" <?= set_select('state', 'RO'); ?>>Rondônia</option>
                                    <option value="RR" <?= set_select('state', 'RR'); ?>>Roraima</option>
                                    <option value="SC" <?= set_select('state', 'SC'); ?>>Santa Catarina</option>
                                    <option value="SP" <?= set_select('state', 'SP'); ?>>São Paulo</option>
                                    <option value="SE" <?= set_select('state', 'SE'); ?>>Sergipe</option>
                                    <option value="TO" <?= set_select('state', 'TO'); ?>>Tocantins</option>
                                </select>
                                <?= form_error('state', '<small class="text-danger">', '</small>'); ?>
                            </div>
                        </div>

                        <h5 class="mt-4 mb-3">Payment Information</h5>
                        <div class="alert alert-info">
                            <p class="mb-0">This is a demo application. No real payment will be processed.</p>
                        </div>

                        <div class="form-group">
                            <label>Payment Method</label>
                            <div class="custom-control custom-radio">
                                <input type="radio" id="payment_method_1" name="payment_method" class="custom-control-input" value="credit_card" checked>
                                <label class="custom-control-label" for="payment_method_1">Credit Card</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg mt-3">
                            <i class="fas fa-check"></i> Complete Order
                        </button>
                    <?= form_close(); ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <tbody>
                                <?php foreach($cart_items as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="<?= base_url('assets/images/products/' . $item['image']); ?>" alt="<?= $item['product_name']; ?>" class="img-thumbnail mr-2" style="width: 40px;">
                                                <div>
                                                    <small><?= $item['product_name']; ?></small>
                                                    <?php if($item['variation_name']): ?>
                                                        <br><small class="text-muted"><?= $item['variation_name']; ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-right">
                                            <small><?= $item['quantity']; ?> x $<?= number_format($item['price'], 2); ?></small>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span>$<?= number_format($subtotal, 2); ?></span>
                    </div>

                    <?php if(isset($discount) && $discount > 0): ?>
                        <div class="d-flex justify-content-between mb-2 text-success">
                            <span>Discount:</span>
                            <span>-$<?= number_format($discount, 2); ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Shipping:</span>
                        <span><?= $shipping > 0 ? '$' . number_format($shipping, 2) : 'Free'; ?></span>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between mb-0">
                        <strong>Total:</strong>
                        <strong>$<?= number_format($total, 2); ?></strong>
                    </div>
                </div>
            </div>

            <?php if(isset($coupon)): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Applied Coupon</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-success mb-0">
                            <strong><?= $coupon['code']; ?></strong>
                            <p class="mb-0 small">
                                <?= $coupon['type'] == 'percentage' ? $coupon['discount'] . '% off' : '$' . number_format($coupon['discount'], 2) . ' off'; ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
