<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Order_model');
        $this->load->model('Product_model');
        $this->load->model('User_model');
        $this->load->model('Stock_model');
        $this->load->helper('url');

        // Verificar permissões de administrador
        if (!$this->user || $this->user['role'] != 'admin') {
            redirect('customer');
        }
    }

    public function index() {
        $data['title'] = 'Dashboard';

        // Definir período de análise (padrão: 30 dias)
        $period = $this->input->get('period');

        switch ($period) {
            case '7days':
                $start_date = date('Y-m-d', strtotime('-7 days'));
                break;
            case '90days':
                $start_date = date('Y-m-d', strtotime('-90 days'));
                break;
            default:
                $start_date = date('Y-m-d', strtotime('-30 days'));
                break;
        }

        $end_date = date('Y-m-d');

        // Estatísticas gerais
        $data['total_orders'] = $this->Order_model->count_orders($start_date, $end_date);
        $data['total_revenue'] = $this->Order_model->sum_revenue($start_date, $end_date);
        $data['total_products'] = $this->Product_model->count_products();
        $data['total_customers'] = $this->User_model->count_users_by_role('customer');

        // Dados para gráfico de vendas
        $data['sales_data'] = $this->get_sales_data($start_date, $end_date);

        // Dados para gráfico de status de pedidos
        $data['order_status_data'] = $this->get_order_status_data($start_date, $end_date);

        // Produtos mais vendidos
        $data['top_products'] = $this->Order_model->get_top_selling_products($start_date, $end_date, 5);

        // Produtos com estoque baixo
        $data['low_stock_products'] = $this->Stock_model->get_low_stock_products(5);

        // Adicione o usuário aos dados
        $data['user'] = $this->user;

        // Preparar dados para os gráficos
        $data['chart_data'] = [
            'salesLabels' => json_encode($data['sales_data']['labels']),
            'salesData' => json_encode($data['sales_data']['revenue']),
            'statusLabels' => json_encode($data['order_status_data']['labels']),
            'statusData' => json_encode($data['order_status_data']['data'])
        ];

        $data['scripts'] = ['dashboard' => 'charts.js'];

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('dashboard/index', $data);
        $this->load->view('templates/footer');
    }

    public function reports() {
        $data['title'] = 'Detailed Reports';

        // Obter datas do filtro
        $data['start_date'] = $this->input->get('start_date') ? $this->input->get('start_date') : date('Y-m-d', strtotime('-30 days'));
        $data['end_date'] = $this->input->get('end_date') ? $this->input->get('end_date') : date('Y-m-d');

        // Relatório de vendas diárias
        $data['sales_report'] = $this->Order_model->get_daily_sales($data['start_date'], $data['end_date']);

        // Estatísticas gerais para o período
        $data['total_orders'] = $this->Order_model->count_orders($data['start_date'], $data['end_date']);
        $data['total_revenue'] = $this->Order_model->sum_revenue($data['start_date'], $data['end_date']);

        // Produtos mais vendidos
        $data['top_products'] = $this->Order_model->get_top_selling_products($data['start_date'], $data['end_date'], 10);

        // Dados para gráfico de status de pedidos
        $data['order_status_data'] = $this->get_order_status_data($data['start_date'], $data['end_date']);

        // Dados para gráfico de aquisição de clientes
        $data['customer_data'] = $this->get_customer_acquisition_data($data['start_date'], $data['end_date']);

        // Adicione o usuário aos dados
        $data['user'] = $this->user;

        // Preparar dados para os gráficos
        $data['chart_data'] = [
            'salesReportLabels' => json_encode(array_column($data['sales_report'], 'date')),
            'salesReportRevenue' => json_encode(array_column($data['sales_report'], 'revenue')),
            'salesReportOrders' => json_encode(array_column($data['sales_report'], 'orders'))
        ];

        $data['scripts'] = ['dashboard' => 'reports.js'];

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('dashboard/reports', $data);
        $this->load->view('templates/footer');
    }

    private function get_sales_data($start_date, $end_date) {
        $sales_data = $this->Order_model->get_daily_sales($start_date, $end_date);

        $labels = [];
        $revenue = [];

        foreach ($sales_data as $day) {
            $labels[] = $day['date'];
            $revenue[] = $day['revenue'];
        }

        return [
            'labels' => $labels,
            'revenue' => $revenue
        ];
    }

    private function get_order_status_data($start_date, $end_date) {
        $statuses = ['pending', 'processing', 'shipped', 'delivered', 'canceled'];
        $data = [];
        $labels = [];

        foreach ($statuses as $status) {
            $count = $this->Order_model->count_orders_by_status($status, $start_date, $end_date);
            if ($count > 0) {
                $data[] = $count;
                $labels[] = ucfirst($status);
            }
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    private function get_customer_acquisition_data($start_date, $end_date) {
        $customer_data = $this->User_model->get_customer_acquisition($start_date, $end_date);

        $dates = [];
        $new_customers = [];
        $total_customers = [];

        foreach ($customer_data as $day) {
            $dates[] = $day['date'];
            $new_customers[] = $day['new_customers'];
            $total_customers[] = $day['total_customers'];
        }

        return [
            'dates' => $dates,
            'new' => $new_customers,
            'total' => $total_customers
        ];
    }

    /**
     * Exportar relatório de vendas
     */
    public function export_sales_report() {
        // Verificar permissões de administrador
        if (!$this->user || $this->user['role'] != 'admin') {
            redirect('auth');
        }

        // Determinar período
        $period = $this->input->get('period');
        $end_date = date('Y-m-d');

        if ($period == '7days') {
            $start_date = date('Y-m-d', strtotime('-7 days'));
        } else if ($period == '90days') {
            $start_date = date('Y-m-d', strtotime('-90 days'));
        } else if ($period == 'custom') {
            $start_date = $this->input->get('start_date') ?: date('Y-m-d', strtotime('-30 days'));
            $end_date = $this->input->get('end_date') ?: date('Y-m-d');
        } else {
            // Padrão: 30 dias
            $start_date = date('Y-m-d', strtotime('-30 days'));
        }

        // Obter dados do relatório
        $sales_report = $this->Order_model->get_sales_report($start_date, $end_date);
        $top_products = $this->Order_model->get_top_selling_products($start_date, $end_date, 10);

        // Calcular totais
        $total_orders = 0;
        $total_revenue = 0;

        foreach ($sales_report as $day) {
            $total_orders += $day['orders'];
            $total_revenue += $day['revenue'];
        }

        $format = $this->input->get('format') ?: 'csv';

        if ($format == 'csv') {
            // Exportar como CSV
            $this->load->helper('download');

            $csv = "Date,Orders,Revenue\n";
            foreach ($sales_report as $day) {
                $csv .= "{$day['date']},{$day['orders']},{$day['revenue']}\n";
            }

            $csv .= "\nTop Products\n";
            $csv .= "Product,Quantity Sold,Revenue\n";
            foreach ($top_products as $product) {
                $csv .= "{$product['name']},{$product['total_quantity']},{$product['total_revenue']}\n";
            }

            $filename = 'sales_report_' . $start_date . '_to_' . $end_date . '.csv';
            force_download($filename, $csv);
        } else if ($format == 'pdf') {
            // Exportar como PDF
            $this->load->library('pdf');

            $html = '<h1>Sales Report</h1>';
            $html .= '<p>Period: ' . $start_date . ' to ' . $end_date . '</p>';
            $html .= '<p>Total Orders: ' . $total_orders . '</p>';
            $html .= '<p>Total Revenue: $' . number_format($total_revenue, 2) . '</p>';

            $html .= '<h2>Daily Sales</h2>';
            $html .= '<table border="1" cellpadding="5">';
            $html .= '<tr><th>Date</th><th>Orders</th><th>Revenue</th></tr>';

            foreach ($sales_report as $day) {
                $html .= '<tr>';
                $html .= '<td>' . $day['date'] . '</td>';
                $html .= '<td>' . $day['orders'] . '</td>';
                $html .= '<td>$' . number_format($day['revenue'], 2) . '</td>';
                $html .= '</tr>';
            }

            $html .= '</table>';

            $html .= '<h2>Top Products</h2>';
            $html .= '<table border="1" cellpadding="5">';
            $html .= '<tr><th>Product</th><th>Quantity Sold</th><th>Revenue</th></tr>';

            foreach ($top_products as $product) {
                $html .= '<tr>';
                $html .= '<td>' . $product['name'] . '</td>';
                $html .= '<td>' . $product['total_quantity'] . '</td>';
                $html .= '<td>$' . number_format($product['total_revenue'], 2) . '</td>';
                $html .= '</tr>';
            }

            $html .= '</table>';

            $this->pdf->loadHtml($html);
            $this->pdf->setPaper('A4', 'portrait');
            $this->pdf->render();

            $filename = 'sales_report_' . $start_date . '_to_' . $end_date . '.pdf';
            $this->pdf->stream($filename, array('Attachment' => 1));
        }
    }
}
