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

// Add this near the top with other requires
require_once PETSHOP_PLUGIN_DIR . 'modules/admin/petsmanagement.php';

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
register_activation_hook(__FILE__, function() {
    global $wpdb;
    
    // Create tables first
    petshop_create_tables();
    
    // Get table name
    $table_users = $wpdb->prefix . 'petshop_users';
    
    // Check if users table exists and is empty
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_users'") === $table_users;
    $user_count = 0;
    
    if ($table_exists) {
        $user_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_users");
    }
    
    // Only insert data if table is empty
    if ($table_exists && $user_count == 0) {
        insert_sample_data();
    }
});

function petshop_create_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    
    // Define table names
    $table_users = $wpdb->prefix . 'petshop_users';
    $table_categories = $wpdb->prefix . 'petshop_categories';
    $table_products = $wpdb->prefix . 'petshop_products';
    $table_carts = $wpdb->prefix . 'petshop_carts';
    $table_cart_items = $wpdb->prefix . 'petshop_cart_items';
    $table_orders = $wpdb->prefix . 'petshop_orders';
    $table_order_items = $wpdb->prefix . 'petshop_order_items';

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    // Create users table first
    $sql = "CREATE TABLE IF NOT EXISTS $table_users (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        username VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) NOT NULL,
        phone VARCHAR(20),
        role VARCHAR(50) NOT NULL DEFAULT 'user',
        image_url TEXT, -- User image column added
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        status ENUM('active', 'banned') DEFAULT 'active',
        PRIMARY KEY (id)
    ) $charset_collate;";
    
    dbDelta($sql);

    // Create other tables...
    $sql = "CREATE TABLE IF NOT EXISTS $table_categories (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        PRIMARY KEY (id)
    ) $charset_collate;";
    dbDelta($sql);

    $sql = "CREATE TABLE IF NOT EXISTS $table_products (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        stock_quantity INT(11) DEFAULT 0,
        category_id BIGINT(20) UNSIGNED,
        image_url TEXT,
        PRIMARY KEY (id),
        FOREIGN KEY (category_id) REFERENCES $table_categories(id) ON DELETE SET NULL
    ) $charset_collate;";
    dbDelta($sql);

    $sql = "CREATE TABLE IF NOT EXISTS $table_carts (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        created_at DATETIME NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";
    dbDelta($sql);

    $sql = "CREATE TABLE IF NOT EXISTS $table_cart_items (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        cart_id BIGINT(20) UNSIGNED NOT NULL,
        product_id BIGINT(20) UNSIGNED NOT NULL,
        quantity INT(11) NOT NULL,
        PRIMARY KEY (id),
        FOREIGN KEY (cart_id) REFERENCES $table_carts(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES $table_products(id) ON DELETE CASCADE
    ) $charset_collate;";
    dbDelta($sql);

    $sql = "CREATE TABLE IF NOT EXISTS $table_orders (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        status VARCHAR(50) NOT NULL DEFAULT 'pending',
        created_at DATETIME NOT NULL,
        customer_name VARCHAR(100),
        customer_email VARCHAR(100),
        customer_phone VARCHAR(50),
        customer_address VARCHAR(255),
        PRIMARY KEY (id)
    ) $charset_collate;";
    dbDelta($sql);

    $sql = "CREATE TABLE IF NOT EXISTS $table_order_items (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        order_id BIGINT(20) UNSIGNED NOT NULL,
        product_id BIGINT(20) UNSIGNED NOT NULL,
        quantity INT(11) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        PRIMARY KEY (id),
        FOREIGN KEY (order_id) REFERENCES $table_orders(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES $table_products(id) ON DELETE CASCADE
    ) $charset_collate;";
    dbDelta($sql);

    // Create OTP table
    $table_otp = $wpdb->prefix . 'petshop_otp';
    $sql = "CREATE TABLE IF NOT EXISTS $table_otp (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        email VARCHAR(100) NOT NULL,
        otp VARCHAR(6) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        expires_at DATETIME NOT NULL,
        is_used TINYINT(1) DEFAULT 0,
        PRIMARY KEY (id)
    ) $charset_collate;";
    
    dbDelta($sql);

    // Create pets table
    $table_pets = $wpdb->prefix . 'petshop_pets';
    $sql = "CREATE TABLE IF NOT EXISTS $table_pets (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        name VARCHAR(100) NOT NULL,
        type VARCHAR(50) NOT NULL,
        breed VARCHAR(100),
        age INT,
        health_status VARCHAR(50),
        last_checkup DATETIME,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}petshop_users(id)
    ) $charset_collate;";
    
    dbDelta($sql);
}

