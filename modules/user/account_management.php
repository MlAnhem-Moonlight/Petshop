<?php
require_once('../../../../../wp-load.php'); // Adjust path if needed

if (!function_exists('is_user_logged_in')) {
    function is_user_logged_in() {
        return isset($_SESSION['ps_logged_in']) && $_SESSION['ps_logged_in'];
    }
}

global $wpdb;
$table_users = $wpdb->prefix . 'petshop_users';
$user_id = isset($_SESSION['ps_user_id']) ? intval($_SESSION['ps_user_id']) : 0;

if (!$user_id) {
    echo '<div style="padding:24px;">Bạn chưa đăng nhập.</div>';
    exit;
}

$user = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_users WHERE id = %d", $user_id));

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $new_email = sanitize_email($_POST['email']);
    $new_phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : $user->phone;
    $image_url = $user->image_url;

    // Handle image upload
    if (!empty($_FILES['profile_image']['name'])) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        $uploaded = wp_handle_upload($_FILES['profile_image'], ['test_form' => false]);
        if (!isset($uploaded['error'])) {
            $image_url = esc_url_raw($uploaded['url']);
        } else {
            $message = '<div style="color:red;">Lỗi tải ảnh: ' . esc_html($uploaded['error']) . '</div>';
        }
    }

    // Update user info
    $result = $wpdb->update(
        $table_users,
        [
            'email' => $new_email,
            'phone' => $new_phone,
            'image_url' => $image_url
        ],
        ['id' => $user_id]
    );

    if ($result !== false) {
        $message = '<div style="color:green;">Cập nhật thành công!</div>';
        // Refresh user data
        $user = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_users WHERE id = %d", $user_id));
    } else {
        $message = '<div style="color:red;">Không có thay đổi hoặc có lỗi xảy ra.</div>';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cập nhật tài khoản</title>
    <style>
        body { font-family: Arial,sans-serif; background: #f8fafc; margin:0; }
        .ps-account-form { padding: 16px 8px 8px 8px; }
        .ps-form-group { margin-bottom: 16px; }
        .ps-form-group label { display:block; margin-bottom:6px; font-weight:500; }
        .ps-form-group input[type="email"], .ps-form-group input[type="file"], .ps-form-group input[type="text"] {
            width: 100%; padding: 8px; border-radius: 5px; border: 1px solid #d1d5db;
        }
        .ps-profile-preview { text-align:center; margin-bottom:16px; }
        .ps-profile-preview img { width: 80px; height: 80px; border-radius: 50%; object-fit:cover; border:2px solid #e5e7eb; }
        .ps-account-form button { background: #0d8abc; color: #fff; border: none; border-radius: 5px; padding: 8px 24px; font-weight:600; cursor:pointer; }
        .ps-account-form button:hover { background: #2563eb; }
    </style>
</head>
<body>
    <form class="ps-account-form" method="post" enctype="multipart/form-data">
        <?php echo $message; ?>
        <div class="ps-profile-preview">
            <img src="<?php echo !empty($user->image_url) ? esc_url($user->image_url) : 'https://ui-avatars.com/api/?name=' . urlencode($user->username) . '&background=0D8ABC&color=fff'; ?>" alt="Ảnh đại diện hiện tại">
        </div>
        <div class="ps-form-group">
            <label>Email mới</label>
            <input type="email" name="email" value="<?php echo esc_attr($user->email); ?>" required>
        </div>
        <div class="ps-form-group">
            <label>Số điện thoại mới</label>
            <input type="text" name="phone" value="<?php echo esc_attr($user->phone); ?>" required>
        </div>
        <div class="ps-form-group">
            <label>Ảnh đại diện mới</label>
            <input type="file" name="profile_image" accept="image/*">
        </div>
        <button type="submit">Lưu thay đổi</button>
    </form>
</body>
</html>