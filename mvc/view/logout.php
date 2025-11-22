<?php 
    class Logout{
        public function __construct()
        {
            session_destroy();
            session_unset();

            header("Location: index.php");
            exit();
        }
    }
?>