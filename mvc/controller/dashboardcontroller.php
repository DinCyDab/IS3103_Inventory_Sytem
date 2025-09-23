<?php 
    require_once __DIR__ . "/../model/dashboardmodel.php";
    class DashboardController{
        private $model;
        public function __construct(){
            $this->model = new DashboardModel();
        }

        public function loadReports(){
            $result = $this->model->loadData();
            $this->model->close();

            return $result;
        }
    }
?>