// Update insert_sample_data function to include error checking
function insert_sample_data() {
    global $wpdb;
    $wpdb->show_errors();
    
    // Insert sample categories
    $categories = [
        ['name' => 'Dogs', 'description' => 'All dog related products'],
        ['name' => 'Cats', 'description' => 'All cat related products'],
        ['name' => 'Birds', 'description' => 'Bird supplies and accessories'],
        ['name' => 'Fish', 'description' => 'Aquarium and fish supplies']
    ];
    
    foreach ($categories as $category) {
        $result = $wpdb->insert($wpdb->prefix . 'petshop_categories', $category);
        if ($result === false) {
            error_log('Failed to insert category: ' . $wpdb->last_error);
        }
    }
    
    // Insert sample products
    $products = [
        ['name' => 'Premium Dog Food', 'description' => 'High quality dog food', 'price' => 299000, 'stock_quantity' => 100, 'category_id' => 1],
        ['name' => 'Cat Litter Box', 'description' => 'Large cat litter box', 'price' => 159000, 'stock_quantity' => 50, 'category_id' => 2],
        ['name' => 'Bird Cage', 'description' => 'Medium sized bird cage', 'price' => 499000, 'stock_quantity' => 30, 'category_id' => 3],
        ['name' => 'Fish Tank', 'description' => '20 gallon fish tank', 'price' => 899000, 'stock_quantity' => 20, 'category_id' => 4]
    ];
    
    foreach ($products as $product) {
        $result = $wpdb->insert($wpdb->prefix . 'petshop_products', $product);
        if ($result === false) {
            error_log('Failed to insert product: ' . $wpdb->last_error);
        }
    }
    
    // Insert admin user first
    $admin = [
        'username' => 'admin',
        'password' => password_hash('123456', PASSWORD_DEFAULT),
        'email' => 'admin@gmail.com',
        'phone' => '0909123456',
        'role' => 'admin',
        'created_at' => current_time('mysql')
    ];
    
    $result = $wpdb->insert($wpdb->prefix . 'petshop_users', $admin);
    if ($result === false) {
        error_log('Failed to insert admin user: ' . $wpdb->last_error);
    }
    
    // Insert sample users
    $users = [
        ['username' => 'customer1', 'password' => password_hash('123456', PASSWORD_DEFAULT), 'email' => 'customer1@example.com', 'phone' => '0981234567', 'role' => 'user', 'created_at' => current_time('mysql')],
        ['username' => 'customer2', 'password' => password_hash('123456', PASSWORD_DEFAULT), 'email' => 'customer2@example.com', 'phone' => '0987654321', 'role' => 'user', 'created_at' => current_time('mysql')]
    ];
    
    foreach ($users as $user) {
        $result = $wpdb->insert($wpdb->prefix . 'petshop_users', $user);
        if ($result === false) {
            error_log('Failed to insert user: ' . $wpdb->last_error);
        }
    }
    
    // Insert sample orders with proper error checking
    $locations = ['Hà Nội', 'Bắc Ninh', 'Thanh Hóa', 'Quảng Ninh', 'Tp Hồ Chí Minh'];
    $statuses = ['pending', 'completed', 'cancelled'];
    
    for ($i = 0; $i < 20; $i++) {
        $order = [
            'user_id' => rand(1, 3),
            'total_amount' => rand(100000, 2000000),
            'status' => $statuses[array_rand($statuses)],
            'created_at' => date('Y-m-d H:i:s', strtotime(-rand(0, 365) . ' days')),
            'customer_name' => 'Customer ' . rand(1, 10),
            'customer_email' => 'customer' . rand(1, 10) . '@example.com',
            'customer_phone' => '098' . rand(1000000, 9999999),
            'customer_address' => $locations[array_rand($locations)]
        ];
        
        $result = $wpdb->insert($wpdb->prefix . 'petshop_orders', $order);
        if ($result === false) {
            error_log('Failed to insert order: ' . $wpdb->last_error);
            continue;
        }
        
        $order_id = $wpdb->insert_id;
        
        // Add order items
        for ($j = 0; $j < rand(1, 4); $j++) {
            $order_item = [
                'order_id' => $order_id,
                'product_id' => rand(1, 4),
                'quantity' => rand(1, 5),
                'price' => rand(100000, 500000)
            ];
            $result = $wpdb->insert($wpdb->prefix . 'petshop_order_items', $order_item);
            if ($result === false) {
                error_log('Failed to insert order item: ' . $wpdb->last_error);
            }
        }
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

// Update the enqueue function

function petshop_enqueue_admin_scripts($hook) {
    if (strpos($hook, 'ps-') !== false) {
        wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', [], '3.7.0', true);
        wp_enqueue_script('petshop-dashboard', plugins_url('assets/js/dashboard.js', __FILE__), ['jquery', 'chart-js'], '1.0', true);
        
        wp_localize_script('petshop-dashboard', 'petshopDashboardVars', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('petshop_dashboard_nonce')
        ]);
    }
}
add_action('admin_enqueue_scripts', 'petshop_enqueue_admin_scripts');

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
    // Always use 'read' so all logged-in users can see the menu
    add_menu_page(
        'Pet Shop Management',
        'Pet Shop',
        'read',
        'petshop-management',
        'petshop_login_page',
        'dashicons-pets',
        6
    );

    // Only add other menu items if logged in
    if (petshop_is_logged_in() && isset($_SESSION['ps_user_role'])) {
        if ($_SESSION['ps_user_role'] === 'admin') {
            // Admin-specific menu items (keep 'manage_options' for these)
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

            add_submenu_page(
                'petshop-management',
                'Products',
                'Products',
                'manage_options',
                'ps-products',
                'petshop_products_page'
            );
            
            add_submenu_page(
                'petshop-management',
                'Categories',
                'Categories',
                'manage_options',
                'ps-categories',
                'petshop_categories_page'
            );
            
            add_submenu_page(
                'petshop-management',
                'Customer Pets',
                'Pets Information',
                'manage_options',
                'ps-pets',
                'petshop_pets_page'
            );
        } elseif ($_SESSION['ps_user_role'] === 'user') {
            // User-specific menu items (use 'read')
            add_submenu_page(
                'petshop-management',
                'Shop',
                'Shop',
                'read',
                'ps-shop',
                function() {
                    petshop_check_auth();
                    require_once PETSHOP_PLUGIN_DIR . 'modules/user/shop.php';
                }
            );
            add_submenu_page(
                'petshop-management',
                'My Cart',
                'Cart',
                'read',
                'ps-cart',
                function() {
                    petshop_check_auth();
                    require_once PETSHOP_PLUGIN_DIR . 'modules/user/cart.php';
                }
            );
            add_submenu_page(
                'petshop-management',
                'My Orders',
                'My Orders',
                'read',
                'ps-my-orders',
                function() {
                    petshop_check_auth();
                    require_once PETSHOP_PLUGIN_DIR . 'modules/user/my_orders.php';
                }
            );
            add_submenu_page(
                'petshop-management',
                'Account Info',
                'Account',
                'read',
                'ps-account',
                function() {
                    petshop_check_auth();
                    require_once PETSHOP_PLUGIN_DIR . 'modules/user/account.php';
                }
            );
        }
    }
}
add_action('admin_menu', 'petshop_register_menu');

