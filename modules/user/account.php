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

    $profile_img = !empty($user->image_url) ? esc_url($user->image_url) : 'https://ui-avatars.com/api/?name=' . urlencode($user->username) . '&background=0D8ABC&color=fff';
    ?>
    <style>
        .petshop-profile-card {
            max-width: 420px;
            margin: 40px auto;
            background: linear-gradient(135deg, #f8fafc 60%, #dbeafe 100%);
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.10);
            padding: 0 0 32px 0;
            overflow: hidden;
        }
        .petshop-profile-header {
            background: #0d8abc;
            padding: 32px 0 24px 0;
            color: #fff;
            text-align: center;
        }
        .petshop-profile-avatar {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid #fff;
            margin-bottom: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            background: #f9f9f9;
        }
        .petshop-profile-username {
            font-size: 1.5em;
            font-weight: 700;
            margin-bottom: 2px;
        }
        .petshop-profile-role {
            font-size: 1em;
            font-weight: 400;
            opacity: 0.85;
            margin-bottom: 0;
        }
        .petshop-profile-details {
            padding: 24px 32px 0 32px;
        }
        .petshop-profile-details-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .petshop-profile-details-row:last-child {
            border-bottom: none;
        }
        .petshop-profile-label {
            color: #888;
            font-weight: 500;
        }
        .petshop-profile-value {
            color: #222;
            font-weight: 400;
        }
        .petshop-profile-actions {
            margin-top: 28px;
            text-align: center;
        }
        .petshop-profile-actions .button {
            min-width: 120px;
            background: #0d8abc;
            border: none;
            color: #fff;
            font-weight: 600;
            border-radius: 6px;
            padding: 10px 28px;
            transition: background 0.2s;
            margin: 0 8px 8px 0;
        }
        .petshop-profile-actions .button:hover {
            background: #2563eb;
        }
        /* Modal styles */
        .ps-modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0; top: 0; width: 100vw; height: 100vh;
            background: rgba(0,0,0,0.35);
        }
        .ps-modal-content {
            background: #fff;
            border-radius: 12px;
            max-width: 400px;
            margin: 60px auto 0 auto;
            padding: 24px 24px 16px 24px;
            position: relative;
            box-shadow: 0 8px 32px rgba(0,0,0,0.18);
        }
        .ps-modal-close {
            position: absolute;
            right: 18px;
            top: 12px;
            font-size: 1.5em;
            color: #888;
            cursor: pointer;
        }
        .ps-modal-iframe {
            width: 100%;
            height: 340px;
            border: none;
            border-radius: 8px;
            background: #f8fafc;
        }
    </style>
    <div class="petshop-profile-card">
        <div class="petshop-profile-header">
            <img src="<?php echo $profile_img; ?>" alt="Profile Image" class="petshop-profile-avatar" />
            <div class="petshop-profile-username"><?php echo esc_html($user->username); ?></div>
            <div class="petshop-profile-role"><?php echo esc_html(ucfirst($user->role)); ?></div>
        </div>
        <div class="petshop-profile-details">
            <div class="petshop-profile-details-row">
                <span class="petshop-profile-label">Email:</span>
                <span class="petshop-profile-value"><?php echo esc_html($user->email); ?></span>
            </div>
            <div class="petshop-profile-details-row">
                <span class="petshop-profile-label">Phone:</span>
                <span class="petshop-profile-value"><?php echo esc_html($user->phone); ?></span>
            </div>
        </div>
        <div class="petshop-profile-actions">
            <a href="<?php echo admin_url('admin.php?ps_action=logout'); ?>" 
               class="button button-primary">
                Logout
            </a>
            <button type="button" class="button" onclick="document.getElementById('editProfileModal').style.display='block'">
                Đổi thông tin cá nhân
            </button>
        </div>
    </div>

    <!-- Edit Profile Modal with iframe -->
    <div id="editProfileModal" class="ps-modal">
        <div class="ps-modal-content">
            <span class="ps-modal-close" onclick="document.getElementById('editProfileModal').style.display='none'">&times;</span>
            <iframe class="ps-modal-iframe" src="<?php echo plugins_url('Petshop-main/modules/user/account_management.php'); ?>" title="Cập nhật tài khoản"></iframe>
        </div>
    </div>
    <script>
    // Open modal
    // (already handled by button onclick)
    // Close modal when clicking outside content
    document.addEventListener('click', function(e) {
        var modal = document.getElementById('editProfileModal');
        if (modal && e.target === modal) {
            modal.style.display = 'none';
        }
    });
    </script>
    <?php
}
petshop_user_info_page();