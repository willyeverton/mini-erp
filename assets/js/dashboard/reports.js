/**
 * Dashboard Reports JavaScript
 */
$(document).ready(function () {
  // Gráfico de relatório de vendas
  if ($('#salesReportChart').length) {
    var ctx = $('#salesReportChart').get(0).getContext('2d');
    var salesReportChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: salesReportLabels,
        datasets: [
          {
            label: 'Revenue',
            data: salesReportRevenue,
            backgroundColor: 'rgba(78, 115, 223, 0.5)',
            borderColor: 'rgba(78, 115, 223, 1)',
            borderWidth: 1
          },
          {
            label: 'Orders',
            data: salesReportOrders,
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
                callback: function (value) {
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
            label: function (tooltipItem, chart) {
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
  }
});