// Login page
function petshop_login_page() {
    // If already logged in as user, show Shop page instead of login
    if (petshop_is_logged_in() && isset($_SESSION['ps_user_role'])) {
        if ($_SESSION['ps_user_role'] === 'user') {
            petshop_check_auth();
            require_once PETSHOP_PLUGIN_DIR . 'modules/user/shop.php'; // or your custom user dashboard
            return;
        }
        if ($_SESSION['ps_user_role'] === 'admin') {
            petshop_check_auth();
            require_once PETSHOP_PLUGIN_DIR . 'modules/admin/dashboard.php';
            petshop_admin_dashboard();
            return;
        }
    }

    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'register':
                require_once PETSHOP_PLUGIN_DIR . 'modules/login/register.php';
                break;
            case 'forgot-password':
                require_once PETSHOP_PLUGIN_DIR . 'modules/login/forgot-password.php';
                break;
            case 'verify-otp':
                require_once PETSHOP_PLUGIN_DIR . 'modules/login/verify-otp.php';
                break;
            case 'reset-password':
                require_once PETSHOP_PLUGIN_DIR . 'modules/login/reset-password.php';
                break;
            default:
                require_once PETSHOP_PLUGIN_DIR . 'modules/login/login.php';
        }
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

// Add AJAX handler for fetching dashboard data
add_action('wp_ajax_petshop_fetch_dashboard_data', 'petshop_ajax_fetch_dashboard_data');

