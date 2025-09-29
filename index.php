<?php
    //Add your view here
    require_once __DIR__ . "/mvc/view/navigation.php";
    require_once __DIR__ . "/mvc/view/dashboard.php";
    require_once __DIR__ . "/mvc/view/accounts.php";
    require_once __DIR__ . "/mvc/view/products.php";
    require_once __DIR__ . "/mvc/view/reports.php";


    session_start();

    $navigation = new Navigation();

    //$_GET['view'] comes from navigation.php
    $view = $_GET["view"] ?? "dashboard";

    //This is equivalent to new Dashboard() or new Accounts() but it's dynamic
    $page = new $view();
?>

<!DOCTYPE html>
<html>
    <head>
        <meta name="color-scheme" content="dark light">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="./public/src/css/styles.css">
    </head>
    <body>
        <header>
            <!-- Add your view as a list in class Navigation inside navigation.php -->
            <?php $navigation->render();?>
        </header>
        <main>
            <?php $page->render();?>
        </main>
    </body>
</html>