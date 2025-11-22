<?php 
    require_once __DIR__ . "/../controller/logincontroller.php";
    // require_once __DIR__ . "/../controller/accountcontroller.php";
    class Login{
        public function __construct(){
            if(isset($_POST["login"])){
                $id = $_POST["account_ID"];
                $password = $_POST["password"];
                $login_controller = new LoginController();
                $login_controller->loginHandler($id, $password);
            }

            //Create account for admin with hash password
            // $account_controller = new AccountController();
            // $account_controller->createAccount("Juan", "Dela Cruz", "1234", "1234@gmail.com", "", 'admin');
        }
        public function render(){
            $this->header("Login");
            $this->loginForm();
        }
        public function loginForm(){
            ?>
                <form method="POST">
                    <input type="text" name="account_ID" placeholder="ID Number" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <input type="submit" name="login" value="Login">
                </form>
            <?php
        }
        public function header($header_name){
            ?>
                <h2><?php echo $header_name?></h2>
            <?php
        }
    }
?>