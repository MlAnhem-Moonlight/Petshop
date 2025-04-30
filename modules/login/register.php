<?php
if (!defined('ABSPATH')) {
    die('Direct access not permitted.');
}

global $wpdb;
$table_name = $wpdb->prefix . 'petshop_users';

// Kiểm tra bảng tồn tại
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
if (!$table_exists) {
    error_log('Table not found: ' . $table_name);
}

// Load hàm hash mật khẩu nếu chưa có
if (!function_exists('wp_hash_password')) {
    require_once(ABSPATH . 'wp-includes/pluggable.php');
}
?>

<style>
    .ps-register-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        background: url('<?php echo plugin_dir_url(__FILE__) . "../../assets/bg-register.jpg"; ?>') no-repeat center center;
        background-size: cover;
        
    }

    .ps-register-container {
        width: 400px;
        padding: 40px;
        background: rgba(255, 255, 255, 0.9);
        border-radius: 30px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.25);
        position: relative;
        z-index: 2;
    }

    .ps-register-container h2 {
        font-size: 30px;
        color: #cc0000;
        margin-bottom: 20px;
        text-align: left;
    }

    .ps-register-container label {
        font-weight: 600;
        margin-bottom: 5px;
        display: block;
        color: #333;
    }

    .ps-register-container input[type="text"],
    .ps-register-container input[type="email"],
    .ps-register-container input[type="password"] {
        width: 100%;
        padding: 12px;
        margin-bottom: 15px;
        border-radius: 8px;
        border: 1px solid #ccc;
        font-size: 14px;
    }

    .ps-btn-register {
        background-color: #f5c542;
        color: white;
        border: none;
        width: 100%;
        padding: 12px;
        border-radius: 8px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        margin-bottom: 10px;
    }

    .notice.success {
        background-color: #d4edda;
        color: #155724;
        padding: 10px;
        margin-bottom: 10px;
        border-radius: 5px;
    }

    .notice.error {
        background-color: #f8d7da;
        color: #721c24;
        padding: 10px;
        margin-bottom: 10px;
        border-radius: 5px;
    }

    .ps-register-footer {
        text-align: center;
        margin-top: 15px;
    }

    .ps-social-login {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 10px;
    }

    .ps-social-btn {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 15px;
        border-radius: 5px;
        color: white;
        font-weight: bold;
        border: none;
        cursor: pointer;
        font-size: 14px;
    }

    .ps-facebook {
        background-color: #3b5998;
    }

    .ps-google {
        background-color: #dd4b39;
    }
</style>

<div class="ps-register-wrapper">
    <div class="ps-register-container">
        <h2>Đăng ký</h2>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ps_register'])) {
            $username = sanitize_user($_POST['username']);
            $password = $_POST['password'];
            $email    = sanitize_email($_POST['email']);
            $phone    = sanitize_text_field($_POST['phone']);

            $errors = [];

            $username_exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table_name WHERE username = %s",
                $username
            ));

            $email_exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table_name WHERE email = %s",
                $email
            ));

            if ($username_exists > 0) {
                $errors[] = 'Tên đăng nhập đã tồn tại.';
            }

            if (!is_email($email) || $email_exists > 0) {
                $errors[] = 'Email không hợp lệ hoặc đã được sử dụng.';
            }

            if (empty($password)) {
                $errors[] = 'Vui lòng nhập mật khẩu.';
            }

            if (empty($errors)) {
                $hashed_password = wp_hash_password($password);

                try {
                    $result = $wpdb->insert(
                        $table_name,
                        array(
                            'username'   => $username,
                            'password'   => $hashed_password,
                            'email'      => $email,
                            'phone'      => $phone,
                            'created_at' => current_time('mysql', 1)
                        ),
                        array('%s', '%s', '%s', '%s', '%s')
                    );

                    if ($result === false) {
                        $errors[] = 'Không thể đăng ký: ' . $wpdb->last_error;
                    } else {
                        echo '<div class="notice success">Đăng ký thành công!</div>';
                    }
                } catch (Exception $e) {
                    $errors[] = 'Lỗi khi thêm user: ' . $e->getMessage();
                }
            }

            if (!empty($errors)) {
                foreach ($errors as $error) {
                    echo '<div class="notice error">' . esc_html($error) . '</div>';
                }
            }
        }
        ?>

        <form method="post">
            <label for="username">Họ và tên:</label>
            <input type="text" name="username" id="username" required>

            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required>

            <label for="phone">Số điện thoại:</label>
            <input type="text" name="phone" id="phone">

            <label for="password">Mật khẩu:</label>
            <input type="password" name="password" id="password" required>

            <button type="submit" name="ps_register" class="ps-btn-register">Đăng ký</button>
        </form>

        <div class="ps-register-footer">
            <p>Hoặc đăng nhập bằng</p>
            <div class="ps-social-login">
                <button class="ps-social-btn ps-facebook">Facebook</button>
                <button class="ps-social-btn ps-google">Google</button>
            </div>
            <p style="margin-top: 15px;">Đã có tài khoản?
                <a href="<?php echo admin_url('admin.php?page=petshop-management'); ?>">Đăng nhập</a>
            </p>
        </div>
    </div>
</div>
