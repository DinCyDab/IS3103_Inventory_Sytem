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
        
        public function createAccount($account_ID, $first_name, $last_name, $password, $email, $contact_number, $role){
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $this->model->createAccount($account_ID, $first_name, $last_name, $hashed_password, $email, $contact_number, $role);

            $this->model->close();
        }

        public function deleteAccount($account_ID){
            $this->model->deleteAccount($account_ID);

            $this->model->close();
        }

        public function loadAccount($filter){
            $results = $this->model->loadAccount($filter);

            return $results;
        }

        public function updateAccount($account_ID, $first_name, $last_name, $password, $email, $contact_number, $role, $status){
            if($password != ""){
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $this->model->updateAccount($account_ID, $first_name, $last_name, $hashed_password, $email, $contact_number, $role, $status);
            }
            else{
                $this->model->updateAccountWithoutPassword($account_ID, $first_name, $last_name, $email, $contact_number, $role, $status);
            }
        
            $this->model->close();
        }

        public function closeConnection(){
            $this->model->close();
        }
    }
?>