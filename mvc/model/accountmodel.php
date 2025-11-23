<?php 
    //Use this as a template for model, just copy and paste
    require_once __DIR__ . "/../api/config.php";

    //Change ModelName to the real model name
    class AccountModel{
        //Read the config.php file for reference. Inside mvc/api/config.php
        private $config;
        public function __construct(){
            //Change port to any associated port if necessary, for example:
            //$this->config = new Config(port: 3307);            
            $this->config = new Config();
        }

        public function createAccount($first_name, $last_name, $password, $email, $contact_number, $role){
            $sql = "INSERT INTO account(first_name, last_name, password, email, contact_number, role)
                    VALUES('$first_name', '$last_name', '$password', '$email', '$contact_number', '$role')";

            return $this->config->query($sql);
        }

        //Don't forget to close the connection
        public function close(){
            $this->config->close();
        }
    }
?>