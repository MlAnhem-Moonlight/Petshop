<?php
// filepath: c:\xampp\htdocs\wordpress\wp-content\plugins\Petshop-tuan\modules\user\my-pets.php

if (!petshop_is_logged_in() || $_SESSION['ps_user_role'] !== 'user') {
    wp_redirect(admin_url('admin.php?page=petshop-management'));
    exit;
}

global $wpdb;
$table_pets = $wpdb->prefix . 'petshop_pets';
$user_id = $_SESSION['ps_user_id'];

// Lấy danh sách thú cưng của user
$pets = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $table_pets WHERE user_id = %d ORDER BY created_at DESC",
    $user_id
));
?>
<style>
body, .mypets-bg {
    min-height: 100vh;
    width: 100vw;
    margin: 0;
    padding: 0;
    background: linear-gradient(135deg, #fffbe7 0%, #ffe066 100%) !important;
}
.mypets-bg {
    min-height: 100vh;
    width: 100vw;
    background: none !important;
    padding: 0;
    margin: 0;
}
.mypets-container {
    max-width: 900px;
    margin: 40px auto 0 auto;
    background: transparent;
}
.mypets-header {
    background: linear-gradient(90deg, #FFA000 60%, #FF6F00 100%);
    border-radius: 18px 18px 0 0;
    padding: 18px 40px 12px 40px;
    margin-bottom: 0;
    box-shadow: 0 2px 12px rgba(255, 152, 0, 0.18);
    display: flex;
    align-items: center;
}
.mypets-title {
    color: #fff;
    font-size: 25px;
    font-weight: 700;
    letter-spacing: 1px;
    text-shadow: 0 2px 8px rgba(255,183,77,0.10);
}
.mypets-table-wrap {
    background: #fff;
    border-radius: 0 0 18px 18px;
    box-shadow: 0 4px 24px rgba(255,183,77,0.10);
    padding: 0 0 18px 0;
}
.mypets-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    border-radius: 0 0 18px 18px;
    overflow: hidden;
    margin: 0;
    font-size: 16px;
}
.mypets-table th {
    background: #ffe082;
    color: #1976d2;
    font-weight: 700;
    padding: 15px 10px;
    border-bottom: 2px solid #ffe082;
    text-align: center;
    font-size: 17px;
}
.mypets-table td {
    padding: 13px 10px;
    font-size: 16px;
    border-bottom: 1px solid #fffde7;
    text-align: center;
    vertical-align: middle;
}
.mypets-table tr:last-child td {
    border-bottom: none;
}
.mypets-row-alt {
    background: #fffde7;
}
.mypets-status {
    padding: 5px 12px;
    border-radius: 15px;
    font-size: 14px;
    font-weight: bold;
    display: inline-block;
}
.mypets-status-healthy {
    background-color: #e3fcef;
    color: #00a854;
}
.mypets-status-needs-attention {
    background-color: #fff7e6;
    color: #fa8c16;
}
.mypets-status-critical {
    background-color: #fff1f0;
    color: #f5222d;
}
@media (max-width: 700px) {
    .mypets-container { max-width: 99vw; }
    .mypets-header, .mypets-table-wrap { border-radius: 0; }
    .mypets-header { padding: 12px 8px; font-size: 18px;}
    .mypets-table th, .mypets-table td { font-size: 14px; padding: 8px 4px;}
}
</style>
<div class="mypets-bg">
    <div class="mypets-container">
        <div class="mypets-header">
            <span class="mypets-title">🐾 Thú cưng của tôi</span>
        </div>
        <div class="mypets-table-wrap">
            <table class="mypets-table">
                <thead>
                    <tr>
                        <th>Tên thú cưng</th>
                        <th>Loài</th>
                        <th>Giống</th>
                        <th>Tuổi</th>
                        <th>Tình trạng sức khỏe</th>
                        <th>Lần khám gần nhất</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pets as $i => $pet): ?>
                    <tr<?php if ($i % 2 == 1) echo ' class="mypets-row-alt"'; ?>>
                        <td><?php echo esc_html($pet->name); ?></td>
                        <td><?php echo esc_html($pet->type); ?></td>
                        <td><?php echo esc_html($pet->breed); ?></td>
                        <td><?php echo esc_html($pet->age); ?></td>
                        <td>
                            <span class="mypets-status mypets-status-<?php echo strtolower(str_replace(' ', '-', $pet->health_status)); ?>">
                                <?php echo esc_html($pet->health_status); ?>
                            </span>
                        </td>
                        <td><?php echo $pet->last_checkup ? date('d/m/Y', strtotime($pet->last_checkup)) : 'N/A'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($pets)): ?>
                    <tr>
                        <td colspan="6" style="text-align:center;color:#bbb;font-style:italic;">Bạn chưa có thú cưng nào.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>