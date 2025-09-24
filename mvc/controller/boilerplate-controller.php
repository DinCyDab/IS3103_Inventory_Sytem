<?php 
    //Reference your model here
    //Change the dashboardmodel.php to your specific model
    require_once __DIR__ . "/../model/dashboardmodel.php";

    //Change the ControllerName to your controller name
    class ControllerName{
        private $model;
        public function __construct(){
            //Change DashboardModel() to your class model
            $this->model = new DashboardModel();
        }
        
        //public functions here to be called from the 'view'
    }
?>