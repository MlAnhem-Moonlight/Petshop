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