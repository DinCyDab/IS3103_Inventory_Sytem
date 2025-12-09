<?php 
    //Reference your model here
    //Change the dashboardmodel.php to your specific model
    require_once __DIR__ . "/../model/staffreportmodel.php";

    //Change the ControllerName to your controller name
    class StaffReportController{
        private $model;
        public function __construct(){
            //Change DashboardModel() to your class model
            $this->model = new StaffReportModel();
        }
        
        //public functions here to be called from the 'view'
    }
?>