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

    if (modeBtn && modeList) {
        // open/close
        modeBtn.addEventListener("click", () => modeList.classList.toggle("show"));
        document.addEventListener("click", e => {
            if (!modeBtn.contains(e.target) && !modeList.contains(e.target)) {
                modeList.classList.remove("show");
            }
        });

        // switching weekly or monthly
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
    }

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

    // SEARCH
    const searchInput = document.getElementById('reportsSearch');
    const searchResultsDiv = document.getElementById('reportsProductOverview');
    
    // Check if both elements exist AND we're actually on reports page
    const isReportsPage = searchInput && searchResultsDiv && document.querySelector('.reports-row');
    
    if (isReportsPage) {
        let searchTimeout;

        searchInput.addEventListener('input', function(e) {
            // Stop propagation to prevent script.js from interfering
            e.stopPropagation();
            
            const query = this.value.trim();
            
            // Clear previous timeout
            clearTimeout(searchTimeout);
            
            // Hide results if query is empty
            if (query.length === 0) {
                searchResultsDiv.style.display = 'none';
                searchResultsDiv.innerHTML = '';
                return;
            }
            
            // Debounce search - wait 300ms after user stops typing
            searchTimeout = setTimeout(() => {
                performSearch(query);
            }, 300);
        });

        // Close search results when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchResultsDiv.contains(e.target)) {
                searchResultsDiv.style.display = 'none';
            }
        });

        async function performSearch(query) {
            try {
                const response = await fetch(`index.php?view=reports&action=search&query=${encodeURIComponent(query)}`, {
                    headers: { "X-Requested-With": "XMLHttpRequest" }
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (!data.success) {
                    showSearchError(data.message || 'Search failed. Please try again.');
                    return;
                }

                displaySearchResults(data.results);

            } catch (error) {
                console.error('Search error:', error);
                showSearchError('Network error. Please try again.');
            }
        }

        function displaySearchResults(results) {
            if (!searchResultsDiv) return;

            if (results.length === 0) {
                searchResultsDiv.innerHTML = `
                    <div style="padding: 20px; text-align: center; color: #6b7280;">
                        No products found
                    </div>
                `;
                searchResultsDiv.style.display = 'block';
                return;
            }

            let html = '<div style="max-height: 400px; overflow-y: auto;">';
            html += '<table style="width: 100%; border-collapse: collapse;">';
            html += `
                <thead>
                    <tr style="background: #f3f4f6; text-align: left;">
                        <th style="padding: 12px; font-size: 13px; color: #6b7280;">Product</th>
                        <th style="padding: 12px; font-size: 13px; color: #6b7280;">Product ID</th>
                        <th style="padding: 12px; font-size: 13px; color: #6b7280;">Category</th>
                        <th style="padding: 12px; font-size: 13px; color: #6b7280;">Remaining</th>
                        <th style="padding: 12px; font-size: 13px; color: #6b7280;">Turnover</th>
                        <th style="padding: 12px; font-size: 13px; color: #6b7280;">Sold</th>
                    </tr>
                </thead>
                <tbody>
            `;

            results.forEach((item, index) => {
                const bgColor = index % 2 === 0 ? '#ffffff' : '#f9fafb';
                html += `
                    <tr style="background: ${bgColor}; border-bottom: 1px solid #e5e7eb;">
                        <td style="padding: 12px; font-size: 14px; color: #111827;">${escapeHtml(item.product_name || 'N/A')}</td>
                        <td style="padding: 12px; font-size: 14px; color: #6b7280;">${escapeHtml(item.productID || 'N/A')}</td>
                        <td style="padding: 12px; font-size: 14px; color: #6b7280;">${escapeHtml(item.category || 'Unknown')}</td>
                        <td style="padding: 12px; font-size: 14px; color: #6b7280;">${escapeHtml(item.remaining_qty || 'N/A')}</td>
                        <td style="padding: 12px; font-size: 14px; color: #059669; font-weight: 600;">₱${formatNumber(item.turnover || 0)}</td>
                        <td style="padding: 12px; font-size: 14px; color: #6b7280;">${escapeHtml(item.increase || 0)}</td>
                    </tr>
                `;
            });

            html += '</tbody></table></div>';

            searchResultsDiv.innerHTML = html;
            searchResultsDiv.style.display = 'block';
        }

        function showSearchError(message) {
            if (!searchResultsDiv) return;
            
            searchResultsDiv.innerHTML = `
                <div style="padding: 20px; text-align: center; color: #ef4444;">
                    ${escapeHtml(message)}
                </div>
            `;
            searchResultsDiv.style.display = 'block';
        }

        function escapeHtml(str) {
            if (!str) return '';
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        function formatNumber(num) {
            return parseFloat(num || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }
    }
});