<?php
/*
Plugin Name: Pet Shop Management
Description: Complete pet shop management system with products, categories, cart functionality and admin dashboard.
Version: 1.2
Author: Your Name
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
ob_start();

define('PETSHOP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PETSHOP_PLUGIN_URL', plugin_dir_url(__FILE__));

// Start session for user tracking
function petshop_start_session() {
    if (!session_id()) {
        session_start([
            'cookie_lifetime' => 86400,
            'read_and_close' => false,
        ]);
    }
}
add_action('init', 'petshop_start_session', 1);

// Add this near the top of your plugin file, after the session start
add_action('admin_init', function() {
    $current_page = isset($_GET['page']) ? $_GET['page'] : '';
    
    // Skip auth check for login page
    if ($current_page === 'petshop-management') {
        return;
    }
    
    // Check auth for all other petshop pages
    if (strpos($current_page, 'ps-') === 0) {
        petshop_check_auth();
    }
});

// Handle logout
function petshop_handle_logout() {
    if (isset($_GET['ps_action']) && $_GET['ps_action'] === 'logout') {
        session_destroy();
        wp_redirect(admin_url('admin.php?page=petshop-management'));
        exit;
    }
}
add_action('init', 'petshop_handle_logout');

// Check if user is logged in
function petshop_is_logged_in() {
    return isset($_SESSION['ps_logged_in']) && $_SESSION['ps_logged_in'] === true;
}

// Check authentication and redirect if not logged in
function petshop_check_auth() {
    if (!petshop_is_logged_in()) {
        wp_redirect(admin_url('admin.php?page=petshop-management'));
        exit;
    }
}

// Create database tables when plugin is activated
register_activation_hook(__FILE__, 'petshop_create_tables');
function petshop_create_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    
    $table_categories = $wpdb->prefix . 'petshop_categories';
    $table_products = $wpdb->prefix . 'petshop_products';
    $table_carts = $wpdb->prefix . 'petshop_carts';
    $table_cart_items = $wpdb->prefix . 'petshop_cart_items';
    $table_orders = $wpdb->prefix . 'petshop_orders';
    $table_order_items = $wpdb->prefix . 'petshop_order_items';
    $table_users = $wpdb->prefix . 'petshop_users';
    $user_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_users'") == $table_users;

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    // Check if tables exist before creating them
    $categories_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_categories'") == $table_categories;
    $products_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_products'") == $table_products;
    $carts_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_carts'") == $table_carts;
    $cart_items_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_cart_items'") == $table_cart_items;
    $orders_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_orders'") == $table_orders;
    $order_items_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_order_items'") == $table_order_items;

    // Create categories table if not exists
    if (!$categories_exists) {
        dbDelta("CREATE TABLE $table_categories (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            PRIMARY KEY (id)
        ) $charset_collate;");
    }
    if (!$user_exists) {
        dbDelta("CREATE TABLE $table_users (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            username VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(100),
            phone VARCHAR(20) NULL,  // Thêm trường số điện thoại, có thể có hoặc không
            role VARCHAR(50) NOT NULL DEFAULT 'user',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;");
    }
    
    $admin_username = 'admin';
    $admin_email = 'admin@gmail.com';
    $admin_password = password_hash('123456', PASSWORD_DEFAULT); // Mã hóa mật khẩu
    $admin_role = 'admin';
    $admin_phone = NULL; // Nếu không có số điện thoại, đặt giá trị NULL
    
    $wpdb->insert(
        $table_users,
        array(
            'username' => $admin_username,
            'password' => $admin_password,
            'email'    => $admin_email,
            'phone'    => $admin_phone,  // Thêm số điện thoại nếu có
            'role'     => $admin_role,
            'created_at' => current_time('mysql')
        ),
        array('%s', '%s', '%s', '%s', '%s', '%s')
    );
    
        

    // Create products table if not exists
    if (!$products_exists) {
        dbDelta("CREATE TABLE $table_products (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            price DECIMAL(10,2) NOT NULL,
            stock_quantity INT(11) DEFAULT 0,
            category_id BIGINT(20) UNSIGNED,
            image_url TEXT,
            PRIMARY KEY (id),
            FOREIGN KEY (category_id) REFERENCES $table_categories(id) ON DELETE SET NULL
        ) $charset_collate;");
    }

    // Create carts table if not exists
    if (!$carts_exists) {
        dbDelta("CREATE TABLE $table_carts (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;");
    }

    // Create cart items table if not exists
    if (!$cart_items_exists) {
        dbDelta("CREATE TABLE $table_cart_items (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            cart_id BIGINT(20) UNSIGNED NOT NULL,
            product_id BIGINT(20) UNSIGNED NOT NULL,
            quantity INT(11) NOT NULL,
            PRIMARY KEY (id),
            FOREIGN KEY (cart_id) REFERENCES $table_carts(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES $table_products(id) ON DELETE CASCADE
        ) $charset_collate;");
    }

    // Create orders table if not exists
    if (!$orders_exists) {
        dbDelta("CREATE TABLE $table_orders (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            total_amount DECIMAL(10,2) NOT NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'pending',
            created_at DATETIME NOT NULL,
            customer_name VARCHAR(100),
            customer_email VARCHAR(100),
            customer_phone VARCHAR(50),
            customer_address TEXT,
            PRIMARY KEY (id)
        ) $charset_collate;");
    }

    // Create order items table if not exists
    if (!$order_items_exists) {
        dbDelta("CREATE TABLE $table_order_items (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            order_id BIGINT(20) UNSIGNED NOT NULL,
            product_id BIGINT(20) UNSIGNED NOT NULL,
            quantity INT(11) NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            PRIMARY KEY (id),
            FOREIGN KEY (order_id) REFERENCES $table_orders(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES $table_products(id) ON DELETE CASCADE
        ) $charset_collate;");
    }
}

// Enqueue stylesheets and scripts
function petshop_enqueue_scripts() {
    // Admin styles
    if (is_admin()) {
        wp_enqueue_style('petshop-admin-style', plugins_url('assets/style.css', __FILE__));
    }
    
    // Frontend styles and scripts
    if (!is_admin()) {
        // Main stylesheet
        wp_enqueue_style('petshop-style', get_stylesheet_uri());
        
        // Font Awesome for icons
        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');
        
        // jQuery (included with WordPress)
        wp_enqueue_script('jquery');
        
        // Custom scripts
        wp_enqueue_script('petshop-scripts', plugins_url('assets/js/scripts.js', __FILE__), array('jquery'), '1.0', true);
        
        // Localize script for AJAX
        wp_localize_script('petshop-scripts', 'petshop_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php')
        ));
    }
}
add_action('wp_enqueue_scripts', 'petshop_enqueue_scripts');
add_action('admin_enqueue_scripts', 'petshop_enqueue_scripts');

function petshop_enqueue_admin_assets($hook) {
    if (strpos($hook, 'ps-') !== false || $hook === 'toplevel_page_petshop-management') {
        // Enqueue Chart.js
        wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js@4.0.1/dist/chart.umd.min.js', [], '4.0.1', true);
        
        // Add custom dashboard JS
        wp_enqueue_script('petshop-dashboard-js', PETSHOP_PLUGIN_URL . 'assets/js/dashboard.js', ['chart-js'], '1.0', true);
        
        // Add custom CSS
        wp_enqueue_style('petshop-admin-dashboard', PETSHOP_PLUGIN_URL . 'modules/admin/css/admin-dashboard.css', [], '1.0');
        
        // Get data for charts
        global $wpdb;
        $orders_by_location = [];
        $sales_by_location = [];
        $monthly_data = [];
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        
        // Localize script
        wp_localize_script('petshop-dashboard-js', 'petshopData', [
            'ordersByLocation' => $orders_by_location,
            'salesByLocation' => $sales_by_location,
            'monthlyData' => $monthly_data,
            'months' => $months
        ]);
    }
}
add_action('admin_enqueue_scripts', 'petshop_enqueue_admin_assets');

// Add to cart AJAX handler
function petshop_add_to_cart() {
    petshop_check_auth();
    
    if (!isset($_POST['product_id'])) {
        wp_send_json_error('No product specified');
        die();
    }
    
    $product_id = intval($_POST['product_id']);
    
    global $wpdb;
    $table_products = $wpdb->prefix . 'petshop_products';
    $table_carts = $wpdb->prefix . 'petshop_carts';
    $table_cart_items = $wpdb->prefix . 'petshop_cart_items';
    
    // Check if product exists
    $product = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_products WHERE id = %d",
        $product_id
    ));
    
    if (!$product) {
        wp_send_json_error('Product not found');
        die();
    }
    
    // Get user ID (0 for guests)
    $user_id = get_current_user_id();
    
    // Get or create cart
    $cart = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_carts WHERE user_id = %d",
        $user_id
    ));
    
    if (!$cart) {
        // Create new cart
        $wpdb->insert(
            $table_carts,
            array(
                'user_id' => $user_id,
                'created_at' => current_time('mysql')
            )
        );
        $cart_id = $wpdb->insert_id;
    } else {
        $cart_id = $cart->id;
    }
    
    // Check if item already in cart
    $cart_item = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_cart_items WHERE cart_id = %d AND product_id = %d",
        $cart_id, $product_id
    ));
    
    if ($cart_item) {
        // Update quantity
        $wpdb->update(
            $table_cart_items,
            array('quantity' => $cart_item->quantity + 1),
            array('id' => $cart_item->id)
        );
    } else {
        // Add new item
        $wpdb->insert(
            $table_cart_items,
            array(
                'cart_id' => $cart_id,
                'product_id' => $product_id,
                'quantity' => 1
            )
        );
    }
    
    wp_send_json_success('Product added to cart');
    die();
}
add_action('wp_ajax_add_to_cart', 'petshop_add_to_cart');
add_action('wp_ajax_nopriv_add_to_cart', 'petshop_add_to_cart');

// Get cart count function
function petshop_get_cart_count() {
    global $wpdb;
    $table_carts = $wpdb->prefix . 'petshop_carts';
    $table_cart_items = $wpdb->prefix . 'petshop_cart_items';
    
    $user_id = get_current_user_id();
    
    $cart = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_carts WHERE user_id = %d",
        $user_id
    ));
    
    if (!$cart) {
        return 0;
    }
    
    $count = $wpdb->get_var($wpdb->prepare(
        "SELECT SUM(quantity) FROM $table_cart_items WHERE cart_id = %d",
        $cart->id
    ));
    
    return $count ? intval($count) : 0;
}

// Add a shortcode to display cart count
function petshop_cart_count_shortcode() {
    return petshop_get_cart_count();
}
add_shortcode('petshop_cart_count', 'petshop_cart_count_shortcode');

// Register custom menu locations
function petshop_register_menus() {
    register_nav_menus(
        array(
            'primary' => __('Primary Menu', 'petshop'),
            'footer-company' => __('Footer Company Menu', 'petshop'),
            'footer-links' => __('Footer Useful Links Menu', 'petshop'),
            'footer-customer' => __('Footer Customer Service Menu', 'petshop')
        )
    );
}
add_action('init', 'petshop_register_menus');

// Add support for featured images
function petshop_theme_setup() {
    add_theme_support('post-thumbnails');
    add_theme_support('title-tag');
    add_theme_support('custom-logo');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));
}
add_action('after_setup_theme', 'petshop_theme_setup');

// Custom function to get featured products
function petshop_get_featured_products($limit = 3) {
    global $wpdb;
    $table_products = $wpdb->prefix . 'petshop_products';
    
    // In a real scenario, you would have a 'featured' column
    // For now, just get random products
    $products = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $table_products ORDER BY RAND() LIMIT %d",
            $limit
        )
    );
    
    return $products;
}

// Custom function to get best selling products
function petshop_get_best_selling_products($limit = 8) {
    global $wpdb;
    $table_products = $wpdb->prefix . 'petshop_products';
    $table_order_items = $wpdb->prefix . 'petshop_order_items';
    
    // In a real scenario, you would join with order_items and count sales
    // For now, just get random products
    $products = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $table_products ORDER BY RAND() LIMIT %d",
            $limit
        )
    );
    
    return $products;
}

// Register admin menu
function petshop_register_menu() {
    // Main menu page - always accessible for login
    add_menu_page(
        'Pet Shop Management',
        'Pet Shop',
        'manage_options',
        'petshop-management',
        'petshop_login_page',
        'dashicons-pets',
        6
    );

    // Only add other menu items if logged in as admin
    if (petshop_is_logged_in() && isset($_SESSION['ps_user_role']) && $_SESSION['ps_user_role'] === 'admin') {
        $submenus = array(
            array(
                'title' => 'Dashboard',
                'menu' => 'Dashboard',
                'slug' => 'ps-dashboard',
                'callback' => function(): void {
                    petshop_check_auth();
                    require_once PETSHOP_PLUGIN_DIR . 'modules/admin/dashboard.php';
                    petshop_admin_dashboard();
                }
            ),
            array(
                'title' => 'User Management',
                'menu' => 'Users',
                'slug' => 'ps-users',
                'callback' => function() {
                    petshop_check_auth();
                    require_once PETSHOP_PLUGIN_DIR . 'modules/admin/usersmanagement.php';
                    petshop_users_page();
                }
            ),
            array(
                'title' => 'Reports & Analytics',
                'menu' => 'Reports',
                'slug' => 'ps-reports',
                'callback' => function() {
                    petshop_check_auth();
                    require_once PETSHOP_PLUGIN_DIR . 'modules/admin/reports.php';
                    petshop_reports_page();
                }
            ),
            array(
                'title' => 'User Info',
                'menu' => 'User Info',
                'slug' => 'ps-user-info',
                'callback' => function() {
                    petshop_check_auth();
                    require_once PETSHOP_PLUGIN_DIR . 'modules/admin/user-info.php';
                    petshop_user_info_page();
                }
            ),
        );

        foreach ($submenus as $submenu) {
            add_submenu_page(
                'petshop-management',
                $submenu['title'],
                $submenu['menu'],
                'manage_options',
                $submenu['slug'],
                $submenu['callback']
            );
        }
    }
}
add_action('admin_menu', 'petshop_register_menu');

// Login page
function petshop_login_page() {
    if (isset($_GET['action']) && $_GET['action'] === 'register') {
        require_once PETSHOP_PLUGIN_DIR . 'modules/login/register.php';
    } else {
        require_once PETSHOP_PLUGIN_DIR . 'modules/login/login.php';
    }
}

// Products page
function petshop_products_page() {
    petshop_check_auth();
    require_once PETSHOP_PLUGIN_DIR . 'modules/products/products.php';
}

// Add product page
function petshop_add_product_page() {
    petshop_check_auth();
    require_once PETSHOP_PLUGIN_DIR . 'modules/products/add-product.php';
}

// Categories page
function petshop_categories_page() {
    petshop_check_auth();
    require_once PETSHOP_PLUGIN_DIR . 'modules/categories/categories.php';
}

// Orders page
function petshop_orders_page() {
    petshop_check_auth();
    require_once PETSHOP_PLUGIN_DIR . 'modules/orders/orders.php';
}

// Template page
function petshop_render_template_page() {
    if (petshop_is_logged_in()) {
        require_once PETSHOP_PLUGIN_DIR . 'templates/petshop-template.php';
        echo petshop_render_template(); // Call the function from the template file
    } else {
        wp_redirect(admin_url('admin.php?page=petshop-management'));
        exit;
    }
}

// Legacy admin menu functions for compatibility
function petshop_admin_page() {
    ?>
    <div class="wrap">
        <h1>Pet Shop Dashboard</h1>
        <p>Welcome to the Pet Shop plugin dashboard. Use the tabs below to manage your pet shop.</p>
        
        <!-- Dashboard content here -->
    </div>
    <?php
}