function petshop_ajax_fetch_dashboard_data() {
    check_ajax_referer('petshop_dashboard_nonce', 'nonce');
    
    $time_filter = $_POST['time_filter'] ?? 'month';
    $time_tab = $_POST['time_tab'] ?? 'month';
    $year = intval($_POST['year'] ?? date('Y'));
    
    global $wpdb;
    
    // Get data based on filters
    $data = array(
        'kpi' => get_kpi_data($time_filter, $time_tab, $year),
        'charts' => array(
            'orders_location' => get_orders_location_data($time_filter, $year),
            'sales_location' => get_sales_location_data($time_filter, $year),
            'revenue' => get_revenue_data($time_tab, $year),
            'mini' => array(
                'orders' => get_mini_orders_data($time_filter, $year),
                'users' => get_mini_users_data($time_filter, $year)
            )
        )
    );
    
    wp_send_json_success($data);
}

// Add this function to handle AJAX requests

function petshop_fetch_dashboard_data() {
    check_ajax_referer('petshop_dashboard_nonce', 'nonce');
    
    global $wpdb;
    $time_filter = $_POST['time_filter'] ?? 'month';
    $time_tab = $_POST['time_tab'] ?? 'month';
    $year = intval($_POST['year'] ?? date('Y'));
    
    // Build date conditions based on filters
    $date_condition = "YEAR(created_at) = $year";
    if ($time_tab === 'week') {
        $date_condition .= " AND WEEK(created_at) = WEEK(CURRENT_DATE())";
    } elseif ($time_tab === 'month') {
        $date_condition .= " AND MONTH(created_at) = MONTH(CURRENT_DATE())";
    }
    
    // Get orders by location
    $orders_by_location = $wpdb->get_results("
        SELECT customer_address as location, COUNT(*) as count
        FROM {$wpdb->prefix}petshop_orders
        WHERE $date_condition
        GROUP BY customer_address
        ORDER BY count DESC
        LIMIT 5
    ", ARRAY_A);
    
    // Get sales by location
    $sales_by_location = $wpdb->get_results("
        SELECT customer_address as location, 
               SUM(total_amount) as total,
               (SUM(total_amount) / (SELECT SUM(total_amount) FROM {$wpdb->prefix}petshop_orders WHERE $date_condition) * 100) as percentage
        FROM {$wpdb->prefix}petshop_orders
        WHERE $date_condition AND status = 'completed'
        GROUP BY customer_address
        ORDER BY total DESC
        LIMIT 5
    ", ARRAY_A);
    
    // Get revenue data
    $revenue_data = $wpdb->get_results("
        SELECT " . ($time_filter === 'month' ? 'MONTH' : 'QUARTER') . "(created_at) as period,
               SUM(total_amount) as revenue
        FROM {$wpdb->prefix}petshop_orders
        WHERE $date_condition AND status = 'completed'
        GROUP BY period
        ORDER BY period
    ", ARRAY_A);
    
    wp_send_json_success([
        'orders_location' => [
            'labels' => array_column($orders_by_location, 'location'),
            'data' => array_column($orders_by_location, 'count')
        ],
        'sales_location' => [
            'labels' => array_column($sales_by_location, 'location'),
            'data' => array_column($sales_by_location, 'percentage')
        ],
        'revenue' => [
            'labels' => $time_filter === 'month' ? 
                ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'] :
                ['Q1', 'Q2', 'Q3', 'Q4'],
            'data' => array_column($revenue_data, 'revenue')
        ]
    ]);
}
add_action('wp_ajax_petshop_fetch_dashboard_data', 'petshop_fetch_dashboard_data');

function petshop_delete_product() {
    check_ajax_referer('petshop_delete_product', 'security');
    
    if (!petshop_is_logged_in() || $_SESSION['ps_user_role'] !== 'admin') {
        wp_send_json_error('Permission denied');
        return;
    }
    
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    
    if (!$product_id) {
        wp_send_json_error('Invalid product ID');
        return;
    }
    
    global $wpdb;
    $result = $wpdb->delete(
        $wpdb->prefix . 'petshop_products',
        ['id' => $product_id]
    );
    
    if ($result !== false) {
        wp_send_json_success();
    } else {
        wp_send_json_error('Database delete failed');
    }
}
add_action('wp_ajax_petshop_delete_product', 'petshop_delete_product');

// Get product details
function petshop_get_product() {
    check_ajax_referer('petshop_edit_product', 'security');
    
    if (!petshop_is_logged_in() || $_SESSION['ps_user_role'] !== 'admin') {
        wp_send_json_error('Permission denied');
        return;
    }
    
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    
    if (!$product_id) {
        wp_send_json_error('Invalid product ID');
        return;
    }
    
    global $wpdb;
    $product = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}petshop_products WHERE id = %d",
        $product_id
    ));
    
    if ($product) {
        wp_send_json_success($product);
    } else {
        wp_send_json_error('Product not found');
    }
}
add_action('wp_ajax_petshop_get_product', 'petshop_get_product');

