<div class="wrap petshop-products-wrap">
    <h1 class="petshop-title">Danh sách sản phẩm</h1>

    <div class="petshop-search-box">
        <input type="search" id="product-search-input" placeholder="Tìm kiếm sản phẩm...">
        <button type="button" id="search-button" class="button">Tìm kiếm</button>
    </div>

    <a href="<?php echo admin_url('admin.php?page=ps-add-product'); ?>" class="page-title-action">Thêm sản phẩm mới</a>

    <table class="wp-list-table widefat fixed striped petshop-products-table">
        <thead>
            <tr>
                <th>Tên sản phẩm</th>
                <th>Giá</th>
                <th>Số lượng</th>
                <th>Danh mục</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php
            global $wpdb;
            $products = $wpdb->get_results("SELECT p.*, c.name as category_name 
                                          FROM {$wpdb->prefix}petshop_products p
                                          LEFT JOIN {$wpdb->prefix}petshop_categories c 
                                          ON p.category_id = c.id");
            if ($products):
                foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo esc_html($product->name); ?></td>
                        <td><?php echo number_format($product->price) . 'đ'; ?></td>
                        <td><?php echo esc_html($product->stock_quantity); ?></td>
                        <td><?php echo esc_html($product->category_name); ?></td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=ps-edit-product&id=' . $product->id); ?>" 
                               class="button button-small edit-product" 
                               data-id="<?php echo $product->id; ?>">Sửa</a>
                            <button class="button button-small delete-product" 
                                    data-id="<?php echo $product->id; ?>">Xóa</button>
                        </td>
                    </tr>
                <?php endforeach;
            else: ?>
                <tr><td colspan="5">Chưa có sản phẩm nào.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Edit Product Modal -->
    <div id="editProductModal" class="ps-modal">
        <div class="ps-modal-content">
            <span class="ps-modal-close">&times;</span>
            <h2>Sửa thông tin sản phẩm</h2>
            <form id="editProductForm">
                <input type="hidden" id="edit_product_id">
                <div class="ps-form-group">
                    <label>Tên sản phẩm</label>
                    <input type="text" id="edit_name" required>
                </div>
                <div class="ps-form-group">
                    <label>Giá</label>
                    <input type="number" id="edit_price" required>
                </div>
                <div class="ps-form-group">
                    <label>Số lượng</label>
                    <input type="number" id="edit_stock" required>
                </div>
                <div class="ps-form-group">
                    <label>Danh mục</label>
                    <select id="edit_category">
                        <?php
                        $categories = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}petshop_categories");
                        foreach ($categories as $category) {
                            echo '<option value="' . $category->id . '">' . esc_html($category->name) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="ps-form-group">
                    <label>Mô tả</label>
                    <textarea id="edit_description"></textarea>
                </div>
                <button type="submit" class="button button-primary">Lưu thay đổi</button>
            </form>
        </div>
    </div>
</div>

<style>
.petshop-products-wrap {
    background-color: #f9fff5;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.petshop-title {
    color: #4CAF50;
    margin-bottom: 20px;
    font-size: 24px;
    border-bottom: 2px solid #8BC34A;
    padding-bottom: 10px;
}

.petshop-products-table {
    width: 100%;
    border-collapse: collapse;
    border-radius: 6px;
    overflow: hidden;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
    margin-top: 20px;
}

.petshop-products-table th {
    background-color: #8BC34A;
    color: white;
    font-weight: bold;
    text-align: left;
    padding: 12px;
    border: none;
}

.petshop-products-table tr:nth-child(even) {
    background-color: #f2f9eb;
}

.petshop-products-table tr:nth-child(odd) {
    background-color: #ffffff;
}

.petshop-products-table tr:hover {
    background-color: #e8f5e9;
}

.petshop-products-table td {
    padding: 10px 12px;
    border-bottom: 1px solid #E0E0E0;
}

.petshop-search-box {
    display: flex;
    margin-bottom: 20px;
    gap: 10px;
}

.petshop-search-box input {
    flex-grow: 1;
    padding: 8px 12px;
    border: 1px solid #AED581;
    border-radius: 4px;
    font-size: 14px;
}

.petshop-search-box button {
    background-color: #8BC34A;
    color: white;
    border: none;
    border-radius: 4px;
    padding: 8px 16px;
    cursor: pointer;
    font-weight: bold;
}

.petshop-search-box button:hover {
    background-color: #689F38;
}

.page-title-action {
    background-color: #8BC34A;
    color: white;
    border: none;
    border-radius: 4px;
    padding: 8px 16px;
    text-decoration: none;
    display: inline-block;
    margin-bottom: 20px;
    font-weight: bold;
}

.page-title-action:hover {
    background-color: #689F38;
    color: white;
}

.button-small {
    padding: 4px 8px;
    margin: 0 4px;
}

.delete-product {
    background-color: #f44336;
    color: white;
    border: none;
}

.delete-product:hover {
    background-color: #d32f2f;
    color: white;
}

.ps-modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.ps-modal-content {
    background-color: #fff;
    margin: 5% auto;
    padding: 20px;
    border-radius: 8px;
    width: 50%;
    max-width: 500px;
    position: relative;
}

.ps-modal-close {
    position: absolute;
    right: 15px;
    top: 10px;
    font-size: 24px;
    cursor: pointer;
    color: #666;
}

.ps-form-group {
    margin-bottom: 15px;
}

.ps-form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
    color: #333;
}

