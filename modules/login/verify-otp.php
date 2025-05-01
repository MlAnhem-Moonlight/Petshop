<?php
if (!defined('ABSPATH')) {
    die('Direct access not permitted.');
}

global $wpdb;
$table_otp = $wpdb->prefix . 'petshop_otp';
$email = isset($_GET['email']) ? sanitize_email($_GET['email']) : '';

if (!$email) {
    wp_redirect(admin_url('admin.php?page=petshop-management'));
    exit;
}

if (isset($_POST['ps_otp'])) {
    $otp = sanitize_text_field($_POST['ps_otp']);
    
    $valid_otp = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_otp 
        WHERE email = %s AND otp = %s AND is_used = 0 
        AND expires_at > NOW() 
        ORDER BY created_at DESC LIMIT 1",
        $email, $otp
    ));

    if ($valid_otp) {
        // Mark OTP as used
        $wpdb->update($table_otp, 
            ['is_used' => 1], 
            ['id' => $valid_otp->id]
        );

        // Redirect to reset password
        wp_redirect(admin_url('admin.php?page=petshop-management&action=reset-password&email=' . urlencode($email)));
        exit;
    } else {
        $error = 'Mã OTP không đúng hoặc đã hết hạn';
    }
}
?>

<style>
    /* Copy existing login styles */
    .ps-otp-timer {
        text-align: center;
        margin-top: 15px;
        color: #666;
    }
    .ps-otp-timer span {
        font-weight: bold;
        color: #e74a3b;
    }
</style>

<div class="ps-login-bg"></div>

<div class="ps-login-container">
    <h2>Xác thực OTP</h2>
    
    <?php if (isset($error)): ?>
        <div style="color:red; margin-bottom:10px;"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="text" name="ps_otp" placeholder="Nhập mã OTP" required pattern="[0-9]{6}" maxlength="6">
        <button type="submit" class="ps-btn">Xác nhận</button>
        <p class="ps-otp-timer">Mã OTP sẽ hết hạn sau <span id="timer">60</span> giây</p>
    </form>

    <div class="ps-login-footer">
        <p>Quay lại <a href="<?php echo admin_url('admin.php?page=petshop-management'); ?>">đăng nhập</a></p>
    </div>
</div>

<script>
let timeLeft = 60;
const timerElement = document.getElementById('timer');

const countdown = setInterval(() => {
    timeLeft--;
    timerElement.textContent = timeLeft;
    
    if (timeLeft <= 0) {
        clearInterval(countdown);
        document.querySelector('form').innerHTML = `
            <p style="color: #e74a3b; text-align: center;">Mã OTP đã hết hạn. Vui lòng yêu cầu mã mới.</p>
            <a href="<?php echo admin_url('admin.php?page=petshop-management&action=forgot-password'); ?>" 
               class="ps-btn" style="display: block; text-align: center; margin-top: 15px;">
                Yêu cầu mã OTP mới
            </a>
        `;
    }
}, 1000);
</script>