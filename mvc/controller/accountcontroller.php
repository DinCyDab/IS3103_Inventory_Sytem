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

        // Check if current user can modify target account
        private function canModifyAccount($target_account_id){
            if(!isset($_SESSION["account"])){
                return false;
            }

            $current_user = $_SESSION["account"];
            $current_role = $current_user["role"];

            // Get target account details
            $target_account = $this->model->getAccountById($target_account_id);

            if(!$target_account){
                return false;
            }

            $target_role = $target_account["role"];

            // Super admin can modify everyone except other super admins
            if($current_role === 'super_admin'){
                return $target_role !== 'super_admin' || $target_account_id == $current_user["account_ID"];
            }

            // Admin can only modify staff
            if($current_role === 'admin'){
                return $target_role === 'staff';
            }

            // Staff cannot modify anyone
            return false;
        }
        
        public function createAccount($account_ID, $first_name, $last_name, $password, $email, $contact_number, $role){
            // Check permissions
            if(!isset($_SESSION["account"])){
                return false;
            }

            $current_role = $_SESSION["account"]["role"];

            // Only super_admin and admin can create accounts
            if(!in_array($current_role, ['super_admin', 'admin'])){
                return false;
            }

            // Admin cannot create admin or super_admin accounts
            if($current_role === ' admin' && in_array($role, ['admin', 'super_admin'])){
                return false;
            }
            
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $this->model->createAccount($account_ID, $first_name, $last_name, $hashed_password, $email, $contact_number, $role);

            $this->model->close();

            return true;
        }

        public function deleteAccount($account_ID){
            if(!$this->canModifyAccount($account_ID)){
                return false;
            }

            $this->model->deleteAccount($account_ID);

            $this->model->close();

            return true;
        }

        public function loadAccount($filter){
            $results = $this->model->loadAccount($filter);

            return $results;
        }

        public function updateAccount($account_ID, $first_name, $last_name, $password, $email, $contact_number, $role, $status){
            if(!$this->canModifyAccount($account_ID)){
                return false;
            }

            // prevent role elevation
            $current_role = $_SESSION["account"]["role"];
            $target_account = $this->model->getAccountById($account_ID);

            // Admin cannot change role to admin or super admin
            if($current_role === 'admin' && in_array($role, ['admin', 'super_admin'])){
                return false;
            }
            
            if($password != ""){
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $this->model->updateAccount($account_ID, $first_name, $last_name, $hashed_password, $email, $contact_number, $role, $status);
            }
            else{
                $this->model->updateAccountWithoutPassword($account_ID, $first_name, $last_name, $email, $contact_number, $role, $status);
            }
        
            $this->model->close();
            return true;
        }

        public function closeConnection(){
            $this->model->close();
        }
    }
?>