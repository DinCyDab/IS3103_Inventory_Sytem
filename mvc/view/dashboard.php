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
                            <canvas id="salesChart"></canvas>
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

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            // Pass PHP data to JavaScript
            window.dashboardSalesSummary = <?php echo json_encode($salesSummary); ?>;
        </script>
        
        <script>
        const data = window.dashboardSalesSummary || {
            labels: [],
            sales: [],
            purchase_cost: []
        };

        const ctx = document.getElementById('salesChart').getContext('2d');

        // Ensure canvas uses exact size
        ctx.canvas.width = 400;
        ctx.canvas.height = 270;

        // Calculate dynamic max value for Y-axis
        function calculateMaxValue(arr1, arr2){
            const all = [...arr1, ...arr2];
            if(all.length === 0) return 100000;
            const max = Math.max(...all);
            const magnitude = Math.pow(10, Math.floor(Math.log10(max)));
            return Math.ceil(max / magnitude) * magnitude * 1.2;
        }

        const maxY = calculateMaxValue(data.sales, data.purchase_cost);

        // Custom tooltip
        function customTooltip(context){
            let el = document.getElementById("sales-tip");
            if(!el){
                el = document.createElement("div");
                el.id = "sales-tip";
                el.style.position = "absolute";
                el.style.background = "#fff";
                el.style.padding = "10px 15px";
                el.style.borderRadius = "12px";
                el.style.boxShadow = "0 4px 15px rgba(0,0,0,0.15)";
                el.style.pointerEvents = "none";
                el.style.opacity = 0;
                el.style.transition = ".15s";
                document.body.appendChild(el);
            }

            const tooltip = context.tooltip;
            if(tooltip.opacity === 0){ el.style.opacity = 0; return; }

            if(tooltip.dataPoints){
                let html = `<div style="font-size:12px;color:#9ca3af;margin-bottom:5px;">${tooltip.dataPoints[0].label}</div>`;
                tooltip.dataPoints.forEach(p => {
                    html += `<div style="font-size:14px;font-weight:700;margin-bottom:3px;color:${p.dataset.borderColor};">
                                ${p.dataset.label}: ₱${p.raw.toLocaleString()}
                            </div>`;
                });
                el.innerHTML = html;
            }

            const rect = context.chart.canvas.getBoundingClientRect();
            el.style.opacity = 1;
            el.style.left = rect.left + window.scrollX + tooltip.caretX - 60 + "px";
            el.style.top = rect.top + window.scrollY + tooltip.caretY - 70 + "px";
        }

        // Chart.js
        new Chart(ctx, {
            type: "line",
            data: {
                labels: data.labels.length > 0 ? data.labels : ['No Data'],
                datasets: [
                    {
                        label: "Purchase Cost",
                        data: data.purchase_cost.length > 0 ? data.purchase_cost : [0],
                        borderColor: "#f7db93",
                        backgroundColor: "rgba(251,191,36,0.15)",
                        borderWidth: 3,
                        pointRadius: 3,
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: "Sales",
                        data: data.sales.length > 0 ? data.sales : [0],
                        borderColor: "#3b82f6",
                        backgroundColor: "rgba(59,130,246,0.15)",
                        borderWidth: 3,
                        pointRadius: 3,
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: false, // disable automatic resizing
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: { enabled: false, external: customTooltip }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        min: 0,
                        max: maxY,
                        ticks: { 
                            stepSize: Math.ceil(maxY/5),
                            callback: v => '₱' + v.toLocaleString() 
                        },
                        grid: { color: "rgba(0,0,0,0.08)" }
                    },
                    x: { grid: { display: false } }
                },
                interaction: { mode: "index", intersect: false }
            }
        });
        </script>
        <link rel="stylesheet" href="./public/src/css/dashboard.css">
        
        <?php
    }
}
?>