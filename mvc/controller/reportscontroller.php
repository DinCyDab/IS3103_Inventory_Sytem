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
}