<?php 
    // require_once __DIR__ . "/../controller/dashboardcontroller.php";
    class DashboardView{
        public function render(){
            $this->graph();
        }

        public function graph(){
            ?>
                <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
                <canvas id="myChart" style="width:100%;max-width:700px"></canvas>
                
                <script>
                    var xValues = ["Italy", "France", "Spain", "USA", "Argentina"];
                    var yValues = [55, 49, 44, 24, 15];
                    var barColors = ["red", "green","blue","orange","brown"];

                    new Chart("myChart", {
                        type: "bar",
                        data: {
                            labels: xValues,
                            datasets: [{
                                data: yValues
                            }]
                        }
                    });
                </script>
            <?php
        }
    }
?>