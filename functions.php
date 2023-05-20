<?php
/**
 * Theme functions and definitions.
 */
// function wpschool_picture_shortcode() {

//     return;
// }
// add_shortcode( 'picture', 'wpschool_picture_shortcode' );


//canonical
add_filter('wpseo_canonical', 'removeCanonical');
function removeCanonical($link) {
	$link = preg_replace('#\\??/page[\\/=]\\d+#', '', $link);
	return $link;
}

//hide out of stock
add_filter('pre_get_posts', 'myshop_show_in_stock', 25);

function myshop_show_in_stock($query) {

    if(
        !is_admin()
        && $query->is_main_query()
        && (is_shop() || is_product_category() || is_product_tag())
    ) {

        $query->set(
            'meta_query',
            array(
                array(
                    'key' => '_stock_status',
                    'value' => 'outofstock',
                    'compare' => 'NOT IN'
                )
            )
        );

    }

}
//фильтр на минимальное количество товара
// Set a minimum number of products requirement before checking out
/*add_action( 'woocommerce_check_cart_items', 'spyr_set_min_num_products' );
function spyr_set_min_num_products() {
	// Only run in the Cart or Checkout pages
	if( is_cart() || is_checkout() ) {
		global $woocommerce;

		// Set the minimum number of products before checking out
		$minimum_num_products = 2;
		// Get the Cart's total number of products
		$cart_num_products = WC()->cart->cart_contents_count;


		if( $cart_num_products < $minimum_num_products ) {
			// Display our error message
	        wc_add_notice( sprintf( '<strong>A Minimum of %s products is required before checking out.</strong>'
	        	. '<br />Current number of items in the cart: %s.',
	        	$minimum_num_products,
	        	$cart_num_products ),
	        'error' );
		}
	}
}*/

/**
 * Update CSS within in Admin
 */
add_action('admin_enqueue_scripts', 'admin_style');
function admin_style() {
	wp_enqueue_style('admin-styles', get_stylesheet_directory_uri() . '/admin.css');
	wp_enqueue_script( 'admin-scripts', get_stylesheet_directory_uri() . '/admin.js', array('jquery'), null, true );
}

add_filter('manage_edit-shop_order_columns', 'wc_new_order_columns');
function wc_new_order_columns($columns) {
	var_dump($columns);
	// exit;
	array_splice($columns, 4, 0, ['custom_order_content' => 'Комментарий менеджера']);

	return $columns;
}

/** изменение названий статусов */
add_filter( 'woocommerce_register_shop_order_post_statuses', 'filter_function_name_7010' );
function filter_function_name_7010( $array ){
	$array['wc-processing']['label_count'] = _n_noop( 'Новый <span class="count">(%s)</span>', 'Новые <span class="count">(%s)</span>', 'woocommerce' );
	$array['wc-on-hold']['label_count'] = _n_noop( 'В обработке <span class="count">(%s)</span>', 'В обработке <span class="count">(%s)</span>', 'woocommerce' );
	$array['wc-completed']['label_count'] = _n_noop( 'Выполенен <span class="count">(%s)</span>', 'Выполенено <span class="count">(%s)</span>', 'woocommerce' );

	return $array;
}

add_filter( 'wc_order_statuses', 'rename_statuses' );
function rename_statuses( $statuses ) {
	$statuses['wc-processing'] = 'Новый';
	$statuses['wc-on-hold'] = 'В обработке';
	$statuses['wc-on-completed'] = 'Выполнен';

	return $statuses;
}

/** изменить действия над заказами */
add_filter( 'bulk_actions-edit-shop_order', 'shop_order_bulk_actions_shinastyle', 11 );
function shop_order_bulk_actions_shinastyle( $actions ) {
	if ( isset( $actions['edit'] ) ) {
		unset( $actions['edit'] );
	}

	$actions['mark_processing'] = __( 'Отметить как НОВЫЙ', 'woocommerce' );
	$actions['mark_on-hold']    = __( 'Отметить как В ОБРАБОТКЕ', 'woocommerce' );
	$actions['mark_completed']  = __( 'Отметить как ВЫПОЛНЕН', 'woocommerce' );
	$actions['mark_cancelled']  = __( 'Отметить как ОТМЕНЕН', 'woocommerce' );

	return $actions;
}

/** запись кто изменил заказ */
add_filter( 'handle_bulk_actions-edit-shop_order', 'handle_shop_order_bulk_actions_shinastyle', 9, 3 );
function handle_shop_order_bulk_actions_shinastyle( $redirect_to, $action, $ids ) {
	$ids = array_map( 'absint', $ids );

	foreach ( $ids as $id ) {
		update_post_meta($id, '_edit_last', get_current_user_id());
	}
}

/**
 * Adds the order processing count to the menu.
 */
add_action( 'admin_head', 'menu_order_count_shinastyle', 11 );
function menu_order_count_shinastyle() {
	global $submenu;

	if ( isset( $submenu['woocommerce'] ) ) {
		// Remove 'WooCommerce' sub menu item
		unset( $submenu['woocommerce'][0] );

		// Add count if user has access
		if ( apply_filters( 'woocommerce_include_processing_order_count_in_menu', true ) && current_user_can( 'manage_woocommerce' ) && ( $order_count = wc_orders_count( 'on-hold' ) ) ) {
			foreach ( $submenu['woocommerce'] as $key => $menu_item ) {
				if ( 0 === strpos( $menu_item[0], _x( 'Orders', 'Admin menu name', 'woocommerce' ) ) ) {
					$submenu['woocommerce'][ $key ][0] .= ' <span class="awaiting-mod awaiting-mod-on-hold update-plugins count-' . $order_count . '"><span class="on-hold-count">' . number_format_i18n( $order_count ) . '</span></span>';
					break;
				}
			}
		}
	}
}

add_action(
	'manage_shop_order_posts_custom_column',
	'sv_wc_cogs_add_order_profit_column_content', 1
);
function sv_wc_cogs_add_order_profit_column_content( $column ) {
	global $post;

	if ( $column == 'custom_order_content' ) {
		echo '<section data-order-notes="'.$post->ID.'">';
		WC_Meta_Box_Order_Notes::output($post);
		echo '</section>';
	}
}
