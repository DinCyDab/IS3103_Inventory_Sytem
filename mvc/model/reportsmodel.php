<?php
require_once __DIR__ . "/../api/config.php";

class ReportsModel {

    private $conn;

    public function __construct() {
        $this->conn = new Config();
        }

        /* this part loads the sales report */
        public function loadSalesReport() {
            $query = "
                SELECT 
                    r.report_ID,
                    r.report_date,
                    r.report_time,
                    r.total_price,
                    r.status,
                    a.first_name,
                    a.last_name
                FROM salesreport r
                INNER JOIN account a ON r.account_ID = a.account_ID
                ORDER BY r.report_date DESC, r.report_time DESC
            ";

            return $this->conn->read($query);
        }

        /* 1. overview */
        public function getOverviewStats() {

            $sql = "
                SELECT  
                    SUM(total_price) AS revenue,
                    COUNT(report_ID) AS total_sales
                FROM salesreport
            ";

            return $this->conn->readOne($sql);
        }

        /* 2. best selling categories */
        public function getBestSellingCategories() {

            $sql = "
                SELECT 
                    c.category_name AS category_name,
                    SUM(s.quantity) AS total_sold,
                    SUM(s.price * s.quantity) AS total_revenue
                FROM salesreportitem s
                INNER JOIN products p ON p.product_ID = s.product_ID
                INNER JOIN category c ON c.category_ID = p.category_ID
                GROUP BY p.category_ID
                ORDER BY total_sold DESC
                LIMIT 5
            ";

            return $this->conn->read($sql);
        }

        /* 3. data for the monthly chart   */
        public function getMonthlySales() {

        $query = "
            SELECT 
                m.month_name,
                COALESCE(SUM(sri.quantity * sri.price), 0) AS revenue
            FROM (
                SELECT 1 AS m, 'January' AS month_name UNION
                SELECT 2, 'February' UNION
                SELECT 3, 'March' UNION
                SELECT 4, 'April' UNION
                SELECT 5, 'May' UNION
                SELECT 6, 'June' UNION
                SELECT 7, 'July' UNION
                SELECT 8, 'August' UNION
                SELECT 9, 'September' UNION
                SELECT 10, 'October' UNION
                SELECT 11, 'November' UNION
                SELECT 12, 'December'
            ) m
            LEFT JOIN salesreport sr ON MONTH(sr.report_date) = m.m
            LEFT JOIN salesreportitem sri ON sri.report_ID = sr.report_ID
            GROUP BY m.m, m.month_name
            ORDER BY m.m ASC
        ";

        return $this->conn->read($query);
    }



        /* 3. best selling products*/
        public function getBestSellingProducts() {

            $sql = "
                SELECT 
                    p.product_name AS product_name,
                    SUM(s.quantity) AS total_sold
                FROM salesreportitem s
                INNER JOIN products p ON p.product_ID = s.product_id
                GROUP BY s.product_id
                ORDER BY total_sold DESC
                LIMIT 5
            ";

            return $this->conn->read($sql);
        }

        /* 4. profit and revenue weekly chart */
        public function getProfitRevenueChart() {

            // get revenue per week
            $sql = "
                SELECT 
                    WEEK(report_date) AS week_number,
                    SUM(total_price) AS revenue
                FROM salesreport
                GROUP BY WEEK(report_date)
                ORDER BY WEEK(report_date)
            ";

            $rows = $this->conn->read($sql);

            if (!$rows || count($rows) == 0) {
                return [];
            }

            $labels = [];
            $revenue = [];
            $profit = [];

            foreach ($rows as $row) {
                $labels[] = "Week " . $row["week_number"];

                $revenue[] = (float)$row["revenue"];

                $profit[] = 0;
            }

            return [
                "labels" => $labels,
                "revenue" => $revenue,
                "profit" => $profit
            ];
        }


        /* 5. search bar */
        public function searchProducts($keyword) {

            $keyword = "%{$keyword}%";

            $sql = "
                SELECT productName, productID, category, quantity, price 
                FROM inventory 
                WHERE productName LIKE ? 
                OR productID LIKE ? 
                OR category LIKE ?
            ";

            return $this->conn->read($sql, [$keyword, $keyword, $keyword]);
        }


        // Fetch the products from inventory page
        public function getTopSellingProducts($limit = 5){
            $sql = "
                SELECT
                    p.productName AS product_name,
                    p.productID,
                    p.category AS category_name,
                    CONCAT(p.quantity, ' ', p.unit) AS remaining_qty,
                    SUM(s.quantity * s.price) AS turnover,
                    SUM(s.quantity) AS increase
                FROM 
            ";

            return $this->conn->read($sql);
        }

}
