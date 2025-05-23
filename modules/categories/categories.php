<div class="wrap petshop-products-wrap">
    <h1 class="petshop-title" style="display:flex;justify-content:space-between;align-items:center;">
        <span>Quản lý danh mục</span>
        <button id="addCategoryBtn" class="page-title-action">Thêm danh mục mới</button>
    </h1>

    <table class="wp-list-table widefat fixed striped petshop-products-table" id="petshop-category-table">
        <thead>
            <tr>
                <th>Tên danh mục</th>
                <th>Mô tả</th>
                <th style="width:120px;">Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php
            global $wpdb;
            $categories = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}petshop_categories");
            if ($categories):
                foreach ($categories as $category): ?>
                    <tr data-id="<?php echo $category->id; ?>">
                        <td class="cat-name"><?php echo esc_html($category->name); ?></td>
                        <td class="cat-desc"><?php echo esc_html($category->description); ?></td>
                        <td>
                            <button type="button" class="button button-small btn-edit-category" data-id="<?php echo $category->id; ?>" data-name="<?php echo esc_attr($category->name); ?>" data-desc="<?php echo esc_attr($category->description); ?>">Sửa</button>
                            <button type="button" class="button button-small btn-delete-category" data-id="<?php echo $category->id; ?>">Xóa</button>
                        </td>
                    </tr>
                <?php endforeach;
            else: ?>
                <tr><td colspan="3">Chưa có danh mục nào.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Modal Thêm/Sửa Danh Mục -->
    <div id="categoryModal" class="ps-modal">
        <div class="ps-modal-content" style="max-width:400px;">
            <span class="ps-modal-close">&times;</span>
            <h2 id="categoryModalTitle" style="font-size:1.15em;margin-bottom:12px;">Thêm danh mục mới</h2>
            <form id="categoryForm">
                <input type="hidden" name="id" id="cat-id">
                <div class="ps-form-group">
                    <label>Tên danh mục</label>
                    <input type="text" name="name" id="cat-name" required>
                </div>
                <div class="ps-form-group">
                    <label>Mô tả</label>
                    <textarea name="description" id="cat-desc" rows="4"></textarea>
                </div>
                <button type="submit" class="button button-primary" style="width:100%;margin-top:8px;">Lưu</button>
            </form>
        </div>
    </div>

    <!-- Modal xác nhận xóa -->
    <div id="deleteModal" class="ps-modal">
        <div class="ps-modal-content" style="max-width:340px;text-align:center;">
            <span class="ps-modal-close">&times;</span>
            <h2 style="margin-top:0;font-size:1.1em;">Xác nhận xóa</h2>
            <p>Bạn có chắc chắn muốn xóa danh mục này?</p>
            <button id="confirmDeleteBtn" class="button button-primary" style="margin-right:12px;">Xóa</button>
            <button id="cancelDeleteBtn" class="button">Hủy</button>
        </div>
    </div>

    <style>
    .petshop-products-wrap {
        background-color: #f9fff5;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    .petshop-title {
        color: #4CAF50;
        margin-bottom: 20px;
        font-size: 24px;
        border-bottom: 2px solid #8BC34A;
        padding-bottom: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .petshop-products-table {
        width: 100%;
        border-collapse: collapse;
        border-radius: 6px;
        overflow: hidden;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        margin-top: 20px;
    }
    .petshop-products-table th {
        background-color: #8BC34A;
        color: white;
        font-weight: bold;
        text-align: left;
        padding: 12px;
        border: none;
    }
    .petshop-products-table tr:nth-child(even) {
        background-color: #f2f9eb;
    }
    .petshop-products-table tr:nth-child(odd) {
        background-color: #ffffff;
    }
    .petshop-products-table tr:hover {
        background-color: #e8f5e9;
    }
    .petshop-products-table td {
        padding: 10px 12px;
        border-bottom: 1px solid #E0E0E0;
    }
    .page-title-action {
        background-color: #8BC34A;
        color: white;
        border: none;
        border-radius: 4px;
        padding: 8px 16px;
        text-decoration: none;
        display: inline-block;
        font-weight: bold;
        margin-bottom: 0;
        margin-left: 16px;
        transition: background 0.2s;
    }
    .page-title-action:hover {
        background-color: #689F38;
        color: white;
    }
    .button-small {
        padding: 4px 8px;
        margin: 0 4px;
        background: #f6f7f7;
        color: #2271b1;
        border: 1px solid #ccd0d4;
        border-radius: 3px;
        transition: background 0.2s, color 0.2s;
    }
    .button-small:hover {
        background: #e4e6e9;
        color: #135e96;
    }
    .btn-delete-category {
        background: #f6f7f7;
        color: #2271b1;
        border: 1px solid #ccd0d4;
    }
    .btn-delete-category:hover {
        background: #e4e6e9;
        color: #135e96;
    }
    .ps-modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
    }
    .ps-modal-content {
        background-color: #fff;
        margin: 5% auto;
        padding: 20px;
        border-radius: 8px;
        width: 50%;
        max-width: 500px;
        position: relative;
    }
    .ps-modal-close {
        position: absolute;
        right: 15px;
        top: 10px;
        font-size: 24px;
        cursor: pointer;
        color: #666;
    }
    .ps-form-group {
        margin-bottom: 15px;
    }
    .ps-form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
        color: #333;
    }
    .ps-form-group input,
    .ps-form-group textarea {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    .ps-form-group textarea {
        height: 80px;
        resize: vertical;
    }
    </style>

    <script>
    jQuery(document).ready(function($){
        var deleteId = 0;
        var editId = 0;

        // Hiện modal thêm mới
        $('#addCategoryBtn').on('click', function(){
            $('#categoryModalTitle').text('Thêm danh mục mới');
            $('#cat-id').val('');
            $('#cat-name').val('');
            $('#cat-desc').val('');
            $('#categoryModal').show();
        });

        // Hiện modal sửa
        $('.btn-edit-category').on('click', function(){
            editId = $(this).data('id');
            $('#categoryModalTitle').text('Sửa danh mục');
            $('#cat-id').val(editId);
            $('#cat-name').val($(this).data('name'));
            $('#cat-desc').val($(this).data('desc'));
            $('#categoryModal').show();
        });

        // Đóng modal
        $('.ps-modal-close, #cancelDeleteBtn').on('click', function(){
            $('.ps-modal').hide();
            deleteId = 0;
            editId = 0;
        });
        $(window).on('click', function(e) {
            if ($(e.target).hasClass('ps-modal')) {
                $('.ps-modal').hide();
            }
        });

        // Xử lý submit thêm/sửa
        $('#categoryForm').on('submit', function(e){
            e.preventDefault();
            var id = $('#cat-id').val();
            var name = $('#cat-name').val();
            var desc = $('#cat-desc').val();
            var action = id ? 'petshop_edit_category' : 'petshop_add_category';
            var nonce = id ? '<?php echo wp_create_nonce('petshop_edit_category'); ?>' : '<?php echo wp_create_nonce('petshop_add_category'); ?>';
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: action,
                    id: id,
                    name: name,
                    description: desc,
                    _ajax_nonce: nonce
                },
                dataType: 'json',
                success: function(res){
                    if(res.success){
                        location.reload();
                    }else{
                        alert('Lưu thất bại!');
                    }
                },
                error: function(){
                    alert('Có lỗi xảy ra!');
                }
            });
        });

        // XÓA
        $('.btn-delete-category').on('click', function(){
            deleteId = $(this).data('id');
            $('#deleteModal').show();
        });
        $('#confirmDeleteBtn').on('click', function(){
            if(deleteId > 0){
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'petshop_delete_category',
                        id: deleteId,
                        _ajax_nonce: '<?php echo wp_create_nonce('petshop_delete_category'); ?>'
                    },
                    dataType: 'json',
                    success: function(res){
                        if(res.success){
                            $('tr[data-id="'+deleteId+'"]').fadeOut(300, function(){$(this).remove();});
                        }else{
                            alert('Xóa thất bại!');
                        }
                        $('#deleteModal').hide();
                        deleteId = 0;
                    },
                    error: function(){
                        alert('Có lỗi xảy ra!');
                        $('#deleteModal').hide();
                        deleteId = 0;
                    }
                });
            }
        });
    });
    </script>
</div>