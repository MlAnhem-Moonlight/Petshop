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

    // Xử lý tìm kiếm
    $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
    $where = '';
    $params = [];
    if ($search !== '') {
        $where = "WHERE (p.name LIKE %s OR p.description LIKE %s OR c.name LIKE %s)";
        $like = '%' . $wpdb->esc_like($search) . '%';
        $params = [$like, $like, $like];
    }

    // Get products and categories
    $sql = "SELECT p.*, c.name as category_name 
            FROM $table_products p 
            LEFT JOIN $table_categories c ON p.category_id = c.id 
            $where
            ORDER BY p.name";
    if ($where) {
        $products = $wpdb->get_results($wpdb->prepare($sql, ...$params));
    } else {
        $products = $wpdb->get_results($sql);
    }
    $categories = $wpdb->get_results("SELECT * FROM $table_categories ORDER BY name");

    ?>
    <div class="wrap">
        <h1>Product Management</h1>

        <!-- Search Form -->
        <form method="get" style="margin-bottom:18px; display:flex; gap:10px; align-items:center;">
            <input type="hidden" name="page" value="petshop-products">
            <input type="search" name="s" placeholder="Tìm kiếm sản phẩm..." value="<?php echo esc_attr($search); ?>" style="padding:8px 14px; border-radius:6px; border:1.5px solid #aed581; min-width:220px;">
            <button type="submit" class="button button-primary"><span class="dashicons dashicons-search"></span> Tìm kiếm</button>
        </form>

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
                            <button class="button button-small" onclick="showEditProductForm(<?php echo htmlspecialchars(json_encode($product), ENT_QUOTES, 'UTF-8'); ?>)">Edit</button>
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
        .wrap {
            background: linear-gradient(120deg, #f9fff5 80%, #e8f5e9 100%);
            border-radius: 14px;
            box-shadow: 0 4px 24px rgba(76,175,80,0.10);
            padding: 32px 28px 28px 28px;
            margin-top: 24px;
        }
        h1 {
            color: #388e3c;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .page-title-action {
            background: linear-gradient(90deg, #8bc34a 60%, #4caf50 100%);
            color: white;
            border: none;
            border-radius: 6px;
            padding: 10px 22px;
            cursor: pointer;
            font-weight: bold;
            font-size: 15px;
            margin-bottom: 18px;
            margin-left: 0;
            box-shadow: 0 2px 8px rgba(76,175,80,0.07);
            transition: background 0.2s;
        }
        .page-title-action:hover {
            background: linear-gradient(90deg, #689f38 60%, #388e3c 100%);
        }
        .wp-list-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
            margin-bottom: 0;
        }
        .wp-list-table th {
            background: linear-gradient(90deg, #8bc34a 60%, #4caf50 100%);
            color: white;
            font-weight: bold;
            text-align: left;
            padding: 14px 10px;
            border: none;
            font-size: 15px;
        }
        .wp-list-table tr:nth-child(even) {
            background-color: #f2f9eb;
        }
        .wp-list-table tr:nth-child(odd) {
            background-color: #ffffff;
        }
        .wp-list-table tr:hover {
            background-color: #e8f5e9;
        }
        .wp-list-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 15px;
            vertical-align: middle;
        }
        .button.button-small {
            background: #fffde7;
            color: #fbc02d;
            border: 1.5px solid #ffe082;
            border-radius: 6px;
            padding: 6px 14px;
            font-weight: bold;
            font-size: 14px;
            margin-right: 4px;
            transition: background 0.2s, color 0.2s;
        }
        .button.button-small:hover {
            background: #ffe082;
            color: #e65100;
        }
        .button.button-primary {
            background: linear-gradient(90deg, #8bc34a 60%, #4caf50 100%);
            color: white;
            border: none;
            border-radius: 6px;
            padding: 10px 22px;
            cursor: pointer;
            font-weight: bold;
            font-size: 15px;
            transition: background 0.2s;
        }
        .button.button-primary:hover {
            background: linear-gradient(90deg, #689f38 60%, #388e3c 100%);
        }
        .ps-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.25);
        }
        .ps-modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 30px 32px 24px 32px;
            border-radius: 10px;
            width: 420px;
            box-shadow: 0 8px 32px rgba(76,175,80,0.15);
            position: relative;
            animation: fadeInModal 0.3s;
        }
        @keyframes fadeInModal {
            from {transform: translateY(-40px); opacity: 0;}
            to {transform: translateY(0); opacity: 1;}
        }
        .ps-close {
            position: absolute;
            right: 18px;
            top: 10px;
            font-size: 28px;
            color: #388e3c;
            cursor: pointer;
            font-weight: bold;
            transition: color 0.2s;
        }
        .ps-close:hover {
            color: #d32f2f;
        }
        .ps-form-row {
            margin-bottom: 18px;
        }
        .ps-form-row label {
            display: block;
            margin-bottom: 6px;
            color: #388e3c;
            font-weight: 500;
        }
        .ps-form-row input,
        .ps-form-row textarea,
        .ps-form-row select {
            width: 100%;
            padding: 9px 12px;
            border: 1.5px solid #aed581;
            border-radius: 5px;
            background: #fff;
            font-size: 15px;
            transition: border 0.2s;
        }
        .ps-form-row input:focus,
        .ps-form-row textarea:focus,
        .ps-form-row select:focus {
            border: 1.5px solid #4caf50;
            outline: none;
        }
    </style>
    <link rel="stylesheet" href="<?php echo includes_url('css/dashicons.min.css'); ?>">

    <script>
        function showAddProductForm() {
            document.getElementById('modalTitle').textContent = 'Add New Product';
            document.getElementById('productAction').value = 'add_product';
            document.getElementById('productId').value = '';
            document.getElementById('productName').value = '';
            document.getElementById('productDescription').value = '';
            document.getElementById('productPrice').value = '';
            document.getElementById('productStock').value = '';
            document.getElementById('productCategory').value = '';
            document.getElementById('productModal').style.display = 'block';
        }

        function showEditProductForm(product) {
            if (typeof product === 'string') {
                product = JSON.parse(product);
            }
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
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelector('.ps-close').onclick = function() {
                document.getElementById('productModal').style.display = 'none';
            }
            window.onclick = function(event) {
                if (event.target == document.getElementById('productModal')) {
                    document.getElementById('productModal').style.display = 'none';
                }
            }
        });
    </script>
    <?php
}