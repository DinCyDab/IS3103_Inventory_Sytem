<?php
require_once __DIR__ . "/../model/reportsmodel.php";
require_once __DIR__ . "/../view/reports.php";

class ReportsController {
    private $model;

    public function __construct() {
        $this->model = new ReportsModel();
    }

    public function index() {
        $overview = $this->model->getOverviewStats();
        $categories = $this->model->getBestSellingCategories(3);
        $products = $this->model->getTopSellingProducts(4);
        $salesReportList = $this->model->loadSalesReport();
        
        // Chart data
        $monthlyChart = $this->model->getMonthlySalesChart();
        $weeklyChart  = $this->model->getWeeklySalesChart();

        $data = [
            "overview"        => $overview,
            "categories"      => $categories,
            "products"        => $products,
            "chartMonthly"    => $monthlyChart,
            "chartWeekly"     => $weeklyChart,
            "salesReportList" => $salesReportList
        ];

        return new ReportsView($data);
    }

    public function search() {
        // Set headers first
        header("Content-Type: application/json");
        
        if (!isset($_GET["q"])) {
            echo json_encode([]);
            return;
        }

        $keyword = trim($_GET["q"]);
        
        if (empty($keyword)) {
            echo json_encode([]);
            return;
        }

        try {
            // Use the existing Config class connection
            require_once __DIR__ . "/../api/config.php";
            $config = new Config();
            
            // Search in sales report with product info
            $sql = "
                SELECT DISTINCT
                    s.products AS product_name,
                    COALESCE(i.productID, 'N/A') AS productID,
                    COALESCE(i.category, 'Unknown') AS category,
                    COALESCE(CONCAT(i.quantity, ' ', i.unit), 'N/A') AS remaining_qty,
                    SUM(CAST(s.order_value AS DECIMAL(10,2))) AS turnover,
                    SUM(CAST(s.quantity_sold AS UNSIGNED)) AS increase
                FROM salesreport s
                LEFT JOIN inventory i ON i.productName = s.products
                WHERE s.products LIKE ? 
                   OR i.productID LIKE ? 
                   OR i.category LIKE ?
                   OR s.transaction_ID LIKE ?
                GROUP BY s.products, i.productID, i.category, i.quantity, i.unit
                LIMIT 10
            ";
            
            $searchTerm = "%$keyword%";
            $stmt = $config->prepare($sql);
            $stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            
            echo json_encode($data);
            
        } catch (Exception $e) {
            error_log("Search error: " . $e->getMessage());
            echo json_encode(["error" => "Search failed"]);
        }
    }
}