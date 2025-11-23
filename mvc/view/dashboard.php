<?php 
    // require_once __DIR__ . "/../controller/dashboardcontroller.php";
    class Dashboard{
        public function render(){
            $this->graph();
        }

        public function graph(){
            ?>
        <script>
            // Fetch sales data from PHP endpoint
            async function fetchSalesData() {
                try {
                    const response = await fetch('http://localhost/IS3103_Inventory_Sytem/mvc/view/path.php');  // Replace with actual path
                    const data = await response.json();

                    if (data.length === 0) {
                        console.log('No data found');
                        return;
                    }

                    console.log(data);

                    // // Extract labels and values from fetched data
                    // const xValues = data.map(item => item.country);
                    // const yValues = data.map(item => item.sales);

                    // // Generate random colors
                    // function getRandomColor() {
                    //     const letters = "0123456789ABCDEF";
                    //     let color = "#";
                    //     for (let i = 0; i < 6; i++) {
                    //         color += letters[Math.floor(Math.random() * 16)];
                    //     }
                    //     return color;
                    // }
                    // const barColors = data.map(() => getRandomColor());

                    // // Create Chart
                    // new Chart("myChart", {
                    //     type: "bar",
                    //     data: {
                    //         labels: xValues,
                    //         datasets: [{
                    //             label: "Sales",
                    //             backgroundColor: barColors,
                    //             data: yValues
                    //         }]
                    //     },
                    //     options: {
                    //         legend: { display: false },
                    //         title: {
                    //             display: true,
                    //             text: "Country Sales Data"
                    //         }
                    //     }
                    // });
                } catch (error) {
                    console.error('Error fetching data:', error);
                }
            }

            // Call fetchSalesData to fetch and display the chart
            fetchSalesData();
        </script>
            <?php
        }
    }
?>
