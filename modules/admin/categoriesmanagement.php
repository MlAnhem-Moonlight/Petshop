<?php
function petshop_categories_page() {
    if (!petshop_is_logged_in() || $_SESSION['ps_user_role'] !== 'admin') {
        wp_redirect(admin_url('admin.php?page=petshop-management'));
        exit;
    }

    global $wpdb;
    $table_categories = $wpdb->prefix . 'petshop_categories';

    // Handle actions
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_category':
                $wpdb->insert($table_categories, [
                    'name' => sanitize_text_field($_POST['name']),
                    'description' => sanitize_textarea_field($_POST['description'])
                ]);
                break;

            case 'edit_category':
                $wpdb->update($table_categories,
                    [
                        'name' => sanitize_text_field($_POST['name']),
                        'description' => sanitize_textarea_field($_POST['description'])
                    ],
                    ['id' => intval($_POST['category_id'])]
                );
                break;

            case 'delete_category':
                $wpdb->delete($table_categories, ['id' => intval($_POST['category_id'])]);
                break;
        }
    }

    // Get categories
    $categories = $wpdb->get_results("SELECT * FROM $table_categories ORDER BY name");

    ?>
    <style>
    .wrap {
        background: linear-gradient(120deg, #f9fff5 80%, #e8f5e9 100%);
        border-radius: 14px;
        box-shadow: 0 4px 24px rgba(76,175,80,0.10);
        padding: 32px 28px 28px 28px;
        margin-top: 24px;
        max-width: 700px;
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
    .ps-form-row textarea {
        width: 100%;
        padding: 9px 12px;
        border: 1.5px solid #aed581;
        border-radius: 5px;
        background: #fff;
        font-size: 15px;
        transition: border 0.2s;
    }
    .ps-form-row input:focus,
    .ps-form-row textarea:focus {
        border: 1.5px solid #4caf50;
        outline: none;
    }
    </style>
    <link rel="stylesheet" href="<?php echo includes_url('css/dashicons.min.css'); ?>">

    <script>
    function showAddCategoryForm() {
        document.getElementById('categoryModal').innerHTML = `
            <div class="ps-modal-content">
                <span class="ps-close" onclick="closeCategoryModal()">&times;</span>
                <h2>Add New Category</h2>
                <form method="post">
                    <input type="hidden" name="action" value="add_category">
                    <div class="ps-form-row">
                        <label>Name:</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="ps-form-row">
                        <label>Description:</label>
                        <textarea name="description" required></textarea>
                    </div>
                    <button type="submit" class="button button-primary">Save Category</button>
                </form>
            </div>
        `;
        document.getElementById('categoryModal').style.display = 'block';
    }
    function showEditCategoryForm(category) {
        if (typeof category === 'string') category = JSON.parse(category);
        document.getElementById('categoryModal').innerHTML = `
            <div class="ps-modal-content">
                <span class="ps-close" onclick="closeCategoryModal()">&times;</span>
                <h2>Edit Category</h2>
                <form method="post">
                    <input type="hidden" name="action" value="edit_category">
                    <input type="hidden" name="category_id" value="${category.id}">
                    <div class="ps-form-row">
                        <label>Name:</label>
                        <input type="text" name="name" value="${category.name.replace(/"/g, '&quot;')}" required>
                    </div>
                    <div class="ps-form-row">
                        <label>Description:</label>
                        <textarea name="description" required>${category.description ? category.description.replace(/</g, '&lt;').replace(/>/g, '&gt;') : ''}</textarea>
                    </div>
                    <button type="submit" class="button button-primary">Save Changes</button>
                </form>
            </div>
        `;
        document.getElementById('categoryModal').style.display = 'block';
    }
    function closeCategoryModal() {
        document.getElementById('categoryModal').style.display = 'none';
    }
    window.onclick = function(event) {
        var modal = document.getElementById('categoryModal');
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
    </script>

    <div class="wrap">
        <h1>Category Management</h1>

        <!-- Add Category Button -->
        <button class="page-title-action" onclick="showAddCategoryForm()">Add New Category</button>

        <!-- Category List -->
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><?php echo esc_html($category->name); ?></td>
                        <td><?php echo esc_html($category->description); ?></td>
                        <td>
                            <button class="button button-small" onclick="showEditCategoryForm(<?php echo json_encode($category); ?>)">Edit</button>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="action" value="delete_category">
                                <input type="hidden" name="category_id" value="<?php echo $category->id; ?>">
                                <button type="submit" class="button button-small" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Add/Edit Category Modal -->
        <div id="categoryModal" class="ps-modal">
            <!-- Similar modal content as products but for categories -->
        </div>
    </div>
    <?php
}