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

    if(isset($_GET["view"])){
        $view = $_GET["view"];
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