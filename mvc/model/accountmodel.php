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

        public function createAccount($account_ID, $first_name, $last_name, $password, $email, $contact_number, $role){
            $sql = "INSERT INTO account(account_ID, first_name, last_name, password, email, contact_number, role)
                    VALUES('$account_ID', '$first_name', '$last_name', '$password', '$email', '$contact_number', '$role')";

            return $this->config->query($sql);
        }

        public function deleteAccount($account_ID){
            $sql = "DELETE FROM account
                    WHERE account_ID = $account_ID";
                    
            return $this->config->query($sql);
        }

        public function loadAccount($filter){
            $sql = "SELECT * FROM Account " . $filter;

            return $this->config->read($sql);
        }

        public function updateAccount($account_ID, $first_name, $last_name, $hashed_password, $email, $contact_number, $role, $status){
            $sql = "UPDATE Account
                    SET first_name = '$first_name',
                    last_name = '$last_name',
                    password = '$hashed_password',
                    email = '$email',
                    contact_number = '$contact_number',
                    role = '$role',
                    status = '$status'
                    WHERE account_ID = $account_ID";
            
            return $this->config->query($sql);
        }

        public function updateAccountWithoutPassword($account_ID, $first_name, $last_name, $email, $contact_number, $role, $status){
            $sql = "UPDATE Account
                    SET first_name = '$first_name',
                    last_name = '$last_name',
                    email = '$email',
                    contact_number = '$contact_number',
                    role = '$role',
                    status = '$status'
                    WHERE account_ID = $account_ID";
            
            return $this->config->query($sql);
        }

        //Don't forget to close the connection
        public function close(){
            $this->config->close();
        }
    }
?>