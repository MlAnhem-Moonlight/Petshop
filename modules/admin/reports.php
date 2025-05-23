<?php
// filepath: c:\xampp\htdocs\wordpress\wp-content\plugins\Petshop-tuan\modules\admin\reports.php

function petshop_reports_page() {
    // Xử lý AJAX trả về bảng báo cáo
    if (isset($_GET['ajax']) && $_GET['ajax'] === 'report') {
        // Debug: kiểm tra code có chạy không
        // echo 'Hello from AJAX!'; exit;

        $year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
        $report_type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : 'monthly';
        global $wpdb;
        $rows = [];
        if ($report_type === 'monthly') {
            for ($m = 1; $m <= 12; $m++) {
                $total = $wpdb->get_var($wpdb->prepare(
                    "SELECT SUM(total_amount) FROM {$wpdb->prefix}petshop_orders WHERE YEAR(created_at) = %d AND MONTH(created_at) = %d AND status = 'completed'",
                    $year, $m
                ));
                $count = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->prefix}petshop_orders WHERE YEAR(created_at) = %d AND MONTH(created_at) = %d AND status = 'completed'",
                    $year, $m
                ));
                $rows[] = [$m, $total ? $total : 0, $count ? $count : 0];
            }
            $thead = '<tr><th>Tháng</th><th>Doanh thu</th><th>Số đơn</th></tr>';
        } elseif ($report_type === 'quarterly') {
            for ($q = 1; $q <= 4; $q++) {
                $start_month = ($q - 1) * 3 + 1;
                $end_month = $q * 3;
                $total = $wpdb->get_var($wpdb->prepare(
                    "SELECT SUM(total_amount) FROM {$wpdb->prefix}petshop_orders WHERE YEAR(created_at) = %d AND MONTH(created_at) BETWEEN %d AND %d AND status = 'completed'",
                    $year, $start_month, $end_month
                ));
                $count = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->prefix}petshop_orders WHERE YEAR(created_at) = %d AND MONTH(created_at) BETWEEN %d AND %d AND status = 'completed'",
                    $year, $start_month, $end_month
                ));
                $rows[] = ["Quý $q", $total ? $total : 0, $count ? $count : 0];
            }
            $thead = '<tr><th>Quý</th><th>Doanh thu</th><th>Số đơn</th></tr>';
        } elseif ($report_type === 'yearly') {
            for ($y = $year - 5; $y <= $year; $y++) {
                $total = $wpdb->get_var($wpdb->prepare(
                    "SELECT SUM(total_amount) FROM {$wpdb->prefix}petshop_orders WHERE YEAR(created_at) = %d AND status = 'completed'",
                    $y
                ));
                $count = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->prefix}petshop_orders WHERE YEAR(created_at) = %d AND status = 'completed'",
                    $y
                ));
                $rows[] = [$y, $total ? $total : 0, $count ? $count : 0];
            }
            $thead = '<tr><th>Năm</th><th>Doanh thu</th><th>Số đơn</th></tr>';
        }
        // Xuất HTML bảng
        echo '<table class="petshop-report-table" style="width:100%;border-collapse:collapse;background:#fff;border-radius:10px;box-shadow:0 2px 8px #eee;">';
        echo '<thead style="background:#8bc34a;color:#fff;">' . $thead . '</thead><tbody>';
        foreach ($rows as $row) {
            echo '<tr>';
            foreach ($row as $i => $cell) {
                if ($i === 1) $cell = number_format($cell, 0, ',', '.') . ' đ';
                echo '<td style="padding:10px 8px;text-align:center;border-bottom:1px solid #f1f1f1;">' . esc_html($cell) . '</td>';
            }
            echo '</tr>';
        }
        echo '</tbody></table>';
        exit;
    }

    // Xử lý xuất file CSV nếu có tham số export=excel
    if (isset($_GET['export']) && $_GET['export'] === 'excel') {
        if (!current_user_can('manage_options')) {
            wp_die('Bạn không có quyền xuất báo cáo!');
        }
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'petshop_export_report')) {
            wp_die('Invalid nonce.');
        }
        $year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
        $report_type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : 'monthly';
        global $wpdb;
        $data = [];
        if ($report_type === 'monthly') {
            $data[] = ['Tháng', 'Doanh thu', 'Số đơn'];
            for ($m = 1; $m <= 12; $m++) {
                $total = $wpdb->get_var($wpdb->prepare(
                    "SELECT SUM(total_amount) FROM {$wpdb->prefix}petshop_orders WHERE YEAR(created_at) = %d AND MONTH(created_at) = %d AND status = 'completed'",
                    $year, $m
                ));
                $count = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->prefix}petshop_orders WHERE YEAR(created_at) = %d AND MONTH(created_at) = %d AND status = 'completed'",
                    $year, $m
                ));
                $data[] = [$m, $total ? $total : 0, $count ? $count : 0];
            }
        } elseif ($report_type === 'quarterly') {
            $data[] = ['Quý', 'Doanh thu', 'Số đơn'];
            for ($q = 1; $q <= 4; $q++) {
                $start_month = ($q - 1) * 3 + 1;
                $end_month = $q * 3;
                $total = $wpdb->get_var($wpdb->prepare(
                    "SELECT SUM(total_amount) FROM {$wpdb->prefix}petshop_orders WHERE YEAR(created_at) = %d AND MONTH(created_at) BETWEEN %d AND %d AND status = 'completed'",
                    $year, $start_month, $end_month
                ));
                $count = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->prefix}petshop_orders WHERE YEAR(created_at) = %d AND MONTH(created_at) BETWEEN %d AND %d AND status = 'completed'",
                    $year, $start_month, $end_month
                ));
                $data[] = ["Quý $q", $total ? $total : 0, $count ? $count : 0];
            }
        } elseif ($report_type === 'yearly') {
            $data[] = ['Năm', 'Doanh thu', 'Số đơn'];
            for ($y = $year - 5; $y <= $year; $y++) {
                $total = $wpdb->get_var($wpdb->prepare(
                    "SELECT SUM(total_amount) FROM {$wpdb->prefix}petshop_orders WHERE YEAR(created_at) = %d AND status = 'completed'",
                    $y
                ));
                $count = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->prefix}petshop_orders WHERE YEAR(created_at) = %d AND status = 'completed'",
                    $y
                ));
                $data[] = [$y, $total ? $total : 0, $count ? $count : 0];
            }
        }
        // Xuất file CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=petshop_report_' . $year . '_' . $report_type . '.csv');
        $output = fopen('php://output', 'w');
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }

    // Chặn truy cập nếu không phải admin (chỉ khi không phải AJAX/export)
    if (!current_user_can('manage_options')) {
        wp_redirect(admin_url('admin.php?page=petshop-management'));
        exit;
    }

    $year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
    $report_type = isset($_GET['type']) ? $_GET['type'] : 'monthly';
    $nonce = wp_create_nonce('petshop_export_report');
    ?>
    <div class="wrap">
        <h1>Reports & Analytics</h1>
        <div class="report-filters" style="margin-bottom:20px;">
            <select name="report_type" id="report-type">
                <option value="monthly" <?php selected($report_type, 'monthly'); ?>>Monthly</option>
                <option value="quarterly" <?php selected($report_type, 'quarterly'); ?>>Quarterly</option>
                <option value="yearly" <?php selected($report_type, 'yearly'); ?>>Yearly</option>
            </select>
            <select name="year" id="report-year">
                <?php
                $current_year = date('Y');
                for ($i = $current_year; $i >= $current_year - 5; $i--) {
                    echo sprintf('<option value="%d" %s>%d</option>', 
                        $i, selected($year, $i, false), $i);
                }
                ?>
            </select>
            <button class="button action export-excel" onclick="exportReport()">Export Report</button>
        </div>
        <div id="report-container" style="margin-top:24px;">
            <!-- Bảng báo cáo sẽ được load ở đây -->
        </div>
    </div>
    <script>
    function exportReport() {
        var year = document.getElementById('report-year').value;
        var type = document.getElementById('report-type').value;
        var url = '<?php echo admin_url('admin.php?page=petshop-reports'); ?>&export=excel&year=' + year + '&type=' + type + '&_wpnonce=' + '<?php echo $nonce; ?>';
        var xhr = new XMLHttpRequest();
        xhr.open('GET', url, true);
        xhr.responseType = 'blob';
        xhr.onload = function() {
            if (xhr.status === 200) {
                var blob = new Blob([xhr.response], { type: 'text/csv' });
                var link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = 'petshop_report_' + year + '_' + type + '.csv';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            } else {
                alert('Có lỗi xảy ra khi xuất báo cáo!');
            }
        };
        xhr.onerror = function() {
            alert('Có lỗi xảy ra khi gửi yêu cầu!');
        };
        xhr.send();
    }
    function loadReportTable() {
        var year = document.getElementById('report-year').value;
        var type = document.getElementById('report-type').value;
        var container = document.getElementById('report-container');
        container.innerHTML = '<div style="padding:24px;text-align:center;">Đang tải dữ liệu...</div>';
        var url = '<?php echo admin_url('admin.php?page=petshop-reports'); ?>&ajax=report&year=' + year + '&type=' + type;
        var xhr = new XMLHttpRequest();
        xhr.open('GET', url, true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                container.innerHTML = xhr.responseText;
            } else {
                container.innerHTML = '<div style="color:red;padding:24px;text-align:center;">Không thể tải dữ liệu báo cáo!</div>';
            }
        };
        xhr.onerror = function() {
            container.innerHTML = '<div style="color:red;padding:24px;text-align:center;">Không thể tải dữ liệu báo cáo!</div>';
        };
        xhr.send();
    }
    document.getElementById('report-type').addEventListener('change', loadReportTable);
    document.getElementById('report-year').addEventListener('change', loadReportTable);
    window.addEventListener('DOMContentLoaded', loadReportTable);
    </script>
    <style>
    .petshop-report-table {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 8px #eee;
        margin-bottom: 24px;
    }
    .petshop-report-table th {
        background: #8bc34a;
        color: #fff;
        font-weight: 700;
        padding: 12px 8px;
        font-size: 16px;
        border-bottom: 2px solid #c5e1a5;
        text-align: center;
    }
    .petshop-report-table td {
        padding: 10px 8px;
        font-size: 15px;
        border-bottom: 1px solid #f1f1f1;
        text-align: center;
    }
    .petshop-report-table tr:last-child td {
        border-bottom: none;
    }
    </style>
    <?php
}
?>