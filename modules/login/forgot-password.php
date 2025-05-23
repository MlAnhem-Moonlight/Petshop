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
.ps-forgot-bg {
    position: fixed;
    inset: 0;
    background: linear-gradient(120deg, #e0f7fa 0%, #fffde4 100%);
    z-index: 0;
}
.ps-forgot-container {
    width: 400px;
    margin: 70px auto;
    padding: 36px 32px 28px 32px;
    background: #fff;
    border-radius: 22px;
    box-shadow: 0 8px 32px rgba(44, 62, 80, 0.13);
    position: relative;
    z-index: 2;
    display: flex;
    flex-direction: column;
    align-items: center;
    animation: fadeInDown 0.7s;
}
@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-40px);}
    to { opacity: 1; transform: translateY(0);}
}
.ps-forgot-container h2 {
    color: #009688;
    font-weight: 700;
    margin-bottom: 18px;
    letter-spacing: 1px;
}
.ps-forgot-container form {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 18px;
}
.ps-forgot-container input[type="email"] {
    padding: 12px 14px;
    border: 1.5px solid #b2dfdb;
    border-radius: 7px;
    font-size: 16px;
    outline: none;
    transition: border 0.2s;
}
.ps-forgot-container input[type="email"]:focus {
    border-color: #009688;
}
.ps-btn {
    background: linear-gradient(90deg, #26c6da 0%, #009688 100%);
    color: #fff;
    border: none;
    padding: 12px 0;
    border-radius: 7px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
    box-shadow: 0 2px 8px #b2dfdb55;
}
.ps-btn:hover {
    background: linear-gradient(90deg, #009688 0%, #26c6da 100%);
}
.ps-login-footer {
    margin-top: 22px;
    text-align: center;
    color: #666;
    font-size: 15px;
}
.ps-login-footer a {
    color: #009688;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.2s;
}
.ps-login-footer a:hover {
    color: #00796b;
    text-decoration: underline;
}
.ps-forgot-logo {
    width: 60px;
    height: 60px;
    margin-bottom: 10px;
    border-radius: 50%;
    background: #e0f2f1;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    color: #009688;
    box-shadow: 0 2px 8px #b2dfdb55;
}
</style>

<div class="ps-forgot-bg"></div>

<div class="ps-forgot-container">
    <div class="ps-forgot-logo">
        <span class="dashicons dashicons-unlock"></span>
    </div>
    <h2>Quên mật khẩu</h2>
    
    <?php if (isset($error)): ?>
        <div style="color:#e53935; background:#ffebee; border-radius:5px; padding:10px 12px; margin-bottom:14px;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="post" autocomplete="off">
        <input type="email" name="ps_email" placeholder="Nhập email của bạn" required>
        <button type="submit" class="ps-btn">Gửi mã OTP</button>
    </form>

    <div class="ps-login-footer">
        <p>Quay lại <a href="<?php echo admin_url('admin.php?page=petshop-management'); ?>">đăng nhập</a></p>
    </div>
</div>
<link rel="stylesheet" href="<?php echo includes_url('css/dashicons.min.css'); ?>">