.ps-form-group input,
.ps-form-group select,
.ps-form-group textarea {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.ps-form-group textarea {
    height: 100px;
    resize: vertical;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Handle product search
    $('#search-button').on('click', function() {
        var searchTerm = $('#product-search-input').val().toLowerCase();
        $('.petshop-products-table tbody tr').each(function() {
            var text = $(this).text().toLowerCase();
            $(this).toggle(text.indexOf(searchTerm) > -1);
        });
    });

    // Handle search on Enter key
    $('#product-search-input').on('keypress', function(e) {
        if (e.which === 13) {
            $('#search-button').click();
        }
    });

    // Handle delete product
    $('.delete-product').on('click', function() {
        if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')) {
            var productId = $(this).data('id');
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'petshop_delete_product',
                    product_id: productId,
                    security: '<?php echo wp_create_nonce("petshop_delete_product"); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Không thể xóa sản phẩm');
                    }
                }
            });
        }
    });

    // Show edit modal
    $('.edit-product').on('click', function(e) {
        e.preventDefault();
        var productId = $(this).data('id');
        
        // Fetch product data
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'petshop_get_product',
                product_id: productId,
                security: '<?php echo wp_create_nonce("petshop_edit_product"); ?>'
            },
            success: function(response) {
                if (response.success) {
                    var product = response.data;
                    $('#edit_product_id').val(product.id);
                    $('#edit_name').val(product.name);
                    $('#edit_price').val(product.price);
                    $('#edit_stock').val(product.stock_quantity);
                    $('#edit_category').val(product.category_id);
                    $('#edit_description').val(product.description);
                    $('#editProductModal').show();
                }
            }
        });
    });

    // Handle form submission
    $('#editProductForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'petshop_update_product',
                security: '<?php echo wp_create_nonce("petshop_edit_product"); ?>',
                product_id: $('#edit_product_id').val(),
                name: $('#edit_name').val(),
                price: $('#edit_price').val(),
                stock_quantity: $('#edit_stock').val(),
                category_id: $('#edit_category').val(),
                description: $('#edit_description').val()
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Không thể cập nhật sản phẩm');
                }
            }
        });
    });

    // Close modal
    $('.ps-modal-close').on('click', function() {
        $('#editProductModal').hide();
    });

    $(window).on('click', function(e) {
        if ($(e.target).hasClass('ps-modal')) {
            $('.ps-modal').hide();
        }
    });
});
</script>
