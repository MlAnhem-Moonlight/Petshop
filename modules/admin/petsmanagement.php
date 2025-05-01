<?php
function petshop_pets_page() {
    if (!petshop_is_logged_in() || $_SESSION['ps_user_role'] !== 'admin') {
        wp_redirect(admin_url('admin.php?page=petshop-management'));
        exit;
    }

    global $wpdb;
    $table_pets = $wpdb->prefix . 'petshop_pets';
    $table_users = $wpdb->prefix . 'petshop_users';

    // Get pets with owner information
    $pets = $wpdb->get_results("
        SELECT p.*, u.username as owner_name, u.email as owner_email, u.phone as owner_phone
        FROM $table_pets p 
        LEFT JOIN $table_users u ON p.user_id = u.id 
        ORDER BY p.created_at DESC
    ");

    ?>
    <div class="wrap">
        <h1>Customer Pets Information</h1>

        <!-- Search Box -->
        <div class="ps-search-box">
            <input type="text" id="petSearch" placeholder="Search pets or owners..." onkeyup="searchPets()">
        </div>

        <!-- Pets List -->
        <table class="wp-list-table widefat fixed striped" id="petsTable">
            <thead>
                <tr>
                    <th>Pet Name</th>
                    <th>Type</th>
                    <th>Breed</th>
                    <th>Age</th>
                    <th>Health Status</th>
                    <th>Last Checkup</th>
                    <th>Owner Details</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pets as $pet): ?>
                <tr>
                    <td><?php echo esc_html($pet->name); ?></td>
                    <td><?php echo esc_html($pet->type); ?></td>
                    <td><?php echo esc_html($pet->breed); ?></td>
                    <td><?php echo esc_html($pet->age); ?> years</td>
                    <td>
                        <span class="ps-status ps-status-<?php echo strtolower(str_replace(' ', '-', $pet->health_status)); ?>">
                            <?php echo esc_html($pet->health_status); ?>
                        </span>
                    </td>
                    <td><?php echo $pet->last_checkup ? date('Y-m-d', strtotime($pet->last_checkup)) : 'N/A'; ?></td>
                    <td>
                        <strong><?php echo esc_html($pet->owner_name); ?></strong><br>
                        <small>Email: <?php echo esc_html($pet->owner_email); ?></small><br>
                        <small>Phone: <?php echo esc_html($pet->owner_phone); ?></small>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <style>
    .ps-search-box {
        margin: 20px 0;
    }
    .ps-search-box input {
        width: 300px;
        padding: 8px;
        font-size: 14px;
    }
    .ps-status {
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 12px;
        font-weight: bold;
    }
    .ps-status-healthy {
        background-color: #e3fcef;
        color: #00a854;
    }
    .ps-status-needs-attention {
        background-color: #fff7e6;
        color: #fa8c16;
    }
    .ps-status-critical {
        background-color: #fff1f0;
        color: #f5222d;
    }
    </style>

    <script>
    function searchPets() {
        const input = document.getElementById('petSearch');
        const filter = input.value.toLowerCase();
        const table = document.getElementById('petsTable');
        const tr = table.getElementsByTagName('tr');

        for (let i = 1; i < tr.length; i++) {
            const td = tr[i].getElementsByTagName('td');
            let txtValue = '';
            
            // Combine all columns for searching
            for (let j = 0; j < td.length; j++) {
                txtValue += td[j].textContent || td[j].innerText;
            }
            
            if (txtValue.toLowerCase().indexOf(filter) > -1) {
                tr[i].style.display = '';
            } else {
                tr[i].style.display = 'none';
            }
        }
    }
    </script>
    <?php
}