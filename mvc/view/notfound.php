<?php 

    // require_once __DIR__ . "/../controller/accountcontroller.php";
    class NotFound{
        public function render(){
            ?>
               <div class="section">
                <h1 class="error">404</h1>
                <div class="page">Ooops!!! The page you are looking for is not found</div>
                </div>

                 <link rel="stylesheet" href="./public/src/css/notfound.css">
            <?php
        }
       
    }
?>