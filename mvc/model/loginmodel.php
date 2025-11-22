<?php 
    //Use this as a template for model, just copy and paste
    require_once __DIR__ . "/../api/config.php";

    //Change ModelName to the real model name
    class LoginModel{
        //Read the config.php file for reference. Inside mvc/api/config.php
        private $config;
        public function __construct(){
            //Change port to any associated port if necessary, for example:
            //$this->config = new Config(port: 3307);            
            $this->config = new Config();
        }
        public function login($account_ID){
            $sql = "SELECT * from account
                    WHERE account_ID = $account_ID";
            return $this->config->read($sql);
        }

        //Don't forget to close the connection
        public function close(){
            $this->config->close();
        }
    }
?>