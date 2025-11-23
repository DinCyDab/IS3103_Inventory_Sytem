<?php 
    //Reference your model here
    //Change the dashboardmodel.php to your specific model
    require_once __DIR__ . "/../model/accountmodel.php";

    //Change the ControllerName to your controller name
    class AccountController{
        private $model;
        public function __construct(){
            $this->model = new AccountModel();
        }
        
        public function createAccount($first_name, $last_name, $password, $email, $contact_number, $role){
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $this->model->createAccount($first_name, $last_name, $hashed_password, $email, $contact_number, $role);

            $this->model->close();
        }
    }
?>