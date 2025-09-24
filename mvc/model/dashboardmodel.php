<?php 
    require_once __DIR__ . "/../api/config.php";
    class DashboardModel{
        //Read the config.php file for reference. Inside mvc/api/config.php
        private $config;
        public function __construct(){
            //Change port to any associated port if necessary for example:
            //$this->config = new Config(port: 3307);            
            $this->config = new Config();
        }

        //Fetch all data inside the salesreport table
        public function loadData(){
            $sql = "SELECT * FROM salesreport";
            return $this->config->read($sql);
        }

        //Don't forget to close the connection
        public function close(){
            $this->config->close();
        }
    }
?>