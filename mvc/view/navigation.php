<?php 
    class Navigation{
        private $nav_items = [
            [ "label" => "Dashboard", "icon" => "bx bx-home-alt", "view" => "dashboard" ],
            [ "label" => "Accounts", "icon" => "bx bx-user", "view" => "accounts" ],
            [ "label" => "Inventory", "icon" => "bx bx-package", "view" => "inventory" ],
            [ "label" => "Sales", "icon" => "bx bx-cart", "view" => "sales" ],
            [ "label" => "Reports", "icon" => "bx bx-line-chart", "view" => "reports" ],
            [ "label" => "Settings", "icon" => "bx bx-cog", "view" => "settings" ],
            [ "label" => "Logout", "icon" => "bx bx-log-out", "view" => "logout" ],

        ];
        public function render(){
            // detect active view
            $active = $_GET["view"] ?? "dashboard";

            foreach($this->nav_items as $item){
                $view = $item['label'];
                $icon = $item['icon'];
                $isLogout = isset($item["logout"]) && $item["logout"] === true;

                // compute link
                $href = $isLogout ? "logout.php" : "index.php?view=" . $item["view"];

                // detect active class
                $isActive = !$isLogout && $active === $item["view"];
                ?>
                    <a href="<?= $href; ?>" class="<?= $isActive ? 'active' : ''; ?> <?= $isLogout ? 'logout-btn' : '' ?>">
                    <i class="<?php echo $icon; ?>"></i>
                    <span><?php echo $view; ?></span>
                    </a>
                <?php
            }
        }
    }
?>