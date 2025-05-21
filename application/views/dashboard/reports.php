<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Sales Reports</h1>
        <div class="btn-group">
            <a href="<?= base_url('dashboard/reports?period=7days'); ?>" class="btn btn-sm btn-outline-secondary <?= $this->input->get('period') == '7days' ? 'active' : ''; ?>">7 Days</a>
            <a href="<?= base_url('dashboard/reports'); ?>" class="btn btn-sm btn-outline-secondary <?= !$this->input->get('period') ? 'active' : ''; ?>">30 Days</a>
            <a href="<?= base_url('dashboard/reports?period=90days'); ?>" class="btn btn-sm btn-outline-secondary <?= $this->input->get('period') == '90days' ? 'active' : ''; ?>">90 Days</a>
            <a href="<?= base_url('dashboard/reports?period=custom'); ?>" class="btn btn-sm btn-outline-secondary <?= $this->input->get('period') == 'custom' ? 'active' : ''; ?>">Custom</a>
        </div>
    </div>

    <?php if ($this->input->get('period') == 'custom'): ?>
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body">
                    <form action="<?= base_url('dashboard/reports'); ?>" method="get" class="form-inline">
                        <input type="hidden" name="period" value="custom">
                        <div class="form-group mr-2">
                            <label for="start_date" class="mr-2">Start Date:</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" value="<?= $this->input->get('start_date'); ?>" required>
                        </div>
                        <div class="form-group mr-2">
                            <label for="end_date" class="mr-2">End Date:</label>
                            <input type="date" name="end_date" id="end_date" class="form-control" value="<?= $this->input->get('end_date'); ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Apply</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Cards de estatísticas -->
    <div class="row">
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Orders</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $total_orders; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Revenue</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">$<?= number_format($total_revenue, 2); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row">
        <div class="col-xl-12 col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Sales Report</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                            <div class="dropdown-header">Export:</div>
                            <a class="dropdown-item" href="<?= base_url('dashboard/export_sales_report?format=csv' . ($this->input->get('period') ? '&period=' . $this->input->get('period') : '') . ($this->input->get('start_date') ? '&start_date=' . $this->input->get('start_date') : '') . ($this->input->get('end_date') ? '&end_date=' . $this->input->get('end_date') : '')); ?>">
                                <i class="fas fa-file-csv fa-sm fa-fw mr-2 text-gray-400"></i>
                                CSV
                            </a>
                            <a class="dropdown-item" href="<?= base_url('dashboard/export_sales_report?format=pdf' . ($this->input->get('period') ? '&period=' . $this->input->get('period') : '') . ($this->input->get('start_date') ? '&start_date=' . $this->input->get('start_date') : '') . ($this->input->get('end_date') ? '&end_date=' . $this->input->get('end_date') : '')); ?>">
                                <i class="fas fa-file-pdf fa-sm fa-fw mr-2 text-gray-400"></i>
                                PDF
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="salesReportChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela de produtos mais vendidos -->
    <div class="row">
        <div class="col-xl-12 col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top Selling Products</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity Sold</th>
                                    <th>Revenue</th>
                                    <th>% of Total Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($top_products as $product): ?>
                                <tr>
                                    <td><?= $product['name']; ?></td>
                                    <td><?= $product['total_quantity']; ?></td>
                                    <td>$<?= number_format($product['total_revenue'], 2); ?></td>
                                    <td>
                                        <?php
                                        $percentage = ($total_revenue > 0) ? ($product['total_revenue'] / $total_revenue) * 100 : 0;
                                        echo number_format($percentage, 2) . '%';
                                        ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Gráfico de relatório de vendas
var ctx = document.getElementById('salesReportChart').getContext('2d');
var salesReportChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($sales_report, 'date')); ?>,
        datasets: [
            {
                label: 'Revenue',
                data: <?= json_encode(array_column($sales_report, 'revenue')); ?>,
                backgroundColor: 'rgba(78, 115, 223, 0.5)',
                borderColor: 'rgba(78, 115, 223, 1)',
                borderWidth: 1
            },
            {
                label: 'Orders',
                data: <?= json_encode(array_column($sales_report, 'orders')); ?>,
                backgroundColor: 'rgba(28, 200, 138, 0.5)',
                borderColor: 'rgba(28, 200, 138, 1)',
                borderWidth: 1,
                type: 'line',
                yAxisID: 'y-axis-2'
            }
        ]
    },
    options: {
        maintainAspectRatio: false,
        scales: {
            xAxes: [{
                gridLines: {
                    display: false,
                    drawBorder: false
                },
                ticks: {
                    maxTicksLimit: 10
                }
            }],
            yAxes: [
                {
                    id: 'y-axis-1',
                    position: 'left',
                    ticks: {
                        maxTicksLimit: 5,
                        padding: 10,
                        callback: function(value) {
                            return '$' + value;
                        }
                    },
                    gridLines: {
                        color: "rgb(234, 236, 244)",
                        zeroLineColor: "rgb(234, 236, 244)",
                        drawBorder: false,
                        borderDash: [2],
                        zeroLineBorderDash: [2]
                    }
                },
                {
                    id: 'y-axis-2',
                    position: 'right',
                    ticks: {
                        maxTicksLimit: 5,
                        padding: 10,
                        beginAtZero: true
                    },
                    gridLines: {
                        display: false
                    }
                }
            ]
        },
        tooltips: {
            backgroundColor: "rgb(255,255,255)",
            bodyFontColor: "#858796",
            titleMarginBottom: 10,
            titleFontColor: '#6e707e',
            titleFontSize: 14,
            borderColor: '#dddfeb',
            borderWidth: 1,
            xPadding: 15,
            yPadding: 15,
            displayColors: false,
            intersect: false,
            mode: 'index',
            caretPadding: 10,
            callbacks: {
                label: function(tooltipItem, chart) {
                    var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
                    if (datasetLabel === 'Revenue') {
                        return datasetLabel + ': $' + tooltipItem.yLabel.toFixed(2);
                    } else {
                        return datasetLabel + ': ' + tooltipItem.yLabel;
                    }
                }
            }
        }
    }
});
</script>
