<?php
function petshop_user_my_orders_page() {
    if (!is_user_logged_in()) {
        echo '<div style="padding:40px;text-align:center;font-size:1.2em;color:#0d8abc;">Bạn cần đăng nhập để xem đơn hàng.</div>';
        return;
    }
    global $wpdb;
    $user_id = isset($_SESSION['ps_user_id']) ? intval($_SESSION['ps_user_id']) : 0;
    $table_orders = $wpdb->prefix . 'petshop_orders';
    $table_order_items = $wpdb->prefix . 'petshop_order_items';
    $table_products = $wpdb->prefix . 'petshop_products';

    // Filter logic
    $allowed_sort = ['id', 'created_at', 'total_amount'];
    $sort = isset($_GET['sort']) && in_array($_GET['sort'], $allowed_sort) ? $_GET['sort'] : 'created_at';
    $order = (isset($_GET['order']) && strtolower($_GET['order']) === 'asc') ? 'ASC' : 'DESC';
    $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';

    // Paging logic like shop.php
    $items_per_page = 5;
    $current_page = isset($_GET['order_page']) ? max(1, intval($_GET['order_page'])) : 1;
    $offset = ($current_page - 1) * $items_per_page;

    // Build WHERE clause for status filter
    $where = "user_id = %d";
    $where_args = [$user_id];
    if ($status_filter && in_array($status_filter, ['pending', 'completed', 'cancelled'])) {
        $where .= " AND status = %s";
        $where_args[] = $status_filter;
    }

    // Get total count for pagination
    $count_sql = "SELECT COUNT(*) FROM $table_orders WHERE $where";
    $total_items = $wpdb->get_var($wpdb->prepare($count_sql, ...$where_args));
    $total_pages = ceil($total_items / $items_per_page);

    // Get orders for current page with sorting and status filter
    $orders_sql = "SELECT * FROM $table_orders WHERE $where ORDER BY $sort $order LIMIT %d OFFSET %d";
    $orders_args = array_merge($where_args, [$items_per_page, $offset]);
    $orders_page = $wpdb->get_results($wpdb->prepare($orders_sql, ...$orders_args));

    // Helper for filter links (not used with dropdown, but kept for reference)
    function ps_orders_filter_link($label, $sort, $current_sort, $current_order) {
        $order = ($current_sort === $sort && $current_order === 'ASC') ? 'desc' : 'asc';
        $query = $_GET;
        $query['sort'] = $sort;
        $query['order'] = $order;
        $url = '?' . http_build_query($query);
        $arrow = '';
        if ($current_sort === $sort) {
            $arrow = $current_order === 'ASC' ? ' ▲' : ' ▼';
        }
        return '<a href="' . esc_url($url) . '">' . esc_html($label) . $arrow . '</a>';
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
        .ps-orders-filter-bar {
            text-align: right;
            margin: 0 32px 10px 0;
            font-size: 1em;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 10px;
        }
        .ps-orders-filter-bar label {
            font-weight: 700;
            color: #0d8abc;
            margin-right: 6px;
        }
        .ps-orders-filter-bar select {
            padding: 8px 32px 8px 14px;
            border-radius: 24px;
            border: 1.5px solid #ffe066;
            background: linear-gradient(90deg, #fffbe7 60%, #ffe066 100%);
            color: #0d8abc;
            font-weight: 700;
            font-size: 1em;
            margin-left: 0;
            margin-right: 0;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url('data:image/svg+xml;utf8,<svg fill="gray" height="18" viewBox="0 0 20 20" width="18" xmlns="http://www.w3.org/2000/svg"><path d="M7.293 8.293a1 1 0 011.414 0L10 9.586l1.293-1.293a1 1 0 111.414 1.414l-2 2a1 1 0 01-1.414 0l-2-2a1 1 0 010-1.414z"/></svg>');
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 18px 18px;
            transition: border-color 0.2s, box-shadow 0.2s;
            box-shadow: 0 2px 8px rgba(255, 214, 0, 0.08);
        }
        .ps-orders-filter-bar select:focus {
            border-color: #ff9800;
            outline: none;
            box-shadow: 0 0 0 2px #ffe06655;
        }
        .ps-orders-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 1em;
        }
        .ps-orders-table th, .ps-orders-table td {
            padding: 12px 16px;
            text-align: center;
        }
        .ps-orders-table th {
            background: #ffe066;
            color: #0d8abc;
            font-weight: 700;
        }
        .ps-orders-table tr:nth-child(even) td {
            background: #fffbe7;
        }
        .ps-order-status {
            font-weight: 700;
            color: #43c465;
        }
        .ps-order-status.pending { color: #ff9800; }
        .ps-order-status.completed { color: #43c465; }
        .ps-order-status.cancelled { color: #e53935; }
        .ps-order-items-list {
            margin: 0;
            padding-left: 18px;
            text-align: left;
        }
        .ps-order-items-list li {
            margin-bottom: 2px;
        }
        .ps-orders-pagination {
            text-align: center;
            margin: 18px 0 0 0;
        }
        .ps-orders-pagination a, .ps-orders-pagination span {
            display: inline-block;
            padding: 6px 14px;
            margin: 0 2px;
            border-radius: 6px;
            font-weight: 700;
            text-decoration: none;
        }
        .ps-orders-pagination .active {
            background: #ff9800;
            color: #fff;
        }
        .ps-orders-pagination a {
            background: #ffe066;
            color: #0d8abc;
        }
        @media (max-width: 700px) {
            .ps-cart-header-bar, .ps-cart-table-wrap {
                max-width: 100vw;
                padding-left: 0;
                padding-right: 0;
            }
            .ps-orders-table th, .ps-orders-table td {
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
            <div class="ps-cart-title">📦 Đơn hàng của bạn</div>
        </div>
        <div class="ps-cart-table-wrap">
            <form class="ps-orders-filter-bar" method="get">
                <?php
                    // Preserve other GET params
                    foreach ($_GET as $key => $value) {
                        if (!in_array($key, ['sort', 'order', 'order_page', 'status'])) {
                            echo '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '">';
                        }
                    }
                ?>
                <label for="ps-orders-sort">Sắp xếp:</label>
                <select name="sort" id="ps-orders-sort" onchange="this.form.submit()">
                    <option value="id" <?php selected($sort, 'id'); ?>>Mã đơn</option>
                    <option value="created_at" <?php selected($sort, 'created_at'); ?>>Ngày đặt</option>
                    <option value="total_amount" <?php selected($sort, 'total_amount'); ?>>Tổng tiền</option>
                </select>
                <select name="order" onchange="this.form.submit()">
                    <option value="desc" <?php selected(strtolower($order), 'desc'); ?>>Giảm dần</option>
                    <option value="asc" <?php selected(strtolower($order), 'asc'); ?>>Tăng dần</option>
                </select>
                <label for="ps-orders-status">Trạng thái:</label>
                <select name="status" id="ps-orders-status" onchange="this.form.submit()">
                    <option value="" <?php selected($status_filter, ''); ?>>Tất cả</option>
                    <option value="pending" <?php selected($status_filter, 'pending'); ?>>Chờ xử lý</option>
                    <option value="completed" <?php selected($status_filter, 'completed'); ?>>Hoàn thành</option>
                    <option value="cancelled" <?php selected($status_filter, 'cancelled'); ?>>Đã hủy</option>
                </select>
            </form>
            <?php if (!empty($orders_page)): ?>
                <table class="ps-orders-table">
                    <thead>
                        <tr>
                            <th>Mã đơn</th>
                            <th>Ngày đặt</th>
                            <th>Trạng thái</th>
                            <th>Tổng tiền</th>
                            <th>Chi tiết</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($orders_page as $order): ?>
                        <tr>
                            <td>#<?php echo $order->id; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($order->created_at)); ?></td>
                            <td>
                                <span class="ps-order-status <?php echo esc_attr($order->status); ?>">
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
                                    <summary style="cursor:pointer;">Xem</summary>
                                    <ul class="ps-order-items-list">
                                    <?php
                                    $items = $wpdb->get_results($wpdb->prepare(
                                        "SELECT oi.*, p.name FROM $table_order_items oi
                                         LEFT JOIN $table_products p ON oi.product_id = p.id
                                         WHERE oi.order_id = %d", $order->id
                                    ));
                                    if ($items) {
                                        foreach ($items as $item) {
                                            echo '<li>';
                                            echo esc_html($item->name) . ' x ' . intval($item->quantity) . ' - ' . number_format($item->price, 0, ',', '.') . ' đ';
                                            echo '</li>';
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
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if ($total_pages > 1): ?>
                <div class="ps-orders-pagination">
                    <?php
                    $query_args = $_GET;
                    for ($i = 1; $i <= $total_pages; $i++):
                        $query_args['order_page'] = $i;
                        $page_url = '?' . http_build_query($query_args);
                    ?>
                        <?php if ($i == $current_page): ?>
                            <span class="active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="<?php echo esc_url($page_url); ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="ps-cart-empty">Bạn chưa có đơn hàng nào.</div>
            <?php endif; ?>
        </div>
    </div>
<?php
}
petshop_user_my_orders_page();