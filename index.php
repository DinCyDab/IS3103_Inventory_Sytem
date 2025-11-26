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

    //Add your controller here
    require_once "./mvc/controller/inventorycontroller.php";

    // ROUTER
    //$_GET['view'] comes from navigation.php

    // Check login status
    if(isset($_SESSION["account"])){
        $isLoggedIn = true;
        $view =  $_GET['view'] ?? "dashboard";
    } else{
        // $view = $_GET["view"] ?? "login";
        $isLoggedIn = false;
        $view = "login";
    }

    // if(isset($_GET["view"])){
    //     $view = $_GET["view"];
    // }


    // Handle inventory routing only if logged in
    if($isLoggedIn && in_array($view, ['inventory', 'createProduct', 'updateProduct', 'deleteProduct'])){

        $controller = new ProductController();

        switch($view){
            case "inventory":
                $products = $controller->index(); // Get Data
                $page = new InventoryView($products); // Load View
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
        }
    } else{
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
            default:
                $page = new LoginView();
        }
    }

    // Load navigation
    $navigation = $isLoggedIn ? new Navigation() : null;
?>

<!DOCTYPE html>
<html>
    <head>
        <meta name="color-scheme" content="dark light">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="./public/src/css/styles.css">
        <link rel="stylesheet" href="./public/src/css/inventory.css">
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    </head>
    <body>
        <header>
            <!-- Add your view as a list in class Navigation inside navigation.php -->
            <?php if($isLoggedIn && $navigation instanceof Navigation): ?>
                <div class="sidebar">
                    <?php $navigation->render(); ?>
                </div>
            <?php endif; ?>
        </header>
        <main>
            <?php $page->render();?>
        </main>

        <script src="./public/src/js/inventoryscript.js"></script>
        <script src="./public/src/js/script.js"></script>
    </body>
</html>