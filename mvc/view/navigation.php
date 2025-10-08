<?php 
    class Navigation{
        private $nav_items = [
            "Dashboard",
            "Accounts",
            "Inventory",
            "Sales",
            "Reports",
            "Settings"
        ];
        public function render(){
            foreach($this->nav_items as $view){
                ?>
                    <a href="<?php echo "?view=".$view?>"><?php echo $view?></a>
                <?php
            }
        }
    }
?>