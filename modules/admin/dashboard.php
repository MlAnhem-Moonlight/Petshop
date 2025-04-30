<?php
function petshop_admin_dashboard() {
    if (!petshop_is_logged_in() || $_SESSION['ps_user_role'] !== 'admin') {
        wp_redirect(admin_url('admin.php?page=petshop-management'));
        exit;
    }
    
    global $wpdb;
    
    // Get real data from database
    $total_orders = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}petshop_orders");
    $total_revenue = $wpdb->get_var("SELECT SUM(total_amount) FROM {$wpdb->prefix}petshop_orders WHERE status = 'completed'") ?: 0;
    $total_users = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}petshop_users WHERE role = 'user'");
    
    // Get sales by location (simplified example)
    $orders_by_location = [
        'Saint Lucia' => 845,
        'Liberia' => 548,
        'Saint Helena' => 624,
        'Kenya' => 624,
        'Christmas Island' => 412,
    ];
    
    // Sales by location data
    $sales_by_location = [
        'Germany' => 25,
        'Australia' => 40, 
        'United Kingdom' => 10,
        'Brazil' => 5,
        'Romania' => 19
    ];
    
    // Monthly revenue data (mock data for visualization)
    $monthly_data = [30, 40, 35, 50, 30, 45, 60, 40, 35, 45, 50, 45];
    $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    
    // Daily sales data for last 30 days (mock data)
    $last_login = '23 hours ago';
    ?>
    <div class="wrap petshop-dashboard-wrap">
        <!-- User Info Bar -->
        <div class="ps-user-info-bar">
            <div class="ps-user-details">
                <h2>Derek Wallace</h2>
                <span class="ps-login-info">Last login was <?php echo $last_login; ?> <a href="#">View details</a></span>
            </div>
            <div class="ps-time-filter">
                <button class="ps-btn ps-btn-active">Monthly</button>
                <button class="ps-btn">Quarterly</button>
            </div>
        </div>
        
        <!-- KPI Cards Row -->
        <div class="ps-kpi-cards">
            <div class="ps-kpi-card">
                <div class="ps-kpi-header">
                    <h3>Revenue</h3>
                    <span class="ps-badge ps-badge-success">+15%</span>
                </div>
                <div class="ps-kpi-date">May 20 - Jun 20, 2019</div>
                <div class="ps-kpi-value">
                    <span class="ps-kpi-icon">✈️</span>
                    <span class="ps-kpi-number">$24,583</span>
                </div>
                <a href="#" class="ps-read-more">Read more</a>
            </div>
            
            <div class="ps-kpi-card">
                <div class="ps-kpi-header">
                    <h3>Profit Share</h3>
                    <span class="ps-badge ps-badge-info">+61%</span>
                </div>
                <div class="ps-kpi-date">May 20 - Jun 20, 2019</div>
                <div class="ps-kpi-value">
                    <span class="ps-kpi-icon">🧳</span>
                    <span class="ps-kpi-number">$1046</span>
                </div>
                <a href="#" class="ps-read-more">Read more</a>
            </div>
            
            <div class="ps-kpi-card">
                <div class="ps-kpi-header">
                    <h3>Daily Sales</h3>
                    <span class="ps-badge ps-badge-warning">+34%</span>
                </div>
                <div class="ps-kpi-date">May 20 - Jun 20, 2019</div>
                <div class="ps-kpi-value">
                    <span class="ps-kpi-icon">📦</span>
                    <span class="ps-kpi-number">$342</span>
                </div>
                <a href="#" class="ps-read-more">Read more</a>
            </div>
        </div>
        
        <!-- Sales Status Section -->
        <div class="ps-sales-status">
            <div class="ps-section-header">
                <h3>Sales Status <span class="ps-subtitle">Performance For Online Revenue</span></h3>
                <div class="ps-time-tabs">
                    <button class="ps-tab">Week</button>
                    <button class="ps-tab ps-tab-active">Month</button>
                    <button class="ps-tab">Year</button>
                    <button class="ps-tab">All</button>
                </div>
            </div>
            
            <div class="ps-charts-container">
                <div class="ps-chart-card">
                    <h4>Orders by Location</h4>
                    <div class="ps-chart-container">
                        <canvas id="ordersLocationChart"></canvas>
                    </div>
                    <div class="ps-chart-legend">
                        <?php foreach ($orders_by_location as $location => $value): ?>
                        <div class="ps-legend-item">
                            <span class="ps-legend-color" style="background-color: <?php echo get_chart_color($location); ?>"></span>
                            <span class="ps-legend-label"><?php echo $location; ?></span>
                            <span class="ps-legend-value">$<?php echo $value; ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="ps-chart-card">
                    <h4>Sales by Location</h4>
                    <div class="ps-chart-container">
                        <canvas id="salesLocationChart"></canvas>
                    </div>
                    <div class="ps-chart-legend">
                        <?php foreach ($sales_by_location as $location => $percentage): ?>
                        <div class="ps-legend-item">
                            <span class="ps-legend-color" style="background-color: <?php echo get_chart_color($location); ?>"></span>
                            <span class="ps-legend-label"><?php echo $location; ?></span>
                            <span class="ps-legend-value"><?php echo $percentage; ?>%</span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="ps-chart-card ps-chart-card-large">
                    <h4>Revenue for Last Month</h4>
                    <div class="ps-chart-container">
                        <canvas id="revenueChart"></canvas>
                    </div>
                    <div class="ps-summary-stats">
                        <div class="ps-stat">
                            <div class="ps-stat-label">Total Income</div>
                            <div class="ps-stat-value">$3,567.56</div>
                        </div>
                        <div class="ps-stat">
                            <div class="ps-stat-label">Monthly Avg</div>
                            <div class="ps-stat-value">$769.08</div>
                        </div>
                        <div class="ps-stat">
                            <div class="ps-stat-label">Total Sales</div>
                            <div class="ps-stat-value">5489</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Detailed Reports Section -->
        <div class="ps-reports-section">
            <div class="ps-report-card">
                <div class="ps-report-header">
                    <h4>Detailed Report</h4>
                    <div class="ps-pagination">
                        <button class="ps-nav-btn"><span>❮</span></button>
                        <button class="ps-nav-btn"><span>❯</span></button>
                    </div>
                </div>
                
                <div class="ps-report-stats">
                    <div class="ps-stat-column">
                        <div class="ps-stat-header">
                            <div>Orders</div>
                            <div class="ps-stat-date">06 Jan 2019</div>
                        </div>
                        <div class="ps-stat-number">
                            <div>3,450</div>
                            <div class="ps-stat-change ps-negative">-35.00</div>
                        </div>
                        <div class="ps-mini-chart">
                            <canvas id="ordersChart"></canvas>
                        </div>
                    </div>
                    
                    <div class="ps-stat-column">
                        <div class="ps-stat-header">
                            <div>Users</div>
                            <div class="ps-stat-date">06 Jan 2019</div>
                        </div>
                        <div class="ps-stat-number">
                            <div>3,450</div>
                            <div class="ps-stat-change ps-positive">+25.00</div>
                        </div>
                        <div class="ps-mini-chart">
                            <canvas id="usersChart"></canvas>
                        </div>
                    </div>
                </div>
                
                <div class="ps-report-stats">
                    <div class="ps-stat-column">
                        <div class="ps-stat-header">
                            <div>Users</div>
                            <div class="ps-stat-date">06 Jan 2019</div>
                        </div>
                        <div class="ps-stat-number">
                            <div>18,390</div>
                            <div class="ps-stat-change ps-negative">-2.0</div>
                        </div>
                        <div class="ps-stat-meta">7,578 avg</div>
                    </div>
                    
                    <div class="ps-stat-column">
                        <div class="ps-stat-header">
                            <div>Visitors</div>
                            <div class="ps-stat-date">06 Jan 2019</div>
                        </div>
                        <div class="ps-stat-number">
                            <div>23,461</div>
                            <div class="ps-stat-change ps-positive">+5.0</div>
                        </div>
                        <div class="ps-stat-meta">6,154 avg</div>
                    </div>
                </div>
            </div>
            
            <div class="ps-report-card">
                <h4>Sales By Time</h4>
                <div class="ps-sales-time-header">
                    <div class="ps-sales-number">6,576 <span class="ps-sales-change ps-positive">+25.00</span></div>
                    <div class="ps-sales-legend">
                        <div class="ps-legend-item">
                            <span class="ps-legend-color ps-blue"></span>
                            <span>Impression</span>
                        </div>
                        <div class="ps-legend-item">
                            <span class="ps-legend-color ps-purple"></span>
                            <span>Turnover</span>
                        </div>
                    </div>
                </div>
                <div class="ps-chart-container">
                    <canvas id="salesTimeChart"></canvas>
                </div>
                <div class="ps-sales-description">
                    <p>Many people sign up for affiliate programs with the hopes of making some serious money. They advertise a few...</p>
                    <a href="#" class="ps-read-more">Read more</a>
                </div>
            </div>
            
            <div class="ps-report-card">
                <h4>Users From Viet Nam</h4>
                <div class="ps-map-container">
                    <img src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . 'assets/vn-map.png'; ?>" alt="VN Map" class="ps-us-map">
                </div>
                <div class="ps-state-stats">
                    <div class="ps-state-row">
                        <div class="ps-state-name">Florida</div>
                        <div class="ps-state-bar"><div class="ps-bar-fill ps-teal" style="width: 163%"></div></div>
                        <div class="ps-state-percent">163%</div>
                    </div>
                    <div class="ps-state-row">
                        <div class="ps-state-name">Hawaii</div>
                        <div class="ps-state-bar"><div class="ps-bar-fill ps-pink" style="width: 85%"></div></div>
                        <div class="ps-state-percent">86.2%</div>
                    </div>
                    <div class="ps-state-row">
                        <div class="ps-state-name">New York</div>
                        <div class="ps-state-bar"><div class="ps-bar-fill ps-blue" style="width: 123%"></div></div>
                        <div class="ps-state-percent">122%</div>
                    </div>
                    <div class="ps-state-row">
                        <div class="ps-state-name">Texas</div>
                        <div class="ps-state-bar"><div class="ps-bar-fill ps-orange" style="width: 165%"></div></div>
                        <div class="ps-state-percent">165%</div>
                    </div>
                    <div class="ps-state-row">
                        <div class="ps-state-name">Georgia</div>
                        <div class="ps-state-bar"><div class="ps-bar-fill ps-teal" style="width: 65%"></div></div>
                        <div class="ps-state-percent">65%</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Chart color function
        function getRandomColors(count) {
            const colors = [
                '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
                '#5a5c69', '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0'
            ];
            return colors.slice(0, count);
        }
        
        // Orders by Location Chart
        new Chart(document.getElementById('ordersLocationChart'), {
            type: 'pie',
            data: {
                labels: <?php echo json_encode(array_keys($orders_by_location)); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_values($orders_by_location)); ?>,
                    backgroundColor: getRandomColors(Object.keys(<?php echo json_encode($orders_by_location); ?>).length)
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
        
        // Sales by Location Chart
        new Chart(document.getElementById('salesLocationChart'), {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_keys($sales_by_location)); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_values($sales_by_location)); ?>,
                    backgroundColor: getRandomColors(Object.keys(<?php echo json_encode($sales_by_location); ?>).length)
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
        
        // Revenue Chart
        new Chart(document.getElementById('revenueChart'), {
            type: 'line',
            data: {
                labels: ['2015', '2016', '2017', '2018', '2019', '2020', '2021', '2022', '2023', '2024', '2025'],
                datasets: [{
                    label: 'Revenue',
                    data: [30, 40, 60, 35, 45, 30, 60, 35, 45, 50, 40],
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
        
        // Mini Charts
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
        new Chart(document.getElementById('ordersChart'), {
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
        
        // Users Mini Chart
        new Chart(document.getElementById('usersChart'), {
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
        
        // Sales by Time Chart
        new Chart(document.getElementById('salesTimeChart'), {
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
    });
    </script>
    <?php
}

// Helper function to get chart colors
function get_chart_color($key) {
    $colors = [
        'Saint Lucia' => '#4e73df',
        'Liberia' => '#ffce56',
        'Saint Helena' => '#1cc88a',
        'Kenya' => '#ff6384',
        'Christmas Island' => '#6c757d',
        'Germany' => '#4e73df',
        'Australia' => '#ffce56',
        'United Kingdom' => '#1cc88a',
        'Brazil' => '#ff6384',
        'Romania' => '#6c757d'
    ];
    
    return isset($colors[$key]) ? $colors[$key] : '#36a2eb';
}
?>