<?php 
    //Use this as a template for model, just copy and paste
    require_once __DIR__ . "/../api/config.php";

    //Change ModelName to the real model name
    class SettingsModel{
        //Read the config.php file for reference. Inside mvc/api/config.php
        private $config;
        public function __construct(){
            //Change port to any associated port if necessary, for example:
            //$this->config = new Config(port: 3307);            
            $this->config = new Config();
        }

        public function updateInfo($account_ID, $first_name, $last_name){
            $sql = "UPDATE account
                    SET first_name = '$first_name',
                    last_name = '$last_name'
                    WHERE account_ID = '$account_ID'";

            return $this->config->query($sql);
        }

        public function updatePassword($account_ID, $new_password){
            $sql = "UPDATE account
                    SET password = '$new_password'
                    WHERE account_ID = '$account_ID'";
            
            return $this->config->query($sql);
        }

        //Don't forget to close the connection
        public function close(){
            $this->config->close();
        }
    }
?>