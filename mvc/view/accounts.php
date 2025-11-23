<?php 
    require_once __DIR__ . "/../controller/accountcontroller.php";
    class Accounts{
        private $account_controller;
        public function __construct(){
            $this->account_controller = new AccountController();
        }
        public function render(){
            echo "Accounts View";
        }
    }
?>