<style>
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

    .ps-login-bg {
        background: url('<?php echo plugin_dir_url(__FILE__) . "../../assets/bg-login.jpg"; ?>') no-repeat center center;
        background-size: cover;
        position: fixed;
        top: 0; left: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
        filter: blur(5px);
    }

    .ps-login-container h2 {
        margin-bottom: 20px;
    }

    .ps-login-container input[type="text"],
    .ps-login-container input[type="password"] {
        width: 100%;
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid #ccc;
        border-radius: 5px;
    }

    .ps-btn {
        background: linear-gradient(to right, #f6d365, #fda085);
        border: none;
        color: white;
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
        width: 100%;
        font-size: 16px;
    }

    .ps-login-footer {
        text-align: center;
        margin-top: 20px;
    }

    .ps-social-login {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 10px;
    }

    .ps-social-btn {
        border: none;
        color: white;
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
    }

    .ps-facebook {
        background-color: #3b5998;
    }

    .ps-google {
        background-color: #dd4b39;
    }

    .ps-login-footer a {
        color: #333;
        text-decoration: underline;
    }
</style>

<div class="ps-login-bg"></div>

<div class="ps-login-container">
    <h2>Đăng nhập</h2>

    <?php
    if (!empty($_POST['ps_username']) && !empty($_POST['ps_password'])) {
        $username = sanitize_text_field($_POST['ps_username']);
        $password = sanitize_text_field($_POST['ps_password']);

        if ($username === 'admin' && $password === 'admin') {
            $_SESSION['ps_logged_in'] = true;
            wp_redirect(admin_url('admin.php?page=ps-products'));
            exit;
        } else {
            echo '<div style="color:red; margin-bottom:10px;">Sai tên đăng nhập hoặc mật khẩu</div>';
        }
    }
    ?>

    <form method="post">
        <input type="text" name="ps_username" placeholder="Email" required>
        <input type="password" name="ps_password" placeholder="Mật khẩu" required>
        <button type="submit" class="ps-btn">Đăng nhập</button>
    </form>

    <div class="ps-login-footer">
        <a href="#">Quên mật khẩu</a>
        <p>Hoặc đăng nhập bằng</p>
        <div class="ps-social-login">
            <button class="ps-social-btn ps-facebook">Facebook</button>
            <button class="ps-social-btn ps-google">Google</button>
        </div>
        <p>Bạn chưa có tài khoản, vui lòng đăng ký <a href="<?php echo admin_url('admin.php?page=petshop-management&action=register'); ?>">tại đây</a></p>
    </div>
</div>
