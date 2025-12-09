<?php
    // Enable PHP errors
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    
    session_start();

    // Navigation
    require_once __DIR__ . "/mvc/view/navigation.php";

    //Add your view here
    require_once __DIR__ . "/mvc/view/dashboard.php";
    require_once __DIR__ . "/mvc/view/accounts.php";
    require_once __DIR__ . "/mvc/view/login.php";
    require_once __DIR__ . "/mvc/view/logout.php";
    require_once __DIR__ . "/mvc/view/inventory.php";
    require_once __DIR__ . "/mvc/view/sales.php";
    require_once __DIR__ . "/mvc/view/reports.php";
    require_once __DIR__ . "/mvc/view/settings.php";
    require_once __DIR__ . "/mvc/view/notfound.php";

    //Add your controller here
    require_once "./mvc/controller/inventorycontroller.php";
    require_once "./mvc/controller/reportscontroller.php";
    require_once "./mvc/controller/dashboardcontroller.php";
    require_once "./mvc/controller/salescontroller.php";

    // Check login status
    if(isset($_SESSION["account"])){
        $isLoggedIn = true;
        $userRole = $_SESSION["account"]["role"] ?? 'staff';
        
        // Define permissions
        $permissions = [
            'super_admin' => ['dashboard', 'accounts', 'inventory', 'sales', 'reports', 'settings', 'logout', 
                            'createProduct', 'updateProduct', 'deleteProduct', 'fetchStats', 'paginated', 'allProducts', 'searchProducts', 'getPaginatedSales'],
            'admin' => ['dashboard', 'accounts', 'inventory', 'sales', 'reports', 'settings', 'logout',
                       'createProduct', 'updateProduct', 'deleteProduct', 'fetchStats', 'paginated', 'allProducts', 'searchProducts', 'getPaginatedSales'],
            'staff' => ['inventory', 'paginated', 'allProducts', 'searchProducts', 'fetchStats', 'sales', 'reports', 'settings', 'logout', 'getPaginatedSales']
        ];
        
        $allowedViews = $permissions[$userRole] ?? ['sales', 'reports', 'settings', 'logout'];
        
        // Get requested view
        $requestedView = $_GET['view'] ?? null;
        
        // If no view requested, redirect to default based on role
        if(!$requestedView){
            if($userRole === 'staff'){
                header("Location: index.php?view=sales");
                exit();
            } else {
                header("Location: index.php?view=dashboard");
                exit();
            }
        }
        
        // Check if user has permission to access requested view
        if(!in_array($requestedView, $allowedViews)){
            // For AJAX requests, return JSON error instead of redirect
            if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'){
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'error' => 'permission_denied',
                    'message' => 'You do not have permission to access this resource'
                ]);
                exit();
            }
            
            $view = "notfound";
        }
        
        $view = $requestedView;
    } else {
        $isLoggedIn = false;
        $view = "login";
        $userRole = null;
        
        // If not logged in and trying to access protected resources
        $requestedView = $_GET['view'] ?? null;
        if($requestedView && $requestedView !== 'login'){
            // For AJAX requests, return JSON error
            if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'){
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'error' => 'not_authenticated',
                    'message' => 'Please login to access this resource'
                ]);
                exit();
            }
            
            // For regular requests, redirect to login
            header("Location: index.php?view=login");
            exit();
        }
    }

    // Handle dashboard routing
    if($isLoggedIn && $view === 'dashboard'){
        $dashboardController = new DashboardController();
        $dashboardData = $dashboardController->index();

        $page = new DashboardView();
        $page->setDashboardData($dashboardData);
    }

    // Handle dashboard stats API endpoint
    elseif($isLoggedIn && $view === 'dashboardStats'){
        $dashboardController = new DashboardController();
        $dashboardController->fetchStats();
        exit;
    }

    // SALES router
    elseif ($isLoggedIn && $view === 'sales') {
        $salesController = new SalesController();

        // Check if this is an AJAX request for paginated data
        if (isset($_GET['action']) && $_GET['action'] === 'getPaginatedSales') {
            $salesController->getPaginatedSales();
            exit;
        }

        // Otherwise render the sales page normally
        $page = new SalesView();
    }

    // Handle inventory routing - check permissions first
    elseif($isLoggedIn && in_array($view, ['inventory', 'createProduct', 'updateProduct', 'deleteProduct', 'fetchStats', 'paginated', 'allProducts', 'searchProducts'])){
        
        // Double-check permission (already checked above, but extra safety)
        if(!in_array($view, $allowedViews)){
            if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'){
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'error' => 'permission_denied',
                    'message' => 'You do not have permission to access inventory'
                ]);
                exit();
            }
            
            $view = "notfound";
        }

        $controller = new ProductController();

        switch($view){
            case "inventory":
                $inventoryData = $controller->index();

                $page = new InventoryView();
                $page->setProducts($inventoryData["products"]);
                $page->setOverviewStats($inventoryData["overviewStats"]);
                break;

            case "createProduct":
                $controller->create();
                exit;

            case "updateProduct":
                $controller->update();
                exit;

            case "deleteProduct":
                $controller->delete();
                exit;
            
            case 'fetchStats':
                $controller->fetchStats();
                exit;

            case "paginated":
                $controller->paginated();
                exit;

            case "allProducts":
                $controller->allProducts();
                exit;

            case "searchProducts":
                $controller->search();
                exit;
        }
    } 
    
    // Handle reports routing
    elseif($isLoggedIn && $view === 'reports'){
        $reportsController = new ReportsController();

        // Check if this is a search AJAX request
        if(isset($_GET['action']) && $_GET['action'] === 'search'){
            $reportsController->search();
            exit;
        }

        // Otherwise render the reports page normally
        $page = $reportsController->index();
    }
    
    else{
        // Handle other views
        switch($view){
            case "dashboard":
                $page = new DashboardView();
                break;
            case "login":
                $page = new LoginView();
                break;
            case "logout":
                $page = new LogoutView();
                break;
            case "accounts":
                $page = new AccountsView();
                break;
            case "sales":
                $page = new SalesView();
                break;
            case "reports":
                $page = new ReportsView();
                break;
            case "settings":
                $page = new SettingsView();
                break;
            case "staffreport":
                $page = new StaffReport();
                break;
            default:
                $page = new NotFound();
        }
    }

    // Load navigation
    $navigation = $isLoggedIn ? new Navigation() : null;
?>

<!DOCTYPE html>
<html>
    <head>
        <meta name="color-scheme" content="light">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="./public/src/css/styles.css">
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    </head>
    <body>
        <header>
            <?php if($isLoggedIn && $navigation instanceof Navigation): ?>
                <div class="sidebar">
                    <?php $navigation->render(); ?>
                </div>
            <?php endif; ?>
        </header>
        <main>
            <?php $page->render();?>
        </main>

        <script src="./public/src/js/script.js"></script>
    </body>
</html>