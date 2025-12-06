<?php
require_once __DIR__ . "/../api/config.php";

class ReportsModel {

    private $conn;

    public function __construct() {
        $this->conn = new Config();
    }

    /* Load the sales report */
    public function loadSalesReport() {
        $sql = "
            SELECT
                s.transaction_ID,
                s.date_time,
                s.products AS product_name,
                COALESCE(i.productID, 'N/A') AS productID,
                COALESCE(i.category, 'Unknown') AS category_name,
                COALESCE(CONCAT(i.quantity, ' ', i.unit), 'N/A') AS remaining_qty,
                CAST(s.order_value AS DECIMAL(10,2)) AS turnover,
                CAST(s.quantity_sold AS UNSIGNED) AS increase,
                s.customer_name,
                s.payment_method
            FROM salesreport s
            LEFT JOIN inventory i ON i.productName = s.products
            ORDER BY s.date_time DESC
        ";
        $result = $this->conn->read($sql);
        return $result ?: [];
    }

    /* Overview stats */
    public function getOverviewStats() {
        // Total revenue and profit
        $sql = "
            SELECT  
                SUM(CAST(order_value AS DECIMAL(10,2))) AS revenue,
                SUM(CAST(order_value AS DECIMAL(10,2)) * 0.30) AS profit,
                COUNT(transaction_ID) AS total_sales
            FROM salesreport
        ";
        $row = $this->conn->readOne($sql);
        $revenue = floatval($row['revenue'] ?? 0);
        $profit  = floatval($row['profit'] ?? 0);
        $totalSales = intval($row['total_sales'] ?? 0);

        // Net purchase value
        // No purchase_price in inventory implemented -- initialized to 0
        $sqlPurchase = "
            SELECT 
                SUM(
                    CAST(COALESCE(i.purchase_price,0) AS DECIMAL(10,2)) *
                    CAST(COALESCE(s.quantity_sold,0) AS UNSIGNED)
                ) AS net_purchase
            FROM salesreport s
            LEFT JOIN inventory i ON TRIM(LOWER(i.productName)) = TRIM(LOWER(s.products))
        ";
        $purchaseRow = $this->conn->readOne($sqlPurchase);
        $netPurchase = floatval($purchaseRow['net_purchase'] ?? 0);

        // MoM profit in peso
        $sqlMoM = "
            SELECT
                SUM(CAST(order_value AS DECIMAL(10,2)) * 0.30) AS current_month_profit
            FROM salesreport
            WHERE YEAR(date_time) = YEAR(CURDATE())
            AND MONTH(date_time) = MONTH(CURDATE())
        ";
        $currentMonthRow = $this->conn->readOne($sqlMoM);
        $currentMonthProfit = floatval($currentMonthRow['current_month_profit'] ?? 0);

        $sqlPrevMonth = "
            SELECT
                SUM(CAST(order_value AS DECIMAL(10,2)) * 0.30) AS prev_month_profit
            FROM salesreport
            WHERE YEAR(date_time) = YEAR(CURDATE())
            AND MONTH(date_time) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
        ";
        $prevMonthRow = $this->conn->readOne($sqlPrevMonth);
        $prevMonthProfit = floatval($prevMonthRow['prev_month_profit'] ?? 0);

        $momProfit = $currentMonthProfit;

        // YoY profit in peso
        $sqlCurrentYear = "
            SELECT SUM(CAST(order_value AS DECIMAL(10,2)) * 0.30) AS current_year_profit
            FROM salesreport
            WHERE YEAR(date_time) = YEAR(CURDATE())
        ";
        $sqlPrevYear = "
            SELECT SUM(CAST(order_value AS DECIMAL(10,2)) * 0.30) AS prev_year_profit
            FROM salesreport
            WHERE YEAR(date_time) = YEAR(CURDATE())-1
        ";
        $currentYearProfit = floatval($this->conn->readOne($sqlCurrentYear)['current_year_profit'] ?? 0);
        $prevYearProfit = floatval($this->conn->readOne($sqlPrevYear)['prev_year_profit'] ?? 0);
        $yoyProfit = $currentYearProfit;

        return [
            'total_profit' => $profit,
            'revenue' => $revenue,
            'sales' => $totalSales,
            'net_purchase_value' => $netPurchase,
            'net_sales_value' => $revenue,
            'mom_profit' => $momProfit, // in peso
            'yoy_profit' => $yoyProfit  // in peso
        ];
    }

