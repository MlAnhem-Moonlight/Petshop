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
.ps-otp-bg {
    position: fixed;
    inset: 0;
    background: linear-gradient(120deg, #e0f7fa 0%, #fffde4 100%);
    z-index: 0;
}
.ps-otp-container {
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
.ps-otp-logo {
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
.ps-otp-container h2 {
    color: #009688;
    font-weight: 700;
    margin-bottom: 18px;
    letter-spacing: 1px;
}
.ps-otp-container form {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 18px;
}
.ps-otp-container input[type="text"] {
    padding: 12px 14px;
    border: 1.5px solid #b2dfdb;
    border-radius: 7px;
    font-size: 16px;
    outline: none;
    transition: border 0.2s;
    text-align: center;
    letter-spacing: 4px;
    font-weight: 600;
}
.ps-otp-container input[type="text"]:focus {
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
.ps-otp-timer {
    text-align: center;
    margin-top: 10px;
    color: #666;
    font-size: 15px;
}
.ps-otp-timer span {
    font-weight: bold;
    color: #e74a3b;
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
</style>

<div class="ps-otp-bg"></div>

<div class="ps-otp-container">
    <div class="ps-otp-logo">
        <span class="dashicons dashicons-shield"></span>
    </div>
    <h2>Xác thực OTP</h2>
    
    <?php if (isset($error)): ?>
        <div style="color:#e53935; background:#ffebee; border-radius:5px; padding:10px 12px; margin-bottom:14px;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="post" autocomplete="off">
        <input type="text" name="ps_otp" placeholder="Nhập mã OTP" required pattern="[0-9]{6}" maxlength="6" autofocus>
        <button type="submit" class="ps-btn">Xác nhận</button>
        <p class="ps-otp-timer">Mã OTP sẽ hết hạn sau <span id="timer">60</span> giây</p>
    </form>

    <div class="ps-login-footer">
        <p>Quay lại <a href="<?php echo admin_url('admin.php?page=petshop-management'); ?>">đăng nhập</a></p>
    </div>
</div>
<link rel="stylesheet" href="<?php echo includes_url('css/dashicons.min.css'); ?>">

<script>
let timeLeft = 60;
const timerElement = document.getElementById('timer');
const form = document.querySelector('.ps-otp-container form');

const countdown = setInterval(() => {
    timeLeft--;
    timerElement.textContent = timeLeft;
    if (timeLeft <= 0) {
        clearInterval(countdown);
        form.innerHTML = `
            <p style="color: #e74a3b; text-align: center;">Mã OTP đã hết hạn. Vui lòng yêu cầu mã mới.</p>
            <a href="<?php echo admin_url('admin.php?page=petshop-management&action=forgot-password'); ?>" 
               class="ps-btn" style="display: block; text-align: center; margin-top: 15px;">
                Yêu cầu mã OTP mới
            </a>
        `;
    }
}, 1000);
</script>