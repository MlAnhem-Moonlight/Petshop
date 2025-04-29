<div class="wrap">
    <h1>Quản lý danh mục</h1>

    <?php
    global $wpdb;

    if (isset($_POST['submit_category'])) {
        $name = sanitize_text_field($_POST['name']);
        $description = sanitize_textarea_field($_POST['description']);

        $wpdb->insert(
            $wpdb->prefix . 'petshop_categories',
            [
                'name' => $name,
                'description' => $description,
            ]
        );

        echo '<div class="notice notice-success is-dismissible"><p>Đã thêm danh mục!</p></div>';
    }

    $categories = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}petshop_categories");
    ?>

    <h2>Thêm danh mục mới</h2>
    <form method="post">
        <table class="form-table">
            <tr>
                <th>Tên danh mục</th>
                <td><input type="text" name="name" required class="regular-text"></td>
            </tr>
            <tr>
                <th>Mô tả</th>
                <td><textarea name="description" rows="5" class="large-text"></textarea></td>
            </tr>
        </table>
        <?php submit_button('Thêm danh mục'); ?>
    </form>

    <h2>Danh sách danh mục</h2>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Tên danh mục</th>
                <th>Mô tả</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($categories):
                foreach ($categories as $category): ?>
                    <tr>
                        <td><?php echo esc_html($category->name); ?></td>
                        <td><?php echo esc_html($category->description); ?></td>
                    </tr>
                <?php endforeach;
            else: ?>
                <tr><td colspan="2">Chưa có danh mục nào.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
