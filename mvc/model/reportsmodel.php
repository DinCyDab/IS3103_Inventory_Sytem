<?php
require_once __DIR__ . "/../api/config.php";

class ReportsModel {
    private $conn;

    public function __construct() {
        $this->conn = new Config();
    }

    public function loadSalesReport() {
        $sql = "
            SELECT
                st.transaction_id AS transaction_ID,
                st.date_time,
                si.product_name,
                si.product_id AS productID,
                pc.category_name,
                CONCAT(i.quantity, ' ', COALESCE(i.unit, 'pcs')) AS remaining_qty,
                CAST(si.subtotal AS DECIMAL(10,2)) AS turnover,
                CAST(si.quantity_sold AS UNSIGNED) AS increase,
                st.customer_name,
                st.payment_method
            FROM sales_transactions st
            JOIN sales_items si ON st.transaction_id = si.transaction_id
            LEFT JOIN inventory i ON si.product_id = i.productID
            LEFT JOIN product_categories pc ON i.category_id = pc.category_id
            WHERE st.status = 'completed'
            ORDER BY st.date_time DESC
        ";
        $result = $this->conn->read($sql);
        return $result ?: [];
    }

    public function getOverviewStats() {
        // Total revenue
        $sqlRevenue = "
            SELECT  
                SUM(CAST(total_amount AS DECIMAL(10,2))) AS revenue,
                COUNT(DISTINCT transaction_id) AS total_transactions
            FROM sales_transactions
            WHERE status = 'completed'
        ";
        $row = $this->conn->readOne($sqlRevenue);
        $revenue = floatval($row['revenue'] ?? 0);

        // Check if cost_price exists in inventory table
        $costPriceExists = !empty($this->conn->read("SHOW COLUMNS FROM inventory LIKE 'cost_price'"));

        if ($costPriceExists) {
            // Total cost using cost_price
            $sqlCost = "
                SELECT 
                    SUM(CAST(si.quantity_sold AS UNSIGNED) * CAST(i.cost_price AS DECIMAL(10,2))) AS total_cost
                FROM sales_items si
                JOIN sales_transactions st ON si.transaction_id = st.transaction_id
                JOIN inventory i ON si.product_id = i.productID
                WHERE st.status = 'completed'
            ";
            $costRow = $this->conn->readOne($sqlCost);
            $totalCost = floatval($costRow['total_cost'] ?? 0);

            $grossProfit = $revenue - $totalCost;
        } else {
            // Fallback if cost_price doesn't exist
            $totalCost = 0;
            $grossProfit = $revenue * 0.3; // Assume 30% profit margin
        }

        // Month-over-Month (MoM)
        $sqlCurrentMonth = "
            SELECT SUM(total_amount) AS current_month_revenue
            FROM sales_transactions
            WHERE status = 'completed'
            AND YEAR(date_time) = YEAR(CURDATE())
            AND MONTH(date_time) = MONTH(CURDATE())
        ";
        $currentMonth = floatval($this->conn->readOne($sqlCurrentMonth)['current_month_revenue'] ?? 0);

        $sqlPreviousMonth = "
            SELECT SUM(total_amount) AS previous_month_revenue
            FROM sales_transactions
            WHERE status = 'completed'
            AND YEAR(date_time) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
            AND MONTH(date_time) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
        ";
        $previousMonth = floatval($this->conn->readOne($sqlPreviousMonth)['previous_month_revenue'] ?? 0);

        $momProfit = $previousMonth > 0 ? (($currentMonth - $previousMonth) / $previousMonth) * 100 : 0;

        // Year-over-Year (YoY)
        $sqlCurrentYear = "
            SELECT SUM(total_amount) AS current_year_revenue
            FROM sales_transactions
            WHERE status = 'completed'
            AND YEAR(date_time) = YEAR(CURDATE())
        ";
        $currentYear = floatval($this->conn->readOne($sqlCurrentYear)['current_year_revenue'] ?? 0);

        $sqlPreviousYear = "
            SELECT SUM(total_amount) AS previous_year_revenue
            FROM sales_transactions
            WHERE status = 'completed'
            AND YEAR(date_time) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 YEAR))
        ";
        $previousYear = floatval($this->conn->readOne($sqlPreviousYear)['previous_year_revenue'] ?? 0);

        $yoyProfit = $previousYear > 0 ? (($currentYear - $previousYear) / $previousYear) * 100 : 0;

        return [
            'total_profit' => $grossProfit,
            'revenue' => $revenue,
            'sales' => $revenue,
            'net_purchase_value' => $totalCost,
            'net_sales_value' => $revenue,
            'mom_profit' => $momProfit,
            'yoy_profit' => $yoyProfit,
            'current_month_revenue' => $currentMonth,
            'current_year_revenue' => $currentYear
        ];
    }

        public function getTopSellingProducts($limit = 4) {
            $sql = "
                SELECT 
                    si.product_name,
                    si.product_id AS productID,
                    pc.category_name AS category,
                    CONCAT(i.quantity, ' ', COALESCE(i.unit, 'pcs')) AS remaining_qty,
                    SUM(CAST(si.subtotal AS DECIMAL(10,2))) AS turnover,
                    SUM(CAST(si.quantity_sold AS UNSIGNED)) AS total_sold
                FROM sales_items si
                JOIN sales_transactions st ON si.transaction_id = st.transaction_id
                LEFT JOIN inventory i ON si.product_id = i.productID
                LEFT JOIN product_categories pc ON i.category_id = pc.category_id
                WHERE st.status = 'completed'
                GROUP BY si.product_name, si.product_id, pc.category_name, i.quantity, i.unit
                ORDER BY turnover DESC
                LIMIT ?
            ";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $products = [];
            while ($row = $result->fetch_assoc()) {
                // Calculate increase percent (could be based on previous month or just a placeholder)
                // For now, using a simple calculation based on total sold
                $increasePercent = min(100, ($row['total_sold'] / 10) * 5); // Adjust formula as needed
                
                $products[] = [
                    'product_name' => $row['product_name'],
                    'productID' => $row['productID'],
                    'category' => $row['category'] ?? 'Uncategorized',
                    'remaining_qty' => $row['remaining_qty'],
                    'turnover' => $row['turnover'],
                    'total_sold' => $row['total_sold'],
                    'increase_percent' => round($increasePercent, 2)
                ];
            }
            
            return $products;
        }

    public function getBestSellingCategories($limit = 3) {
        $sql = "
            SELECT 
                pc.category_name,
                SUM(CAST(si.quantity_sold AS UNSIGNED)) AS total_sold,
                SUM(CAST(si.subtotal AS DECIMAL(10,2))) AS total_revenue
            FROM sales_items si
            JOIN sales_transactions st ON si.transaction_id = st.transaction_id
            LEFT JOIN inventory i ON si.product_id = i.productID
            LEFT JOIN product_categories pc ON i.category_id = pc.category_id
            WHERE st.status = 'completed' AND pc.category_name IS NOT NULL
            GROUP BY pc.category_name
            ORDER BY total_revenue DESC
            LIMIT ?
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $categories = [];
        while ($row = $result->fetch_assoc()) {
            // Calculate percentage increase (placeholder - could be MoM comparison)
            $percentageIncrease = min(100, ($row['total_sold'] / 50) * 10); // Adjust formula as needed
            
            $categories[] = [
                'category_name' => $row['category_name'],
                'total_sold' => $row['total_sold'],
                'total_revenue' => $row['total_revenue'],
                'percentage_increase' => round($percentageIncrease, 2)
            ];
        }
        
        return $categories;
    }

    public function getMonthlySalesChart() {
        $sql = "
            SELECT 
                MONTH(st.date_time) AS month_number,
                DATE_FORMAT(st.date_time, '%M') AS month_name,
                SUM(CAST(st.total_amount AS DECIMAL(10,2))) AS revenue
            FROM sales_transactions st
            WHERE st.status = 'completed'
            AND YEAR(st.date_time) = YEAR(CURDATE())
            GROUP BY month_number, month_name
            ORDER BY month_number
        ";
        $rows = $this->conn->read($sql);

        $labels = [];
        $revenue = [];

        foreach ($rows as $row) {
            $labels[] = $row['month_name'];
            $revenue[] = floatval($row['revenue']);
        }

        // Calculate profit as 30% of revenue (adjust this based on your business logic)
        $profit = array_map(fn($r) => $r * 0.3, $revenue);

        return [
            'labels' => $labels,
            'revenue' => $revenue,
            'profit' => $profit
        ];
    }

    public function getWeeklySalesChart() {
        $sql = "
            SELECT 
                WEEK(st.date_time, 1) AS week_number,
                SUM(CAST(st.total_amount AS DECIMAL(10,2))) AS revenue
            FROM sales_transactions st
            WHERE st.status = 'completed'
            AND YEAR(st.date_time) = YEAR(CURDATE())
            GROUP BY week_number
            ORDER BY week_number
        ";
        $rows = $this->conn->read($sql);

        $labels = [];
        $revenue = [];

        foreach ($rows as $row) {
            $labels[] = "Week " . $row['week_number'];
            $revenue[] = floatval($row['revenue']);
        }

        // Calculate profit as 30% of revenue
        $profit = array_map(fn($r) => $r * 0.3, $revenue);

        return [
            'labels' => $labels,
            'revenue' => $revenue,
            'profit' => $profit
        ];
    }

    public function searchProducts($query) {
        $searchPattern = "%" . $query . "%";
        
        $sql = "
            SELECT
                si.product_name,
                si.product_id AS productID,
                pc.category_name AS category,
                CONCAT(i.quantity, ' ', COALESCE(i.unit, 'pcs')) AS remaining_qty,
                SUM(CAST(si.subtotal AS DECIMAL(10,2))) AS turnover,
                SUM(CAST(si.quantity_sold AS UNSIGNED)) AS increase
            FROM sales_items si
            JOIN sales_transactions st ON si.transaction_id = st.transaction_id
            LEFT JOIN inventory i ON si.product_id = i.productID
            LEFT JOIN product_categories pc ON i.category_id = pc.category_id
            WHERE st.status = 'completed'
            AND (si.product_name LIKE ?
                 OR si.product_id LIKE ?
                 OR pc.category_name LIKE ?)
            GROUP BY si.product_name, si.product_id, pc.category_name, i.quantity, i.unit
            ORDER BY st.date_time DESC
            LIMIT 20
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sss", $searchPattern, $searchPattern, $searchPattern);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        
        return $products;
    }
}
?>