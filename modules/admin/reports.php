<?php
function petshop_reports_page() {
    if (!petshop_is_logged_in() || $_SESSION['ps_user_role'] !== 'admin') {
        wp_redirect(admin_url('admin.php?page=petshop-management'));
        exit;
    }
    
    $year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
    $report_type = isset($_GET['type']) ? $_GET['type'] : 'monthly';
    
    ?>
    <div class="wrap">
        <h1>Reports & Analytics</h1>
        
        <div class="report-filters">
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
            
            <button class="button action export-excel">Export Report</button>
        </div>
        
        <div id="report-container">
            <!-- Report content will be loaded here -->
        </div>
    </div>
    <?php
}