<?php
if (!defined('ABSPATH')) {
    die('Direct access not permitted.');
}

// Verify WordPress functions are available
if (!function_exists('wp_create_user')) {
    require_once(ABSPATH . 'wp-includes/pluggable.php');
}
?>

<style>
    .ps-register-bg {
        background: url('<?php echo plugin_dir_url(__FILE__) . "../../assets/bg-register.jpg"; ?>') no-repeat center center;
        background-size: cover;
        position: fixed;
        top: 0; left: 0;
        width: 100%;
        height: 100%;
        filter: blur(4px);
        z-index: 1;
    }

    .ps-register-container {
        max-width: 500px;
        margin: 50px auto;
        padding: 40px;
        background: rgba(255, 255, 255, 0.85);
        border-radius: 25px;
        box-shadow: 0 0 15px rgba(0,0,0,0.2);
        position: relative;
        z-index: 2;
    }

    .ps-register-container h2 {
        text-align: left;
        color: #333;
        margin-bottom: 20px;
    }

    .ps-register-container label {
        display: block;
        margin-bottom: 5px;
        color: #333;
        font-weight: bold;
    }

    .ps-register-container input[type="text"],
    .ps-register-container input[type="email"],
    .ps-register-container input[type="password"] {
        width: 100%;
        padding: 12px;
        margin-bottom: 15px;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 15px;
    }

    .ps-btn-register {
        background: linear-gradient(to right, #f6d365, #fda085);
        border: none;
        color: white;
        padding: 12px;
        border-radius: 8px;
        width: 100%;
        font-size: 16px;
        cursor: pointer;
        margin-bottom: 10px;
    }

    .ps-register-footer {
        text-align: center;
        margin-top: 10px;
    }

    .ps-register-footer a {
        color: #333;
        text-decoration: underline;
    }

    .ps-social-login {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 10px;
    }

    .ps-social-btn {
        padding: 10px 15px;
        border: none;
        border-radius: 5px;
        color: white;
        cursor: pointer;
        font-weight: bold;
    }

    .ps-facebook {
        background-color: #3b5998;
    }

    .ps-google {
        background-color: #dd4b39;
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
</style>

<div class="ps-register-bg"></div>

<div class="ps-register-container">
    <h2>Đăng ký</h2>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ps_register'])) {
        $username = sanitize_user($_POST['username']);
        $password = $_POST['password'];
        $email    = sanitize_email($_POST['email']);

        $errors = [];

        if (username_exists($username)) {
            $errors[] = 'Tên đăng nhập đã tồn tại.';
        }

        if (!is_email($email) || email_exists($email)) {
            $errors[] = 'Email không hợp lệ hoặc đã được sử dụng.';
        }

        if (empty($password)) {
            $errors[] = 'Vui lòng nhập mật khẩu.';
        }

        if (empty($errors)) {
            // Hash the password before creating user
            $user_id = wp_create_user($username, $password, $email);

            if (is_wp_error($user_id)) {
                // Get detailed error message
                $errors[] = $user_id->get_error_message();
            } else {
                // Set default role
                $user = new WP_User($user_id);
                $user->set_role('user');
                
                // Add custom user meta if needed
                if (!empty($_POST['phone'])) {
                    update_user_meta($user_id, 'phone', sanitize_text_field($_POST['phone']));
                }
                
                // Debug information
                error_log('User created successfully. User ID: ' . $user_id);
                
                echo '<div class="notice success">Đăng ký thành công! Bạn có thể <a href="' . admin_url('admin.php?page=petshop-management') . '">đăng nhập</a>.</div>';
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
        <span>Hoặc đăng nhập bằng</span>
        <div class="ps-social-login">
            <button class="ps-social-btn ps-facebook">Facebook</button>
            <button class="ps-social-btn ps-google">Google</button>
        </div>
        <p>Đã có tài khoản? <a href="<?php echo admin_url('admin.php?page=petshop-management'); ?>">Đăng nhập</a></p>
    </div>
</div>