    // Top selling products
    public function getTopSellingProducts($limit = 4) {
        $sql = "
            SELECT 
                s.products AS product_name,
                COALESCE(i.productID, 'N/A') AS productID,
                COALESCE(i.category, 'Unknown') AS category,
                COALESCE(CONCAT(i.quantity, ' ', i.unit), 'N/A') AS remaining_qty,
                SUM(CAST(s.order_value AS DECIMAL(10,2))) AS turnover,
                SUM(CAST(s.quantity_sold AS UNSIGNED)) AS total_sold,
                CASE 
                    WHEN (i.quantity + SUM(CAST(s.quantity_sold AS UNSIGNED))) > 0 
                    THEN ROUND((SUM(CAST(s.quantity_sold AS UNSIGNED)) / (i.quantity + SUM(CAST(s.quantity_sold AS UNSIGNED)))) * 100, 2)
                    ELSE 0
                END AS increase_percent
            FROM salesreport s
            LEFT JOIN inventory i ON i.productName = s.products
            GROUP BY s.products, i.productID, i.category, i.quantity, i.unit
            ORDER BY turnover DESC
            LIMIT $limit
        ";
        
        $result = $this->conn->read($sql);
        return $result ?: [];
    }

    // Best selling categories
    public function getBestSellingCategories($limit = 3) {
        $sql = "
            SELECT 
                COALESCE(i.category, 'Unknown') AS category_name,
                SUM(CAST(s.quantity_sold AS UNSIGNED)) AS total_sold,
                SUM(CAST(s.order_value AS DECIMAL(10,2))) AS total_revenue,
                ROUND(
                    SUM(CAST(s.quantity_sold AS UNSIGNED)) * 100.0 / 
                    (SELECT SUM(CAST(quantity_sold AS UNSIGNED)) FROM salesreport),
                2) AS percentage_increase
            FROM salesreport s
            LEFT JOIN inventory i ON i.productName = s.products
            WHERE i.category IS NOT NULL
            GROUP BY i.category
            ORDER BY total_revenue DESC
            LIMIT $limit
        ";

        $result = $this->conn->read($sql);

        if (empty($result)) {
            $sql = "
                SELECT 
                    'All Products' AS category_name,
                    SUM(CAST(quantity_sold AS UNSIGNED)) AS total_sold,
                    SUM(CAST(order_value AS DECIMAL(10,2))) AS total_revenue,
                    100 AS percentage_increase
                FROM salesreport
            ";
            $result = $this->conn->read($sql);
        }

        return $result ?: [];
    }

    // Monthly sales chart
    public function getMonthlySalesChart() {
        $sql = "
            SELECT 
                MONTH(date_time) AS month_number,
                DATE_FORMAT(date_time, '%M') AS month_name,
                SUM(CAST(order_value AS DECIMAL(10,2))) AS revenue,
                SUM(CAST(order_value AS DECIMAL(10,2)) * 0.3) AS profit
            FROM salesreport
            WHERE YEAR(date_time) = YEAR(CURDATE())
            GROUP BY month_number, month_name
            ORDER BY month_number
        ";
        $rows = $this->conn->read($sql);

        $labels = [];
        $revenue = [];
        $profit = [];

        if ($rows && is_array($rows)) {
            foreach ($rows as $row) {
                $labels[] = $row['month_name'];
                $revenue[] = floatval($row['revenue']);
                $profit[] = floatval($row['profit']);
            }
        }

        return [
            'labels' => $labels,
            'revenue' => $revenue,
            'profit' => $profit
        ];
    }

    // Weekly sales chart
    public function getWeeklySalesChart() {
        $sql = "
            SELECT 
                WEEK(date_time, 1) AS week_number,
                SUM(CAST(order_value AS DECIMAL(10,2))) AS revenue,
                SUM(CAST(order_value AS DECIMAL(10,2)) * 0.3) AS profit
            FROM salesreport
            WHERE YEAR(date_time) = YEAR(CURDATE())
            GROUP BY week_number
            ORDER BY week_number
        ";
        $rows = $this->conn->read($sql);

        $labels = [];
        $revenue = [];
        $profit = [];

        if ($rows && is_array($rows)) {
            foreach ($rows as $row) {
                $labels[] = "Week " . $row['week_number'];
                $revenue[] = floatval($row['revenue']);
                $profit[] = floatval($row['profit']);
            }
        }

        return [
            'labels' => $labels,
            'revenue' => $revenue,
            'profit' => $profit
        ];
    }
}