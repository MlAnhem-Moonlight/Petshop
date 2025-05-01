<?php
function petshop_products_page() {
    if (!petshop_is_logged_in() || $_SESSION['ps_user_role'] !== 'admin') {
        wp_redirect(admin_url('admin.php?page=petshop-management'));
        exit;
    }

    global $wpdb;
    $table_products = $wpdb->prefix . 'petshop_products';
    $table_categories = $wpdb->prefix . 'petshop_categories';

    // Handle actions
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_product':
                $wpdb->insert($table_products, [
                    'name' => sanitize_text_field($_POST['name']),
                    'description' => sanitize_textarea_field($_POST['description']),
                    'price' => floatval($_POST['price']),
                    'stock_quantity' => intval($_POST['stock_quantity']),
                    'category_id' => intval($_POST['category_id'])
                ]);
                break;

            case 'edit_product':
                $wpdb->update($table_products, 
                    [
                        'name' => sanitize_text_field($_POST['name']),
                        'description' => sanitize_textarea_field($_POST['description']),
                        'price' => floatval($_POST['price']),
                        'stock_quantity' => intval($_POST['stock_quantity']),
                        'category_id' => intval($_POST['category_id'])
                    ],
                    ['id' => intval($_POST['product_id'])]
                );
                break;

            case 'delete_product':
                $wpdb->delete($table_products, ['id' => intval($_POST['product_id'])]);
                break;
        }
    }

    // Get products and categories
    $products = $wpdb->get_results("
        SELECT p.*, c.name as category_name 
        FROM $table_products p 
        LEFT JOIN $table_categories c ON p.category_id = c.id 
        ORDER BY p.name
    ");
    $categories = $wpdb->get_results("SELECT * FROM $table_categories ORDER BY name");

    ?>
    <div class="wrap">
        <h1>Product Management</h1>

        <!-- Add Product Button -->
        <button class="page-title-action" onclick="showAddProductForm()">Add New Product</button>

        <!-- Product List -->
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Category</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo esc_html($product->name); ?></td>
                        <td><?php echo esc_html($product->description); ?></td>
                        <td><?php echo number_format($product->price); ?>đ</td>
                        <td><?php echo esc_html($product->stock_quantity); ?></td>
                        <td><?php echo esc_html($product->category_name); ?></td>
                        <td>
                            <button class="button button-small" onclick="showEditProductForm(<?php echo json_encode($product); ?>)">Edit</button>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="action" value="delete_product">
                                <input type="hidden" name="product_id" value="<?php echo $product->id; ?>">
                                <button type="submit" class="button button-small" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Add/Edit Product Modal -->
        <div id="productModal" class="ps-modal">
            <div class="ps-modal-content">
                <span class="ps-close">&times;</span>
                <h2 id="modalTitle">Add New Product</h2>
                <form method="post">
                    <input type="hidden" name="action" id="productAction" value="add_product">
                    <input type="hidden" name="product_id" id="productId">
                    
                    <div class="ps-form-row">
                        <label>Name:</label>
                        <input type="text" name="name" id="productName" required>
                    </div>
                    
                    <div class="ps-form-row">
                        <label>Description:</label>
                        <textarea name="description" id="productDescription" required></textarea>
                    </div>
                    
                    <div class="ps-form-row">
                        <label>Price:</label>
                        <input type="number" name="price" id="productPrice" required>
                    </div>
                    
                    <div class="ps-form-row">
                        <label>Stock:</label>
                        <input type="number" name="stock_quantity" id="productStock" required>
                    </div>
                    
                    <div class="ps-form-row">
                        <label>Category:</label>
                        <select name="category_id" id="productCategory" required>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category->id; ?>"><?php echo esc_html($category->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="button button-primary">Save Product</button>
                </form>
            </div>
        </div>
    </div>

    <style>
        .ps-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
        }

        .ps-modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 50%;
            border-radius: 5px;
        }

        .ps-close {
            float: right;
            cursor: pointer;
            font-size: 28px;
        }

        .ps-form-row {
            margin-bottom: 15px;
        }

        .ps-form-row label {
            display: block;
            margin-bottom: 5px;
        }

        .ps-form-row input,
        .ps-form-row textarea,
        .ps-form-row select {
            width: 100%;
            padding: 8px;
        }
    </style>

    <script>
        function showAddProductForm() {
            document.getElementById('modalTitle').textContent = 'Add New Product';
            document.getElementById('productAction').value = 'add_product';
            document.getElementById('productId').value = '';
            document.getElementById('productName').value = '';
            document.getElementById('productDescription').value = '';
            document.getElementById('productPrice').value = '';
            document.getElementById('productStock').value = '';
            document.getElementById('productModal').style.display = 'block';
        }

        function showEditProductForm(product) {
            document.getElementById('modalTitle').textContent = 'Edit Product';
            document.getElementById('productAction').value = 'edit_product';
            document.getElementById('productId').value = product.id;
            document.getElementById('productName').value = product.name;
            document.getElementById('productDescription').value = product.description;
            document.getElementById('productPrice').value = product.price;
            document.getElementById('productStock').value = product.stock_quantity;
            document.getElementById('productCategory').value = product.category_id;
            document.getElementById('productModal').style.display = 'block';
        }

        // Close modal
        document.querySelector('.ps-close').onclick = function() {
            document.getElementById('productModal').style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == document.getElementById('productModal')) {
                document.getElementById('productModal').style.display = 'none';
            }
        }
    </script>
    <?php
}