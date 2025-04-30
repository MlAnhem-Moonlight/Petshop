<?php
function petshop_user_info_page() {
    if (!petshop_is_logged_in()) {
        wp_redirect(admin_url('admin.php?page=petshop-management'));
        exit;
    }

    global $wpdb;
    $table_users = $wpdb->prefix . 'petshop_users';
    $user_id = $_SESSION['ps_user_id'];

    $user = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_users WHERE id = %d",
        $user_id
    ));
    ?>
    <div class="wrap">
        <h1>User Information</h1>
        <div class="user-info-container">
            <table class="form-table">
                <tr>
                    <th>Username:</th>
                    <td><?php echo esc_html($user->username); ?></td>
                </tr>
                <tr>
                    <th>Email:</th>
                    <td><?php echo esc_html($user->email); ?></td>
                </tr>
                <tr>
                    <th>Role:</th>
                    <td><?php echo esc_html($user->role); ?></td>
                </tr>
                <tr>
                    <th>Phone:</th>
                    <td><?php echo esc_html($user->phone); ?></td>
                </tr>
            </table>

            <div class="user-actions">
                <a href="<?php echo admin_url('admin.php?ps_action=logout'); ?>" 
                   class="button button-primary">
                    Logout
                </a>
            </div>
        </div>
    </div>
    <?php
}