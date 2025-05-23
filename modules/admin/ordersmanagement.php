<?php
// filepath: c:\xampp\htdocs\wordpress\wp-content\plugins\Petshop-tuan\modules\admin\ordersmanagement.php

function petshop_admin_orders_page() {
    if (!petshop_is_logged_in() || $_SESSION['ps_user_role'] !== 'admin') {
        echo '<div style="padding:40px;text-align:center;">Bạn không có quyền truy cập.</div>';
        return;
    }
    global $wpdb;
    $table_orders = $wpdb->prefix . 'petshop_orders';
    $table_order_items = $wpdb->prefix . 'petshop_order_items';
    $table_products = $wpdb->prefix . 'petshop_products';
    $table_users = $wpdb->prefix . 'petshop_users';

    // Handle update status
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
        $order_id = intval($_POST['order_id']);
        $status = in_array($_POST['status'], ['pending', 'completed', 'cancelled']) ? $_POST['status'] : 'pending';
        $wpdb->update($table_orders, ['status' => $status], ['id' => $order_id]);
        echo '<div style="color:green;text-align:center;margin:10px 0;">Đã cập nhật trạng thái đơn hàng!</div>';
    }

    // Xử lý tìm kiếm
    $search = isset($_GET['search']) ? trim(sanitize_text_field($_GET['search'])) : '';

    // Paging
    $items_per_page = 10;
    $current_page = isset($_GET['page_num']) ? max(1, intval($_GET['page_num'])) : 1;
    $offset = ($current_page - 1) * $items_per_page;

    // Thêm điều kiện tìm kiếm vào truy vấn
    $where = "1=1";
    $params = [];
    if ($search !== '') {
        $where .= " AND (o.id = %d OR u.username LIKE %s OR o.customer_phone LIKE %s)";
        $params[] = intval($search);
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
    }

    // Đếm tổng số đơn hàng (có tìm kiếm)
    $total_items = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_orders o
         LEFT JOIN $table_users u ON o.user_id = u.id
         WHERE $where", ...$params
    ));
    $total_pages = ceil($total_items / $items_per_page);

    // Lấy danh sách đơn hàng (có tìm kiếm)
    $args = array_merge($params, [$items_per_page, $offset]);
    $orders = $wpdb->get_results($wpdb->prepare(
        "SELECT o.*, u.username FROM $table_orders o
         LEFT JOIN $table_users u ON o.user_id = u.id
         WHERE $where
         ORDER BY o.created_at DESC
         LIMIT %d OFFSET %d",
        ...$args
    ));
    ?>
    <style>
    .ps-admin-orders-bg {
        background: #f8fff5;
        min-height: 100vh;
        padding: 0;
        margin: 0;
    }
    .ps-admin-orders-wrap {
        max-width: 1200px;
        margin: 40px auto;
        background: #f6fff2;
        border-radius: 22px;
        box-shadow: 0 8px 32px rgba(76,175,80,0.13);
        padding: 0 0 36px 0;
    }
    .ps-admin-orders-header {
        color: #388e3c;
        font-size: 2.1em;
        font-weight: 700;
        padding: 32px 36px 0 36px;
        letter-spacing: 1px;
    }
    .ps-admin-orders-table-wrap {
        margin: 0 32px;
    }
    .ps-admin-orders-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        background: #fff;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(76,175,80,0.08);
    }
    .ps-admin-orders-table th {
        background: #8bc34a;
        color: #fff;
        font-weight: 700;
        padding: 16px 8px;
        font-size: 17px;
        border-bottom: 2px solid #c5e1a5;
        text-align: center;
    }
    .ps-admin-orders-table td {
        padding: 13px 8px;
        font-size: 15px;
        border-bottom: 1px solid #e0e0e0;
        text-align: center;
        vertical-align: middle;
    }
    .ps-admin-orders-table tr:last-child td {
        border-bottom: none;
    }
    .ps-admin-orders-table tr:nth-child(even) td {
        background: #f1f8e9;
    }
    .ps-admin-orders-status {
        font-weight: 700;
        padding: 5px 16px;
        border-radius: 16px;
        display: inline-block;
        font-size: 15px;
        min-width: 90px;
    }
    .ps-admin-orders-status.pending {
        background: #fffde7;
        color: #ff9800;
    }
    .ps-admin-orders-status.completed {
        background: #e3fcef;
        color: #00a854;
    }
    .ps-admin-orders-status.cancelled {
        background: #fff1f0;
        color: #f5222d;
    }
    .ps-admin-orders-form {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .ps-admin-orders-form select {
        padding: 6px 14px;
        border-radius: 8px;
        border: 1.5px solid #1976d2;
        background: #fafbfc;
        color: #1976d2;
        font-size: 15px;
        font-weight: 500;
        outline: none;
        transition: border-color 0.2s;
        min-width: 120px;
        height: 36px;
    }
    .ps-admin-orders-form select:focus {
        border-color: #388e3c;
    }
    .ps-admin-orders-form button {
        padding: 6px 18px;
        border-radius: 8px;
        border: 1.5px solid #1976d2;
        background: #fafbfc;
        color: #1976d2;
        font-size: 15px;
        font-weight: 500;
        cursor: pointer;
        transition: border-color 0.2s, color 0.2s, background 0.2s;
        height: 36px;
        box-shadow: 0 2px 8px rgba(25, 118, 210, 0.04);
        margin-left: 0;
    }
    .ps-admin-orders-form button:hover {
        border-color: #0d47a1;
        color: #fff;
        background: #1976d2;
    }
    .ps-admin-orders-pagination {
        text-align: center;
        margin: 22px 0 0 0;
    }
    .ps-admin-orders-pagination a, .ps-admin-orders-pagination span {
        display: inline-block;
        padding: 8px 18px;
        margin: 0 2px;
        border-radius: 8px;
        font-weight: 700;
        text-decoration: none;
        font-size: 15px;
    }
    .ps-admin-orders-pagination .active {
        background: #8bc34a;
        color: #fff;
    }
    .ps-admin-orders-pagination a {
        background: #f1f8e9;
        color: #388e3c;
        border: 1px solid #c5e1a5;
    }
    .ps-admin-orders-searchbar {
        padding: 0 32px 18px 32px;
        margin-top: 10px;
        display: flex;
        justify-content: flex-start;
    }
    .ps-admin-orders-searchform {
        display: flex;
        gap: 10px;
        align-items: center;
        width: 100%;
        max-width: 420px;
    }
    .ps-admin-orders-searchform input[type="text"] {
        flex: 1;
        max-width: 320px;
        padding: 10px 16px;
        border-radius: 10px;
        border: 1.5px solid #8bc34a;
        font-size: 15px;
        background: #fff;
        transition: border-color 0.2s;
    }
    .ps-admin-orders-searchform input[type="text"]:focus {
        border-color: #388e3c;
        outline: none;
    }
    .ps-admin-orders-searchform button {
        padding: 9px 22px;
        border-radius: 10px;
        border: 1.5px solid #1976d2;
        background: #fff;
        color: #1976d2;
        font-size: 15px;
        font-weight: 500;
        cursor: pointer;
        transition: border-color 0.2s, color 0.2s, background 0.2s;
    }
    .ps-admin-orders-searchform button:hover {
        border-color: #0d47a1;
        color: #fff;
        background: #1976d2;
    }
    details { text-align: left;}
    summary { cursor: pointer; color: #1976d2; font-weight: 500; font-size: 15px;}
    details[open] summary { color: #388e3c; }
    details ul { margin: 0 0 0 8px; padding: 0 0 0 12px;}
    details li { font-size: 15px; margin-bottom: 2px;}
    details div { margin-top: 8px; font-size: 0.97em; color: #888;}
    @media (max-width: 900px) {
        .ps-admin-orders-wrap, .ps-admin-orders-table-wrap { max-width: 99vw; margin: 0; }
        .ps-admin-orders-header { font-size: 1.2em; padding: 18px 8px 0 8px;}
        .ps-admin-orders-table th, .ps-admin-orders-table td { font-size: 13px; padding: 7px 4px;}
        .ps-admin-orders-form { flex-direction: column; gap: 4px;}
    }
    </style>
    <div class="ps-admin-orders-bg">
        <div class="ps-admin-orders-wrap">
            <div class="ps-admin-orders-header">Quản lý đơn hàng</div>
            <div class="ps-admin-orders-searchbar">
                <form method="get" class="ps-admin-orders-searchform">
                    <input type="hidden" name="page" value="ps-admin-orders">
                    <input type="text" name="search" value="<?php echo esc_attr($search); ?>" placeholder="Tìm theo mã đơn, tên khách, SĐT...">
                    <button type="submit">Tìm kiếm</button>
                </form>
            </div>
            <div class="ps-admin-orders-table-wrap">
            <table class="ps-admin-orders-table">
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Khách hàng</th>
                        <th>Ngày đặt</th>
                        <th>Trạng thái</th>
                        <th>Tổng tiền</th>
                        <th>Chi tiết</th>
                        <th>Cập nhật trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>#<?php echo $order->id; ?></td>
                        <td><?php echo esc_html($order->username); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($order->created_at)); ?></td>
                        <td>
                            <span class="ps-admin-orders-status <?php echo esc_attr($order->status); ?>">
                                <?php
                                    if ($order->status == 'pending') echo 'Chờ xử lý';
                                    elseif ($order->status == 'completed') echo 'Hoàn thành';
                                    elseif ($order->status == 'cancelled') echo 'Đã hủy';
                                    else echo esc_html($order->status);
                                ?>
                            </span>
                        </td>
                        <td><?php echo number_format($order->total_amount, 0, ',', '.'); ?> đ</td>
                        <td style="text-align:left;">
                            <details>
                                <summary>Xem</summary>
                                <ul style="margin:0;padding-left:18px;">
                                <?php
                                $items = $wpdb->get_results($wpdb->prepare(
                                    "SELECT oi.*, p.name FROM $table_order_items oi
                                     LEFT JOIN $table_products p ON oi.product_id = p.id
                                     WHERE oi.order_id = %d", $order->id
                                ));
                                if ($items) {
                                    foreach ($items as $item) {
                                        echo '<li>' . esc_html($item->name) . ' x ' . intval($item->quantity) . ' - ' . number_format($item->price, 0, ',', '.') . ' đ</li>';
                                    }
                                } else {
                                    echo '<li>Không có sản phẩm nào.</li>';
                                }
                                ?>
                                </ul>
                                <div style="margin-top:8px;font-size:0.97em;color:#888;">
                                    Người nhận: <?php echo esc_html($order->customer_name); ?><br>
                                    Địa chỉ: <?php echo esc_html($order->customer_address); ?><br>
                                    SĐT: <?php echo esc_html($order->customer_phone); ?>
                                </div>
                            </details>
                        </td>
                        <td>
                            <form class="ps-admin-orders-form" method="post" style="margin:0;">
                                <input type="hidden" name="order_id" value="<?php echo $order->id; ?>">
                                <select name="status">
                                    <option value="pending" <?php selected($order->status, 'pending'); ?>>Chờ xử lý</option>
                                    <option value="completed" <?php selected($order->status, 'completed'); ?>>Hoàn thành</option>
                                    <option value="cancelled" <?php selected($order->status, 'cancelled'); ?>>Đã hủy</option>
                                </select>
                                <button type="submit">Cập nhật</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <?php if ($total_pages > 1): ?>
            <div class="ps-admin-orders-pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i == $current_page): ?>
                        <span class="active"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=ps-admin-orders&page_num=<?php echo $i; ?><?php if ($search) echo '&search=' . urlencode($search); ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
<?php
}
petshop_admin_orders_page();