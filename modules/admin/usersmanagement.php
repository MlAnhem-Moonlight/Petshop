<?php
function petshop_users_page() {
    if (!petshop_is_logged_in() || $_SESSION['ps_user_role'] !== 'admin') {
        wp_redirect(admin_url('admin.php?page=petshop-management'));
        exit;
    }

    global $wpdb;
    $table_users = $wpdb->prefix . 'petshop_users';

    // Handle user actions
    if (isset($_POST['action']) && isset($_POST['user_id'])) {
        $user_id = intval($_POST['user_id']);
        $action = $_POST['action'];

        switch ($action) {
            case 'ban':
                $wpdb->update(
                    $table_users,
                    array('status' => 'banned'),
                    array('id' => $user_id)
                );
                break;
            case 'unban':
                $wpdb->update(
                    $table_users,
                    array('status' => 'active'),
                    array('id' => $user_id)
                );
                break;
        }
    }

    // Get users list - exclude admin accounts
    $users = $wpdb->get_results("SELECT * FROM $table_users WHERE role != 'admin' ORDER BY created_at DESC");
    
    ?>
    <div class="wrap">
        <h1>User Management</h1>
        
        <div class="tablenav top">
            <div class="alignleft actions">
                <button class="button action export-excel">Export to Excel</button>
            </div>
        </div>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Registered</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo esc_html($user->username); ?></td>
                        <td><?php echo esc_html($user->email); ?></td>
                        <td><?php echo esc_html($user->phone); ?></td>
                        <td><?php echo esc_html($user->role); ?></td>
                        <td><?php echo isset($user->status) ? esc_html($user->status) : 'active'; ?></td>
                        <td><?php echo date('Y-m-d', strtotime($user->created_at)); ?></td>
                        <td>
                            <?php if (!isset($user->status) || $user->status === 'active'): ?>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="action" value="ban">
                                    <input type="hidden" name="user_id" value="<?php echo $user->id; ?>">
                                    <button type="submit" class="button button-small">Ban</button>
                                </form>
                            <?php else: ?>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="action" value="unban">
                                    <input type="hidden" name="user_id" value="<?php echo $user->id; ?>">
                                    <button type="submit" class="button button-small">Unban</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}