<?php
function petshop_users_page() {
    if (!petshop_is_logged_in() || $_SESSION['ps_user_role'] !== 'admin') {
        wp_redirect(admin_url('admin.php?page=petshop-management'));
        exit;
    }

    global $wpdb;
    $table_users = 'wp_petshop_users';

    // Handle user actions
    if (isset($_POST['action']) && isset($_POST['user_id'])) {
        $user_id = sanitize_text_field($_POST['user_id']);
        $action = $_POST['action'];

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

    // Get users list with pagination
    $page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $per_page = isset($_GET['per_page']) ? intval($_GET['per_page']) : 10;
    $offset = ($page - 1) * $per_page;
    
    // Search functionality
    $search_term = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
    $search_query = '';
    
    if (!empty($search_term)) {
        $search_query = $wpdb->prepare(
            " WHERE fullname LIKE %s OR username LIKE %s OR email LIKE %s",
            '%' . $wpdb->esc_like($search_term) . '%',
            '%' . $wpdb->esc_like($search_term) . '%',
            '%' . $wpdb->esc_like($search_term) . '%'
        );
    }
    
    $users = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_users $search_query ORDER BY created_at DESC LIMIT %d OFFSET %d", 
        $per_page, $offset
    ));
    
    $total_users = $wpdb->get_var("SELECT COUNT(*) FROM $table_users $search_query");
    $total_pages = ceil($total_users / $per_page);
    
    ?>
    <div class="wrap petshop-users-wrap">
        <h1 class="petshop-title">Quản lý người dùng PetShop</h1>
        
        <div class="petshop-search-box">
            <input type="search" id="user-search-input" placeholder="Tìm theo tên, username hoặc email" 
                   value="<?php echo esc_attr($search_term); ?>">
            <button type="button" id="search-button" class="button">Tìm kiếm</button>
        </div>
        
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
                    // Format timestamps
                    $created_date = date('Y-m-d\nH:i:s', strtotime($user->created_at));
                    $updated_date = !empty($user->updated_at) ? date('Y-m-d\nH:i:s', strtotime($user->updated_at)) : '-';
                ?>
                    <tr data-status="<?php echo esc_attr($user->status ?? 'active'); ?>">
                        <td class="check-column">
                            <input type="checkbox" name="user_id[]" value="<?php echo esc_attr($user->id); ?>">
                        </td>
                        <td><?php echo esc_html($user->id); ?></td>
                        <td><?php echo esc_html(!empty($user->fullname) ? $user->fullname : $user->username); ?></td>
                        <td><?php echo esc_html($user->email); ?></td>
                        <td><?php echo esc_html($created_date); ?></td>
                        <td><?php echo esc_html($updated_date); ?></td>
                        <td>
                            <select class="user-status-selector" data-user-id="<?php echo esc_attr($user->id); ?>" data-previous-status="<?php echo esc_attr($user->status ?? 'active'); ?>">
                                <option value="active" <?php selected($user->status, 'active'); ?>>Active</option>
                                <option value="banned" <?php selected($user->status, 'banned'); ?>>Banned</option>
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
                                        <a class="prev-page" href="<?php echo add_query_arg('paged', $page - 1); ?>">
                                            <span>«</span>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <span class="paging-input">
                                        <?php echo $page; ?> of <?php echo $total_pages; ?>
                                    </span>
                                    
                                    <?php if ($page < $total_pages): ?>
                                        <a class="next-page" href="<?php echo add_query_arg('paged', $page + 1); ?>">
                                            <span>»</span>
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
        
        <div class="petshop-bulk-actions">
            <select id="bulk-action-selector">
                <option value="">Chọn hành động</option>
                <option value="ban">Cấm người dùng</option>
                <option value="unban">Bỏ cấm người dùng</option>
            </select>
            <button id="bulk-action-apply" class="button">Áp dụng</button>
        </div>
    </div>

    <style>
    .petshop-users-wrap {
        background-color: #f9fff5;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    
    .petshop-title {
        color: #4CAF50;
        margin-bottom: 20px;
        font-size: 24px;
        border-bottom: 2px solid #8BC34A;
        padding-bottom: 10px;
    }
    
    .petshop-users-table {
        width: 100%;
        border-collapse: collapse;
        border-radius: 6px;
        overflow: hidden;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
    }
    
    .petshop-users-table th {
        background-color: #8BC34A;
        color: white;
        font-weight: bold;
        text-align: left;
        padding: 12px;
        border: none;
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
        padding: 10px 12px;
        border-bottom: 1px solid #E0E0E0;
    }
    
    tr[data-status="banned"] {
        background-color: #ffebee !important;
        color: #d32f2f;
    }
    
    .check-column {
        width: 30px;
        text-align: center;
    }
    
    .petshop-search-box {
        display: flex;
        margin-bottom: 20px;
        gap: 10px;
    }
    
    .petshop-search-box input {
        flex-grow: 1;
        padding: 8px 12px;
        border: 1px solid #AED581;
        border-radius: 4px;
        font-size: 14px;
    }
    
    .petshop-search-box button {
        background-color: #8BC34A;
        color: white;
        border: none;
        border-radius: 4px;
        padding: 8px 16px;
        cursor: pointer;
        font-weight: bold;
    }
    
    .petshop-search-box button:hover {
        background-color: #689F38;
    }
    
    .petshop-pagination {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 0;
    }
    
    .pagination-controls {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .pagination-links {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .pagination-links a {
        display: inline-block;
        padding: 5px 10px;
        background-color: #8BC34A;
        color: white;
        text-decoration: none;
        border-radius: 3px;
        font-weight: bold;
    }
    
    .pagination-links a:hover {
        background-color: #689F38;
    }
    
    .petshop-bulk-actions {
        margin-top: 15px;
        display: flex;
        gap: 10px;
    }
    
    .petshop-bulk-actions select,
    #per-page-selector,
    .user-status-selector {
        padding: 6px 10px;
        border: 1px solid #AED581;
        border-radius: 4px;
        background-color: white;
    }
    
    .petshop-bulk-actions button {
        background-color: #8BC34A;
        color: white;
        border: none;
        border-radius: 4px;
        padding: 6px 16px;
        cursor: pointer;
        font-weight: bold;
    }
    
    .petshop-bulk-actions button:hover {
        background-color: #689F38;
    }
    </style>

    <script>
    jQuery(document).ready(function($) {
        // Handle bulk actions
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
            
            // Xác nhận trước khi thực hiện hành động
            var confirmMessage = action === 'ban' ? 
                'Bạn chắc chắn muốn cấm ' + selectedUsers.length + ' người dùng đã chọn?' : 
                'Bạn chắc chắn muốn bỏ cấm ' + selectedUsers.length + ' người dùng đã chọn?';
            
            if (confirm(confirmMessage)) {
                // Thực hiện AJAX cho từng user
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
                            
                            // Khi tất cả đã xử lý xong
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
        
        // Handle user status change
        $('.user-status-selector').on('change', function() {
            var $this = $(this);
            var userId = $this.data('user-id');
            var newStatus = $this.val();
            var previousStatus = $this.data('previous-status') || 'active';
            
            // Prepare confirmation message
            var userName = $this.closest('tr').find('td:nth-child(3)').text();
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
                        console.error('Ajax error:', error);
                        alert('Đã xảy ra lỗi khi cập nhật trạng thái');
                        $this.val(previousStatus);
                    }
                });
            } else {
                $this.val(previousStatus);
            }
        });
        
        // Handle per page selector
        $('#per-page-selector').on('change', function() {
            var newPerPage = $(this).val();
            window.location.href = '<?php echo admin_url('admin.php?page=petshop-users'); ?>&per_page=' + newPerPage;
        });
        
        // Handle search button
        $('#search-button').on('click', function() {
            var searchTerm = $('#user-search-input').val();
            window.location.href = '<?php echo admin_url('admin.php?page=petshop-users'); ?>&s=' + encodeURIComponent(searchTerm);
        });
        
        // Handle search on Enter key
        $('#user-search-input').on('keypress', function(e) {
            if (e.which === 13) {
                var searchTerm = $(this).val();
                window.location.href = '<?php echo admin_url('admin.php?page=petshop-users'); ?>&s=' + encodeURIComponent(searchTerm);
            }
        });
        
        // Handle select all checkbox
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

// Add this function at the bottom of the file
function petshop_update_user_status() {
    // Verify nonce
    check_ajax_referer('petshop_update_user_status', 'security');
    
    // Check admin permissions
    if (!petshop_is_logged_in() || $_SESSION['ps_user_role'] !== 'admin') {
        wp_send_json_error('Permission denied');
        return;
    }
    
    // Get and sanitize parameters
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
    
    // Validate parameters
    if (!$user_id || !in_array($status, ['active', 'banned'])) {
        wp_send_json_error('Invalid parameters');
        return;
    }
    
    global $wpdb;
    $table_users = $wpdb->prefix . 'petshop_users';
    
    // Update user status
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