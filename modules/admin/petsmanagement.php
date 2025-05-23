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
    <div class="ps-card">
        <div class="ps-card-header">
            <span class="ps-card-title">Quản lý thông tin thú cưng</span>
            <button onclick="openPetModal(false)" class="ps-btn ps-btn-primary">Thêm thú cưng mới</button>
        </div>

        <!-- Modal for Add/Edit Pet -->
        <div id="petModal" class="pet-modal">
          <div class="pet-modal-content">
            <span class="pet-modal-close" onclick="closePetModal()">&times;</span>
            <h2 id="petModalTitle">Thêm/Sửa thú cưng</h2>
            <form id="petForm" enctype="multipart/form-data">
              <input type="hidden" name="pet_id" id="pet_id">
              <input type="text" name="name" id="pet_name" placeholder="Tên thú cưng" required>
              <input type="text" name="type" id="pet_type" placeholder="Loài" required>
              <input type="text" name="breed" id="pet_breed" placeholder="Giống">
              <input type="number" name="age" id="pet_age" placeholder="Tuổi" min="0">
              <input type="text" name="health_status" id="pet_health_status" placeholder="Tình trạng sức khỏe">
              <input type="date" name="last_checkup" id="pet_last_checkup">
              <select name="user_id" id="pet_user_id" required>
                <option value="">-- Chọn chủ sở hữu --</option>
                <?php
                $users = $wpdb->get_results("SELECT id, username FROM $table_users");
                foreach ($users as $u) {
                    echo '<option value="'.$u->id.'">'.esc_html($u->username).'</option>';
                }
                ?>
              </select>
              <div style="margin-top:10px; text-align:right;">
                <button type="submit" id="petFormSubmit" class="ps-btn ps-btn-primary">Lưu</button>
                <button type="button" onclick="closePetModal()" class="ps-btn">Hủy</button>
              </div>
            </form>
          </div>
        </div>

        <!-- Search Box -->
        <div class="ps-search-box">
            <input type="text" id="petSearch" placeholder="Tìm kiếm thú cưng hoặc chủ sở hữu..." onkeyup="searchPets()">
        </div>

        <!-- Pets List -->
        <table class="ps-table" id="petsTable">
            <thead>
                <tr>
                    <th>Tên thú cưng</th>
                    <th>Loài</th>
                    <th>Giống</th>
                    <th>Tuổi</th>
                    <th>Tình trạng sức khỏe</th>
                    <th>Lần khám gần nhất</th>
                    <th>Chủ sở hữu</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pets as $i => $pet): ?>
                <tr<?php if ($i % 2 == 1) echo ' class="ps-row-alt"'; ?>>
                    <td><?php echo esc_html($pet->name); ?></td>
                    <td><?php echo esc_html($pet->type); ?></td>
                    <td><?php echo esc_html($pet->breed); ?></td>
                    <td><?php echo esc_html($pet->age); ?></td>
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
                    <td>
                        <button class="ps-btn" onclick="editPet(<?php echo $pet->id; ?>)">Sửa</button>
                        <button class="ps-btn" onclick="deletePet(<?php echo $pet->id; ?>)">Xóa</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <style>
    .ps-card {
        background: #f9fff5;
        border-radius: 14px;
        box-shadow: 0 4px 24px rgba(76,175,80,0.10);
        padding: 0 0 24px 0;
        margin: 40px auto 0 auto;
        max-width: 1100px;
    }
    .ps-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 24px 32px 0 32px;
    }
    .ps-card-title {
        color: #388e3c;
        font-size: 26px;
        font-weight: 700;
    }
    .ps-btn {
        border: 1.5px solid #1976d2;
        background: #fafbfc;
        color: #1976d2;
        border-radius: 8px;
        padding: 7px 18px;
        font-size: 15px;
        font-weight: 500;
        cursor: pointer;
        transition: border-color 0.2s, color 0.2s, background 0.2s;
        margin-left: 4px;
        margin-right: 4px;
        box-shadow: none;
    }
    .ps-btn:hover, .ps-btn:focus {
        border-color: #0d47a1;
        color: #0d47a1;
        background: #f5faff;
    }
    .ps-btn-primary {
        border: 1.5px solid #1976d2;
        background: #fafbfc;
        color: #1976d2;
    }
    .ps-table {
        width: 98%;
        margin: 0 auto;
        border-collapse: separate;
        border-spacing: 0;
        background: #fff;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 1px 6px rgba(76,175,80,0.06);
    }
    .ps-table th {
        background: #8bc34a;
        color: #fff;
        font-weight: 600;
        padding: 14px 18px;
        font-size: 16px;
        border-bottom: 2px solid #c5e1a5;
        text-align: left;
    }
    .ps-table td {
        padding: 12px 18px;
        font-size: 15px;
        border-bottom: 1px solid #e0e0e0;
        vertical-align: top;
    }
    .ps-table tr:last-child td {
        border-bottom: none;
    }
    .ps-row-alt {
        background: #f1f8e9;
    }
    .ps-status {
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 12px;
        font-weight: bold;
        display: inline-block;
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
    .ps-search-box {
        margin: 18px 32px 18px 32px;
    }
    .ps-search-box input {
        width: 320px;
        padding: 8px 12px;
        font-size: 15px;
        border-radius: 6px;
        border: 1.5px solid #c5e1a5;
        background: #f9fff5;
    }
    /* Modal styles */
    .pet-modal {
      display: none;
      position: fixed;
      z-index: 9999;
      left: 0; top: 0; width: 100%; height: 100%;
      overflow: auto; background: rgba(0,0,0,0.3);
    }
    .pet-modal-content {
      background: #fff; margin: 60px auto; padding: 30px 24px 18px 24px;
      border-radius: 8px; width: 100%; max-width: 420px; position: relative;
      box-shadow: 0 8px 32px rgba(0,0,0,0.18);
    }
    .pet-modal-close {
      color: #888; position: absolute; right: 18px; top: 12px; font-size: 28px; cursor: pointer;
    }
    .pet-modal-close:hover { color: #d32f2f; }
    #petModal input, #petModal select {
      width: 100%; margin-bottom: 12px; padding: 8px; border-radius: 4px; border: 1px solid #ccc;
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

    function openPetModal(edit = false) {
        document.getElementById('petModal').style.display = 'block';
        document.getElementById('petModalTitle').innerText = edit ? 'Sửa thú cưng' : 'Thêm thú cưng';
        if (!edit) {
            document.getElementById('petForm').reset();
            document.getElementById('pet_id').value = '';
        }
    }
    function closePetModal() {
        document.getElementById('petModal').style.display = 'none';
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('petForm').onsubmit = function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'petshop_save_pet');
            fetch(ajaxurl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) location.reload();
                else alert(res.data || 'Error');
            });
        };
        // Đóng modal khi click ra ngoài vùng modal-content
        window.onclick = function(event) {
            const modal = document.getElementById('petModal');
            if (event.target === modal) {
                closePetModal();
            }
        }
    });

    function editPet(id) {
        fetch(ajaxurl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=petshop_get_pet&id=' + encodeURIComponent(id)
        })
        .then(r => r.json())
        .then(res => {
            if(res.success) {
                openPetModal(true);
                for (const key in res.data) {
                    if (document.getElementById('pet_' + key)) {
                        document.getElementById('pet_' + key).value = res.data[key];
                    }
                }
            } else alert('Not found');
        });
    }

    function deletePet(id) {
        if (!confirm('Bạn có chắc chắn muốn xóa thú cưng này?')) return;
        fetch(ajaxurl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=petshop_delete_pet&id=' + encodeURIComponent(id)
        })
        .then(r => r.json())
        .then(res => {
            if(res.success) location.reload();
            else alert(res.data || 'Error');
        });
    }
    </script>
    <?php
}