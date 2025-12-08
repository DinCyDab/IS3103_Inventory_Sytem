document.addEventListener("DOMContentLoaded", function () {

    // Monthly and weekly chart data from PHP
    const monthlyData = window.reportsChartData || {
        labels: [],
        revenue: [],
        profit: []
    };

    const weeklyData = window.reportsChartDataWeekly || {
        labels: [],
        revenue: [],
        profit: [],
    };

    const canvas = document.getElementById("profitChart");
    if (!canvas) return;

    const ctx = canvas.getContext("2d");

    // Calculate dynamic max value for y-axis
    function calculateMaxValue(data1, data2){
        const allValues = [...data1, ...data2];
        if(allValues.length === 0) return 100000;

        const max = Math.max(...allValues);
        if(max === 0) return 100000;

        // Round up to nearest meaningful value
        const magnitude = Math.pow(10, Math.floor(Math.log10(max)));
        return Math.ceil(max / magnitude) * magnitude * 1.2; // Add 20% padding
    }

    // custom tooltip
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
            let html = `<div style="font-size:12px;color:#9ca3af;margin-bottom:5px;">${tooltip.dataPoints[0].label}</div>`

            tooltip.dataPoints.forEach(p => {
                html += `<div style="font-size:14px;font-weight:700;margin-bottom:3px;color:${p.dataset.borderColor};">
                            ${p.dataset.label}: ₱${p.raw.toLocaleString()}
                        </div>`;
            });

            el.innerHTML = html;
        }

        const rect = context.chart.canvas.getBoundingClientRect();
        el.style.opacity = 1;
        el.style.left = rect.left + window.scrollX + tooltip.caretX - 60 + "px";
        el.style.top = rect.top + window.scrollY + tooltip.caretY - 70 + "px";
    }

    // Initial max value for monthly data
    let initialMax = calculateMaxValue(monthlyData.revenue, monthlyData.profit);

    const revenueData = monthlyData.revenue.map(Number);
    const profitData = monthlyData.profit.map(Number);

    const chart = new Chart(ctx, {
        type: "line",
        data: {
            labels: monthlyData.labels.length > 0 ? monthlyData.labels : ['No Data'],
            datasets: [
                {
                    label: "Revenue",
                    data: revenueData.length > 0 ? revenueData : [0],
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
                    data: profitData.length > 0 ? profitData : [0],
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
                    max: initialMax,
                    ticks: {
                        stepSize: Math.ceil(initialMax / 5),
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

    // Legend toggling
    document.querySelectorAll(".legend-item").forEach((item, i) => {
        item.addEventListener("click", () => {
            let ds = chart.data.datasets[i];
            ds.hidden = !ds.hidden;
            chart.update();
            item.style.opacity = ds.hidden ? "0.4" : "1";
        });
    });

    // Dropdown for weekly/monthly
    const modeBtn = document.getElementById("reportModeBtn");
    const modeList = document.getElementById("reportModeList");

    // open/close
    modeBtn.addEventListener("click", () => modeList.classList.toggle("show"));
    document.addEventListener("click", e => {
        if (!modeBtn.contains(e.target) && !modeList.contains(e.target)) {
            modeList.classList.remove("show");
        }
    });

    // switching weekly or sa monthly
    document.querySelectorAll("#reportModeList li").forEach(item => {
        item.addEventListener("click", () => {

            const mode = item.getAttribute("data-mode");

            // update button text
            modeBtn.innerHTML = item.innerText + ' <i class="bx bx-chevron-down"></i>';

            // close dropdown
            modeList.classList.remove("show");

            const newData = mode === "weekly" ? weeklyData : monthlyData;

            chart.data.labels = newData.labels;
            chart.data.datasets[0].data = newData.revenue.map(Number);
            chart.data.datasets[1].data = newData.profit.map(Number);

            // Recalculate Y-axis max
            chart.options.scales.y.max = calculateMaxValue(newData.revenue, newData.profit);

            chart.update();
        });
    });

    // SEARCH
    const searchInput = document.getElementById("reportsSearch");
    const searchList = document.getElementById("reportsSearchList");

    searchInput.addEventListener("keypress", e => {
        if (e.key !== "Enter") return;
        const query = searchInput.value.trim();
        if (!query) return;

        searchList.innerHTML = '<li style="padding:10px;text-align:center;">Searching…</li>';
        searchList.style.display = "block";

        fetch(`?view=reports&action=search&q=${encodeURIComponent(query)}`, { headers:{'Accept':'application/json'} })
            .then(r => r.json())
            .then(data => {
                searchList.innerHTML = '';
                if (!data.length) {
                    const li = document.createElement('li');
                    li.textContent = 'No results.'; li.style.padding = '8px 14px';
                    searchList.appendChild(li);
                    return;
                }
                data.forEach(item => {
                    const li = document.createElement('li');
                    li.style.padding = '8px 14px'; li.style.cursor = 'pointer'; li.style.borderBottom = '1px solid #f2f2f2';
                    const name = item.product_name || item.productName || item.name || 'Unnamed';
                    li.innerHTML = `<div style="font-weight:600;">${name}</div><div style="font-size:12px;color:#6b7280;">ID: ${item.productID || item.id || ''}</div>`;
                    li.addEventListener('click', () => { searchInput.value = name; searchList.style.display = 'none'; });
                    searchList.appendChild(li);
                });
            })
            .catch(() => { searchList.innerHTML = '<li style="padding:10px;text-align:center;">Error fetching results</li>'; });
    });

    document.addEventListener('click', e => {
        const wrapper = document.querySelector('.search-wrapper');
        if (!wrapper.contains(e.target)) searchList.style.display = 'none';
    });

    // MODALS
    document.querySelectorAll('.bestseller-container .see-all').forEach(btn => {
        btn.addEventListener('click', e => {
            e.preventDefault();
            document.getElementById('bestseller-modal').classList.add('active');
            document.body.classList.add('modal-open');
        });
    });

    document.querySelectorAll('.modal-close').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.modal-overlay.active').forEach(modal => {
                modal.classList.remove('active');
                document.body.classList.remove('modal-open');
            });
        });
    });

    document.querySelectorAll('.modal-overlay').forEach(modal => {
        modal.addEventListener('click', e => {
            if (e.target === modal) { modal.classList.remove('active'); document.body.classList.remove('modal-open'); }
        });
    });

    // SALES REPORT modal
    const salesBtn = document.getElementById("sales-report-see-all");
    if(salesBtn){
        salesBtn.addEventListener("click", e => {
            e.preventDefault();
            document.querySelector("#salesReportModal").style.display = "flex";
        });
    }
    const closeSalesBtn = document.getElementById("closeSalesReportModal");
    if(closeSalesBtn){
        closeSalesBtn.addEventListener("click", () => {
            document.querySelector("#salesReportModal").style.display = "none";
        });
    }

});