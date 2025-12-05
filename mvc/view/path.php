<?php
    require_once __DIR__ . "/../model/dashboardmodel.php";

    $dashboard_model = new DashboardModel();
    $dashboard = $dashboard_model->loadData();

    echo json_encode($dashboard);
?>