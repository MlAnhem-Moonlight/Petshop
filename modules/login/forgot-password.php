<?php
if (!defined('ABSPATH')) {
    die('Direct access not permitted.');
}

global $wpdb;
$table_users = $wpdb->prefix . 'petshop_users';
$table_otp = $wpdb->prefix . 'petshop_otp';

if (isset($_POST['ps_email'])) {
    $email = sanitize_email($_POST['ps_email']);
    
    // Check if email exists and is a user account
    $user = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_users WHERE email = %s AND role = 'user'", 
        $email
    ));

    if ($user) {
        // Generate OTP
        $otp = sprintf("%06d", rand(0, 999999));
        $expires_at = date('Y-m-d H:i:s', strtotime('+1 minute')); 
        
        // Save OTP
        $wpdb->insert($table_otp, [
            'email' => $email,
            'otp' => $otp,
            'expires_at' => $expires_at
        ]);

        // Send OTP email
        $to = $email;
        $subject = 'Reset Password OTP';
        $message = "Your OTP is: $otp\nValid for 1 minute."; // Updated message
        $headers = ['Content-Type: text/plain; charset=UTF-8'];
        
        wp_mail($to, $subject, $message, $headers);

        // Redirect to OTP verification
        wp_redirect(admin_url('admin.php?page=petshop-management&action=verify-otp&email=' . urlencode($email)));
        exit;
    } else {
        $error = 'Email không tồn tại hoặc không phải tài khoản user';
    }
}
?>

<style>
    /* Copy existing login styles and modify as needed */
    .ps-login-container {
        width: 450px;
        margin: 50px auto;
        padding: 30px;
        background: rgba(255, 255, 255, 0.85);
        border-radius: 20px;
        box-shadow: 0 0 10px rgba(0,0,0,0.2);
        position: relative;
        z-index: 2;
    }
    /* ...existing styles... */
</style>

<div class="ps-login-bg"></div>

<div class="ps-login-container">
    <h2>Quên mật khẩu</h2>
    
    <?php if (isset($error)): ?>
        <div style="color:red; margin-bottom:10px;"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="email" name="ps_email" placeholder="Nhập email của bạn" required>
        <button type="submit" class="ps-btn">Gửi mã OTP</button>
    </form>

    <div class="ps-login-footer">
        <p>Quay lại <a href="<?php echo admin_url('admin.php?page=petshop-management'); ?>">đăng nhập</a></p>
    </div>
</div>