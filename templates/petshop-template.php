<?php
function petshop_render_template() {
    global $wpdb;

    // Lấy dữ liệu danh mục
    $categories = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}petshop_categories");

    // Lấy dữ liệu sản phẩm
    $products = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}petshop_products LIMIT 8");

    // Hiển thị giao diện
    ob_start(); ?>

    <div class="petshop-header">
        <nav>
            <a href="#">Home</a>
            <a href="#">Shop</a>
            <a href="#">About Us</a>
            <a href="#">Contact Us</a>
        </nav>
    </div>

    <div class="petshop-banner">
        <h1>A pet store with everything you need</h1>
        <button>Shop Now</button>
    </div>

    <div class="petshop-categories">
        <h2>Categories</h2>
        <div class="categories-container">
            <?php foreach ($categories as $category): ?>
                <div class="category-item">
                    <h3><?php echo esc_html($category->name); ?></h3>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="petshop-products">
        <h2>Featured Products</h2>
        <div class="products-container">
            <?php foreach ($products as $product): ?>
                <div class="product-item">
                    <img src="<?php echo esc_url($product->image_url); ?>" alt="<?php echo esc_html($product->name); ?>">
                    <h3><?php echo esc_html($product->name); ?></h3>
                    <p>$<?php echo esc_html($product->price); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php
    return ob_get_clean();
}
