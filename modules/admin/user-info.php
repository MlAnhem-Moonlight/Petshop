<?php
function petshop_user_info_page() {
    if (!petshop_is_logged_in()) {
        wp_redirect(admin_url('admin.php?page=petshop-management'));
        exit;
    }

    global $wpdb;
    $table_users = $wpdb->prefix . 'petshop_users';
    $user_id = $_SESSION['ps_user_id'];

    // Handle update form submission
    if (isset($_POST['edit_user_info']) && check_admin_referer('edit_user_info_action', 'edit_user_info_nonce')) {
        $new_email = sanitize_email($_POST['email']);
        $new_phone = sanitize_text_field($_POST['phone']);
        $avatar_url = null;

        // Validate email
        if (!is_email($new_email)) {
            echo '<div class="error notice"><p>Email không hợp lệ!</p></div>';
        } else {
            // Handle avatar upload
            if (!empty($_FILES['avatar']['name'])) {
                require_once(ABSPATH . 'wp-admin/includes/file.php');
                $uploaded = wp_handle_upload($_FILES['avatar'], ['test_form' => false]);
                if (!isset($uploaded['error'])) {
                    $avatar_url = $uploaded['url'];
                } else {
                    echo '<div class="error notice"><p>Lỗi upload ảnh: ' . esc_html($uploaded['error']) . '</p></div>';
                }
            }

            $update_data = [
                'email' => $new_email,
                'phone' => $new_phone
            ];
            if ($avatar_url) {
                $update_data['image_url'] = $avatar_url; // Use correct column name
            }

            $result = $wpdb->update(
                $table_users,
                $update_data,
                ['id' => $user_id],
                ['%s', '%s', '%s'], // Format for email, phone, and image_url
                ['%d']
            );

            if ($result !== false) {
                echo '<div class="updated notice"><p>Cập nhật thành công!</p></div>';
                // Reload page to reflect avatar change
                echo '<script>setTimeout(function(){ location.reload(); }, 800);</script>';
            } else {
                echo '<div class="error notice"><p>Lỗi cập nhật: ' . esc_html($wpdb->last_error) . '</p></div>';
            }
        }
    }

    $user = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_users WHERE id = %d",
        $user_id
    ));

    if (!$user) {
        echo '<div class="error notice"><p>Không tìm thấy thông tin người dùng!</p></div>';
        return;
    }

    // Use image_url column and add cache-busting query string
    $avatar = !empty($user->image_url) ? esc_url($user->image_url) . '?v=' . time() : includes_url('images/wpicons/user.png');
    ?>
    <div class="wrap user-info-wrap">
        <h1 class="user-info-title"><span class="dashicons dashicons-admin-users"></span> Thông tin tài khoản</h1>
        <div class="user-info-container">
            <div class="user-info-avatar" id="avatar-preview">
                <img src="<?php echo $avatar; ?>" alt="Avatar" style="width:80px;height:80px;border-radius:50%;object-fit:cover;">
            </div>
            <table class="form-table user-info-table" id="user-info-view">
                <tr>
                    <th>Tên đăng nhập:</th>
                    <td><?php echo esc_html($user->username); ?></td>
                </tr>
                <tr>
                    <th>Email:</th>
                    <td><?php echo esc_html($user->email); ?></td>
                </tr>
                <tr>
                    <th>Vai trò:</th>
                    <td>
                        <span class="role-badge role-<?php echo esc_attr($user->role); ?>">
                            <?php echo esc_html($user->role); ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Số điện thoại:</th>
                    <td><?php echo esc_html($user->phone); ?></td>
                </tr>
            </table>

            <!-- Edit form -->
            <form method="post" id="user-info-edit" enctype="multipart/form-data" style="display:none; width:100%;">
                <?php wp_nonce_field('edit_user_info_action', 'edit_user_info_nonce'); ?>
                <table class="form-table user-info-table">
                    <tr>
                        <th>Email:</th>
                        <td><input type="email" name="email" value="<?php echo esc_attr($user->email); ?>" required style="width:100%;"></td>
                    </tr>
                    <tr>
                        <th>Số điện thoại:</th>
                        <td><input type="text" name="phone" value="<?php echo esc_attr($user->phone); ?>" required style="width:100%;"></td>
                    </tr>
                    <tr>
                        <th>Ảnh đại diện:</th>
                        <td>
                            <input type="file" name="avatar" accept="image/*" onchange="previewAvatar(this)">
                            <div id="avatar-edit-preview" style="margin-top:10px;">
                                <img src="<?php echo $avatar; ?>" alt="Avatar Preview" style="width:60px;height:60px;border-radius:50%;object-fit:cover;">
                            </div>
                        </td>
                    </tr>
                </table>
                <div class="user-actions">
                    <button type="submit" name="edit_user_info" class="button button-primary save-btn">
                        <span class="dashicons dashicons-yes"></span> Lưu
                    </button>
                    <button type="button" class="button cancel-btn" onclick="toggleEdit(false)">
                        <span class="dashicons dashicons-no-alt"></span> Hủy
                    </button>
                </div>
            </form>

            <div class="user-actions" id="user-actions-btns">
                <button type="button" class="button edit-btn" onclick="toggleEdit(true)">
                    <span class="dashicons dashicons-edit"></span> Chỉnh sửa
                </button>
                <a href="<?php echo admin_url('admin.php?ps_action=logout'); ?>" 
                   class="button button-primary logout-btn">
                    <span class="dashicons dashicons-migrate"></span> Đăng xuất
                </a>
            </div>
        </div>
    </div>
    <style>
    .user-info-wrap {
        max-width: 520px;
        margin: 40px auto 0 auto;
        background: linear-gradient(120deg, #f9fff5 80%, #e8f5e9 100%);
        border-radius: 14px;
        box-shadow: 0 4px 24px rgba(76,175,80,0.10);
        padding: 32px 28px 28px 28px;
    }
    .user-info-title {
        color: #388e3c;
        font-size: 26px;
        font-weight: 700;
        margin-bottom: 28px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .user-info-container {
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    .user-info-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: #c8e6c9;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 18px;
        font-size: 48px;
        color: #388e3c;
        box-shadow: 0 2px 8px rgba(76,175,80,0.10);
    }
    .user-info-avatar img {
        box-shadow: 0 2px 8px rgba(76,175,80,0.10);
    }
    .user-info-table {
        width: 100%;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 1px 6px rgba(76,175,80,0.06);
        margin-bottom: 24px;
        overflow: hidden;
    }
    .user-info-table th {
        text-align: left;
        padding: 12px 18px;
        background: #f1f8e9;
        color: #388e3c;
        font-weight: 600;
        width: 140px;
        font-size: 15px;
        border-bottom: 1px solid #e0e0e0;
    }
    .user-info-table td {
        padding: 12px 18px;
        font-size: 15px;
        border-bottom: 1px solid #e0e0e0;
    }
    .user-info-table tr:last-child th,
    .user-info-table tr:last-child td {
        border-bottom: none;
    }
    .role-badge {
        display: inline-block;
        padding: 4px 14px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 500;
        background: #e8f5e9;
        color: #388e3c;
        text-transform: capitalize;
    }
    .role-badge.role-admin {
        background: #ffe0b2;
        color: #e65100;
    }
    .role-badge.role-user {
        background: #e3f2fd;
        color: #1976d2;
    }
    .user-actions {
        margin-top: 10px;
        text-align: center;
        display: flex;
        gap: 10px;
        justify-content: center;
    }
    .logout-btn {
        background: linear-gradient(90deg, #8bc34a 60%, #4caf50 100%);
        color: white;
        border: none;
        border-radius: 6px;
        padding: 10px 28px;
        cursor: pointer;
        font-weight: bold;
        font-size: 15px;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        box-shadow: 0 2px 8px rgba(76,175,80,0.07);
        transition: background 0.2s;
    }
    .logout-btn:hover {
        background: linear-gradient(90deg, #689f38 60%, #388e3c 100%);
    }
    .edit-btn, .save-btn, .cancel-btn {
        background: #fffde7;
        color: #fbc02d;
        border: 1.5px solid #ffe082;
        border-radius: 6px;
        padding: 10px 22px;
        font-weight: bold;
        font-size: 15px;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        box-shadow: 0 2px 8px rgba(251,192,45,0.07);
        transition: background 0.2s, color 0.2s;
    }
    .edit-btn:hover, .save-btn:hover, .cancel-btn:hover {
        background: #ffe082;
        color: #e65100;
    }
    .save-btn {
        background: #e8f5e9;
        color: #388e3c;
        border: 1.5px solid #aed581;
    }
    .save-btn:hover {
        background: #c8e6c9;
        color: #1b5e20;
    }
    .cancel-btn {
        background: #ffebee;
        color: #d32f2f;
        border: 1.5px solid #ffcdd2;
    }
    .cancel-btn:hover {
        background: #ffcdd2;
        color: #b71c1c;
    }
    </style>
    <link rel="stylesheet" href="<?php echo includes_url('css/dashicons.min.css'); ?>">
    <script>
    function toggleEdit(editMode) {
        document.getElementById('user-info-view').style.display = editMode ? 'none' : '';
        document.getElementById('user-info-edit').style.display = editMode ? '' : 'none';
        document.getElementById('user-actions-btns').style.display = editMode ? 'none' : 'flex';
    }
    function previewAvatar(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.querySelector('#avatar-edit-preview img').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    </script>
    <?php
}