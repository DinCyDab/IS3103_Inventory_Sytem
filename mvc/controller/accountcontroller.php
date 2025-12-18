<?php 
    //Reference your model here
    require_once __DIR__ . "/../model/accountmodel.php";
    
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
                return $target_role !== 'super_admin';
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
            
            // No one can create super_admin accounts
            if($role === 'super_admin'){
                return false;
            }
            
            // Admin cannot create admin accounts, only staff
            if($current_role === 'admin' && $role !== 'staff'){
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
            
            // Prevent role elevation
            $current_role = $_SESSION["account"]["role"];
            $target_account = $this->model->getAccountById($account_ID);
            
            // No one can set super_admin role
            if($role === 'super_admin'){
                return false;
            }
            
            // Admin cannot change role to admin, only staff
            if($current_role === 'admin' && $role !== 'staff'){
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