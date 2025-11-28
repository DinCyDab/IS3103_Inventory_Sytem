<?php 
    require_once __DIR__ . "/../controller/accountcontroller.php";
    class AccountsView{
        private $account_controller;
        public function __construct(){
            $this->account_controller = new AccountController();
        }
        public function render(){ ?>
            <!-- <div class="header"></div> -->
    <?php } }
?>