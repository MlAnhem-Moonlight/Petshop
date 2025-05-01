<?php
if (!defined('ABSPATH')) {
    die('Direct access not permitted.');
}

global $wpdb;
$table_users = $wpdb->prefix . 'petshop_users';
$email = isset($_GET['email']) ? sanitize_email($_GET['email']) : '';

if (!$email) {
    wp_redirect(admin_url('admin.php?page=petshop-management'));
    exit;
}

if (isset($_POST['ps_password']) && isset($_POST['ps_confirm_password'])) {
    $password = $_POST['ps_password'];
    $confirm = $_POST['ps_confirm_password'];
    
    if ($password === $confirm) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $wpdb->update(
            $table_users,
            ['password' => $hashed_password],
            ['email' => $email, 'role' => 'user']
        );

        wp_redirect(admin_url('admin.php?page=petshop-management&password_reset=success'));
        exit;
    } else {
        $error = 'Mật khẩu không khớp';
    }
}
?>

<style>
    /* Copy existing login styles */
</style>

<div class="ps-login-bg"></div>

<div class="ps-login-container">
    <h2>Đặt lại mật khẩu</h2>
    
    <?php if (isset($error)): ?>
        <div style="color:red; margin-bottom:10px;"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="password" name="ps_password" placeholder="Mật khẩu mới" required>
        <input type="password" name="ps_confirm_password" placeholder="Xác nhận mật khẩu" required>
        <button type="submit" class="ps-btn">Đặt lại mật khẩu</button>
    </form>

    <div class="ps-login-footer">
        <p>Quay lại <a href="<?php echo admin_url('admin.php?page=petshop-management'); ?>">đăng nhập</a></p>
    </div>
</div>