// Update product
function petshop_update_product() {
    check_ajax_referer('petshop_edit_product', 'security');
    if (!petshop_is_logged_in() || $_SESSION['ps_user_role'] !== 'admin') {
        wp_send_json_error('Không có quyền truy cập');
        return;
    }

    $product_id = intval($_POST['product_id']);
    $name = sanitize_text_field($_POST['name']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock_quantity']);
    $category = intval($_POST['category_id']);
    $description = sanitize_textarea_field($_POST['description']);

    $update_data = array(
        'name' => $name,
        'price' => $price,
        'stock_quantity' => $stock,
        'category_id' => $category,
        'description' => $description
    );

    // --- ADD THIS BLOCK HERE ---
    if (!empty($_FILES['image_file']['name'])) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        $uploaded = wp_handle_upload($_FILES['image_file'], ['test_form' => false]);
        if (!isset($uploaded['error'])) {
            $image_url = $uploaded['url'];
            $update_data['image_url'] = $image_url;
        }
    }
    // --- END BLOCK ---

    global $wpdb;
    $result = $wpdb->update(
        $wpdb->prefix . 'petshop_products',
        $update_data,
        array('id' => $product_id)
    );

    if ($result === false) {
        wp_send_json_error('Lỗi khi cập nhật sản phẩm: ' . $wpdb->last_error);
        return;
    }

    wp_send_json_success();
}
add_action('wp_ajax_petshop_update_product', 'petshop_update_product');

// Add this with your other AJAX handlers

