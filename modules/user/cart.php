<?php
function petshop_user_cart_page() {
    global $wpdb;
    if (!is_user_logged_in()) {
        echo '<div style="padding:40px;text-align:center;font-size:1.2em;color:#0d8abc;">Bạn cần đăng nhập để xem giỏ hàng.</div>';
        return;
    }
    $user_id = get_current_user_id();
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
        // Checkout (clear cart)
        if (isset($_POST['checkout'])) {
            $wpdb->delete($table_cart_items, ['cart_id' => $cart->id]);
            echo "<script>alert('Cảm ơn bạn đã đặt hàng!');location.reload();</script>";
            exit;
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
    </style>
    <div class="wrap">
        <div class="ps-cart-header-bar">
            <div class="ps-cart-title">🛒 Giỏ hàng của bạn</div>
        </div>
        <div class="ps-cart-table-wrap">
            <?php if (!empty($cart_items)): ?>
                <form method="post">
                    <table class="ps-cart-table">
                        <thead>
                            <tr>
                                <th>Ảnh</th>
                                <th>Tên sản phẩm</th>
                                <th>Giá</th>
                                <th>Số lượng</th>
                                <th>Thành tiền</th>
                                <th>Xóa</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php $total = 0; foreach ($cart_items as $item): 
                            $subtotal = $item->price * $item->quantity;
                            $total += $subtotal;
                        ?>
                            <tr>
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
                                    <input type="number" name="qty[<?php echo $item->id; ?>]" value="<?php echo intval($item->quantity); ?>" min="1" style="width:50px;padding:4px 8px;border-radius:6px;border:1.5px solid #ffe066;background:#fffbe7;">
                                </td>
                                <td><?php echo number_format($subtotal, 0, ',', '.'); ?> đ</td>
                                <td>
                                    <button type="submit" name="remove" value="<?php echo $item->id; ?>" style="background:#ff9800;">&times;</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <!-- Actions moved outside the table -->
                    <div class="ps-cart-actions-bar" style="justify-content: flex-end; margin-top: 18px;">
                        <div class="ps-cart-total" style="margin-right: 18px;">Tổng cộng: <?php echo number_format($total, 0, ',', '.'); ?> đ</div>
                        <div class="ps-cart-actions">
                            <button type="submit" name="update_cart">Cập nhật</button>
                            <button type="submit" name="checkout" style="background:#43c465;">Thanh toán</button>
                        </div>
                    </div>
                </form>
            <?php else: ?>
                <div class="ps-cart-empty">Giỏ hàng của bạn đang trống.</div>
            <?php endif; ?>
        </div>
    </div>
<?php
}
petshop_user_cart_page();