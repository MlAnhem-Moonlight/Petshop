<?php
function petshop_suppliers_page() {
    if (!petshop_is_logged_in() || $_SESSION['ps_user_role'] !== 'admin') {
        wp_redirect(admin_url('admin.php?page=petshop-management'));
        exit;
    }
    global $wpdb;
    $table_suppliers = $wpdb->prefix . 'petshop_suppliers';

    // Handle add, edit, delete
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        $name = sanitize_text_field($_POST['name'] ?? '');
        $phone = sanitize_text_field($_POST['phone'] ?? '');
        $email = sanitize_email($_POST['email'] ?? '');
        $address = sanitize_text_field($_POST['address'] ?? '');
        $note = sanitize_textarea_field($_POST['note'] ?? '');

        switch ($_POST['action']) {
            case 'add_supplier':
                if ($name) {
                    $wpdb->insert($table_suppliers, [
                        'name' => $name,
                        'phone' => $phone,
                        'email' => $email,
                        'address' => $address,
                        'note' => $note
                    ]);
                }
                break;
            case 'edit_supplier':
                $id = intval($_POST['supplier_id']);
                if ($id && $name) {
                    $wpdb->update($table_suppliers, [
                        'name' => $name,
                        'phone' => $phone,
                        'email' => $email,
                        'address' => $address,
                        'note' => $note
                    ], ['id' => $id]);
                }
                break;
            case 'delete_supplier':
                $id = intval($_POST['supplier_id']);
                if ($id) {
                    $wpdb->delete($table_suppliers, ['id' => $id]);
                }
                break;
        }
        // Redirect to avoid resubmission
        echo '<script>location.href=location.href;</script>';
        exit;
    }

    // Xử lý tìm kiếm
    $search = isset($_GET['s']) ? trim(sanitize_text_field($_GET['s'])) : '';
    $where = '';
    $params = [];
    if ($search !== '') {
        $where = "WHERE name LIKE %s OR phone LIKE %s OR email LIKE %s OR address LIKE %s";
        $like = '%' . $wpdb->esc_like($search) . '%';
        $params = [$like, $like, $like, $like];
    }
    $sql = "SELECT * FROM $table_suppliers " . ($where ? $where : '') . " ORDER BY id ASC";
    $suppliers = $params ? $wpdb->get_results($wpdb->prepare($sql, ...$params)) : $wpdb->get_results($sql);

    // For edit form
    $edit_supplier = null;
    if (isset($_GET['edit']) && intval($_GET['edit'])) {
        $edit_supplier = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_suppliers WHERE id=%d", intval($_GET['edit'])));
    }
    ?>
    <style>
        .ps-sup-wrap {
            max-width: 950px;
            margin: 32px auto 0 auto;
            background: #f7fff7;
            border-radius: 18px;
            box-shadow: 0 4px 24px #e8f5e9;
            padding: 32px 36px 28px 36px;
        }
        .ps-sup-title {
            color: #388e3c;
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 18px;
        }
        .ps-sup-form {
            background: #e8f5e9;
            border-radius: 10px;
            padding: 18px 22px 10px 22px;
            margin-bottom: 26px;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: flex-end;
        }
        .ps-sup-form input[type="text"],
        .ps-sup-form input[type="email"] {
            border: 1px solid #b2dfdb;
            border-radius: 6px;
            padding: 7px 10px;
            font-size: 15px;
            background: #fff;
            min-width: 120px;
        }
        .ps-sup-form button,
        .ps-sup-form a.button {
            margin-left: 8px;
        }
        .ps-sup-search {
            margin-bottom: 18px;
            display: flex;
            justify-content: flex-end;
        }
        .ps-sup-search input[type="text"] {
            border: 1px solid #b2dfdb;
            border-radius: 6px;
            padding: 7px 10px;
            font-size: 15px;
            background: #fff;
            min-width: 220px;
        }
        .ps-sup-search button {
            margin-left: 8px;
        }
        .ps-sup-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 10px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 2px 8px #e8f5e9;
        }
        .ps-sup-table th {
            background: linear-gradient(90deg, #8bc34a 60%, #4caf50 100%);
            color: #fff;
            font-weight: bold;
            text-align: left;
            padding: 14px 10px;
            font-size: 15px;
        }
        .ps-sup-table tr:nth-child(even) {
            background-color: #f2f9eb;
        }
        .ps-sup-table tr:nth-child(odd) {
            background-color: #ffffff;
        }
        .ps-sup-table tr:hover {
            background-color: #e8f5e9;
        }
        .ps-sup-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 15px;
            vertical-align: middle;
        }
        @media (max-width: 900px) {
            .ps-sup-wrap { padding: 12px 4px; }
            .ps-sup-form { flex-direction: column; gap: 8px; }
            .ps-sup-table th, .ps-sup-table td { font-size: 13px; padding: 8px 5px; }
        }
    </style>
    <div class="ps-sup-wrap">
        <div class="ps-sup-title">Quản lý nhà cung cấp</div>
        <form method="get" class="ps-sup-search" action="">
            <input type="hidden" name="page" value="ps-suppliers">
            <input type="text" name="s" placeholder="Tìm kiếm nhà cung cấp..." value="<?php echo esc_attr($search); ?>">
            <button type="submit" class="button">Tìm kiếm</button>
            <?php if ($search): ?>
                <a href="<?php echo admin_url('admin.php?page=ps-suppliers'); ?>" class="button">Xoá lọc</a>
            <?php endif; ?>
        </form>
        <form method="post" class="ps-sup-form">
            <input type="hidden" name="action" value="<?php echo $edit_supplier ? 'edit_supplier' : 'add_supplier'; ?>">
            <?php if ($edit_supplier): ?>
                <input type="hidden" name="supplier_id" value="<?php echo esc_attr($edit_supplier->id); ?>">
            <?php endif; ?>
            <input type="text" name="name" placeholder="Tên nhà cung cấp" value="<?php echo esc_attr($edit_supplier->name ?? ''); ?>" required>
            <input type="text" name="phone" placeholder="Số điện thoại" value="<?php echo esc_attr($edit_supplier->phone ?? ''); ?>">
            <input type="email" name="email" placeholder="Email" value="<?php echo esc_attr($edit_supplier->email ?? ''); ?>">
            <input type="text" name="address" placeholder="Địa chỉ" value="<?php echo esc_attr($edit_supplier->address ?? ''); ?>" style="min-width:180px;">
            <input type="text" name="note" placeholder="Ghi chú" value="<?php echo esc_attr($edit_supplier->note ?? ''); ?>">
            <button type="submit" class="button button-primary">
                <?php echo $edit_supplier ? 'Cập nhật' : 'Thêm mới'; ?>
            </button>
            <?php if ($edit_supplier): ?>
                <a href="<?php echo admin_url('admin.php?page=ps-suppliers'); ?>" class="button">Huỷ</a>
            <?php endif; ?>
        </form>
        <table class="ps-sup-table wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th style="width:40px;">ID</th>
                    <th>Tên nhà cung cấp</th>
                    <th>Điện thoại</th>
                    <th>Email</th>
                    <th>Địa chỉ</th>
                    <th>Ghi chú</th>
                    <th style="width:120px;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($suppliers): foreach ($suppliers as $s): ?>
                <tr>
                    <td><?php echo $s->id; ?></td>
                    <td><?php echo esc_html($s->name); ?></td>
                    <td><?php echo esc_html($s->phone); ?></td>
                    <td><?php echo esc_html($s->email); ?></td>
                    <td><?php echo esc_html($s->address); ?></td>
                    <td><?php echo esc_html($s->note); ?></td>
                    <td>
                        <a href="<?php echo admin_url('admin.php?page=ps-suppliers&edit=' . $s->id); ?>" class="button">Sửa</a>
                        <form method="post" style="display:inline;" onsubmit="return confirm('Xoá nhà cung cấp này?');">
                            <input type="hidden" name="action" value="delete_supplier">
                            <input type="hidden" name="supplier_id" value="<?php echo $s->id; ?>">
                            <button type="submit" class="button">Xoá</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="7" style="text-align:center;">Chưa có nhà cung cấp nào.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}