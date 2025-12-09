<?php 
    require_once __DIR__ . "/../model/dashboardmodel.php";
    class DashboardController{
        private $model;

        public function __construct(){
            $this->model = new DashboardModel();
        }

        public function index(){
            // Fetch all dashboard data
            $data = [
                'stockSummary' => $this->model->getStockSummary(),
                'inventorySummary' => $this->model->getInventorySummary(),
                'recentTransactions' => $this->model->getRecentTransactions(),
                'salesSummary' => $this->model->getSalesSummary(),
                'lowStockItems' => $this->model->getLowStockItems()
            ];

            return $data;
        }

        // API endpoint for fetching dashboard stats
        public function fetchStats(){
            header('Content-Type: application/json');
            echo json_encode($this->index());
            exit;
        }
    }
?>