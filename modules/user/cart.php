<?php
function petshop_user_cart_page() {
    global $wpdb;
<<<<<<< Updated upstream
    if (!is_user_logged_in()) {
        echo '<div style="padding:40px;text-align:center;font-size:1.2em;color:#0d8abc;">Bạn cần đăng nhập để xem giỏ hàng.</div>';
        return;
    }
    $user_id = get_current_user_id();
=======
    // Get user id from session if available, otherwise 0
    $user_id = isset($_SESSION['ps_user_id']) ? intval($_SESSION['ps_user_id']) : 0;
    if (!$user_id) {
        echo '<div style="padding:40px;text-align:center;font-size:1.2em;color:#0d8abc;">Bạn cần đăng nhập để xem giỏ hàng.</div>';
        return;
    }
>>>>>>> Stashed changes
    $table_carts = $wpdb->prefix . 'petshop_carts';
    $table_cart_items = $wpdb->prefix . 'petshop_cart_items';
    $table_products = $wpdb->prefix . 'petshop_products';

    // Get user's cart
    $cart = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_carts WHERE user_id=%d", $user_id));
    $cart_items = [];
    if ($cart) {
        $cart_items = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT ci.*, p.name, p.price, p.image_url 
                 FROM $table_cart_items ci
                 LEFT JOIN $table_products p ON ci.product_id = p.id
                 WHERE ci.cart_id = %d", $cart->id
            )
        );
    }

<<<<<<< Updated upstream
=======
    // Pagination
    $items_per_page = 5;
    $current_page = isset($_GET['cart_page']) ? max(1, intval($_GET['cart_page'])) : 1;
    $total_items = count($cart_items);
    $total_pages = ceil($total_items / $items_per_page);
    $start = ($current_page - 1) * $items_per_page;
    $cart_items_page = array_slice($cart_items, $start, $items_per_page);

>>>>>>> Stashed changes
    // Handle cart actions (remove, update, checkout)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $cart) {
        // Remove item
        if (isset($_POST['remove'])) {
            $wpdb->delete($table_cart_items, ['id' => intval($_POST['remove'])]);
            echo "<script>location.reload();</script>";
            exit;
        }
        // Update quantity
        if (isset($_POST['update_cart']) && isset($_POST['qty'])) {
            foreach ($_POST['qty'] as $item_id => $qty) {
                $wpdb->update($table_cart_items, ['quantity' => max(1, intval($qty))], ['id' => intval($item_id)]);
            }
            echo "<script>location.reload();</script>";
            exit;
        }
<<<<<<< Updated upstream
        // Checkout (clear cart)
        if (isset($_POST['checkout'])) {
            $wpdb->delete($table_cart_items, ['cart_id' => $cart->id]);
            echo "<script>alert('Cảm ơn bạn đã đặt hàng!');location.reload();</script>";
            exit;
=======
        // Checkout only selected items
        if (isset($_POST['checkout_selected']) && isset($_POST['selected_items']) && isset($_POST['customer_name'])) {
            $selected_ids = array_map('intval', explode(',', $_POST['selected_items']));
            if (!empty($selected_ids)) {
                // Calculate total and get items
                $placeholders = implode(',', array_fill(0, count($selected_ids), '%d'));
                $items = $wpdb->get_results($wpdb->prepare(
                    "SELECT ci.*, p.price FROM $table_cart_items ci
                     LEFT JOIN $table_products p ON ci.product_id = p.id
                     WHERE ci.cart_id = %d AND ci.id IN ($placeholders)",
                    array_merge([$cart->id], $selected_ids)
                ));
                $total_amount = 0;
                foreach ($items as $item) {
                    $total_amount += $item->price * $item->quantity;
                }
                // Insert order (save payment_method in customer_email if needed)
                $customer_name = sanitize_text_field($_POST['customer_name']);
                $customer_email = sanitize_email($_POST['customer_email']);
                $customer_phone = sanitize_text_field($_POST['customer_phone']);
                $customer_address = sanitize_text_field($_POST['customer_address']);
                $payment_method = isset($_POST['payment_method']) ? sanitize_text_field($_POST['payment_method']) : 'cod';
                $table_orders = $wpdb->prefix . 'petshop_orders';
                $table_order_items = $wpdb->prefix . 'petshop_order_items';
                $wpdb->insert($table_orders, [
                    'user_id' => $user_id,
                    'total_amount' => $total_amount,
                    'status' => 'pending',
                    'created_at' => current_time('mysql'),
                    'customer_name' => $customer_name,
                    'customer_email' => $customer_email,
                    'customer_phone' => $customer_phone,
                    'customer_address' => $customer_address
                ]);
                $order_id = $wpdb->insert_id;
                foreach ($items as $item) {
                    $wpdb->insert($table_order_items, [
                        'order_id' => $order_id,
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'price' => $item->price
                    ]);
                    $wpdb->delete($table_cart_items, ['id' => $item->id]);
                }
                echo "<script>alert('Cảm ơn bạn đã đặt hàng các sản phẩm đã chọn! Tổng tiền: " . number_format($total_amount, 0, ',', '.') . " đ');location.reload();</script>";
                exit;
            }
>>>>>>> Stashed changes
        }
    }