function petshop_add_product() {
    check_ajax_referer('petshop_add_product', 'security');
    if (!petshop_is_logged_in() || $_SESSION['ps_user_role'] !== 'admin') {
        wp_send_json_error('Không có quyền truy cập');
        return;
    }

    $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    $stock = isset($_POST['stock_quantity']) ? intval($_POST['stock_quantity']) : 0;
    $category = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    $description = isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '';
    $image_url = '';

    // Handle file upload
    if (!empty($_FILES['image_file']['name'])) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        $uploaded = wp_handle_upload($_FILES['image_file'], ['test_form' => false]);
        if (!isset($uploaded['error'])) {
            $image_url = $uploaded['url'];
        }
    }

    // Validation...
    if (empty($name)) {
        wp_send_json_error('Tên sản phẩm không được để trống');
        return;
    }
    if ($price <= 0) {
        wp_send_json_error('Giá sản phẩm phải lớn hơn 0');
        return;
    }
    if ($stock < 0) {
        wp_send_json_error('Số lượng không được âm');
        return;
    }
    if (empty($category)) {
        wp_send_json_error('Vui lòng chọn danh mục');
        return;
    }

    global $wpdb;
    $result = $wpdb->insert(
        $wpdb->prefix . 'petshop_products',
        array(
            'name' => $name,
            'price' => $price,
            'stock_quantity' => $stock,
            'category_id' => $category,
            'description' => $description,
            'image_url' => $image_url
        ),
        array('%s', '%f', '%d', '%d', '%s', '%s')
    );

    if ($result === false) {
        wp_send_json_error('Lỗi khi thêm sản phẩm: ' . $wpdb->last_error);
        return;
    }

    wp_send_json_success();
}
add_action('wp_ajax_petshop_add_product', 'petshop_add_product');

add_action('wp_ajax_petshop_add_to_cart_db', function() {
    global $wpdb;
    // Get user id from session (custom login system)
    $user_id = isset($_SESSION['ps_user_id']) ? intval($_SESSION['ps_user_id']) : 0;
    if (!$user_id) {
        wp_send_json_error('Bạn cần đăng nhập để thêm sản phẩm vào giỏ hàng.');
    }
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $quantity = isset($_POST['quantity']) ? max(1, intval($_POST['quantity'])) : 1;

    $table_carts = $wpdb->prefix . 'petshop_carts';
    $table_cart_items = $wpdb->prefix . 'petshop_cart_items';
    $table_products = $wpdb->prefix . 'petshop_products';

    // Check product exists
    $product = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_products WHERE id=%d", $product_id));
    if (!$product) {
        wp_send_json_error('Sản phẩm không tồn tại.');
    }

    // Get or create cart for this session user id
    $cart = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_carts WHERE user_id=%d", $user_id));
    if (!$cart) {
        $wpdb->insert($table_carts, [
            'user_id' => $user_id,
            'created_at' => current_time('mysql')
        ]);
        if ($wpdb->last_error) {
            wp_send_json_error('Lỗi khi tạo giỏ hàng: ' . $wpdb->last_error);
        }
        $cart_id = $wpdb->insert_id;
    } else {
        $cart_id = $cart->id;
    }

    // Check if item already in cart
    $cart_item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_cart_items WHERE cart_id=%d AND product_id=%d", $cart_id, $product_id));
    if ($cart_item) {
        $result = $wpdb->update(
            $table_cart_items,
            ['quantity' => $cart_item->quantity + $quantity],
            ['id' => $cart_item->id]
        );
        if ($wpdb->last_error) {
            wp_send_json_error('Lỗi khi cập nhật số lượng: ' . $wpdb->last_error);
        }
    } else {
        $result = $wpdb->insert(
            $table_cart_items,
            [
                'cart_id' => $cart_id,
                'product_id' => $product_id,
                'quantity' => $quantity,
            ]
        );
        if ($wpdb->last_error) {
            wp_send_json_error('Lỗi khi thêm sản phẩm vào giỏ: ' . $wpdb->last_error);
        }
    }
    wp_send_json_success('Đã thêm vào giỏ hàng!');
});