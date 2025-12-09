<?php
    require_once __DIR__ . "/../model/loginmodel.php";
    class LoginController{
        private $login_model;
        public function __construct(){
            $this->login_model = new LoginModel();
        }

        public function loginHandler($account_ID, $password){
            $result = $this->login_model->login($account_ID);
            
            if(empty($result)){
                echo "Account Not Found";
                return false;
            }

            $user = $result[0];

            // Check if account is active
            if(isset($user["status"]) && $user["status"] === 'inactive'){
                echo "Account is inactive. Please contact administrator.";
                return false;
            }

            if(password_verify($password, $user["password"])){
                $_SESSION["account"] = $user;

                // Redirect based on role
                $default_page = $this->getDefaultPage($user["role"]);
                header("Location: index.php?view=" . $default_page);
                exit();
            }

            echo "Invalid Password";
            return false;
        }

        private function getDefaultPage($role){
            switch($role){
                case 'super_admin':
                case 'admin':
                    return 'dashboard';
                case 'staff':
                    return 'inventory';
            }
        }
    }
?>