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
    
    // Get orders by location
    $orders_by_location = $wpdb->get_results("
        SELECT customer_address as location, COUNT(*) as count
        FROM {$wpdb->prefix}petshop_orders
        GROUP BY customer_address
        ORDER BY count DESC
        LIMIT 5
    ", ARRAY_A);
    
    // Convert to associative array
    $orders_by_location = array_column($orders_by_location, 'count', 'location');
    
    // Get sales by location
    $sales_by_location = $wpdb->get_results("
        SELECT customer_address as location, 
               SUM(total_amount) as total,
               (SUM(total_amount) / (SELECT SUM(total_amount) FROM {$wpdb->prefix}petshop_orders) * 100) as percentage
        FROM {$wpdb->prefix}petshop_orders
        WHERE status = 'completed'
        GROUP BY customer_address
        ORDER BY total DESC
        LIMIT 5
    ", ARRAY_A);
    
    // Convert to associative array
    $sales_by_location = array_column($sales_by_location, 'percentage', 'location');
    
    // Get monthly revenue data for current year
    $monthly_data = $wpdb->get_results("
        SELECT MONTH(created_at) as month,
               SUM(total_amount) as revenue
        FROM {$wpdb->prefix}petshop_orders
        WHERE YEAR(created_at) = YEAR(CURRENT_DATE())
        AND status = 'completed'
        GROUP BY MONTH(created_at)
        ORDER BY month
    ", ARRAY_A);
    
    $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    $monthly_revenue = array_fill(0, 12, 0); // Initialize with zeros
    
    foreach ($monthly_data as $data) {
        $monthly_revenue[$data['month'] - 1] = (float)$data['revenue'];
    }
    
    ?>
    <div class="wrap petshop-dashboard-wrap">
        <!-- User Info Bar -->
        <div class="ps-user-info-bar">
            <div class="ps-time-filter">
                <button class="ps-btn ps-btn-active">Tháng</button>
                <button class="ps-btn">Quý</button>
            </div>
        </div>
        
        <!-- KPI Cards Row -->
        <div class="ps-kpi-cards">
            <div class="ps-kpi-card">
                <div class="ps-kpi-header">
                    <h3>Doanh thu</h3>
                    <span class="ps-badge ps-badge-success">+15%</span>
                </div>
                <div class="ps-kpi-date">May 20 - Jun 20, 2019</div>
                <div class="ps-kpi-value">
                    <span class="ps-kpi-icon">✈️</span>
                    <span class="ps-kpi-number">24.583m VND</span>
                </div>
                <a href="#" class="ps-read-more">Xem thêm</a>
            </div>
            
            <div class="ps-kpi-card">
                <div class="ps-kpi-header">
                    <h3>Lợi nhuận</h3>
                    <span class="ps-badge ps-badge-info">+61%</span>
                </div>
                <div class="ps-kpi-date">May 20 - Jun 20, 2019</div>
                <div class="ps-kpi-value">
                    <span class="ps-kpi-icon">🧳</span>
                    <span class="ps-kpi-number">10.46m VND</span>
                </div>
                <a href="#" class="ps-read-more">Xem thêm</a>
            </div>
            
            <div class="ps-kpi-card">
                <div class="ps-kpi-header">
                    <h3>Bán hàng hằng ngày</h3>
                    <span class="ps-badge ps-badge-warning">+34%</span>
                </div>
                <div class="ps-kpi-date">May 20 - Jun 20, 2019</div>
                <div class="ps-kpi-value">
                    <span class="ps-kpi-icon">📦</span>
                    <span class="ps-kpi-number">342k VND</span>
                </div>
                <a href="#" class="ps-read-more">Xem thêm</a>
            </div>
        </div>
        
        <!-- Sales Status Section -->
        <div class="ps-sales-status">
            <div class="ps-section-header">
                <h3>Tình trạng bán hàng <span class="ps-subtitle">Hiệu suất cho doanh thu trực tuyến</span></h3>
                <div class="ps-time-tabs">
                    <button class="ps-tab">Tuần</button>
                    <button class="ps-tab ps-tab-active">Tháng</button>
                    <button class="ps-tab">Năm</button>
                    <button class="ps-tab">Tổng</button>
                </div>
            </div>
            
            <div class="ps-charts-container">
                <div class="ps-chart-card">
                    <h4>Đặt hàng theo vị trí</h4>
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
                    <h4>Bán theo Địa điểm</h4>
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
                    <h4>Doanh thu tháng trước</h4>
                    <div class="ps-chart-container">
                        <canvas id="revenueChart"></canvas>
                    </div>
                    <div class="ps-summary-stats">
                        <div class="ps-stat">
                            <div class="ps-stat-label">Tổng thu nhập</div>
                            <div class="ps-stat-value">3,567.56k VND</div>
                        </div>
                        <div class="ps-stat">
                            <div class="ps-stat-label">Trung bình tháng</div>
                            <div class="ps-stat-value">769.08k VND</div>
                        </div>
                        <div class="ps-stat">
                            <div class="ps-stat-label">Tổng doanh số</div>
                            <div class="ps-stat-value">54.89m VND</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Detailed Reports Section -->
        <div class="ps-reports-section">
            <div class="ps-report-card">
                <div class="ps-report-header">
                    <h4>Báo cáo chi tiết</h4>
                    <div class="ps-pagination">
                        <button class="ps-nav-btn"><span>❮</span></button>
                        <button class="ps-nav-btn"><span>❯</span></button>
                    </div>
                </div>
                
                <div class="ps-report-stats">
                    <div class="ps-stat-column">
                        <div class="ps-stat-header">
                            <div>Đơn hàng</div>
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
                            <div>Người dùng</div>
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
                            <div>Người dùng</div>
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
                            <div>Khách</div>
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
                <h4>Doanh số theo thời gian</h4>
                <div class="ps-sales-time-header">
                    <div class="ps-sales-number">6,576 <span class="ps-sales-change ps-positive">+25.00</span></div>
                    <div class="ps-sales-legend">
                        <div class="ps-legend-item">
                            <span class="ps-legend-color ps-blue"></span>
                            <span>Ấn tượng</span>
                        </div>
                        <div class="ps-legend-item">
                            <span class="ps-legend-color ps-purple"></span>
                            <span>Doanh thu</span>
                        </div>
                    </div>
                </div>
                <div class="ps-chart-container">
                    <canvas id="salesTimeChart"></canvas>
                </div>
                <div class="ps-sales-description">
                    <p>Nhiều người đăng ký chương trình liên kết với hy vọng kiếm được một khoản tiền lớn. Họ quảng cáo một vài...</p>
                    <a href="#" class="ps-read-more">Đọc thêm</a>
                </div>
            </div>
            
            <div class="ps-report-card">
                <h4>Người dùng từ Việt Nam</h4>
                <div class="ps-map-container">
                    <img src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . 'assets/vn-map.png'; ?>" alt="VN Map" class="ps-us-map">
                </div>
                <div class="ps-state-stats">
                    <div class="ps-state-row">
                        <div class="ps-state-name">Hà Nội</div>
                        <div class="ps-state-bar"><div class="ps-bar-fill ps-teal" style="width: 163%"></div></div>
                        <div class="ps-state-percent">163%</div>
                    </div>
                    <div class="ps-state-row">
                        <div class="ps-state-name">Quảng Ninh</div>
                        <div class="ps-state-bar"><div class="ps-bar-fill ps-pink" style="width: 85%"></div></div>
                        <div class="ps-state-percent">86.2%</div>
                    </div>
                    <div class="ps-state-row">
                        <div class="ps-state-name">Bắc Ninh</div>
                        <div class="ps-state-bar"><div class="ps-bar-fill ps-blue" style="width: 123%"></div></div>
                        <div class="ps-state-percent">122%</div>
                    </div>
                    <div class="ps-state-row">
                        <div class="ps-state-name">Tp Hồ Chí Minh</div>
                        <div class="ps-state-bar"><div class="ps-bar-fill ps-orange" style="width: 35%"></div></div>
                        <div class="ps-state-percent">165%</div>
                    </div>
                    <div class="ps-state-row">
                        <div class="ps-state-name">Thanh Hóa</div>
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
                labels: <?php echo json_encode($months); ?>,
                datasets: [{
                    label: 'Revenue',
                    data: <?php echo json_encode($monthly_revenue); ?>,
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

    // Initialize chart data objects
    const ordersLocationChartData = {
        labels: <?php echo json_encode(array_keys($orders_by_location)); ?>,
        data: <?php echo json_encode(array_values($orders_by_location)); ?>
    };

    const salesLocationChartData = {
        labels: <?php echo json_encode(array_keys($sales_by_location)); ?>,
        data: <?php echo json_encode(array_values($sales_by_location)); ?>
    };

    const revenueChartData = {
        labels: <?php echo json_encode($months); ?>,
        data: <?php echo json_encode($monthly_revenue); ?>
    };

    // Add year selector
    document.querySelector('.ps-user-info-bar').insertAdjacentHTML('beforeend', `
        <select id="ps-year-selector" class="ps-year-select">
            ${Array.from({length: 6}, (_, i) => new Date().getFullYear() - i)
                .map(year => `<option value="${year}">${year}</option>`).join('')}
        </select>
    `);

    // Initialize dashboard
    document.addEventListener('DOMContentLoaded', function() {
        PetshopDashboard.init();
    });
    </script>
    <?php
}

// Helper function to get chart colors
function get_chart_color($key) {
    global $wpdb;
    
    // Get distinct cities from orders table
    $cities = $wpdb->get_col("
        SELECT DISTINCT customer_address 
        FROM {$wpdb->prefix}petshop_orders
        ORDER BY customer_address
    ");
    
    // Fixed color palette
    $color_palette = [
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
    
    // Create city-color mapping
    $colors = array_combine(
        $cities, 
        array_slice($color_palette, 0, count($cities))
    );
    
    return isset($colors[$key]) ? $colors[$key] : '#36a2eb';
}
?>