// Define the global dashboard controller
const PetshopDashboard = {
    // Current settings state
    state: {
        timeFilter: 'month', // 'month' or 'quarter'
        timeTab: 'month',    // 'week', 'month', 'year', 'total'
        selectedYear: new Date().getFullYear(),
        charts: {}
    },
    
    // Initialize the dashboard
    init: function() {
        this.setupTimeFilter();
        this.setupTimeTabs();
        this.setupYearSelector();
        this.renderCharts();
        this.setupPagination();
    },
    
    // Setup time filter buttons (Month/Quarter)
    setupTimeFilter: function() {
        const timeFilterBtns = document.querySelectorAll('.ps-time-filter .ps-btn');
        timeFilterBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                timeFilterBtns.forEach(b => b.classList.remove('ps-btn-active'));
                btn.classList.add('ps-btn-active');
                
                this.state.timeFilter = btn.textContent.trim().toLowerCase() === 'tháng' ? 'month' : 'quarter';
                this.fetchDashboardData();
            });
        });
    },
    
    // Setup time tab buttons (Week/Month/Year/Total)
    setupTimeTabs: function() {
        const timeTabs = document.querySelectorAll('.ps-time-tabs .ps-tab');
        timeTabs.forEach(tab => {
            tab.addEventListener('click', () => {
                timeTabs.forEach(t => t.classList.remove('ps-tab-active'));
                tab.classList.add('ps-tab-active');
                
                const tabText = tab.textContent.trim().toLowerCase();
                switch(tabText) {
                    case 'tuần': this.state.timeTab = 'week'; break;
                    case 'tháng': this.state.timeTab = 'month'; break;
                    case 'năm': this.state.timeTab = 'year'; break;
                    case 'tổng': this.state.timeTab = 'total'; break;
                }
                this.fetchDashboardData();
            });
        });
    },
    
    // Set up year selector dropdown
    setupYearSelector: function() {
        const yearSelector = document.getElementById('ps-year-selector');
        if (yearSelector) {
            // Generate year options
            const currentYear = new Date().getFullYear();
            for (let year = currentYear - 5; year <= currentYear; year++) {
                const option = document.createElement('option');
                option.value = year;
                option.textContent = year;
                if (year === currentYear) option.selected = true;
                yearSelector.appendChild(option);
            }

            yearSelector.addEventListener('change', (e) => {
                this.state.selectedYear = parseInt(e.target.value);
                this.fetchDashboardData();
            });
        }
    },
    
    // Fetch all dashboard data
    fetchDashboardData: function() {
        document.querySelectorAll('.ps-loading-indicator').forEach(el => {
            el.style.display = 'flex';
        });
        
        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'petshop_fetch_dashboard_data',
                time_filter: this.state.timeFilter,
                time_tab: this.state.timeTab,
                year: this.state.selectedYear,
                nonce: petshopDashboardVars.nonce
            },
            success: (response) => {
                if (response.success) {
                    this.updateDashboardData(response.data);
                }
                document.querySelectorAll('.ps-loading-indicator').forEach(el => {
                    el.style.display = 'none';
                });
            }
        });
    },
    
    // Update dashboard data
    updateDashboardData: function(data) {
        // Update KPI cards
        if (data.kpi) {
            const kpiCards = document.querySelectorAll('.ps-kpi-card');
            kpiCards.forEach((card, index) => {
                const kpiData = data.kpi[index];
                if (kpiData) {
                    card.querySelector('.ps-kpi-number').textContent = kpiData.value;
                    card.querySelector('.ps-badge').textContent = kpiData.percentage + '%';
                    card.querySelector('.ps-kpi-date').textContent = kpiData.dateRange;
                }
            });
        }

        // Update charts
        if (data.charts) {
            // Orders Location Chart
            if (data.charts.orders_location && this.state.charts.ordersLocationChart) {
                this.state.charts.ordersLocationChart.data.labels = data.charts.orders_location.labels;
                this.state.charts.ordersLocationChart.data.datasets[0].data = data.charts.orders_location.data;
                this.state.charts.ordersLocationChart.update();
            }

            // Sales Location Chart
            if (data.charts.sales_location && this.state.charts.salesLocationChart) {
                this.state.charts.salesLocationChart.data.labels = data.charts.sales_location.labels;
                this.state.charts.salesLocationChart.data.datasets[0].data = data.charts.sales_location.data;
                this.state.charts.salesLocationChart.update();
            }

            // Revenue Chart
            if (data.charts.revenue && this.state.charts.revenueChart) {
                this.state.charts.revenueChart.data.labels = data.charts.revenue.labels;
                this.state.charts.revenueChart.data.datasets[0].data = data.charts.revenue.data;
                this.state.charts.revenueChart.update();
            }

            // Mini Charts
            if (data.charts.mini) {
                if (data.charts.mini.orders && this.state.charts.ordersChart) {
                    this.state.charts.ordersChart.data.labels = data.charts.mini.orders.labels;
                    this.state.charts.ordersChart.data.datasets[0].data = data.charts.mini.orders.data;
                    this.state.charts.ordersChart.update();
                }
                if (data.charts.mini.users && this.state.charts.usersChart) {
                    this.state.charts.usersChart.data.labels = data.charts.mini.users.labels;
                    this.state.charts.usersChart.data.datasets[0].data = data.charts.mini.users.data;
                    this.state.charts.usersChart.update();
                }
            }
        }
    },
    
    // Fetch chart data only
    fetchChartData: function() {
        // Show loading indicators for charts only
        document.querySelectorAll('.ps-chart-container .ps-loading-indicator').forEach(el => {
            el.style.display = 'flex';
        });
        
        // Prepare data for AJAX request
        const data = {
            action: 'petshop_get_chart_data',
            time_filter: this.state.timeFilter,
            time_tab: this.state.timeTab,
            year: this.state.selectedYear,
            nonce: petshopDashboardVars.nonce
        };
        
        // Make AJAX request
        jQuery.post(ajaxurl, data, (response) => {
            if (response.success) {
                // Update charts with new data
                this.updateCharts(response.data);
            } else {
                console.error('Failed to fetch chart data:', response.message);
            }
            
            // Hide loading indicators
            document.querySelectorAll('.ps-chart-container .ps-loading-indicator').forEach(el => {
                el.style.display = 'none';
            });
        }).fail(() => {
            console.error('AJAX request failed');
            // Hide loading indicators
            document.querySelectorAll('.ps-chart-container .ps-loading-indicator').forEach(el => {
                el.style.display = 'none';
            });
        });
    },
    
    // Update KPI cards with new data
    updateKPICards: function(kpiData) {
        const kpiCards = document.querySelectorAll('.ps-kpi-card');
        if (kpiData && kpiData.length >= kpiCards.length) {
            kpiCards.forEach((card, index) => {
                const data = kpiData[index];
                
                // Update date range
                const dateEl = card.querySelector('.ps-kpi-date');
                if (dateEl && data.date_range) {
                    dateEl.textContent = data.date_range;
                }
                
                // Update value
                const valueEl = card.querySelector('.ps-kpi-number');
                if (valueEl && data.value) {
                    valueEl.textContent = data.value;
                }
                
                // Update percentage change
                const badgeEl = card.querySelector('.ps-badge');
                if (badgeEl && data.percentage) {
                    badgeEl.textContent = data.percentage;
                    // Update badge class based on positive/negative
                    if (parseFloat(data.percentage) >= 0) {
                        badgeEl.className = 'ps-badge ps-badge-success';
                    } else {
                        badgeEl.className = 'ps-badge ps-badge-danger';
                    }
                }
            });
        }
    },
    
    // Update charts with new data
    updateCharts: function(chartData) {
        // Update orders location chart
        if (chartData.orders_by_location && this.state.charts.ordersLocationChart) {
            this.state.charts.ordersLocationChart.data.labels = chartData.orders_by_location.labels;
            this.state.charts.ordersLocationChart.data.datasets[0].data = chartData.orders_by_location.data;
            this.state.charts.ordersLocationChart.update();
            
            // Update legend
            this.updateChartLegend('ordersLocationChart', chartData.orders_by_location);
        }
        
        // Update sales location chart
        if (chartData.sales_by_location && this.state.charts.salesLocationChart) {
            this.state.charts.salesLocationChart.data.labels = chartData.sales_by_location.labels;
            this.state.charts.salesLocationChart.data.datasets[0].data = chartData.sales_by_location.data;
            this.state.charts.salesLocationChart.update();
            
            // Update legend
            this.updateChartLegend('salesLocationChart', chartData.sales_by_location);
        }
        
        // Update revenue chart
        if (chartData.monthly_revenue && this.state.charts.revenueChart) {
            this.state.charts.revenueChart.data.labels = chartData.monthly_revenue.labels;
            this.state.charts.revenueChart.data.datasets[0].data = chartData.monthly_revenue.data;
            this.state.charts.revenueChart.update();
            
            // Update summary stats
            if (chartData.monthly_revenue.summary) {
                const summary = chartData.monthly_revenue.summary;
                const statsContainer = document.querySelector('.ps-summary-stats');
                if (statsContainer) {
                    const statEls = statsContainer.querySelectorAll('.ps-stat-value');
                    if (statEls[0] && summary.total) statEls[0].textContent = summary.total;
                    if (statEls[1] && summary.average) statEls[1].textContent = summary.average;
                    if (statEls[2] && summary.grand_total) statEls[2].textContent = summary.grand_total;
                }
            }
        }
        
        // Update sales time chart
        if (chartData.sales_time && this.state.charts.salesTimeChart) {
            this.state.charts.salesTimeChart.data.labels = chartData.sales_time.labels;
            this.state.charts.salesTimeChart.data.datasets[0].data = chartData.sales_time.impression;
            this.state.charts.salesTimeChart.data.datasets[1].data = chartData.sales_time.turnover;
            this.state.charts.salesTimeChart.update();
        }
        
        // Update mini charts
        if (chartData.mini_charts) {
            if (chartData.mini_charts.orders && this.state.charts.ordersChart) {
                this.state.charts.ordersChart.data.labels = chartData.mini_charts.orders.labels;
                this.state.charts.ordersChart.data.datasets[0].data = chartData.mini_charts.orders.data;
                this.state.charts.ordersChart.update();
            }
            
            if (chartData.mini_charts.users && this.state.charts.usersChart) {
                this.state.charts.usersChart.data.labels = chartData.mini_charts.users.labels;
                this.state.charts.usersChart.data.datasets[0].data = chartData.mini_charts.users.data;
                this.state.charts.usersChart.update();
            }
        }
    },
    
    // Update chart legend
    updateChartLegend: function(chartId, chartData) {
        if (chartData.legend) {
            const legendContainer = document.querySelector(`#${chartId}`).closest('.ps-chart-card').querySelector('.ps-chart-legend');
            if (legendContainer) {
                let legendHTML = '';
                
                chartData.legend.forEach((item, index) => {
                    legendHTML += `
                    <div class="ps-legend-item">
                        <span class="ps-legend-color" style="background-color: ${this.getChartColors()[index]}"></span>
                        <span class="ps-legend-label">${item.label}</span>
                        <span class="ps-legend-value">${item.value}</span>
                    </div>`;
                });
                
                legendContainer.innerHTML = legendHTML;
            }
        }
    },
    
    // Update reports section with new data
    updateReports: function(reportsData) {
        if (reportsData.stat_columns) {
            const statColumns = document.querySelectorAll('.ps-stat-column');
            
            reportsData.stat_columns.forEach((statData, index) => {
                if (statColumns[index]) {
                    // Update date
                    const dateEl = statColumns[index].querySelector('.ps-stat-date');
                    if (dateEl && statData.date) {
                        dateEl.textContent = statData.date;
                    }
                    
                    // Update number
                    const numberEl = statColumns[index].querySelector('.ps-stat-number div:first-child');
                    if (numberEl && statData.value) {
                        numberEl.textContent = statData.value;
                    }
                    
                    // Update change percentage
                    const changeEl = statColumns[index].querySelector('.ps-stat-change');
                    if (changeEl && statData.change) {
                        changeEl.textContent = statData.change;
                        // Update class based on positive/negative
                        if (parseFloat(statData.change) >= 0) {
                            changeEl.className = 'ps-stat-change ps-positive';
                        } else {
                            changeEl.className = 'ps-stat-change ps-negative';
                        }
                    }
                    
                    // Update meta info if exists
                    const metaEl = statColumns[index].querySelector('.ps-stat-meta');
                    if (metaEl && statData.meta) {
                        metaEl.textContent = statData.meta;
                    }
                }
            });
        }
        
        // Update sales by time number and change
        if (reportsData.sales_time) {
            const salesNumberEl = document.querySelector('.ps-sales-number');
            if (salesNumberEl) {
                const numberSpan = salesNumberEl.childNodes[0];
                const changeSpan = salesNumberEl.querySelector('.ps-sales-change');
                
                if (numberSpan && reportsData.sales_time.value) {
                    numberSpan.textContent = reportsData.sales_time.value + ' ';
                }
                
                if (changeSpan && reportsData.sales_time.change) {
                    changeSpan.textContent = reportsData.sales_time.change;
                    // Update class based on positive/negative
                    if (parseFloat(reportsData.sales_time.change) >= 0) {
                        changeSpan.className = 'ps-sales-change ps-positive';
                    } else {
                        changeSpan.className = 'ps-sales-change ps-negative';
                    }
                }
            }
        }
        
        // Update Vietnam map stats
        if (reportsData.vn_stats) {
            const stateRows = document.querySelectorAll('.ps-state-row');
            
            reportsData.vn_stats.forEach((statData, index) => {
                if (stateRows[index]) {
                    // Update state name
                    const nameEl = stateRows[index].querySelector('.ps-state-name');
                    if (nameEl && statData.name) {
                        nameEl.textContent = statData.name;
                    }
                    
                    // Update bar fill
                    const barFillEl = stateRows[index].querySelector('.ps-bar-fill');
                    if (barFillEl && statData.percentage) {
                        barFillEl.style.width = `${statData.percentage}%`;
                    }
                    
                    // Update percentage text
                    const percentEl = stateRows[index].querySelector('.ps-state-percent');
                    if (percentEl && statData.percentage) {
                        percentEl.textContent = `${statData.percentage}%`;
                    }
                }
            });
        }
    },
    
    // Initialize and render the charts
    renderCharts: function() {
        // Get chart colors
        const colors = this.getChartColors();
        
        // Orders by Location Chart
        const ordersLocationChart = document.getElementById('ordersLocationChart');
        if (ordersLocationChart) {
            this.state.charts.ordersLocationChart = new Chart(ordersLocationChart, {
                type: 'pie',
                data: {
                    labels: ordersLocationChartData.labels,
                    datasets: [{
                        data: ordersLocationChartData.data,
                        backgroundColor: colors.slice(0, ordersLocationChartData.data.length)
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }
        
        // Sales by Location Chart
        const salesLocationChart = document.getElementById('salesLocationChart');
        if (salesLocationChart) {
            this.state.charts.salesLocationChart = new Chart(salesLocationChart, {
                type: 'doughnut',
                data: {
                    labels: salesLocationChartData.labels,
                    datasets: [{
                        data: salesLocationChartData.data,
                        backgroundColor: colors.slice(0, salesLocationChartData.data.length)
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }
        
        // Revenue Chart
        const revenueChart = document.getElementById('revenueChart');
        if (revenueChart) {
            this.state.charts.revenueChart = new Chart(revenueChart, {
                type: 'line',
                data: {
                    labels: revenueChartData.labels,
                    datasets: [{
                        label: 'Revenue',
                        data: revenueChartData.data,
                        fill: true,
                        borderColor: '#2CC6C6',
                        backgroundColor: 'rgba(44, 198, 198, 0.1)',
                        tension: 0.4,
                        pointRadius: 0,
                        pointHoverRadius: 5,
                        pointBackgroundColor: '#2CC6C6',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }
        
        // Mini Charts options
        const miniChartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    display: false
                },
                x: {
                    display: false
                }
            },
            elements: {
                point: {
                    radius: 0
                }
            }
        };
        
        // Orders Mini Chart
        const ordersChart = document.getElementById('ordersChart');
        if (ordersChart) {
            this.state.charts.ordersChart = new Chart(ordersChart, {
                type: 'bar',
                data: {
                    labels: ['M', 'T', 'W', 'T', 'F', 'S', 'S'],
                    datasets: [{
                        data: [15, 8, 25, 5, 15, 20, 15],
                        backgroundColor: '#4e73df'
                    }]
                },
                options: miniChartOptions
            });
        }
        
        // Users Mini Chart
        const usersChart = document.getElementById('usersChart');
        if (usersChart) {
            this.state.charts.usersChart = new Chart(usersChart, {
                type: 'bar',
                data: {
                    labels: ['M', 'T', 'W', 'T', 'F', 'S', 'S'],
                    datasets: [{
                        data: [20, 5, 15, 25, 10, 20, 25],
                        backgroundColor: '#4e73df'
                    }]
                },
                options: miniChartOptions
            });
        }
        
        // Sales by Time Chart
        const salesTimeChart = document.getElementById('salesTimeChart');
        if (salesTimeChart) {
            this.state.charts.salesTimeChart = new Chart(salesTimeChart, {
                type: 'bar',
                data: {
                    labels: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                    datasets: [{
                        label: 'Impression',
                        data: [25, 30, 35, 40, 25, 30, 35],
                        backgroundColor: '#4e73df'
                    }, {
                        label: 'Turnover',
                        data: [35, 20, 25, 30, 35, 20, 25],
                        backgroundColor: '#9c27b0'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }
    },
    
    // Set up pagination buttons
    setupPagination: function() {
        const prevBtn = document.querySelector('.ps-pagination button:first-child');
        const nextBtn = document.querySelector('.ps-pagination button:last-child');
        
        if (prevBtn && nextBtn) {
            let currentPage = 1;
            const totalPages = 3; // Example, can be dynamic
            
            prevBtn.addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage--;
                    this.fetchReportPage(currentPage);
                }
            });
            
            nextBtn.addEventListener('click', () => {
                if (currentPage < totalPages) {
                    currentPage++;
                    this.fetchReportPage(currentPage);
                }
            });
        }
    },
    
    // Fetch report page data
    fetchReportPage: function(page) {
        // Prepare data for AJAX request
        const data = {
            action: 'petshop_get_report_page',
            page: page,
            time_filter: this.state.timeFilter,
            time_tab: this.state.timeTab,
            year: this.state.selectedYear,
            nonce: petshopDashboardVars.nonce
        };
        
        // Make AJAX request
        jQuery.post(ajaxurl, data, (response) => {
            if (response.success) {
                // Update reports with new data
                this.updateReports(response.data);
            } else {
                console.error('Failed to fetch report page:', response.message);
            }
        }).fail(() => {
            console.error('AJAX request failed');
        });
    },
    
    // Get chart colors
    getChartColors: function() {
        return [
            '#4e73df', // Blue
            '#1cc88a', // Green
            '#36b9cc', // Cyan
            '#f6c23e', // Yellow
            '#e74a3b', // Red
            '#5a5c69', // Gray
            '#FF6384', // Pink
            '#36A2EB', // Light Blue
            '#FFCE56', // Light Yellow
            '#4BC0C0'  // Teal
        ];
    }
};

// Initialize dashboard when DOM is fully loaded
document.addEventListener('DOMContentLoaded', function() {
    PetshopDashboard.init();
});