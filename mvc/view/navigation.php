<?php 
    class Navigation{
        private $nav_items = [
            "Dashboard",
            "Reports",
            "Products",
            "Accounts",
            "Logout"
        ];
        public function render(){
            ?>
                <div>LOGO</div>
            <?php
            foreach($this->nav_items as $view){
                ?>
                    <a href="<?php echo "?view=".$view?>"><?php echo $view?></a>
                <?php
            }
        }
    }
?>