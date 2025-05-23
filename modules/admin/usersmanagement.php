<?php
function petshop_users_page() {
    if (!petshop_is_logged_in() || $_SESSION['ps_user_role'] !== 'admin') {
        wp_redirect(admin_url('admin.php?page=petshop-management'));
        exit;
    }

    global $wpdb;
    $table_users = $wpdb->prefix . 'petshop_users';

    // Handle user actions
    if (isset($_POST['action']) && isset($_POST['user_id']) && check_admin_referer('petshop_user_action')) {
        $user_id = sanitize_text_field($_POST['user_id']);
        $action = sanitize_text_field($_POST['action']);

        switch ($action) {
            case 'ban':
                $wpdb->update(
                    $table_users,
                    array('status' => 'banned', 'updated_at' => current_time('mysql')),
                    array('id' => $user_id)
                );
                break;
            case 'unban':
                $wpdb->update(
                    $table_users,
                    array('status' => 'active', 'updated_at' => current_time('mysql')),
                    array('id' => $user_id)
                );
                break;
        }
    }

    // Pagination & Search
    $page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $per_page = isset($_GET['per_page']) ? intval($_GET['per_page']) : 10;
    $offset = ($page - 1) * $per_page;

    $search_term = isset($_GET['s']) ? sanitize_text_field(trim($_GET['s'])) : '';
    $where = '';
    $params = [];

    if (!empty($search_term)) {
        $where = "WHERE (
            LOWER(CAST(id AS CHAR)) LIKE %s OR
            LOWER(username) LIKE %s OR
            LOWER(email) LIKE %s OR
            LOWER(role) LIKE %s
        )";
        $like = '%' . $wpdb->esc_like(strtolower($search_term)) . '%';
        $params = [$like, $like, $like, $like];
    }

    $sql = "SELECT * FROM $table_users $where ORDER BY created_at DESC LIMIT %d OFFSET %d";
    $params[] = $per_page;
    $params[] = $offset;

    $prepared_sql = $wpdb->prepare($sql, ...$params);
    $users = $wpdb->get_results($prepared_sql);

    // Đếm tổng số user
    $count_sql = "SELECT COUNT(*) FROM $table_users $where";
    $count_params = !empty($search_term) ? $params : [];
    $total_users = !empty($search_term) ? $wpdb->get_var($wpdb->prepare($count_sql, ...$count_params)) : $wpdb->get_var($count_sql);
    $total_pages = ceil($total_users / $per_page);

    ?>
    <div class="wrap petshop-users-wrap">
        <h1 class="petshop-title"><span class="dashicons dashicons-admin-users"></span> Quản lý người dùng PetShop</h1>
        
        <form method="get" class="petshop-search-box" id="petshop-search-form">
            <input type="hidden" name="page" value="petshop-users">
            <input type="hidden" name="per_page" value="<?php echo esc_attr($per_page); ?>">
            <input type="search" id="user-search-input" name="s" placeholder="🔍 Tìm kiếm tất cả thông tin" 
                   value="<?php echo esc_attr($search_term); ?>">
            <button type="submit" id="search-button" class="button button-primary"><span class="dashicons dashicons-search"></span> Tìm kiếm</button>
        </form>

        <?php if (!empty($search_term) && empty($users)): ?>
            <div class="notice notice-info">
                <p>Không tìm thấy người dùng nào với từ khóa "<strong><?php echo esc_html($search_term); ?></strong>".</p>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($users)): ?>
        <table class="wp-list-table widefat fixed striped petshop-users-table">
            <thead>
                <tr>
                    <th class="check-column">
                        <input type="checkbox" id="cb-select-all">
                    </th>
                    <th>ID</th>
                    <th>Họ và tên</th>
                    <th>Email</th>
                    <th>Ngày tạo</th>
                    <th>Ngày cập nhật</th>
                    <th>Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): 
                    $created_date = date('d/m/Y H:i', strtotime($user->created_at));
                    $updated_date = !empty($user->updated_at) ? date('d/m/Y H:i', strtotime($user->updated_at)) : '-';
                ?>
                    <tr data-status="<?php echo esc_attr($user->status ?? 'active'); ?>">
                        <td class="check-column">
                            <input type="checkbox" name="user_id[]" value="<?php echo esc_attr($user->id); ?>">
                        </td>
                        <td><span class="user-id"><?php echo esc_html($user->id); ?></span></td>
                        <td>
                            <span class="user-avatar">
                                <span class="dashicons dashicons-admin-users"></span>
                            </span>
                            <span class="user-fullname"><?php echo esc_html(!empty($user->fullname) ? $user->fullname : $user->username); ?></span>
                        </td>
                        <td><?php echo esc_html($user->email); ?></td>
                        <td><?php echo esc_html($created_date); ?></td>
                        <td><?php echo esc_html($updated_date); ?></td>
                        <td>
                            <select class="user-status-selector" data-user-id="<?php echo esc_attr($user->id); ?>" data-previous-status="<?php echo esc_attr($user->status ?? 'active'); ?>">
                                <option value="active" <?php selected($user->status, 'active'); ?>>🟢 Active</option>
                                <option value="banned" <?php selected($user->status, 'banned'); ?>>🔴 Banned</option>
                            </select>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="7">
                        <div class="tablenav-pages petshop-pagination">
                            <span class="displaying-num">
                                Hiển thị <?php echo sprintf("%d-%d trong tổng số %d", 
                                    ($page - 1) * $per_page + 1, 
                                    min($page * $per_page, $total_users), 
                                    $total_users); ?> người dùng
                            </span>
                            <div class="pagination-controls">
                                <span>Hiển thị: 
                                    <select id="per-page-selector">
                                        <option value="10" <?php selected($per_page, 10); ?>>10</option>
                                        <option value="25" <?php selected($per_page, 25); ?>>25</option>
                                        <option value="50" <?php selected($per_page, 50); ?>>50</option>
                                    </select>
                                </span>
                                <?php if ($total_pages > 1): ?>
                                <span class="pagination-links">
                                    <?php if ($page > 1): ?>
                                        <a class="prev-page" href="<?php echo esc_url(add_query_arg(['paged' => $page - 1, 's' => $search_term, 'per_page' => $per_page])); ?>">
                                            <span class="dashicons dashicons-arrow-left-alt2"></span>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <span class="paging-input">
                                        <?php echo $page; ?> / <?php echo $total_pages; ?>
                                    </span>
                                    
                                    <?php if ($page < $total_pages): ?>
                                        <a class="next-page" href="<?php echo esc_url(add_query_arg(['paged' => $page + 1, 's' => $search_term, 'per_page' => $per_page])); ?>">
                                            <span class="dashicons dashicons-arrow-right-alt2"></span>
                                        </a>
                                    <?php endif; ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                </tr>
            </tfoot>
        </table>
        <?php endif; ?>
        
        <div class="petshop-bulk-actions">
            <select id="bulk-action-selector">
                <option value="">Chọn hành động</option>
                <option value="ban">🚫 Cấm người dùng</option>
                <option value="unban">✅ Bỏ cấm người dùng</option>
            </select>
            <button id="bulk-action-apply" class="button button-primary"><span class="dashicons dashicons-yes"></span> Áp dụng</button>
        </div>
    </div>

    <style>
    .petshop-users-wrap {
        background: linear-gradient(120deg, #f9fff5 80%, #e8f5e9 100%);
        padding: 30px 20px 20px 20px;
        border-radius: 12px;
        box-shadow: 0 4px 24px rgba(76,175,80,0.08);
        margin-top: 20px;
    }
    .petshop-title {
        color: #388e3c;
        margin-bottom: 24px;
        font-size: 28px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .petshop-search-box {
        display: flex;
        margin-bottom: 22px;
        gap: 12px;
        align-items: center;
    }
    .petshop-search-box input {
        flex-grow: 1;
        padding: 10px 14px;
        border: 1.5px solid #aed581;
        border-radius: 6px;
        font-size: 15px;
        background: #fff;
        transition: border 0.2s;
    }
    .petshop-search-box input:focus {
        border: 1.5px solid #4caf50;
        outline: none;
    }
    .petshop-search-box button {
        background: linear-gradient(90deg, #8bc34a 60%, #4caf50 100%);
        color: white;
        border: none;
        border-radius: 6px;
        padding: 10px 22px;
        cursor: pointer;
        font-weight: bold;
        font-size: 15px;
        display: flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 2px 8px rgba(76,175,80,0.07);
        transition: background 0.2s;
    }
    .petshop-search-box button:hover {
        background: linear-gradient(90deg, #689f38 60%, #388e3c 100%);
    }
    .petshop-users-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 0 10px rgba(0,0,0,0.05);
        margin-bottom: 0;
    }
    .petshop-users-table th {
        background: linear-gradient(90deg, #8bc34a 60%, #4caf50 100%);
        color: white;
        font-weight: bold;
        text-align: left;
        padding: 14px 10px;
        border: none;
        font-size: 15px;
    }
    .petshop-users-table tr:nth-child(even) {
        background-color: #f2f9eb;
    }
    .petshop-users-table tr:nth-child(odd) {
        background-color: #ffffff;
    }
    .petshop-users-table tr:hover {
        background-color: #e8f5e9;
    }
    .petshop-users-table td {
        padding: 12px 10px;
        border-bottom: 1px solid #e0e0e0;
        font-size: 15px;
        vertical-align: middle;
    }
    tr[data-status="banned"] {
        background-color: #ffebee !important;
        color: #d32f2f;
    }
    .user-avatar {
        display: inline-block;
        margin-right: 7px;
        vertical-align: middle;
    }
    .user-fullname {
        font-weight: 500;
    }
    .user-id {
        color: #8bc34a;
        font-weight: bold;
    }
    .user-status-selector {
        padding: 7px 12px;
        border: 1px solid #aed581;
        border-radius: 5px;
        background: #fff;
        font-size: 14px;
        min-width: 90px;
        font-weight: 500;
    }
    .user-status-selector option[value="active"] {
        color: #388e3c;
    }
    .user-status-selector option[value="banned"] {
        color: #d32f2f;
    }
    .petshop-pagination {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 18px 0 0 0;
    }
    .pagination-controls {
        display: flex;
        align-items: center;
        gap: 18px;
    }
    .pagination-links {
        display: flex;
        align-items: center;
        gap: 7px;
    }
    .pagination-links a {
        display: inline-block;
        padding: 6px 12px;
        background: linear-gradient(90deg, #8bc34a 60%, #4caf50 100%);
        color: white;
        text-decoration: none;
        border-radius: 4px;
        font-weight: bold;
        font-size: 15px;
        transition: background 0.2s;
    }
    .pagination-links a:hover {
        background: linear-gradient(90deg, #689f38 60%, #388e3c 100%);
    }
    .paging-input {
        font-weight: 500;
        color: #388e3c;
    }
    .petshop-bulk-actions {
        margin-top: 20px;
        display: flex;
        gap: 12px;
        align-items: center;
    }
    .petshop-bulk-actions select,
    #per-page-selector,
    .user-status-selector {
        padding: 8px 12px;
        border: 1.5px solid #aed581;
        border-radius: 5px;
        background-color: white;
        font-size: 14px;
    }
    .petshop-bulk-actions button {
        background: linear-gradient(90deg, #8bc34a 60%, #4caf50 100%);
        color: white;
        border: none;
        border-radius: 6px;
        padding: 8px 20px;
        cursor: pointer;
        font-weight: bold;
        font-size: 15px;
        display: flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 2px 8px rgba(76,175,80,0.07);
        transition: background 0.2s;
    }
    .petshop-bulk-actions button:hover {
        background: linear-gradient(90deg, #689f38 60%, #388e3c 100%);
    }
    </style>
    <link rel="stylesheet" href="<?php echo includes_url('css/dashicons.min.css'); ?>">

    <script>
    jQuery(document).ready(function($) {
        // Handle search form submission
        $('#petshop-search-form').on('submit', function(e) {
            e.preventDefault();
            var searchTerm = $('#user-search-input').val().trim();
            var perPage = $('#per-page-selector').val() || '<?php echo esc_js($per_page); ?>';
            console.log('Search Term:', searchTerm); // Debug: Log search term
            console.log('Per Page:', perPage); // Debug: Log per page value
            var url = new URL(window.location.href);
            url.searchParams.set('s', searchTerm);
            url.searchParams.set('paged', 1);
            url.searchParams.set('per_page', perPage);
            console.log('Redirect URL:', url.toString()); // Debug: Log constructed URL
            window.location.href = url.toString();
        });

        // Bulk actions
        $('#bulk-action-apply').on('click', function() {
            var action = $('#bulk-action-selector').val();
            if (action === '') {
                alert('Vui lòng chọn một hành động');
                return;
            }
            var selectedUsers = $('input[name="user_id[]"]:checked').map(function() {
                return $(this).val();
            }).get();
            if (selectedUsers.length === 0) {
                alert('Vui lòng chọn ít nhất một người dùng');
                return;
            }
            var confirmMessage = action === 'ban' ? 
                'Bạn chắc chắn muốn cấm ' + selectedUsers.length + ' người dùng đã chọn?' : 
                'Bạn chắc chắn muốn bỏ cấm ' + selectedUsers.length + ' người dùng đã chọn?';
            if (confirm(confirmMessage)) {
                var processedCount = 0;
                var successCount = 0;
                selectedUsers.forEach(function(userId) {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'petshop_bulk_user_action',
                            user_id: userId,
                            bulk_action: action,
                            security: '<?php echo wp_create_nonce('petshop_bulk_user_action_nonce'); ?>'
                        },
                        success: function(response) {
                            processedCount++;
                            if (response.success) {
                                successCount++;
                            }
                            if (processedCount === selectedUsers.length) {
                                alert('Đã cập nhật ' + successCount + ' trong tổng số ' + selectedUsers.length + ' người dùng');
                                location.reload();
                            }
                        },
                        error: function() {
                            processedCount++;
                        }
                    });
                });
            }
        });

        // User status change
        $('.user-status-selector').on('change', function() {
            var $this = $(this);
            var userId = $this.data('user-id');
            var newStatus = $this.val();
            var previousStatus = $this.data('previous-status') || 'active';
            var userName = $this.closest('tr').find('td:nth-child(3)').text().trim();
            var confirmMessage = newStatus === 'banned' 
                ? 'Bạn chắc chắn muốn cấm người dùng "' + userName + '"?' 
                : 'Bạn chắc chắn muốn bỏ cấm người dùng "' + userName + '"?';
            if (confirm(confirmMessage)) {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'petshop_update_user_status',
                        user_id: userId,
                        status: newStatus,
                        security: '<?php echo wp_create_nonce("petshop_update_user_status"); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            $this.closest('tr').attr('data-status', newStatus);
                            $this.data('previous-status', newStatus);
                        } else {
                            alert('Không thể cập nhật trạng thái người dùng: ' + response.data);
                            $this.val(previousStatus);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Đã xảy ra lỗi khi cập nhật trạng thái');
                        $this.val(previousStatus);
                    }
                });
            } else {
                $this.val(previousStatus);
            }
        });

        // Per page selector
        $('#per-page-selector').on('change', function() {
            var newPerPage = $(this).val();
            var url = new URL(window.location.href);
            url.searchParams.set('per_page', newPerPage);
            url.searchParams.set('paged', 1);
            console.log('Per Page Change URL:', url.toString()); // Debug: Log URL
            window.location.href = url.toString();
        });

        // Select all
        $('#cb-select-all').on('change', function() {
            $('input[name="user_id[]"]').prop('checked', $(this).prop('checked'));
        });
    });
    </script>
    <?php
}

// Ajax handler for bulk user actions
function petshop_bulk_user_action() {
    check_ajax_referer('petshop_bulk_user_action_nonce', 'security');
    if (!petshop_is_logged_in() || $_SESSION['ps_user_role'] !== 'admin') {
        wp_send_json_error('Permission denied');
        return;
    }
    global $wpdb;
    $table_users = $wpdb->prefix . 'petshop_users';
    $user_id = sanitize_text_field($_POST['user_id']);
    $bulk_action = sanitize_text_field($_POST['bulk_action']);
    $status = ($bulk_action === 'ban') ? 'banned' : 'active';
    $result = $wpdb->update(
        $table_users,
        array('status' => $status, 'updated_at' => current_time('mysql')),
        array('id' => $user_id)
    );
    if ($result !== false) {
        wp_send_json_success();
    } else {
        wp_send_json_error();
    }
}
add_action('wp_ajax_petshop_bulk_user_action', 'petshop_bulk_user_action');

// Ajax handler for single user status update
function petshop_update_user_status() {
    check_ajax_referer('petshop_update_user_status', 'security');
    if (!petshop_is_logged_in() || $_SESSION['ps_user_role'] !== 'admin') {
        wp_send_json_error('Permission denied');
        return;
    }
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
    if (!$user_id || !in_array($status, ['active', 'banned'])) {
        wp_send_json_error('Invalid parameters');
        return;
    }
    global $wpdb;
    $table_users = $wpdb->prefix . 'petshop_users';
    $result = $wpdb->update(
        $table_users,
        [
            'status' => $status,
            'updated_at' => current_time('mysql')
        ],
        ['id' => $user_id]
    );
    if ($result !== false) {
        wp_send_json_success();
    } else {
        wp_send_json_error('Database update failed');
    }
}
add_action('wp_ajax_petshop_update_user_status', 'petshop_update_user_status');