<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Confirmação de Pedido</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
        }
        .header {
            background-color: #4e73df;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 20px;
        }
        .footer {
            background-color: #f8f9fc;
            padding: 15px;
            text-align: center;
            font-size: 12px;
            color: #858796;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fc;
        }
        .total-row {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Confirmação de Pedido #<?= $order['id']; ?></h1>
    </div>

    <div class="content">
        <p>Olá <?= $customer_name; ?>,</p>

        <p>Obrigado por sua compra! Seu pedido foi confirmado e está sendo processado.</p>

        <h2>Detalhes do Pedido</h2>

        <p><strong>Número do pedido:</strong> <?= $order['id']; ?><br>
        <strong>Data:</strong> <?= date('d/m/Y H:i', strtotime($order['created_at'])); ?><br>
        <strong>Status:</strong> <?= ucfirst($order['status']); ?></p>

        <h3>Itens do Pedido</h3>

        <table>
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Quantidade</th>
                    <th>Preço</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($items as $item): ?>
                <tr>
                    <td>
                        <?= $item['product_name']; ?>
                        <?php if(isset($item['variation_name']) && $item['variation_name']): ?>
                            <br><small>(<?= $item['variation_name']; ?>)</small>
                        <?php endif; ?>
                    </td>
                    <td><?= $item['quantity']; ?></td>
                    <td>R$ <?= number_format($item['price'], 2, ',', '.'); ?></td>
                    <td>R$ <?= number_format($item['subtotal'], 2, ',', '.'); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" align="right">Subtotal:</td>
                    <td>R$ <?= number_format($order['subtotal'], 2, ',', '.'); ?></td>
                </tr>
                <?php if($order['discount'] > 0): ?>
                <tr>
                    <td colspan="3" align="right">Desconto:</td>
                    <td>-R$ <?= number_format($order['discount'], 2, ',', '.'); ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td colspan="3" align="right">Frete:</td>
                    <td>
                        <?php if($order['shipping'] > 0): ?>
                            R$ <?= number_format($order['shipping'], 2, ',', '.'); ?>
                        <?php else: ?>
                            Grátis
                        <?php endif; ?>
                    </td>
                </tr>
                <tr class="total-row">
                    <td colspan="3" align="right">Total:</td>
                    <td>R$ <?= number_format($order['total'], 2, ',', '.'); ?></td>
                </tr>
            </tfoot>
        </table>

        <h3>Endereço de Entrega</h3>

        <p>
            <?= $order['address']; ?>, <?= $order['number']; ?><br>
            <?php if($order['complement']): ?>
                <?= $order['complement']; ?><br>
            <?php endif; ?>
            <?= $order['district']; ?><br>
            <?= $order['city']; ?> - <?= $order['state']; ?><br>
            CEP: <?= $order['zipcode']; ?>
        </p>

        <p>Para qualquer dúvida sobre seu pedido, entre em contato com nossa equipe de suporte.</p>

        <p>Atenciosamente,<br>Mini ERP</p>
    </div>

    <div class="footer">
        <p>&copy; <?= date('Y'); ?> Mini ERP. Todos os direitos reservados.</p>
    </div>
</body>
</html>
