<?php
    //Add your view here
    require_once __DIR__ . "/mvc/view/navigation.php";
    require_once __DIR__ . "/mvc/view/dashboard.php";
    require_once __DIR__ . "/mvc/view/accounts.php";
    require_once __DIR__ . "/mvc/view/products.php";
    require_once __DIR__ . "/mvc/view/reports.php";
    require_once __DIR__ . "/mvc/view/login.php";
    require_once __DIR__ . "/mvc/view/logout.php";
    require_once __DIR__ . "/mvc/view/inventory.php";
    require_once __DIR__ . "/mvc/view/sales.php";
    require_once __DIR__ . "/mvc/view/reports.php";
    require_once __DIR__ . "/mvc/view/settings.php";

    session_start();

    $navigation = new Navigation();

    //$_GET['view'] comes from navigation.php

    if(isset($_SESSION["account"])){
        $view = "dashboard";
    }
    else{
        // $view = $_GET["view"] ?? "login";
        $view = "login";
    }

<<<<<<< Updated upstream
    if(isset($_GET["view"])){
        $view = $_GET["view"];
=======
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
                require_once "./mvc/controller/reportscontroller.php";
                $ctrl = new ReportsController();

                if (isset($_GET["action"]) && $_GET["action"] === "search") {
                    $ctrl->search();
                    exit;
                }
                
                $page = $ctrl->index();
                break;

                case "reportsSearch":
                //manages the search request for reports page  
                require_once "./mvc/controller/reportscontroller.php";
                $ctrl = new ReportsController();
                $ctrl->search(); 
                exit;

            case "settings":
                $page = new SettingsView();
                break;
            default:
                $page = new LoginView();
        }
>>>>>>> Stashed changes
    }

    //This is equivalent to new Dashboard() or new Accounts() but it's dynamic
    $page = new $view();
?>

<!DOCTYPE html>
<html>
    <head>
        <meta name="color-scheme" content="dark light">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="./public/src/css/styles.css">
<<<<<<< Updated upstream
=======
        <link rel="stylesheet" href="./public/src/css/inventory.css">
        <link rel="stylesheet" href="./public/src/css/reports.css">

>>>>>>> Stashed changes
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    </head>
    <body>
        <header>
            <!-- Add your view as a list in class Navigation inside navigation.php -->
            <?php if(isset($_SESSION["account"])){ ?>
                <div class="sidebar">
                    <?php $navigation->render(); ?>
                </div>
            <?php }?>
        </header>
        <main>
            <?php $page->render();?>
        </main>

        <script src="./public/src/js/script.js"></script>
    </body>
</html>
