<?php
function petshop_admin_dashboard() {
    if (!petshop_is_logged_in() || $_SESSION['ps_user_role'] !== 'admin') {
        wp_redirect(admin_url('admin.php?page=petshop-management'));
        exit;
    }
    
    ?>
    <div class="wrap">
        <h1>Pet Shop Dashboard</h1>
        
        <div class="dashboard-widgets-wrap">
            <!-- Quick Stats -->
            <div class="dashboard-widget">
                <h3>Quick Statistics</h3>
                <?php 
                    // Get total orders
                    global $wpdb;
                    $total_orders = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}petshop_orders");
                    
                    // Get total revenue
                    $total_revenue = $wpdb->get_var("SELECT SUM(total_amount) FROM {$wpdb->prefix}petshop_orders WHERE status = 'completed'");
                    
                    // Get total users
                    $total_users = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}petshop_users WHERE role = 'user'");
                ?>
                <ul>
                    <li>Total Orders: <?php echo $total_orders; ?></li>
                    <li>Total Revenue: $<?php echo number_format($total_revenue, 2); ?></li>
                    <li>Total Users: <?php echo $total_users; ?></li>
                </ul>
            </div>
            
            <!-- Recent Orders -->
            <div class="dashboard-widget">
                <h3>Recent Orders</h3>
                <?php
                $recent_orders = $wpdb->get_results(
                    "SELECT * FROM {$wpdb->prefix}petshop_orders ORDER BY created_at DESC LIMIT 5"
                );
                if ($recent_orders): ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_orders as $order): ?>
                                <tr>
                                    <td>#<?php echo $order->id; ?></td>
                                    <td><?php echo esc_html($order->customer_name); ?></td>
                                    <td>$<?php echo number_format($order->total_amount, 2); ?></td>
                                    <td><?php echo esc_html($order->status); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No recent orders.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
}