?>
    <style>
        body, .wrap {
            background: linear-gradient(135deg, #fffbe7 0%, #ffe066 100%);
            min-height: 100vh;
            width: 100%;
            margin: 0;
            padding: 0;
        }
        .wrap {
            background: linear-gradient(120deg, #fffbe7 60%, #ffe066 100%);
            min-height: 100vh;
            padding-bottom: 40px;
        }
        .ps-cart-header-bar {
            background: linear-gradient(90deg, #FFA000 60%, #FF6F00 100%);
            padding: 18px 32px;
            border-radius: 14px 14px 0 0;
            margin-bottom: 0;
            display: flex;
            align-items: center;
            gap: 18px;
            box-shadow: 0 2px 12px rgba(255, 152, 0, 0.18);
            width: 100%;
            max-width: 1100px;
            min-width: 0;
            margin-left: auto;
            margin-right: auto;
            position: relative;
            left: 0;
            right: 0;
        }
        .ps-cart-title {
            font-weight: 700;
            font-size: 1.3em;
            color: #fff;
            letter-spacing: 1px;
        }
        .ps-cart-table-wrap {
            max-width: 700px;
            margin: 32px auto 0 auto;
            background: rgba(255,255,255,0.85);
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(255, 214, 0, 0.10);
            padding: 24px 0;
            position: relative;
            min-height: 200px;
        }
        .ps-cart-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 1em;
        }
        .ps-cart-table th, .ps-cart-table td {
            padding: 12px 16px;
            text-align: center;
        }
        .ps-cart-table th {
            background: #ffe066;
            color: #0d8abc;
            font-weight: 700;
        }
        .ps-cart-table tr:nth-child(even) td {
            background: #fffbe7;
        }
        .ps-cart-table img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            background: #f3f4f6;
        }
        .ps-cart-total {
            text-align: right;
            font-size: 1.15em;
            font-weight: 700;
            color: #ff9800;
            margin: 18px 32px 0 0;
        }
        .ps-cart-empty {
            text-align: center;
            color: #888;
            font-size: 1.1em;
            padding: 40px 0;
        }
        .ps-cart-actions button {
            padding: 7px 18px;
            border-radius: 7px;
            background: linear-gradient(90deg,#0d8abc 60%,#00c6fb 100%);
            color: #fff;
            border: none;
            font-weight: 700;
            font-size: 1em;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(13,138,188,0.08);
            transition: background 0.2s;
            margin: 0 4px;
        }
        .ps-cart-actions button:hover {
            background: linear-gradient(90deg, #00c6fb 60%, #0d8abc 100%);
        }
        .ps-cart-actions-bar {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 12px;
            margin: 18px 32px 0 0;
        }
        .ps-cart-actions-bar .ps-cart-total {
            margin: 0;
            font-size: 1.08em;
        }
        .ps-cart-actions-bar .ps-cart-actions {
            margin: 0;
        }
<<<<<<< Updated upstream
=======
        .ps-cart-checkbox {
            width: 18px;
            height: 18px;
        }
        .ps-cart-pagination {
            text-align: center;
            margin: 18px 0 0 0;
        }
        .ps-cart-pagination a, .ps-cart-pagination span {
            display: inline-block;
            padding: 6px 14px;
            margin: 0 2px;
            border-radius: 6px;
            font-weight: 700;
            text-decoration: none;
        }
        .ps-cart-pagination .active {
            background: #ff9800;
            color: #fff;
        }
        .ps-cart-pagination a {
            background: #ffe066;
            color: #0d8abc;
        }
>>>>>>> Stashed changes
        @media (max-width: 700px) {
            .ps-cart-header-bar, .ps-cart-table-wrap {
                max-width: 100vw;
                padding-left: 0;
                padding-right: 0;
            }
            .ps-cart-table th, .ps-cart-table td {
                padding: 8px 4px;
                font-size: 0.95em;
            }
        }
        @media (max-width: 800px) {
            .ps-cart-table-wrap {
                max-width: 98vw;
            }
        }
<<<<<<< Updated upstream
=======
        .ps-modal-overlay {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0; top: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.25);
            justify-content: center;
            align-items: center;
        }
        .ps-modal-overlay.active {
            display: flex;
        }
        .ps-modal {
            background: #fffbe7;
            border-radius: 18px;
            box-shadow: 0 4px 32px rgba(0,0,0,0.12);
            padding: 32px 28px 24px 28px;
            min-width: 320px;
            max-width: 95vw;
            max-height: 90vh;
            position: relative;
        }
        .ps-modal h3 {
            margin-top: 0;
            color: #0d8abc;
            font-size: 1.25em;
            font-weight: 700;
            margin-bottom: 18px;
        }
        .ps-modal label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #0d8abc;
        }
        .ps-modal input, .ps-modal textarea, .ps-modal select {
            width: 100%;
            padding: 8px 12px;
            margin-bottom: 14px;
            border-radius: 8px;
            border: 1.5px solid #ffe066;
            background: #fff;
            font-size: 1em;
        }
        .ps-modal .ps-modal-actions {
            text-align: right;
            margin-top: 8px;
        }
        .ps-modal .ps-modal-actions button {
            padding: 7px 18px;
            border-radius: 7px;
            background: linear-gradient(90deg,#0d8abc 60%,#00c6fb 100%);
            color: #fff;
            border: none;
            font-weight: 700;
            font-size: 1em;
            cursor: pointer;
            margin-left: 8px;
        }
        .ps-modal .ps-modal-actions button.ps-modal-cancel {
            background: #ff9800;
        }
        .ps-modal-total {
            font-weight: 700;
            color: #ff9800;
            font-size: 1.08em;
            margin-bottom: 10px;
            text-align: right;
        }
>>>>>>> Stashed changes
    </style>
    <div class="wrap">
        <div class="ps-cart-header-bar">
            <div class="ps-cart-title">🛒 Giỏ hàng của bạn</div>
        </div>
        <div class="ps-cart-table-wrap">
<<<<<<< Updated upstream
            <?php if (!empty($cart_items)): ?>
                <form method="post">
                    <table class="ps-cart-table">
                        <thead>
                            <tr>
=======
            <?php if (!empty($cart_items_page)): ?>
                <form method="post" id="ps-cart-form">
                    <table class="ps-cart-table">
                        <thead>
                            <tr>
                                <th></th>
>>>>>>> Stashed changes
                                <th>Ảnh</th>
                                <th>Tên sản phẩm</th>
                                <th>Giá</th>
                                <th>Số lượng</th>
                                <th>Thành tiền</th>
                                <th>Xóa</th>
                            </tr>
                        </thead>
                        <tbody>
<<<<<<< Updated upstream
                        <?php $total = 0; foreach ($cart_items as $item): 
                            $subtotal = $item->price * $item->quantity;
                            $total += $subtotal;
                        ?>
                            <tr>
=======
                        <?php $total = 0; foreach ($cart_items_page as $item): 
                            $subtotal = $item->price * $item->quantity;
                            $total += $subtotal;
                        ?>
                            <tr data-item-id="<?php echo $item->id; ?>" data-price="<?php echo $item->price; ?>">
                                <td>
                                    <input type="checkbox" class="ps-cart-checkbox" name="selected_items[]" value="<?php echo $item->id; ?>">
                                </td>
>>>>>>> Stashed changes
                                <td>
                                    <?php if (!empty($item->image_url)): ?>
                                        <img src="<?php echo esc_url($item->image_url); ?>" alt="">
                                    <?php else: ?>
                                        <div class="no-img" style="width:60px;height:60px;line-height:60px;background:#fff;border:1.5px dashed #ffe066;color:#bbb;">No Image</div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html($item->name); ?></td>
                                <td><?php echo number_format($item->price, 0, ',', '.'); ?> đ</td>
                                <td>
<<<<<<< Updated upstream
                                    <input type="number" name="qty[<?php echo $item->id; ?>]" value="<?php echo intval($item->quantity); ?>" min="1" style="width:50px;padding:4px 8px;border-radius:6px;border:1.5px solid #ffe066;background:#fffbe7;">
                                </td>
                                <td><?php echo number_format($subtotal, 0, ',', '.'); ?> đ</td>
=======
                                    <input type="number" name="qty[<?php echo $item->id; ?>]" value="<?php echo intval($item->quantity); ?>" min="1" style="width:50px;padding:4px 8px;border-radius:6px;border:1.5px solid #ffe066;background:#fffbe7;" class="ps-cart-qty-input">
                                </td>
                                <td class="ps-cart-subtotal"><?php echo number_format($subtotal, 0, ',', '.'); ?> đ</td>
>>>>>>> Stashed changes
                                <td>
                                    <button type="submit" name="remove" value="<?php echo $item->id; ?>" style="background:#ff9800;">&times;</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
<<<<<<< Updated upstream
                    <!-- Actions moved outside the table -->
                    <div class="ps-cart-actions-bar" style="justify-content: flex-end; margin-top: 18px;">
                        <div class="ps-cart-total" style="margin-right: 18px;">Tổng cộng: <?php echo number_format($total, 0, ',', '.'); ?> đ</div>
                        <div class="ps-cart-actions">
                            <button type="submit" name="update_cart">Cập nhật</button>
                            <button type="submit" name="checkout" style="background:#43c465;">Thanh toán</button>
                        </div>
                    </div>
                </form>
=======
                    <div class="ps-cart-actions-bar" style="justify-content: flex-end; margin-top: 18px;">
                        <div class="ps-cart-total" style="margin-right: 18px;">Tổng cộng: <span id="ps-cart-total"><?php echo number_format($total, 0, ',', '.'); ?></span> đ</div>
                        <div class="ps-cart-actions">
                            <button type="button" id="ps-checkout-btn" style="background:#43c465;">Thanh toán sản phẩm đã chọn</button>
                        </div>
                    </div>
                </form>
                <?php if ($total_pages > 1): ?>
                <div class="ps-cart-pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if ($i == $current_page): ?>
                            <span class="active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?cart_page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
>>>>>>> Stashed changes
            <?php else: ?>
                <div class="ps-cart-empty">Giỏ hàng của bạn đang trống.</div>
            <?php endif; ?>
        </div>
    </div>
<<<<<<< Updated upstream
<?php
}
petshop_user_cart_page();
=======
    <!-- Modal for payment info -->
    <div class="ps-modal-overlay" id="ps-modal-overlay">
        <div class="ps-modal">
            <h3>Thông tin thanh toán</h3>
            <form id="ps-modal-form" method="post">
                <input type="hidden" name="checkout_selected" value="1">
                <input type="hidden" name="selected_items" id="ps-modal-selected-items">
                <label for="customer_name">Họ tên *</label>
                <input type="text" name="customer_name" id="customer_name" required>
                <label for="customer_email">Email</label>
                <input type="email" name="customer_email" id="customer_email">
                <label for="customer_phone">Số điện thoại *</label>
                <input type="text" name="customer_phone" id="customer_phone" required>
                <label for="customer_address">Địa chỉ nhận hàng *</label>
                <textarea name="customer_address" id="customer_address" required></textarea>
                <label for="payment_method">Phương thức thanh toán *</label>
                <select name="payment_method" id="payment_method" required>
                    <option value="cod">Thanh toán khi nhận hàng (COD)</option>
                    <option value="bank" disabled>Chuyển khoản ngân hàng (Tạm thời khóa)</option>
                </select>
                <div class="ps-modal-total">
                    Tổng tiền: <span id="ps-modal-total">0</span> đ
                </div>
                <div class="ps-modal-actions">
                    <button type="button" class="ps-modal-cancel" onclick="closeModal()">Hủy</button>
                    <button type="submit">Xác nhận đặt hàng</button>
                </div>
            </form>
        </div>
    </div>
    <script>
    // Modal logic
    function closeModal() {
        document.getElementById('ps-modal-overlay').classList.remove('active');
    }
    document.getElementById('ps-checkout-btn').onclick = function(e) {
        e.preventDefault();
        // Collect selected items
        var checked = document.querySelectorAll('.ps-cart-checkbox:checked');
        if (checked.length === 0) {
            alert('Vui lòng chọn ít nhất một sản phẩm để thanh toán.');
            return;
        }
        var ids = [];
        checked.forEach(function(cb) { ids.push(cb.value); });
        document.getElementById('ps-modal-selected-items').value = ids.join(',');
        updateModalTotal();
        document.getElementById('ps-modal-overlay').classList.add('active');
    };
    // Show total price in modal (only ticked items)
    function updateModalTotal() {
        var total = 0;
        document.querySelectorAll('.ps-cart-checkbox:checked').forEach(function(checkbox) {
            var tr = checkbox.closest('tr');
            var price = parseFloat(tr.getAttribute('data-price'));
            var qty = parseInt(tr.querySelector('.ps-cart-qty-input').value, 10) || 1;
            if (qty < 1) qty = 1;
            total += price * qty;
        });
        document.getElementById('ps-modal-total').textContent = total.toLocaleString('vi-VN');
    }
    document.getElementById('ps-modal-form').onsubmit = function() {
        var name = document.getElementById('customer_name').value.trim();
        var phone = document.getElementById('customer_phone').value.trim();
        var address = document.getElementById('customer_address').value.trim();
        if (!name || !phone || !address) {
            alert('Vui lòng nhập đầy đủ thông tin bắt buộc.');
            return false;
        }
        // Ensure selected_items is set
        var checked = document.querySelectorAll('.ps-cart-checkbox:checked');
        if (checked.length === 0) {
            alert('Vui lòng chọn ít nhất một sản phẩm để thanh toán.');
            return false;
        }
        var ids = [];
        checked.forEach(function(cb) { ids.push(cb.value); });
        document.getElementById('ps-modal-selected-items').value = ids.join(',');
        return true;
    };
    // Dynamic price update (only ticked items are counted)
    function updateCartTotal() {
        var total = 0;
        document.querySelectorAll('.ps-cart-checkbox').forEach(function(checkbox) {
            if (checkbox.checked) {
                var tr = checkbox.closest('tr');
                var price = parseFloat(tr.getAttribute('data-price'));
                var qty = parseInt(tr.querySelector('.ps-cart-qty-input').value, 10) || 1;
                if (qty < 1) qty = 1;
                total += price * qty;
            }
        });
        document.getElementById('ps-cart-total').textContent = total.toLocaleString('vi-VN');
    }
    document.querySelectorAll('.ps-cart-qty-input').forEach(function(input) {
        input.addEventListener('input', function() {
            var tr = input.closest('tr');
            var price = parseFloat(tr.getAttribute('data-price'));
            var qty = parseInt(input.value, 10) || 1;
            if (qty < 1) qty = 1;
            var subtotal = price * qty;
            tr.querySelector('.ps-cart-subtotal').textContent = subtotal.toLocaleString('vi-VN') + ' đ';
            updateCartTotal();
            updateModalTotal();
        });
    });
    document.querySelectorAll('.ps-cart-checkbox').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            updateCartTotal();
            updateModalTotal();
        });
    });
    updateCartTotal();
    </script>
<?php
}
petshop_user_cart_page();
?>
>>>>>>> Stashed changes
