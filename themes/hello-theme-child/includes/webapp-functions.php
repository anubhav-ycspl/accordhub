<?php 

function webapp_admin_assets($hook) {

    $version = time(); // prevent caching

    // CSS
    wp_enqueue_style(
        'webapp-admin-style',
        get_stylesheet_directory_uri() . '/includes/admin-assets/admin-style.css',
        array(),
        $version
    );

    // JS
    wp_enqueue_script(
        'webapp-admin-script',
        get_stylesheet_directory_uri() . '/includes/admin-assets/admin-script.js',
        array('jquery'),
        $version,
        true
    );

}
add_action('admin_enqueue_scripts', 'webapp_admin_assets');

/* WebAPP Admin menu code */

add_action('admin_menu', 'webapp_manage_admin_menu', 99);

function webapp_manage_admin_menu()
{

    // Main Menu
    add_menu_page(
        'Manage WebAPP',
        'Manage WebAPP',
        'manage_woocommerce',
        'manage-webapp',
        '',
        'dashicons-admin-generic',
        56
    );

    // Brands submenu
    add_submenu_page(
        'manage-webapp',
        'Restaurant',
        'Restaurant',
        'manage_woocommerce',
        'edit-tags.php?taxonomy=product_brand&post_type=product'
    );

    // Categories main page
    add_submenu_page(
        'manage-webapp',
        'Categories',
        'Categories',
        'manage_woocommerce',
        'edit-tags.php?taxonomy=product_cat&post_type=product'
    );

    // 🔥 Add Dynamic Categories (Recursive)
    webapp_add_category_submenus(0, 0);

    // Remove duplicate auto submenu
    remove_submenu_page('manage-webapp', 'manage-webapp');
}


/**
 * Recursive function to add category + subcategories
 */
function webapp_add_category_submenus($parent_id = 0, $depth = 0)
{

    $terms = get_terms([
        'taxonomy' => 'product_cat',
        'hide_empty' => false,
        'parent' => $parent_id,
        'orderby' => 'menu_order',
        'order' => 'ASC',
    ]);

    add_submenu_page(
        'manage-webapp',
        'meals',
        '- Meals',
        'manage_woocommerce',
        'edit.php?post_type=product&product_cat=meals'
    );

    add_submenu_page(
        'manage-webapp',
        'beverages',
        '- Beverages',
        'manage_woocommerce',
        'edit.php?post_type=product&product_cat=beverages'
    );

    add_submenu_page(
        'manage-webapp',
        'refreshments-2',
        '- Refreshments',
        'manage_woocommerce',
        'edit.php?post_type=product&product_cat=refreshments-2'
    );

    /*if (!empty($terms) && !is_wp_error($terms)) {

        foreach ($terms as $term) {

            if ( $term->slug === 'uncategorized' || $term->slug === 'addons' || $term->slug === 'rooms' ) {
                continue;
            }

            // Indentation styling
            $prefix = str_repeat(' - ', $depth + 1);

            add_submenu_page(
                'manage-webapp',
                $term->name,
                $prefix . $term->name,
                'manage_woocommerce',
                'edit.php?post_type=product&product_cat=' . $term->slug
            );

            // Call function again for children
            //webapp_add_category_submenus($term->term_id, $depth + 1);
        }
    }*/
}


add_action('admin_menu', 'webapp_remove_default_wc_submenus', 999);

function webapp_remove_default_wc_submenus()
{

    // Remove Product Categories
    remove_submenu_page(
        'edit.php?post_type=product',
        'edit-tags.php?taxonomy=product_cat&post_type=product'
    );

    // Remove Product Brands (if registered)
    remove_submenu_page(
        'edit.php?post_type=product',
        'edit-tags.php?taxonomy=product_brand&post_type=product'
    );
}


add_filter('parent_file', 'webapp_fix_admin_menu_highlight');
add_filter('submenu_file', 'webapp_fix_admin_submenu_highlight');

function webapp_fix_admin_menu_highlight($parent_file)
{

    global $current_screen;

    if (
        isset($current_screen->taxonomy) &&
        in_array($current_screen->taxonomy, ['product_cat', 'product_brand'])
    ) {
        $parent_file = 'manage-webapp';
    }

    return $parent_file;
}

function webapp_fix_admin_submenu_highlight($submenu_file)
{

    global $current_screen;

    if (isset($current_screen->taxonomy)) {

        if ($current_screen->taxonomy === 'product_brand') {
            $submenu_file = 'edit-tags.php?taxonomy=product_brand&post_type=product';
        }

        if ($current_screen->taxonomy === 'product_cat') {
            $submenu_file = 'edit-tags.php?taxonomy=product_cat&post_type=product';
        }
    }

    return $submenu_file;
}


// Relocate Brand After Name column

add_filter('manage_edit-product_columns', 'webapp_product_columns_order', 999);

function webapp_product_columns_order($columns) {

    $new_columns = [];

    $new_columns['cb'] = $columns['cb'];
    $new_columns['thumb'] = $columns['thumb'];
    $new_columns['name'] = $columns['name'];
    $new_columns['taxonomy-product_brand'] = __('Brands');
    $new_columns['price'] = $columns['price'];
    $new_columns['product_cat'] = $columns['product_cat'];
    $new_columns['date'] = $columns['date'];

    return $new_columns;
}

// Rename "Count" to "Item Count"
add_filter('manage_edit-product_brand_columns', 'webapp_change_brand_count_column');

function webapp_change_brand_count_column($columns) {

    if (isset($_GET['taxonomy']) && $_GET['taxonomy'] === 'product_brand') {

        if (isset($columns['posts'])) {
            $columns['posts'] = 'Item Count';
        }

    }

    return $columns;
}

// Add Enable/Disable option in Brands

add_filter('manage_edit-product_brand_columns', 'webapp_add_brand_status_column');

function webapp_add_brand_status_column($columns) {

    $new_columns = [];

    foreach ($columns as $key => $value) {

        $new_columns[$key] = $value;

        if ($key === 'posts') {
            $new_columns['brand_status'] = 'Enabled';
        }
    }

    return $new_columns;
}

add_filter('manage_product_brand_custom_column', 'webapp_brand_status_column_content', 10, 3);

function webapp_brand_status_column_content($content, $column_name, $term_id) {

    if ($column_name === 'brand_status') {

        $status = get_field('_enabledisable', 'product_brand_' . $term_id);

        $checked = $status ? 'checked' : '';

        $content = '<input type="checkbox" class="brand-status-toggle" data-term="'.$term_id.'" '.$checked.' />';
    }

    return $content;
}

add_action('wp_ajax_webapp_update_brand_status', 'webapp_update_brand_status');

function webapp_update_brand_status() {

    $term_id = intval($_POST['term_id']);
    $status  = $_POST['status'] === 'true' ? 1 : 0;

    update_field('_enabledisable', $status, 'product_brand_' . $term_id);

    wp_send_json_success();

}


// Remove product description box from product edit page
add_action('init', 'remove_product_description_editor');
function remove_product_description_editor() {
    remove_post_type_support('product', 'editor');
}