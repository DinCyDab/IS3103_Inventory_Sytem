<?php 
    class LogoutView{
        public function __construct()
        {}

        public function render(){
            session_destroy();
            session_unset();

            header("Location: index.php?view=login");
            exit();
        }
    }
?>