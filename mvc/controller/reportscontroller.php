<?php
require_once __DIR__ . "/../model/reportsmodel.php";
require_once __DIR__ . "/../view/reports.php";

class ReportsController {

    private $model;

    public function __construct() {
        $this->model = new ReportsModel();
    }

    public function index() {

        // fetch actual data from model
        $overview     = $this->model->getOverviewStats();
        $categories   = $this->model->getBestSellingCategories();
        $products     = $this->model->getBestSellingProducts();

        // FIXED: Load Sales Report using correct SQL
        $sales = $this->model->loadSalesReport();

        $chart        = $this->model->getProfitRevenueChart();

        // If chart is empty 
        if (empty($chart)) {
            $chart = [
                "labels" => ["Sep","Oct","Nov","Dec","Jan","Feb","Mar"],
                "revenue" => [0,0,0,0,0,0,0],
                "profit" => [0,0,0,0,0,0,0]
            ];
        }

        // Send to View
        $data = [
            "overview"      => $overview,
            "categories"    => $categories,
            "products"      => $products,
            "salesReport"   => $sales,
            "chart"         => $chart,
        ];

        return new ReportsView($data);
    }

    public function search() {

        if (!isset($_GET["q"])) {
            echo json_encode([]);
            return;
        }

        $keyword = $_GET["q"];

        // for the search
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        // connect to database
        require_once __DIR__ . "/../model/db_connection.php";
        $db = new Database();
        $conn = $db->connect();

        // prevent SQL errors when inventory table doesn't exist
        $stmt = $conn->prepare("SELECT * FROM inventory WHERE productName LIKE ?");
        $searchTerm = "%$keyword%";
        $stmt->bind_param("s", $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        // return JSON for AJAX
        header("Content-Type: application/json");
        echo json_encode($data);
    }

}
