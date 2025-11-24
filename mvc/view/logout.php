<?php 
    class LogoutView{
        public function __construct()
        {
            session_destroy();
            session_unset();

            header("Location: index.php");
            exit();
        }
    }
?>