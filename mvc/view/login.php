<?php 
    require_once __DIR__ . "/../controller/logincontroller.php";
    // require_once __DIR__ . "/../controller/accountcontroller.php";
    class LoginView{
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
            ?>
                <div class="main-wrapper">
                    <div class="login-image">
                        <img src="./public/images/login/login-image.png">
                    </div>
                    <div class="login-form-holder">
                        <img src="./public/images/login/login-image2.png">
                        <h2>Log in to your account</h2>
                        <p>Welcome back! Please enter your details</p>
                        <?php 
                            $this->loginForm();
                        ?>
                    </div>
                </div>

                <link href="./public/src/css/login.css" rel="stylesheet">
            <?php
        }
        public function loginForm(){
            ?>
                <form method="POST" class="login-form">
                    <div>
                        <label>Email</label>
                        <input type="text" name="account_ID" placeholder="Enter your email" required>
                    </div>
                    <div>
                        <label>Password</label>
                        <input type="password" name="password" placeholder="*******" required>
                    </div>
                    <div>
                        <a href="#">Forgot Password?</a>
                    </div>
                    <input class="form-sign-in" type="submit" name="login" value="Sign in">
                </form>
            <?php
        }
    }
?>