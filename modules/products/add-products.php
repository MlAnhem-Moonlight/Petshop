<div class="wrap">
    <h1>Thêm sản phẩm mới</h1>

    <?php
    global $wpdb;

    if (isset($_POST['submit_product'])) {
        $name = sanitize_text_field($_POST['name']);
        $description = sanitize_textarea_field($_POST['description']);
        $price = floatval($_POST['price']);
        $stock_quantity = intval($_POST['stock_quantity']);
        $category_id = intval($_POST['category_id']);
        $image_url = esc_url_raw($_POST['image_url']);

        $wpdb->insert(
            $wpdb->prefix . 'petshop_products',
            [
                'name' => $name,
                'description' => $description,
                'price' => $price,
                'stock_quantity' => $stock_quantity,
                'category_id' => $category_id,
                'image_url' => $image_url,
            ]
        );

        echo '<div class="notice notice-success is-dismissible"><p>Đã thêm sản phẩm!</p></div>';
    }

    $categories = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}petshop_categories");
    ?>

    <form method="post">
        <table class="form-table">
            <tr>
                <th>Tên sản phẩm</th>
                <td><input type="text" name="name" required class="regular-text"></td>
            </tr>
            <tr>
                <th>Mô tả</th>
                <td><textarea name="description" rows="5" class="large-text"></textarea></td>
            </tr>
            <tr>
                <th>Giá</th>
                <td><input type="number" step="0.01" name="price" required></td>
            </tr>
            <tr>
                <th>Số lượng</th>
                <td><input type="number" name="stock_quantity" required></td>
            </tr>
            <tr>
                <th>Danh mục</th>
                <td>
                    <select name="category_id" required>
                        <option value="">-- Chọn danh mục --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat->id; ?>"><?php echo esc_html($cat->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th>Link ảnh sản phẩm</th>
                <td><input type="text" name="image_url" class="regular-text"></td>
            </tr>
        </table>
        <?php submit_button('Thêm sản phẩm'); ?>
    </form>
</div>
