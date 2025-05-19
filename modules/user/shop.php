<?php
function petshop_user_shop_page() {
    global $wpdb;
    $table_products = $wpdb->prefix . 'petshop_products';
    $table_categories = $wpdb->prefix . 'petshop_categories';

    // Get categories for dropdown
    $categories = $wpdb->get_results("SELECT id, name FROM $table_categories ORDER BY name ASC");

    // Handle search and category filter
    $search = isset($_GET['ps_search']) ? sanitize_text_field($_GET['ps_search']) : '';
    $category_filter = isset($_GET['ps_category']) ? intval($_GET['ps_category']) : 0;
    $order = isset($_GET['ps_order']) ? $_GET['ps_order'] : '';
    $where = [];
    $params = [];

    if ($search !== '') {
        $where[] = "(p.name LIKE %s OR p.description LIKE %s)";
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
    }
    if ($category_filter > 0) {
        $where[] = "p.category_id = %d";
        $params[] = $category_filter;
    }

    $where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    // Handle order filter
    $order_sql = "ORDER BY p.id DESC";
    if ($order === 'price_asc') $order_sql = "ORDER BY p.price ASC";
    if ($order === 'price_desc') $order_sql = "ORDER BY p.price DESC";
    if ($order === 'name_asc') $order_sql = "ORDER BY p.name ASC";
    if ($order === 'name_desc') $order_sql = "ORDER BY p.name DESC";

    $sql = "SELECT p.*, c.name as category_name FROM $table_products p
            LEFT JOIN $table_categories c ON p.category_id = c.id
            $where_sql
            $order_sql";
    $products = $params ? $wpdb->get_results($wpdb->prepare($sql, ...$params)) : $wpdb->get_results($sql);
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
        .ps-shop-header-bar {
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
        .ps-menu-dropdown {
            position: relative;
            margin-right: 16px;
        }
        .ps-menu-icon {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fffbe7;
            border-radius: 7px;
            border: 1.5px solid #ffe066;
            cursor: pointer;
            transition: box-shadow 0.2s;
        }
        .ps-menu-icon:hover {
            box-shadow: 0 2px 8px rgba(255, 214, 0, 0.18);
        }
        .ps-menu-dropdown-content {
            display: none;
            position: absolute;
            left: 0;
            top: 44px;
            background: #fffbe7;
            min-width: 180px;
            border-radius: 8px;
            box-shadow: 0 4px 18px rgba(255,214,0,0.13);
            z-index: 10;
            padding: 8px 0;
        }
        .ps-menu-dropdown-content.show {
            display: block;
        }
        .ps-menu-dropdown-content a,
        .ps-menu-dropdown-content form button {
            display: block;
            width: 100%;
            padding: 10px 18px;
            background: none;
            border: none;
            text-align: left;
            color: #222;
            font-size: 1em;
            cursor: pointer;
            border-bottom: 1px solid #ffe066;
            transition: background 0.15s;
        }
        .ps-menu-dropdown-content a:last-child,
        .ps-menu-dropdown-content form button:last-child {
            border-bottom: none;
        }
        .ps-menu-dropdown-content a:hover,
        .ps-menu-dropdown-content form button:hover {
            background: #ffe066;
        }
        .ps-shop-search-form {
            display: flex;
            gap: 10px;
            align-items: center;
            margin: 0;
            flex: 1;
            justify-content: flex-end;
        }
        .ps-shop-search-form input[type="text"] {
            min-width: 180px;
            padding: 8px 14px;
            border-radius: 7px;
            border: 1.5px solid #ffe066;
            background: #fffbe7;
            font-size: 1em;
            transition: border 0.2s;
        }
        .ps-shop-search-form input[type="text"]:focus {
            border: 1.5px solid #ffb300;
            outline: none;
        }
        .ps-shop-search-form button {
            padding: 8px 22px;
            border-radius: 7px;
            background: linear-gradient(90deg, #0d8abc 60%, #00c6fb 100%);
            color: #fff;
            border: none;
            font-weight: 700;
            font-size: 1em;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(13,138,188,0.08);
            transition: background 0.2s;
        }
        .ps-shop-search-form button:hover {
            background: linear-gradient(90deg, #00c6fb 60%, #0d8abc 100%);
        }
        .ps-shop-carousel-container {
            width: 100%;
            max-width: 1100px;
            min-width: 0;
            margin: 0 auto 18px auto;
            position: relative;
            overflow: hidden;
            border-radius: 0 0 12px 12px;
            box-shadow: 0 2px 12px rgba(255, 152, 0, 0.10);
            background: #fff;
            left: 0;
            right: 0;
        }
        .ps-carousel-slide {
            display: none;
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .ps-carousel-slide.active {
            display: block;
        }
        .ps-carousel-title-center {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #fff;
            font-size: 2.2em;
            font-weight: 900;
            text-shadow: 0 4px 24px rgba(0,0,0,0.35), 0 1px 0 #ff9800;
            letter-spacing: 2px;
            text-align: center;
            z-index: 2;
            width: 100%;
            pointer-events: none;
            user-select: none;
        }
        .ps-carousel-dots {
            text-align: center;
            margin-top: -28px;
            position: relative;
            z-index: 2;
        }
        .ps-carousel-dot {
            display: inline-block;
            width: 12px;
            height: 12px;
            margin: 0 4px;
            background: #FFD600;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid #fff;
            opacity: 0.7;
            transition: opacity 0.2s;
        }
        .ps-carousel-dot.active {
            background: #FF9800;
            opacity: 1;
        }
        .ps-shop-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 1100px;
            margin: 24px auto 0 auto;
            padding: 0 8px;
            background: rgba(255,255,255,0.85);
            border-radius: 10px;
            box-shadow: 0 2px 12px rgba(255, 214, 0, 0.10);
        }
        .ps-toolbar-title {
            font-weight: 700;
            font-size: 1.2em;
            color: #222;
            letter-spacing: 1px;
        }
        .ps-filter-dropdown {
            position: relative;
            display: inline-block;
        }
        .ps-filter-btn {
            padding: 8px 18px;
            border-radius: 7px;
            border: 1.5px solid #ffe066;
            background: #fffbe7;
            font-weight: 600;
            cursor: pointer;
            font-size: 1em;
            transition: background 0.2s;
        }
        .ps-filter-btn:hover {
            background: #ffe066;
        }
        .ps-filter-dropdown-content {
            display: none;
            position: absolute;
            top: 40px;
            right: 0;
            min-width: 170px;
            background: #fffbe7;
            border-radius: 8px;
            box-shadow: 0 4px 18px rgba(255,214,0,0.13);
            z-index: 20;
            padding: 8px 0;
        }
        .ps-filter-dropdown-content.show {
            display: block;
        }
        .ps-filter-dropdown-content button {
            display: block;
            width: 100%;
            padding: 10px 18px;
            background: none;
            border: none;
            text-align: left;
            color: #222;
            font-size: 1em;
            cursor: pointer;
            border-bottom: 1px solid #ffe066;
            transition: background 0.15s;
        }
        .ps-filter-dropdown-content button:last-child {
            border-bottom: none;
        }
        .ps-filter-dropdown-content button:hover {
            background: #ffe066;
        }
        .ps-shop-items-wrap {
            display: flex;
            flex-wrap: wrap;
            gap: 22px;
            margin-top: 32px;
            justify-content: center;
            max-width: 1100px;
            margin-left: auto;
            margin-right: auto;
            background: rgba(255,255,255,0.85);
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(255, 214, 0, 0.10);
            padding: 24px 0;
        }
        .ps-shop-item-card {
            width: 180px;
            height: 220px;
            background: linear-gradient(135deg, #fffbe7 70%, #ffe066 100%);
            border-radius: 14px;
            box-shadow: 0 4px 18px rgba(255, 214, 0, 0.10), 0 1.5px 6px rgba(13,138,188,0.07);
            padding: 0;
            text-align: center;
            transition: box-shadow 0.2s, transform 0.2s;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
        }
        .ps-shop-item-card img,
        .ps-shop-item-card .no-img {
            width: 100%;
            height: 140px;
            object-fit: cover;
            border-radius: 14px 14px 0 0;
            margin: 0;
            background: #f3f4f6;
            display: block;
            box-shadow: none;
            font-size: 1.1em;
        }
        .ps-shop-item-card .no-img {
            line-height: 140px;
        }
        .ps-shop-item-card .name {
            font-weight: 700;
            font-size: 1.08em;
            margin: 8px 0 2px 0;
            color: #0d8abc;
            letter-spacing: 0.5px;
            padding: 0 8px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .ps-shop-item-card .price {
            margin: 0 0 10px 0;
            font-size: 1.08em;
            color: #ff9800;
            font-weight: 700;
            letter-spacing: 0.5px;
            padding: 0 8px;
        }
        /* Responsive for smaller screens */
        @media (max-width: 1200px) {
            .ps-shop-header-bar,
            .ps-shop-carousel-container,
            .ps-shop-items-wrap {
                max-width: 98vw;
            }
        }
        @media (max-width: 700px) {
            .ps-shop-header-bar,
            .ps-shop-carousel-container,
            .ps-shop-items-wrap {
                max-width: 100vw;
                padding-left: 0;
                padding-right: 0;
            }
            .ps-shop-item-card {
                width: 48vw;
                min-width: 140px;
                max-width: 220px;
            }
        }
    </style>
    <div class="wrap">
        <div class="ps-shop-header-bar">
            <div class="ps-menu-dropdown" id="psMenuDropdown">
                <div class="ps-menu-icon" onclick="toggleMenuDropdown(event)">
                    <!-- Hamburger icon SVG -->
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <rect y="4" width="24" height="3" rx="1.5" fill="#FF9800"/>
                        <rect y="10.5" width="24" height="3" rx="1.5" fill="#FF9800"/>
                        <rect y="17" width="24" height="3" rx="1.5" fill="#FF9800"/>
                    </svg>
                </div>
                <div class="ps-menu-dropdown-content" id="psMenuDropdownContent">
                    <form method="get" style="margin:0;">
                        <input type="hidden" name="page" value="ps-shop">
                        <input type="hidden" name="ps_search" value="<?php echo esc_attr($search); ?>">
                        <input type="hidden" name="ps_order" value="<?php echo esc_attr($order); ?>">
                        <button type="submit" name="ps_category" value="0"<?php if ($category_filter == 0) echo ' style="font-weight:bold;"'; ?>>Tất cả danh mục</button>
                        <?php foreach ($categories as $cat): ?>
                            <button
                                type="submit"
                                name="ps_category"
                                value="<?php echo $cat->id; ?>"
                                <?php if ($category_filter == $cat->id) echo ' style="font-weight:bold;"'; ?>
                            >
                                <?php echo esc_html($cat->name); ?>
                            </button>
                        <?php endforeach; ?>
                    </form>
                </div>
            </div>
            <form method="get" class="ps-shop-search-form" style="width:100%;justify-content:flex-end;">
                <input type="hidden" name="page" value="ps-shop">
                <input type="hidden" name="ps_category" value="<?php echo esc_attr($category_filter); ?>">
                <input type="hidden" name="ps_order" value="<?php echo esc_attr($order); ?>">
                <input type="text" name="ps_search" placeholder="Tìm kiếm sản phẩm..." value="<?php echo esc_attr($search); ?>">
                <button type="submit" title="Tìm kiếm" style="display:flex;align-items:center;justify-content:center;padding:8px 16px;">
                    <svg width="20" height="20" fill="none" viewBox="0 0 20 20">
                        <circle cx="9" cy="9" r="7" stroke="#fff" stroke-width="2"/>
                        <line x1="14.2" y1="14.2" x2="18" y2="18" stroke="#fff" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
            </form>
        </div>
        <!-- Carousel -->
        <div class="ps-shop-carousel-container">
            <img class="ps-carousel-slide active" src="<?php echo plugins_url('../../assets/banner1.jpg', __FILE__); ?>" alt="Banner 1">
            <img class="ps-carousel-slide" src="<?php echo plugins_url('../../assets/banner2.jpg', __FILE__); ?>" alt="Banner 2">
            <img class="ps-carousel-slide" src="<?php echo plugins_url('../../assets/banner3.jpg', __FILE__); ?>" alt="Banner 3">
            <div class="ps-carousel-title-center">🐾 Cửa hàng sản phẩm</div>
            <div class="ps-carousel-dots">
                <span class="ps-carousel-dot active" onclick="psShowSlide(0)"></span>
                <span class="ps-carousel-dot" onclick="psShowSlide(1)"></span>
                <span class="ps-carousel-dot" onclick="psShowSlide(2)"></span>
            </div>
        </div>
        <!-- Toolbar: Title left, Filter right -->
        <div class="ps-shop-toolbar">
            <div class="ps-toolbar-title">Tất cả sản phẩm</div>
            <div class="ps-filter-dropdown">
                <button type="button" class="ps-filter-btn" onclick="toggleFilterDropdown(event)">
                    Bộ lọc
                    <svg style="vertical-align:middle;margin-left:4px;" width="16" height="16" fill="#FF9800" viewBox="0 0 16 16"><path d="M4 6l4 4 4-4"/></svg>
                </button>
                <div class="ps-filter-dropdown-content" id="psFilterDropdownContent">
                    <form method="get" style="margin:0;">
                        <input type="hidden" name="page" value="ps-shop">
                        <input type="hidden" name="ps_search" value="<?php echo esc_attr($search); ?>">
                        <input type="hidden" name="ps_category" value="<?php echo esc_attr($category_filter); ?>">
                        <button type="submit" name="ps_order" value="price_asc">Giá tăng dần</button>
                        <button type="submit" name="ps_order" value="price_desc">Giá giảm dần</button>
                        <button type="submit" name="ps_order" value="name_asc">Tên A-Z</button>
                        <button type="submit" name="ps_order" value="name_desc">Tên Z-A</button>
                    </form>
                </div>
            </div>
        </div>
        <!-- Product Items -->
        <div class="ps-shop-items-wrap">
            <?php if ($products): foreach ($products as $product): ?>
                <div class="ps-shop-item-card"
                    data-id="<?php echo $product->id; ?>"
                    data-name="<?php echo esc_attr($product->name); ?>"
                    data-category="<?php echo esc_attr($product->category_name); ?>"
                    data-price="<?php echo number_format($product->price, 0, ',', '.'); ?> đ"
                    data-stock="<?php echo intval($product->stock_quantity); ?>"
                    data-desc="<?php echo esc_attr($product->description); ?>"
                    data-img="<?php echo esc_url($product->image_url); ?>"
                    onclick="showProductModal(this)">
                    <?php if (!empty($product->image_url)): ?>
                        <img src="<?php echo esc_url($product->image_url); ?>" alt="">
                    <?php else: ?>
                        <div class="no-img">No Image</div>
                    <?php endif; ?>
                    <div class="name"><?php echo esc_html($product->name); ?></div>
                    <div class="price"><?php echo number_format($product->price, 0, ',', '.'); ?> đ</div>
                </div>
            <?php endforeach; else: ?>
                <div style="padding:32px 0; width:100%; text-align:center; color:#888;">Không có sản phẩm nào.</div>
            <?php endif; ?>
        </div>

        <!-- Modal HTML -->
        <div id="psProductModal" style="display:none;position:fixed;z-index:9999;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.35);align-items:center;justify-content:center;">
            <div id="psProductModalContent" style="background:#fffbe7;max-width:400px;width:90vw;border-radius:16px;box-shadow:0 8px 32px rgba(255,214,0,0.18);padding:28px 22px 18px 22px;position:relative;">
                <button onclick="closeProductModal()" style="position:absolute;top:12px;right:16px;background:none;border:none;font-size:1.6em;color:#ff9800;cursor:pointer;">&times;</button>
                <img id="psModalImg" src="" alt="" style="width:100%;height:180px;object-fit:cover;border-radius:12px;">
                <div style="font-size:1.3em;font-weight:700;color:#0d8abc;margin:12px 0 4px 0;" id="psModalName"></div>
                <div style="color:#ff9800;font-weight:600;margin-bottom:6px;" id="psModalCategory"></div>
                <div style="font-size:1.1em;font-weight:700;color:#ff9800;margin-bottom:8px;" id="psModalPrice"></div>
                <div style="color:#666;font-size:1em;margin-bottom:8px;" id="psModalStock"></div>
                <div style="color:#444;font-size:1em;" id="psModalDesc"></div>
                <div style="display:flex;align-items:center;gap:10px;margin:18px 0 0 0;">
                    <label for="psModalQty" style="font-weight:600;color:#0d8abc;">Số lượng:</label>
                    <input id="psModalQty" type="number" min="1" value="1" style="width:60px;padding:4px 8px;border-radius:6px;border:1.5px solid #ffe066;background:#fffbe7;font-size:1em;">
                    <button id="psModalAddCart" style="padding:7px 18px;border-radius:7px;background:linear-gradient(90deg,#0d8abc 60%,#00c6fb 100%);color:#fff;border:none;font-weight:700;font-size:1em;cursor:pointer;box-shadow:0 2px 8px rgba(13,138,188,0.08);transition:background 0.2s;">
                        Thêm vào giỏ
                    </button>
                </div>
                <div id="psModalCartMsg" style="margin-top:10px;font-size:1em;color:#0d8abc;display:none;"></div>
            </div>
        </div>

    </div>
    <script>
        // Simple JS carousel
        let psCurrentSlide = 0;
        const psSlides = document.querySelectorAll('.ps-carousel-slide');
        const psDots = document.querySelectorAll('.ps-carousel-dot');
        function psShowSlide(idx) {
            psSlides.forEach((img, i) => {
                img.classList.toggle('active', i === idx);
                psDots[i].classList.toggle('active', i === idx);
            });
            psCurrentSlide = idx;
        }
        // Auto slide
        setInterval(function() {
            psCurrentSlide = (psCurrentSlide + 1) % psSlides.length;
            psShowSlide(psCurrentSlide);
        }, 4000);

        // Dropdown menu logic
        function toggleMenuDropdown(e) {
            e.stopPropagation();
            var menu = document.getElementById('psMenuDropdownContent');
            menu.classList.toggle('show');
        }
        document.addEventListener('click', function() {
            var menu = document.getElementById('psMenuDropdownContent');
            if (menu) menu.classList.remove('show');
            var filter = document.getElementById('psFilterDropdownContent');
            if (filter) filter.classList.remove('show');
        });
        document.getElementById('psMenuDropdown').addEventListener('click', function(e) {
            e.stopPropagation();
        });

        // Filter dropdown logic
        function toggleFilterDropdown(e) {
            e.stopPropagation();
            var filter = document.getElementById('psFilterDropdownContent');
            filter.classList.toggle('show');
        }
        document.getElementById('psFilterDropdownContent').addEventListener('click', function(e) {
            e.stopPropagation();
        });

        // Modal logic
        function showProductModal(el) {
            document.getElementById('psModalImg').src = el.getAttribute('data-img') || '';
            document.getElementById('psModalName').textContent = el.getAttribute('data-name') || '';
            document.getElementById('psModalCategory').textContent = el.getAttribute('data-category') ? 'Danh mục: ' + el.getAttribute('data-category') : '';
            document.getElementById('psModalPrice').textContent = el.getAttribute('data-price') || '';
            document.getElementById('psModalStock').textContent = el.getAttribute('data-stock') ? 'Kho: ' + el.getAttribute('data-stock') : '';
            document.getElementById('psModalDesc').textContent = el.getAttribute('data-desc') || '';
            document.getElementById('psModalQty').value = 1;
            document.getElementById('psModalQty').max = el.getAttribute('data-stock') || '';
            document.getElementById('psModalAddCart').setAttribute('data-product-id', el.getAttribute('data-id'));
            document.getElementById('psModalAddCart').setAttribute('data-stock', el.getAttribute('data-stock'));
            document.getElementById('psModalCartMsg').style.display = 'none';
            document.getElementById('psProductModal').style.display = 'flex';
        }
        function closeProductModal() {
            document.getElementById('psProductModal').style.display = 'none';
        }
        document.getElementById('psProductModal').addEventListener('click', function(e) {
            if (e.target === this) closeProductModal();
        });

        // Add to cart AJAX
        document.getElementById('psModalAddCart').onclick = function() {
            var btn = this;
            var product_id = btn.getAttribute('data-product-id');
            var qty = parseInt(document.getElementById('psModalQty').value, 10) || 1;
            var max = parseInt(btn.getAttribute('data-stock'), 10) || 1;
            if (qty < 1) qty = 1;
            if (qty > max) qty = max;

            btn.disabled = true;
            btn.textContent = 'Đang thêm...';

            jQuery.ajax({
                url: ajaxurl || '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'petshop_add_to_cart_db',
                    product_id: product_id,
                    quantity: qty
                },
                success: function(res) {
                    btn.disabled = false;
                    btn.textContent = 'Thêm vào giỏ';
                    if (res.success) {
                        document.getElementById('psModalCartMsg').textContent = 'Đã thêm vào giỏ hàng!';
                        document.getElementById('psModalCartMsg').style.display = 'block';
                    } else {
                        document.getElementById('psModalCartMsg').textContent = res.data || 'Có lỗi xảy ra!';
                        document.getElementById('psModalCartMsg').style.display = 'block';
                    }
                },
                error: function() {
                    btn.disabled = false;
                    btn.textContent = 'Thêm vào giỏ';
                    document.getElementById('psModalCartMsg').textContent = 'Có lỗi xảy ra!';
                    document.getElementById('psModalCartMsg').style.display = 'block';
                }
            });
        };
    </script>
    <?php
}
petshop_user_shop_page();