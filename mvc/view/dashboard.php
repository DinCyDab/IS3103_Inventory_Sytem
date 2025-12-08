<?php
class DashboardView {
    private $dashboardData;

    public function setDashboardData($data) {
        $this->dashboardData = $data;
    }

    public function render() {
        $stockSummary = $this->dashboardData['stockSummary'] ?? [];
        $inventorySummary = $this->dashboardData['inventorySummary'] ?? [];
        $transactions = $this->dashboardData['recentTransactions'] ?? [];
        $salesSummary = $this->dashboardData['salesSummary'] ?? [];
        $lowStockItems = $this->dashboardData['lowStockItems'] ?? [];
        
        // Get user's first name for welcome message
        $userName = $_SESSION['account']['first_name'] ?? 'User';
        ?>
        
        <!-- Welcome Header -->
        <div class="dashboard-header">
            <h1>Welcome Back, <?php echo htmlspecialchars($userName); ?>!</h1>
        </div>

        <div class="dashboard-container">
            <!-- Main Dashboard Grid - Left and Right -->
            <div class="dashboard-main-grid">
                
                <!-- LEFT SIDE - Stock Summary and Transactions -->
                <div class="dashboard-left">
                    <!-- Total Stock Summary -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h2>Total Stock Summary</h2>
                            <a href="?view=inventory" class="see-all-link">See All</a>
                        </div>
                        <div class="table-container scrollable-table">
                            <table class="dashboard-table">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Sold Quantity</th>
                                        <th>Remaining Quantity</th>
                                        <th>% of Stock</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($stockSummary)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center">No stock data available</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach($stockSummary as $stock): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($stock['category']); ?></td>
                                                <td><?php echo htmlspecialchars($stock['sold_quantity']); ?></td>
                                                <td><?php echo htmlspecialchars($stock['remaining_quantity']); ?></td>
                                                <td><?php echo htmlspecialchars($stock['percentage']); ?>%</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Transactions -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h2>Transactions</h2>
                            <a href="?view=sales" class="see-all-link">See All</a>
                        </div>
                        <div class="table-container scrollable-table">
                            <table class="dashboard-table">
                                <thead>
                                    <tr>
                                        <th>Transaction ID</th>
                                        <th>Date & Time</th>
                                        <th>Products</th>
                                        <th>Order Value</th>
                                        <th>Quantity Sold</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($transactions)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center">No transactions available</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach($transactions as $txn): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($txn['transaction_id']); ?></td>
                                                <td><?php echo date('Y-m-d H:i', strtotime($txn['date_time'])); ?></td>
                                                <td><?php echo htmlspecialchars($txn['products']); ?></td>
                                                <td>₱<?php echo number_format($txn['order_value'], 2); ?></td>
                                                <td><?php echo htmlspecialchars($txn['quantity_sold']); ?> Packets</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- RIGHT SIDE - Inventory, Sales Chart, Low Stock -->
                <div class="dashboard-right">
                    <!-- Inventory Summary -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h2>Inventory Summary</h2>
                        </div>
                        <div class="inventory-summary-grid">
                            <div class="summary-card orange">
                                <i class='bx bx-package'></i>
                                <div class="summary-value"><?php echo number_format($inventorySummary['quantity_in_hand'] ?? 0); ?></div>
                                <div class="summary-label">Quantity in Hand</div>
                            </div>
                            <div class="summary-card purple">
                                <i class='bx bx-trending-up'></i>
                                <div class="summary-value"><?php echo number_format($inventorySummary['to_be_received'] ?? 0); ?></div>
                                <div class="summary-label">To be received</div>
                            </div>
                        </div>
                    </div>

                    <!-- Sales Summary Chart -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h2>Sales Summary</h2>
                        </div>
                        <div class="chart-container">

                            <canvas id="salesChart1" width="400" height="200"></canvas>
                        </div>
                         <div class="chart-container">

                            <canvas id="salesChart" width="400" height="200"></canvas>
                        </div>
                        <div class="chart-legend">
                            <div class="legend-item">
                                <span class="legend-color orange"></span>
                                <span>Purchase</span>
                            </div>
                            <div class="legend-item">
                                <span class="legend-color blue"></span>
                                <span>Sales</span>
                            </div>
                        </div>
                    </div>

                    <!-- Low Quantity Stock -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h2>Low Quantity Stock</h2>
                            <a href="?view=inventory" class="see-all-link">See All</a>
                        </div>
                        <div class="low-stock-container scrollable-low-stock">
                            <div class="low-stock-grid">
                                <?php if(empty($lowStockItems)): ?>
                                    <p class="text-center">No low stock items</p>
                                <?php else: ?>
                                    <?php foreach($lowStockItems as $item): ?>
                                        <div class="low-stock-item">
                                            <div class="item-image">
                                                <?php if(!empty($item['image'])): ?>
                                                    <img src="./public/images/uploads/<?php echo htmlspecialchars($item['image']); ?>" 
                                                         alt="<?php echo htmlspecialchars($item['productName']); ?>"
                                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                                    <i class='bx bx-package' style="display:none;"></i>
                                                <?php else: ?>
                                                    <i class='bx bx-package'></i>
                                                <?php endif; ?>
                                            </div>
                                            <div class="item-details">
                                                <h3><?php echo htmlspecialchars($item['productName']); ?></h3>
                                                <p>Remaining Quantity: <?php echo htmlspecialchars($item['quantity']); ?> <?php echo htmlspecialchars($item['unit']); ?></p>
                                            </div>
                                            <span class="low-badge">Low</span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <script>
            // Sales Chart Data from PHP
            const salesData = <?php echo json_encode($salesSummary); ?>;
            
            // Prepare chart labels and data
            const labels = salesData.length > 0 
                ? salesData.map(item => item.month_name) 
                : ['Jan', 'Feb', 'Mar', 'Apr', 'May'];
            
            const salesValues = salesData.length > 0 
                ? salesData.map(item => parseFloat(item.total_sales)) 
                : [0, 0, 0, 0, 0];
            
            // Generate purchase data (mock data - replace with real data if available)
            const purchaseValues = salesValues.map((val, index) => {
                const variation = 0.7 + (Math.sin(index) * 0.15);
                return val * variation;
            });

            // async function getData() {
            // const url = "index.php?view=allProducts";
            // try {
            //     const response = await fetch(url);
            //     if (!response.ok) {
            //     throw new Error(`Response status: ${response.status}`);
            //     }

            //     const result = await response.json();
            //     console.log(result);
            // } catch (error) {
            //     console.error(error.message);
            // }
            // }
            
            // Create the chart when DOM is loaded
            document.addEventListener('DOMContentLoaded', function() {

//getData();

                const canvas = document.getElementById('salesChart');
                const ctx = canvas.getContext('2d');

                  

        const data = [
            { label: 'Jan', value: 50 },
            { label: 'Feb', value: 75 },
            { label: 'Mar', value: 60 },
            { label: 'Apr', value: 90 },
            { label: 'May', value: 45 },
            { label: 'Jun', value: 45 },
            { label: 'Jul', value: 45 },
            { label: 'Aug', value: 45 },
            { label: 'Sep', value: 45 },
            { label: 'Oct', value: 45 },
            { label: 'Nov', value: 45 },
            { label: 'Dec', value: 45 }
        ];

        const chartWidth = canvas.width;
        const chartHeight = canvas.height;
        const padding = 50;
        const barWidth = 10;
        const barSpacing = 10;

        // Calculate maximum value for scaling
        const maxValue = Math.max(...data.map(item => item.value));
        const scale = (chartHeight - 2 * padding) / maxValue;

        // Draw X and Y axis
        ctx.strokeStyle = '#961717ff';
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.moveTo(padding, padding);
        ctx.lineTo(padding, chartHeight - padding); // Y-axis
        ctx.lineTo(chartWidth - padding, chartHeight - padding); // X-axis
        ctx.stroke();

        // Draw bars
        let currentX = padding + barSpacing;
        for (let i = 0; i < data.length; i++) {
            const barHeight = data[i].value * scale;

            ctx.fillStyle = 'orange';
            ctx.fillRect(currentX, chartHeight - padding - barHeight, barWidth, barHeight);

            // Draw labels
            ctx.fillStyle = '#c51c1cff';
            ctx.textAlign = 'center';
            ctx.fillText(data[i].label, currentX + barWidth / 2, chartHeight - padding + 20);
            ctx.fillText(data[i].value, currentX + barWidth / 2, chartHeight - padding - barHeight - 10);

            currentX += barWidth + barSpacing;
        }

        // Draw axis labels
        ctx.textAlign = 'center';
        ctx.fillText('Months', chartWidth / 2, chartHeight - 10);
        ctx.save();
        ctx.translate(20, chartHeight / 2);
        ctx.rotate(-Math.PI / 2);
        ctx.fillText('Purchase', 0, 0);
        ctx.restore();
             

        /////SALES////
             const canvas1 = document.getElementById('salesChart1');
                const ctx1 = canvas1.getContext('2d');

                  

        const data1 = [
            { label: 'Jan', value: 50 },
            { label: 'Feb', value: 75 },
            { label: 'Mar', value: 60 },
            { label: 'Apr', value: 90 },
            { label: 'May', value: 45 },
            { label: 'Jun', value: 45 },
            { label: 'Jul', value: 45 },
            { label: 'Aug', value: 45 },
            { label: 'Sep', value: 45 },
            { label: 'Oct', value: 45 },
            { label: 'Nov', value: 45 },
            { label: 'Dec', value: 45 }
        ];

        const chartWidth1 = canvas1.width;
        const chartHeight1 = canvas1.height;
        const padding1 = 50;
        const barWidth1 = 10;
        const barSpacing1 = 10;

        // Calculate maximum value for scaling
        const maxValue1 = Math.max(...data1.map(item => item.value));
        const scale1 = (chartHeight1 - 2 * padding1) / maxValue1;

        // Draw X and Y axis
        ctx1.strokeStyle = '#333';
        ctx1.lineWidth = 2;
        ctx1.beginPath();
        ctx1.moveTo(padding1, padding1);
        ctx1.lineTo(padding1, chartHeight1 - padding1); // Y-axis
        ctx1.lineTo(chartWidth1 - padding1, chartHeight1 - padding1); // X-axis
        ctx1.stroke();

        // Draw bars
        let currentX1 = padding1 + barSpacing1;
        for (let i = 0; i < data1.length; i++) {
            const barHeight1 = data1[i].value * scale1;

            ctx1.fillStyle = 'steelblue';
            ctx1.fillRect(currentX1, chartHeight - padding1 - barHeight1, barWidth1, barHeight1);

            // Draw labels
            ctx1.fillStyle = '#000';
            ctx1.textAlign = 'center';
            ctx1.fillText(data1[i].label, currentX1 + barWidth1 / 2, chartHeight - padding1 + 20);
            ctx1.fillText(data1[i].value, currentX1 + barWidth1 / 2, chartHeight - padding1 - barHeight1 - 10);

            currentX1 += barWidth1 + barSpacing1;
        }

        // Draw axis labels
        ctx1.textAlign = 'center';
        ctx1.fillText('Months', chartWidth1 / 2, chartHeight1 - 10);
        ctx1.save();
        ctx1.translate(20, chartHeight1 / 2);
        ctx1.rotate(-Math.PI / 2);
        ctx1.fillText('Sales', 0, 0);
        ctx1.restore();
             

                // if (canvas && typeof Chart !== 'undefined') {
                //     new Chart(canvas.getContext('2d'), {
                //         type: 'line',
                //         data: {
                //             labels: labels,
                //             datasets: [
                //                 {
                //                     label: 'Sales',
                //                     data: salesValues,
                //                     borderColor: 'rgb(59, 130, 246)',
                //                     backgroundColor: 'rgba(59, 130, 246, 0.1)',
                //                     tension: 0.4,
                //                     fill: true,
                //                     borderWidth: 2,
                //                     pointRadius: 4,
                //                     pointBackgroundColor: 'rgb(59, 130, 246)',
                //                     pointBorderColor: '#fff',
                //                     pointBorderWidth: 2,
                //                     pointHoverRadius: 6
                //                 },
                //                 {
                //                     label: 'Purchase',
                //                     data: purchaseValues,
                //                     borderColor: 'rgb(249, 115, 22)',
                //                     backgroundColor: 'rgba(249, 115, 22, 0.1)',
                //                     tension: 0.4,
                //                     fill: true,
                //                     borderWidth: 2,
                //                     pointRadius: 4,
                //                     pointBackgroundColor: 'rgb(249, 115, 22)',
                //                     pointBorderColor: '#fff',
                //                     pointBorderWidth: 2,
                //                     pointHoverRadius: 6
                //                 }
                //             ]
                //         },
                //         options: {
                //             responsive: true,
                //             maintainAspectRatio: false,
                //             interaction: {
                //                 mode: 'index',
                //                 intersect: false
                //             },
                //             plugins: {
                //                 legend: {
                //                     display: false
                //                 },
                //                 tooltip: {
                //                     backgroundColor: 'rgba(0, 0, 0, 0.8)',
                //                     padding: 12,
                //                     titleColor: '#fff',
                //                     bodyColor: '#fff',
                //                     borderColor: 'rgba(255, 255, 255, 0.1)',
                //                     borderWidth: 1,
                //                     displayColors: true,
                //                     callbacks: {
                //                         label: function(context) {
                //                             let label = context.dataset.label || '';
                //                             if (label) {
                //                                 label += ': ';
                //                             }
                //                             label += '₱' + context.parsed.y.toLocaleString('en-PH', {
                //                                 minimumFractionDigits: 2,
                //                                 maximumFractionDigits: 2
                //                             });
                //                             return label;
                //                         }
                //                     }
                //                 }
                //             },
                //             scales: {
                //                 x: {
                //                     grid: {
                //                         display: false
                //                     },
                //                     ticks: {
                //                         color: '#6b7280',
                //                         font: {
                //                             size: 12
                //                         }
                //                     }
                //                 },
                //                 y: {
                //                     beginAtZero: true,
                //                     grid: {
                //                         color: 'rgba(0, 0, 0, 0.05)',
                //                         drawBorder: false
                //                     },
                //                     ticks: {
                //                         color: '#6b7280',
                //                         font: {
                //                             size: 12
                //                         },
                //                         callback: function(value) {
                //                             if (value >= 1000) {
                //                                 return '₱' + (value / 1000) + 'k';
                //                             }
                //                             return '₱' + value;
                //                         }
                //                     }
                //                 }
                //             }
                //         }
                //     });
                // }
            });
        </script>
        
        <script src="./public/src/js/dashboardscript.js"></script>
        <link rel="stylesheet" href="./public/src/css/dashboard.css">
        <?php
    }
}
?>