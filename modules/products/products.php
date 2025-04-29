<div class="wrap">
    <h1>Danh sách sản phẩm</h1>

    <a href="<?php echo admin_url('admin.php?page=ps-add-product'); ?>" class="page-title-action">Thêm sản phẩm mới</a>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Tên sản phẩm</th>
                <th>Giá</th>
                <th>Số lượng</th>
                <th>Danh mục</th>
            </tr>
        </thead>
        <tbody>
            <?php
            global $wpdb;
            $products = $wpdb->get_results("SELECT p.*, c.name as category_name 
                                             FROM {$wpdb->prefix}petshop_products p
                                             LEFT JOIN {$wpdb->prefix}petshop_categories c ON p.category_id = c.id");
            if ($products):
                foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo esc_html($product->name); ?></td>
                        <td>$<?php echo esc_html($product->price); ?></td>
                        <td><?php echo esc_html($product->stock_quantity); ?></td>
                        <td><?php echo esc_html($product->category_name); ?></td>
                    </tr>
                <?php endforeach;
            else: ?>
                <tr><td colspan="4">Chưa có sản phẩm nào.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
