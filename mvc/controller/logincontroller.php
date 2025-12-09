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

            if(password_verify($password, $user["password"])){
                $_SESSION["account"] = $user;
                
                if($user['role'] == 'staff'){
                    header("Location: index.php?view=staffreport");
                    exit();
                }
                else{
                    header("Location: index.php?view=dashboard");
                    exit();
                }
            }

            echo "Invalid Password";
            return false;
        }
    }
?>