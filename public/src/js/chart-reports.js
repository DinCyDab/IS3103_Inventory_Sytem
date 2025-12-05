document.addEventListener("DOMContentLoaded", function () {

    const chartData = window.reportsChartData || {
        labels: [],
        revenue: [],
        profit: []
    };

    const labels = chartData.labels;
    const revenue = chartData.revenue;
    const profit = chartData.profit;

    

    const canvas = document.getElementById("profitChart");
    if (!canvas) return;

    const ctx = canvas.getContext("2d");

    // custom tooltip thingy
    function customTip(context){
        let el = document.getElementById("my-tip");

        if(!el){
            el = document.createElement("div");
            el.id = "my-tip";
            el.style.position = "absolute";
            el.style.background = "#fff";
            el.style.padding = "15px 18px";
            el.style.borderRadius = "12px";
            el.style.boxShadow = "0 4px 15px rgba(0,0,0,0.15)";
            el.style.pointerEvents = "none";
            el.style.opacity = 0;
            el.style.transition = ".15s";
            document.body.appendChild(el);
        }

        const tooltip = context.tooltip;

        if(tooltip.opacity === 0){
            el.style.opacity = 0;
            return;
        }

        if(tooltip.dataPoints){
            let p = tooltip.dataPoints[0];
            let month = p.label;
            let num = p.raw;

            el.innerHTML = `
                <div style="font-size:12px;color:#9ca3af;margin-bottom:5px;">This Month</div>
                <div style="font-size:22px;font-weight:700;margin-bottom:3px;">₱${num.toLocaleString()}</div>
                <div style="font-size:13px;color:#6b7280;">${month}</div>
            `;
        }

        const rect = context.chart.canvas.getBoundingClientRect();

        el.style.opacity = 1;
        el.style.left = rect.left + window.scrollX + tooltip.caretX - 60 + "px";
        el.style.top = rect.top + window.scrollY + tooltip.caretY - 70 + "px";
    }


    const chart = new Chart(ctx, {
        type: "line",
        data: {
            labels: labels,
            datasets: [
                {
                    label: "Revenue",
                    data: revenue,
                    borderColor: "#3b82f6",
                    backgroundColor: "rgba(59,130,246,0.15)",
                    borderWidth: 3,
                    pointRadius: 3,
                    pointBackgroundColor: "#3b82f6",
                    tension: 0.4,
                    fill: true
                },
                {
                    label: "Profit",
                    data: profit,
                    borderColor: "#f7db93",
                    backgroundColor: "rgba(251,191,36,0.15)",
                    borderWidth: 3,
                    pointRadius: 3,
                    pointBackgroundColor: "#f7db93",
                    tension: 0.4,
                    fill: true
                }
            ]
        },

        options: {
            responsive: true,
            maintainAspectRatio: false,

            plugins: {
                legend: {
                    display: false
                },

                tooltip: {
                    enabled: false,
                    external: customTip
                }
            },

            scales: {
                y: {
                    beginAtZero: true,
                    min: 0,
                    max: 100000,
                    ticks: {
                        stepSize: 25000,
                        color: "#6b7280",
                        callback: function (v) {
                            return "₱" + v.toLocaleString();
                        }
                    },
                    grid: {
                        color: "rgba(0,0,0,0.08)"
                    }
                },  

                x: {
                    grid: { display: false },
                    ticks: { color: "#6b7280" }
                }
            },

            interaction: {
                mode: "index",
                intersect: false
            }
        }
    });

    let legendItems = document.querySelectorAll(".legend-item");

    legendItems.forEach((item, i) => {
        item.addEventListener("click", () => {
            let ds = chart.data.datasets[i];
            ds.hidden = !ds.hidden;
            chart.update();
            item.style.opacity = ds.hidden ? "0.4" : "1";
        });
    });




    const modeBtn = document.getElementById("reportModeBtn");
    const modeList = document.getElementById("reportModeList");

    // open or close dropdown
    modeBtn.addEventListener("click", () => {
        modeList.classList.toggle("show");
    });

    // close when clicking outside
    document.addEventListener("click", (e) => {
        if (!modeBtn.contains(e.target) && !modeList.contains(e.target)) {
            modeList.classList.remove("show");
        }
    });

    // switching weekly or sa monthly
    document.querySelectorAll("#reportModeList li").forEach(item => {
        item.addEventListener("click", () => {

            let mode = item.getAttribute("data-mode");

            // update button text
            modeBtn.innerHTML = item.innerText + ' <i class="bx bx-chevron-down"></i>';

            // close dropdown
            modeList.classList.remove("show");

            if (mode === "weekly") {
                chart.data.labels = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];

                
                chart.data.datasets[0].data = [0, 0, 0, 0, 0, 0, 0];
                chart.data.datasets[1].data = [0, 0, 0, 0, 0, 0, 0];

            } else {
                chart.data.labels = labels;
                chart.data.datasets[0].data = revenue;
                chart.data.datasets[1].data = profit;
            }
            

            chart.options.scales.y.min = 0;
            chart.options.scales.y.max = 100000;
            chart.options.scales.y.ticks.stepSize = 25000;

            chart.update();
        });
    });

});
