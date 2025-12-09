<?php

class ReportsView {

    private $list;

    private $overviewStats = [
        ["label" => "Total Profit",      "value" => "₱0", "footer" => "Total Profit"],
        ["label" => "Revenue",           "value" => "₱0", "footer" => "Revenue"],
        ["label" => "Sales",             "value" => "₱0", "footer" => "Sales"],
        ["label" => "Net purchase value","value" => "₱0", "footer" => "Net purchase value"],
        ["label" => "Net sales value",   "value" => "₱0", "footer" => "Net sales value"],
        ["label" => "MoM Profit",        "value" => "₱0", "footer" => "MoM Profit"],
        ["label" => "YoY Profit",        "value" => "₱0", "footer" => "YoY Profit"],
    ];

    public function __construct($data = []) {
        $this->list = $data;

        // Populate overview stats safely using associative keys
        if (isset($data['overview']) && is_array($data['overview'])) {
            foreach ($this->overviewStats as $i => &$stat) {
                $key = strtolower(str_replace(' ', '_', $stat['footer']));
                if(isset($data['overview'][$key])){
                    $value = $data['overview'][$key];
                    $stat['value'] = "₱" . number_format($value, 2);
                }
            }
            unset($stat);
        }
    }

    public function render() { ?>

        <div class="header"></div>

        <!-- search bar -->
        <div class="topbar">
			<div class="search-wrapper">
				<h1>Monthly Reports</h1>
			</div>
		</div>

        <!-- both boxes side by side -->
        <div class="reports-row">

            <!-- overview section -->
            <div class="overview-container">
                <div class="overview-title">
                    <h3>Overview</h3>
                </div>

                <div class="overview-row">
                    <?php for ($i = 0; $i < 3; $i++): 
                        $labelClass = strtolower(str_replace(' ', '-', $this->overviewStats[$i]['footer'])); 
                    ?>
                        <div class="overview-item">
                            <div class="overview-value"><?= $this->overviewStats[$i]['value'] ?></div>
                            <div class="overview-label <?= $labelClass ?>"><?= $this->overviewStats[$i]['footer'] ?></div>
                        </div>
                    <?php endfor; ?>
                </div>

                <div class="overview-divider-horizontal"></div>

                <div class="overview-row">
                    <?php for ($i = 3; $i < count($this->overviewStats); $i++): 
                        $labelClass = strtolower(str_replace(' ', '-', $this->overviewStats[$i]['footer'])); 
                    ?>
                        <div class="overview-item">
                            <div class="overview-value"><?= $this->overviewStats[$i]['value'] ?></div>
                            <div class="overview-label <?= $labelClass ?>"><?= $this->overviewStats[$i]['footer'] ?></div>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- best selling category section -->
            <div class="bestseller-container">
                <div class="bestseller-header">
                    <h3>Best selling category</h3>
                </div>

                <div class="bestseller-table">
                    <div class="bestseller-labels">
                        <div class="bestseller-col title">Category</div>
                        <div class="bestseller-col title">Turn Over</div>
                        <div class="bestseller-col title">Increase by</div>
                    </div>

                    <div class="bestseller-divider"></div>

                    <?php 
                    $bestCategories = $this->list['categories'] ?? [];

                    if(!empty($bestCategories)):
                        foreach ($bestCategories as $i => $row):
                    ?>
                        <div class="bestseller-row">
                            <div class="bestseller-col"><?= htmlspecialchars($row['category_name']) ?></div>
                            <div class="bestseller-col">
                                ₱<?= number_format(($row['total_revenue'] ?? 0), 2) ?>
                            </div>
                            <div class="bestseller-col increase">
                                <?= $row['percentage_increase'] ?? 0 ?>%
                            </div>
                        </div>

                        <?php if ($i < count($bestCategories) - 1): ?>
                            <div class="bestseller-divider"></div>
                        <?php endif; ?>

                    <?php endforeach; else: ?>
                        <div class="bestseller-row">
                            <div class="bestseller-col" style="text-align:center; padding:20px;">No data available.</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>    

       <!-- profit and revenue container -->
        <div class="profit-container">
            <div class="profit-header">
                <h3>Profit & Revenue</h3>

                <button class="weekly-btn" id="reportModeBtn">
                    Monthly
                    <i class="bx bx-chevron-down"></i>
                </button>

                <ul class="report-mode-list" id="reportModeList">
                    <li data-mode="monthly">Monthly</li>
                    <li data-mode="weekly">Weekly</li>
                </ul>
            </div>

            <div class="chart-placeholder">
                <canvas id="profitChart"></canvas>
            </div>

            <div class="profit-legend">
                <div class="legend-item">
                    <span class="legend-dot blue"></span>
                    Revenue
                </div>

                <div class="legend-item">
                    <span class="legend-dot cream"></span>
                    Profit
                </div>
            </div>
        </div>

        <!--best selling products -->
        <div class="bestseller-product-container">
            <div class="bestseller-product-header">
                <h3>Best selling product</h3>
            </div>

            <div class="bestseller-product-table">
                <div class="product-labels">
                    <div class="product-col">Product</div>
                    <div class="product-col">Product ID</div>
                    <div class="product-col">Category</div>
                    <div class="product-col">Remaining Quantity</div>
                    <div class="product-col">Turn Over</div>
                    <div class="product-col">Increase By</div>
                </div>

                <div class="product-divider"></div>

                <?php
                $bestProducts = $this->list['products'] ?? [];

                if(!empty($bestProducts)):
                    foreach ($bestProducts as $i => $row):
                ?>
                    <div class="product-row">
                        <div class="product-col"><?= htmlspecialchars($row["product_name"]) ?></div>
                        <div class="product-col"><?= htmlspecialchars($row["productID"]) ?></div>
                        <div class="product-col"><?= htmlspecialchars($row["category"]) ?></div>
                        <div class="product-col"><?= htmlspecialchars($row["remaining_qty"]) ?></div>
                        <div class="product-col">₱<?= number_format($row["turnover"], 2) ?></div>
                        <div class="product-col increase"><?= $row["increase_percent"] ?>%</div>
                    </div>

                    <?php if ($i < count($bestProducts) - 1): ?>
                        <div class="product-divider"></div>
                    <?php endif; ?>

                <?php endforeach; else: ?>
                    <div class="product-row">
                        <div class="product-col" style="text-align:center; padding: 20px;">No data available.</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!--pop out screen for best selling category-->
        <div id="bestseller-modal" class="modal-overlay">
            <div class="modal-content">
                <span class="modal-close">&times;</span>
                <h2>Best Selling Categories</h2>

                <div class="modal-table">
                    <div class="modal-row header">
                        <div>Category</div>
                        <div>Total Sold</div>
                        <div>Total Revenue</div>
                    </div>

                    <?php
                        $allCategories = $this->list['categories'] ?? [];
                        if(!empty($allCategories)):
                            foreach($allCategories as $cat):
                    ?>
                        <div class="modal-row">
                            <div><?= htmlspecialchars($cat['category_name']) ?></div>
                            <div><?= number_format($cat['total_sold']) ?></div>
                            <div>₱<?= number_format($cat['total_revenue'], 2) ?></div>
                        </div>
                    <?php endforeach; else: ?>
                        <div class="modal-row">
                            <div style="text-align:center; padding:20px;">No data available.</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- list of sales pop up box -->
        <div id="salesReportModal" class="modal-overlay">
            <div class="modal-content">
                <span class="close-modal" id="closeSalesReportModal">&times;</span>
                <h2>All Sales Reports</h2>

                <table class="modal-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Product ID</th>
                            <th>Category</th>
                            <th>Remaining Qty</th>
                            <th>Turnover</th>
                            <th>Increase By</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                            $salesList = $this->list['salesReportList'] ?? [];
                            if(!empty($salesList)):
                                foreach($salesList as $row):
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($row['product_name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($row['productID'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($row['category_name'] ?? 'Unknown') ?></td>
                                <td><?= htmlspecialchars($row['remaining_qty'] ?? 'N/A') ?></td>
                                <td>₱<?= number_format($row['turnover'] ?? 0, 2) ?></td>
                                <td><?= $row['increase'] ?? 0 ?></td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr>
                                <td colspan="6" style="text-align:center; padding:20px;">No data available.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <script>
            window.reportsChartData = <?= json_encode($this->list['chartMonthly'] ?? ['labels'=>[], 'revenue'=>[], 'profit'=>[]]); ?>;
            window.reportsChartDataWeekly = <?= json_encode($this->list['chartWeekly'] ?? ['labels'=>[], 'revenue'=>[], 'profit'=>[]]); ?>;
        </script>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="./public/src/js/reports.js"></script>
        <link rel="stylesheet" href="./public/src/css/reports.css">
<?php
    }
}
?>