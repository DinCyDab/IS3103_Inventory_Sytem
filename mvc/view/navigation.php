<?php
class Navigation{
    private $userRole;
    private $allowedPages;
    
    private $nav_items = [
        [ "label" => "Dashboard", "icon" => "bx bx-home-alt", "view" => "dashboard" ],
        [ "label" => "Accounts", "icon" => "bx bx-user", "view" => "accounts" ],
        [ "label" => "Inventory", "icon" => "bx bx-package", "view" => "inventory" ],
        [ "label" => "Sales", "icon" => "bx bx-cart", "view" => "sales" ],
        [ "label" => "Reports", "icon" => "bx bx-line-chart", "view" => "reports" ],
        [ "label" => "Settings", "icon" => "bx bx-cog", "view" => "settings" ],
        [ "label" => "Logout", "icon" => "bx bx-log-out", "view" => "logout", "logout" => true ],
    ];
    
    public function __construct(){
        $this->userRole = $_SESSION["account"]["role"] ?? 'staff';
        $this->setAllowedPages();
    }
    
    private function setAllowedPages(){
        $permissions = [
            'super_admin' => ['dashboard', 'accounts', 'inventory', 'sales', 'reports', 'settings'],
            'admin' => ['dashboard', 'accounts', 'inventory', 'sales', 'reports', 'settings'],
            'staff' => ['sales', 'reports', 'settings']
        ];
        
        $this->allowedPages = $permissions[$this->userRole] ?? [];
    }
    
    private function isAllowed($page){
        return in_array($page, $this->allowedPages);
    }
    
    public function render(){
        // Detect active view
        $active = strtolower($_GET["view"] ?? "dashboard");
        ?>
        <!-- Navigation Logo -->
        <div class="nav-logo">
            <img src="./public/images/navigation/KASAMA.png" alt="Kasama Logo">
        </div>
        <?php
        foreach($this->nav_items as $item){
            $view = $item['label'];
            $icon = $item['icon'];
            $page = $item['view'];
            $isLogout = isset($item["logout"]) && $item["logout"] === true;
            
            // Check if user has access to this page (logout is always allowed)
            if(!$isLogout && !$this->isAllowed($page)){
                // Show disabled menu item
                ?>
                <span class="nav-disabled">
                    <i class="<?php echo $icon; ?>"></i>
                    <span><?php echo $view; ?></span>
                </span>
                <?php
                continue;
            }
            
            // Compute link - logout and other views go through index.php
            $href = "index.php?view=" . $page;
            
            // Detect active class (logout should never be active)
            $isActive = !$isLogout && $active === strtolower($page);
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