<<<<<<< Updated upstream
<?php 
    class ReportsView{
        public function render(){
            echo "Reports View";
=======
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

        if (isset($data['overview'])) {
            foreach ($data['overview'] as $i => $value) {
                if (isset($this->overviewStats[$i])) {
                    $this->overviewStats[$i]['value'] = $value;
                }
            }
>>>>>>> Stashed changes
        }

        $this->list = $data;
    }

    public function render() { ?>

        <div class="header"></div>

        <!-- search bar -->
        <div class="topbar">
            <div class="search-wrapper">
                <i class='bx bx-search search-icon'></i>
                
                <!-- functionality for search -->
                <input type="search" class="searchbar" id="reportsSearch" placeholder="Search product or order">
            </div>

            <div id="reportsSearchResults" style="position:relative;">
                <ul id="reportsSearchList" style="display:none; position:absolute; right:0; top:44px; background:#fff; border:1px solid #ddd; border-radius:6px; width:320px; max-height:300px; overflow:auto; padding:6px 0; z-index:9999; list-style:none; margin:0;">
                </ul>
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
                    <a href="#" class="see-all">See All</a>
                </div>

                <div class="bestseller-table">

                    <div class="bestseller-labels">
                        <div class="bestseller-col title">Category</div>
                        <div class="bestseller-col title">Turn Over</div>
                        <div class="bestseller-col title">Total Sold</div>
                    </div>

                    <div class="bestseller-divider"></div>

                    <?php 
                    
                    $bestCategories = $this->list['categories'] ?? [];

                    foreach ($bestCategories as $i => $row):
                    ?>

                    <div class="bestseller-row">
                        <div class="bestseller-col"><?= htmlspecialchars($row['category_name']) ?></div>

                        <div class="bestseller-col">
                            ₱<?= number_format(($row['total_revenue'] ?? 0), 2) ?>
                        </div>

                        <div class="bestseller-col increase">
                            <?= $row['total_sold'] ?? 0 ?>
                        </div>
                    </div>

                    <?php if ($i < count($bestCategories) - 1): ?>
                        <div class="bestseller-divider"></div>
                    <?php endif; ?>

                    <?php endforeach; ?>

                    <?php if (empty($bestCategories)): ?>
                        <div class="bestseller-row">No data available.</div>
                    <?php endif; ?>

                </div>
            </div>


        </div>    

       <!-- profit and revenue container -->
        <div class="profit-container">

            <div class="profit-header">
                <h3>Profit & Revenue</h3>

                <!-- BUTTON + DROPDOWN -->
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


        <!--list of sales of report container -->
        <div class="bestseller-product-container">

            <div class="bestseller-product-header">
                <h3>List of sales report</h3>
                <a href="#" class="see-all">See All</a>
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
                $bestProducts = $this->list['products'] ?? [
                    ["product" => "Tomato", "id" => "0", "category" => "Groceries", "qty" => "0", "turnover" => "₱0", "increase" => "0%"],
                    ["product" => "Onion", "id" => "0", "category" => "Groceries", "qty" => "0", "turnover" => "₱0", "increase" => "0%"],
                    ["product" => "Maggi", "id" => "0", "category" => "Groceries", "qty" => "0", "turnover" => "₱0", "increase" => "0%"],
                    ["product" => "Surf Excel", "id" => "0", "category" => "Groceries", "qty" => "0", "turnover" => "₱0", "increase" => "0%"],
                ];

                foreach ($bestProducts as $i => $row):
                ?>

                <div class="product-row">
                    <div class="product-col"><?= $row["product"] ?></div>
                    <div class="product-col"><?= $row["id"] ?></div>
                    <div class="product-col"><?= $row["category"] ?></div>
                    <div class="product-col"><?= $row["qty"] ?></div>
                    <div class="product-col"><?= $row["turnover"] ?></div>
                    <div class="product-col increase"><?= $row["increase"] ?></div>
                </div>

                <?php if ($i < count($bestProducts) - 1): ?>
                    <div class="product-divider"></div>
                <?php endif; ?>

                <?php endforeach; ?>

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
                    </div>

                    <?php foreach ($this->list['categories'] as $cat): ?>
                        <div class="modal-row">
                            <div><?= $cat['category_name'] ?></div>
                            <div><?= $cat['total_sold'] ?></div>
                        </div>
                    <?php endforeach; ?>
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
                        <?php foreach ($this->list['salesReportList'] as $row): ?>
                            <tr>
                                <td><?= $row['product_name'] ?></td>
                                <td><?= $row['product_ID'] ?></td>
                                <td><?= $row['category_name'] ?></td>
                                <td><?= $row['remaining_qty'] ?></td>
                                <td><?= $row['turnover'] ?></td>
                                <td><?= $row['increase'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        
        



        <script>
            window.reportsChartData = <?= json_encode($this->list['chart'] ?? [
                "labels" => ["Sep","Oct","Nov","Dec","Jan","Feb","Mar"],
                "revenue" => [0,0,0,0,0,0,0],
                "profit" => [0,0,0,0,0,0,0]
            ]); ?>;
        </script>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <script src="./public/src/js/chart-reports.js"></script>


        <!-- search bar function -->
        <script>
            const searchInput = document.getElementById("reportsSearch");

            searchInput.addEventListener("keypress", function(e) {
                if (e.key === "Enter") {
                    const query = searchInput.value.trim();
                    // used AJAX to fetch results and show dropdown
                    if (!query) return;

                    const list = document.getElementById("reportsSearchList");
                    list.innerHTML = '<li style="padding:10px;text-align:center;">Searching…</li>';
                    list.style.display = "block";

                    fetch('?view=reports&action=search&q=' + encodeURIComponent(query), {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(resp => resp.json())
                    .then(data => {
                        list.innerHTML = '';
                        if (!data || data.length === 0) {
                            const li = document.createElement('li');
                            li.style.padding = '8px 14px';
                            li.textContent = 'No results.';
                            list.appendChild(li);
                            return;
                        }

                        // render returned product rows 
                        data.forEach(item => {
                            const li = document.createElement('li');
                            li.style.padding = '8px 14px';
                            li.style.cursor = 'pointer';
                            li.style.borderBottom = '1px solid #f2f2f2';
                            const name = item.product_name || item.productName || item.product || item.productName || item.name || 'Unnamed';
                            const id   = item.product_ID || item.productID || item.id || item.product_id || '';
                            li.innerHTML = `<div style="font-weight:600;">${name}</div><div style="font-size:12px;color:#6b7280;">ID: ${id}</div>`;
                            li.addEventListener('click', () => {

                                searchInput.value = name;
                                list.style.display = 'none';
                            });
                            list.appendChild(li);
                        });
                    })
                    .catch(err => {
                        list.innerHTML = '<li style="padding:10px;text-align:center;">Error fetching results</li>';
                        console.error(err);
                    });
                }
            });

            // hide results 
            document.addEventListener('click', (e) => {
                const list = document.getElementById("reportsSearchList");
                const wrapper = document.querySelector('.search-wrapper');
                if (!wrapper.contains(e.target)) {
                    list.style.display = 'none';
                }
            });
        </script>


        <script>
            // opens the box
            document.querySelector('.bestseller-container .see-all').addEventListener('click', function(e) {
                e.preventDefault();
                document.getElementById('bestseller-modal').classList.add('active');
                document.body.classList.add('modal-open');
            });

            // closes the box
            document.querySelector('.modal-close').addEventListener('click', function() {
                document.getElementById('bestseller-modal').classList.remove('active');
                document.body.classList.remove('modal-open');
            });

            document.getElementById('bestseller-modal').addEventListener('click', function(e) {
                if (e.target.classList.contains('modal-overlay')) {
                    document.getElementById('bestseller-modal').classList.remove('active');
                    document.body.classList.remove('modal-open');
                }
            });

            // open sakes report box
            document.querySelector("#sales-report-see-all").addEventListener("click", function(e) {
                e.preventDefault();
                document.querySelector("#salesReportModal").style.display = "flex";
            });

            // close sales report box
            document.querySelector("#closeSalesReportModal").addEventListener("click", function() {
                document.querySelector("#salesReportModal").style.display = "none";
            });

        </script>





<?php
    }
}
?>