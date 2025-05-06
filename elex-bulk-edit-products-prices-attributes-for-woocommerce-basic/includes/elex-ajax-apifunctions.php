<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $hook_suffix;
add_action( 'wp_ajax_eh_bep_get_attributes_action', 'elex_bep_get_attributes_action_callback' );
add_action( 'wp_ajax_eh_bep_all_products', 'elex_bep_list_table_all_callback' );
add_action( 'wp_ajax_eh_bep_count_products', 'elex_bep_count_products_callback' );
add_action( 'wp_ajax_eh_bep_clear_products', 'elex_bep_clear_all_callback' );
add_action( 'wp_ajax_eh_bep_update_products', 'elex_bep_update_product_callback' );
add_action( 'wp_ajax_eh_bep_filter_products', 'eh_bep_search_filter_callback' );
add_action( 'wp_ajax_eh_bulk_edit_display_count', 'elex_bep_display_count_callback' );
//edit
add_action( 'wp_ajax_eh_bep_all_products', 'eh_bep_list_table_all_callback' );
// Categories filter.
add_action( 'wp_ajax_eh_bep_send_categories_filter_input_value', 'eh_bep_send_categories_filter_input_value_callback' );
// Filter Checkbox Handler. g
add_action( 'wp_ajax_elex_bep_update_checked_status', 'elex_bep_update_checked_status_callback' );


/** Filter Checkbox Handler. */
function elex_bep_update_checked_status_callback() {
	check_ajax_referer( 'ajax-eh-bep-nonce', '_ajax_eh_bep_nonce' );
	$received_data = ! empty( $_POST ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST ) ) : array();
	if ( 'update' === $received_data['operation'] ) {
		$filter_checkbox_data = ! empty( get_option( 'elex_bep_filter_checkbox_data' ) ) ? get_option( 'elex_bep_filter_checkbox_data' ) : array();
		if ( 'false' === $received_data['checkbox_status'] ) { // unchecked.
			if ( ! in_array( intval( $received_data['checkbox_id'] ), array_map( 'intval', $filter_checkbox_data ), true ) ) { // don't update if already exists.
				array_push( $filter_checkbox_data, $received_data['checkbox_id'] );
				update_option( 'elex_bep_filter_checkbox_data', $filter_checkbox_data );
			}
		} else { // checked.
			$filter_checkbox_data = array_diff( $filter_checkbox_data, array( $received_data['checkbox_id'] ) );
			update_option( 'elex_bep_filter_checkbox_data', array_values( $filter_checkbox_data ) );
		}
		wp_die();
	} elseif ( 'delete' === $received_data['operation'] ) { // reset.
		delete_option( 'elex_bep_filter_checkbox_data' );
		wp_die();
	} elseif ( 'count' === $received_data['operation'] ) { // return count.
		$filter_checkbox_data = ! empty( get_option( 'elex_bep_filter_checkbox_data' ) ) ? get_option( 'elex_bep_filter_checkbox_data' ) : array();
		$size                 = count( $filter_checkbox_data );
		wp_die( wp_json_encode( $size ) );
	} elseif ( 'unselect_all' === $received_data['operation'] ) { // reset.
		update_option( 'elex_bep_filter_checkbox_data', array_values( get_option( 'bulk_edit_filtered_product_ids_for_select_unselect' ) ) );
		wp_die();
	}
}
/**
 * Categories Filter Search
 *
 * @return array
 */
function eh_bep_send_categories_filter_input_value_callback() {
	global $wpdb;
	check_ajax_referer( 'ajax-eh-bep-nonce', '_ajax_eh_bep_nonce' );
	if ( isset( $_POST['input_text_value_categories'] ) ) {
		$input_text                   = isset( $_POST['input_text_value_categories'] ) ? sanitize_text_field( wp_unslash( $_POST['input_text_value_categories'] ) ) : '';
		$get_categories               = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'name__like' => $input_text,
				'number'     => 10,
				'hide_empty' => false
			)
		);
		$categories_values            = array();
		$categories_values['results'] = array();
		$cat_group_collection         = array();
		if ( ! empty( $get_categories ) ) {
			foreach ( $get_categories as $key => $cat_object ) {
				$cat_group         = array();
				$cat_group_items   = array();
				$cat_child         = array();
				$get_cat_child     = get_terms(
					array(
						'taxonomy' => 'product_cat',
						'child_of' => $cat_object->term_id,
					)
				);
				$cat_child['id']   = $cat_object->term_id;
				$cat_child['text'] = $cat_object->name;
				$cat_group_items[] = $cat_child;
				if ( ! empty( $get_cat_child ) || is_object( $get_cat_child ) ) {
					foreach ( $get_cat_child as $key => $cat_childs ) {
						$cat_child         = array();
						$cat_child['id']   = $cat_childs->term_id;
						$cat_child['text'] = $cat_childs->name;
						$cat_group_items[] = $cat_child;
					}
				}
				$cat_group['text']      = $cat_object->name;
				$cat_group['children']  = $cat_group_items;
				$cat_group_collection[] = $cat_group;
			}
		}
		$categories_values['results']            = $cat_group_collection;
		$categories_values['pagination']['more'] = false;
		wp_die( wp_json_encode( $categories_values ) );
		
	}
	wp_die();
}

function elex_bep_display_count_callback() {
	check_ajax_referer( 'ajax-eh-bep-nonce', '_ajax_eh_bep_nonce' );
	$value = isset( $_POST['row_count'] ) ? sanitize_text_field( wp_unslash( $_POST['row_count'] ) ) : '';
	update_option( 'eh_bulk_edit_table_row', $value );
	die( 'success' );
}

function elex_bep_count_products_callback() {
	$filtered_products = elex_bep_get_selected_products();
	check_ajax_referer( 'ajax-eh-bep-nonce', '_ajax_eh_bep_nonce' );
	die( json_encode( $filtered_products ) );
}

function elex_bep_get_attributes_action_callback() {
	check_ajax_referer( 'ajax-eh-bep-nonce', '_ajax_eh_bep_nonce' );
	$attribute_name = isset( $_POST['attrib'] ) ? sanitize_text_field( wp_unslash( $_POST['attrib'] ) ) : '';
	$cat_args       = array(
		'hide_empty' => false,
		'order'      => 'ASC',
	);
	$attributes     = wc_get_attribute_taxonomies();
	foreach ( $attributes as $key => $value ) {
		if ( $attribute_name == $value->attribute_name ) {
			$attribute_name  = $value->attribute_name;
			$attribute_label = $value->attribute_label;
		}
	}
	$attribute_value = get_terms( array_merge(
		array( 'taxonomy' => 'pa_' . $attribute_name ),
		$cat_args
	) );	
	if ( isset( $_POST['attr_and'] ) ) {
		$return = "<optgroup label='" . $attribute_label . "' id='grp_and_" . $attribute_name . "'>";
	} else {
		$return = "<optgroup label='" . $attribute_label . "' id='grp_" . $attribute_name . "'>";
	}
	foreach ( $attribute_value as $key => $value ) {
		$return .= "<option value=\"'pa_" . $attribute_name . ':' . $value->name . "'\">" . $value->name . '</option>';
	}
	$return                     .= '</optgroup>';
	$allowed_atts                = array(
		'align'      => array(),
		'class'      => array(),
		'type'       => array(),
		'id'         => array(),
		'dir'        => array(),
		'lang'       => array(),
		'style'      => array(),
		'xml:lang'   => array(),
		'src'        => array(),
		'alt'        => array(),
		'href'       => array(),
		'rel'        => array(),
		'rev'        => array(),
		'target'     => array(),
		'novalidate' => array(),
		'type'       => array(),
		'value'      => array(),
		'name'       => array(),
		'tabindex'   => array(),
		'action'     => array(),
		'method'     => array(),
		'for'        => array(),
		'width'      => array(),
		'height'     => array(),
		'data'       => array(),
		'title'      => array(),
		'label'		 => array(),
	);
	$allowedposttags['form']     = $allowed_atts;
	$allowedposttags['label']    = $allowed_atts;
	$allowedposttags['input']    = $allowed_atts;
	$allowedposttags['textarea'] = $allowed_atts;
	$allowedposttags['iframe']   = $allowed_atts;
	$allowedposttags['script']   = $allowed_atts;
	$allowedposttags['style']    = $allowed_atts;
	$allowedposttags['strong']   = $allowed_atts;
	$allowedposttags['small']    = $allowed_atts;
	$allowedposttags['table']    = $allowed_atts;
	$allowedposttags['span']     = $allowed_atts;
	$allowedposttags['abbr']     = $allowed_atts;
	$allowedposttags['code']     = $allowed_atts;
	$allowedposttags['pre']      = $allowed_atts;
	$allowedposttags['div']      = $allowed_atts;
	$allowedposttags['img']      = $allowed_atts;
	$allowedposttags['h1']       = $allowed_atts;
	$allowedposttags['h2']       = $allowed_atts;
	$allowedposttags['h3']       = $allowed_atts;
	$allowedposttags['h4']       = $allowed_atts;
	$allowedposttags['h5']       = $allowed_atts;
	$allowedposttags['h6']       = $allowed_atts;
	$allowedposttags['ol']       = $allowed_atts;
	$allowedposttags['ul']       = $allowed_atts;
	$allowedposttags['li']       = $allowed_atts;
	$allowedposttags['em']       = $allowed_atts;
	$allowedposttags['hr']       = $allowed_atts;
	$allowedposttags['br']       = $allowed_atts;
	$allowedposttags['tr']       = $allowed_atts;
	$allowedposttags['td']       = $allowed_atts;
	$allowedposttags['p']        = $allowed_atts;
	$allowedposttags['a']        = $allowed_atts;
	$allowedposttags['b']        = $allowed_atts;
	$allowedposttags['i']        = $allowed_atts;
	$allowedposttags['optgroup'] = $allowed_atts;
	$allowedposttags['option']   = $allowed_atts;
	
	echo   wp_kses($return , $allowedposttags );
	exit;
}

// custom rounding


function eh_bep_round_ceiling( $number, $significance = 1 ) {
	return ( is_numeric( $number ) && is_numeric( $significance ) ) ? ( ceil( $number / $significance ) * $significance ) : false;
}

function elex_bep_update_product_callback() {
	set_time_limit( 300 );
	// HTML tags and attributes allowed in description and short description
	$allowed_html = wp_kses_allowed_html( 'post' );
	check_ajax_referer( 'ajax-eh-bep-nonce', '_ajax_eh_bep_nonce' );
	$selected_products     = '';
	$unchecked_product_ids = !empty( $_POST['unchecked_array'] ) 
	? array_map( 'sanitize_text_field', json_decode( wp_unslash( $_POST['unchecked_array'] ) ) )// phpcs:ignore WordPress.Security.NonceVerification
	: array();
	if ( isset( $_POST['pid'] ) && is_array( $_POST['pid'] ) ) {
		$filtered_product_ids = array_map( 'sanitize_text_field', wp_unslash( $_POST['pid'] ) );// phpcs:ignore WordPress.Security.NonceVerification
		$selected_products    = array_diff( $filtered_product_ids, $unchecked_product_ids );
	}
	$product_data = array();

	$title_select             = isset( $_POST['title_select'] ) ? sanitize_text_field( wp_unslash( $_POST['title_select'] ) ) : '';
	$title_text               = isset( $_POST['title_text'] ) ? sanitize_text_field( wp_unslash( $_POST['title_text'] ) ) : '';
	$replace_title_text       = isset( $_POST['replace_title_text'] ) ? sanitize_text_field( wp_unslash( $_POST['replace_title_text'] ) ) : '';
	$regex_replace_title_text = isset( $_POST['regex_replace_title_text'] ) ? sanitize_text_field( wp_unslash( $_POST['regex_replace_title_text'] ) ) : '';

	$sku_select             = isset( $_POST['sku_select'] ) ? sanitize_text_field( wp_unslash( $_POST['sku_select'] ) ) : '';
	$sku_text               = isset( $_POST['sku_text'] ) ? sanitize_text_field( wp_unslash( $_POST['sku_text'] ) ) : '';
	$sku_delimeter          = isset( $_POST['sku_delimeter'] ) ? sanitize_text_field( wp_unslash( $_POST['sku_delimeter'] ) ) : '';
	$sku_padding            = isset( $_POST['sku_padding'] ) ? sanitize_text_field( wp_unslash( $_POST['sku_padding'] ) ) : '';
	$sku_replace_text       = isset( $_POST['sku_replace_text'] ) ? sanitize_text_field( wp_unslash( $_POST['sku_replace_text'] ) ) : '';
	$regex_sku_replace_text = isset( $_POST['regex_sku_replace_text'] ) ? sanitize_text_field( wp_unslash( $_POST['regex_sku_replace_text'] ) ) : '';

	# sale price options
	$sale_select       = isset( $_POST['sale_select'] ) ? sanitize_text_field( wp_unslash( $_POST['sale_select'] ) ) : '';
	$sale_text         = isset( $_POST['sale_text'] ) ? sanitize_text_field( wp_unslash( $_POST['sale_text'] ) ) : '';
	$sale_round_select = isset( $_POST['sale_round_select'] ) ? sanitize_text_field( wp_unslash( $_POST['sale_round_select'] ) ) : '';
	$sale_round_text   = isset( $_POST['sale_round_text'] ) ? sanitize_text_field( wp_unslash( $_POST['sale_round_text'] ) ) : '';
	$sale_warning      = array();

	# regular price options
	$regular_select       = isset( $_POST['regular_select'] ) ? sanitize_text_field( wp_unslash( $_POST['regular_select'] ) ) : '';
	$regular_round_select = isset( $_POST['regular_round_select'] ) ? sanitize_text_field( wp_unslash( $_POST['regular_round_select'] ) ) : '';
	$regular_text         = isset( $_POST['regular_text'] ) ? sanitize_text_field( wp_unslash( $_POST['regular_text'] ) ) : '';
	$regular_round_text   = isset( $_POST['regular_round_text'] ) ? sanitize_text_field( wp_unslash( $_POST['regular_round_text'] ) ) : '';
	$regular_check_val    = isset( $_POST['regular_check_val'] ) ? sanitize_text_field( wp_unslash( $_POST['regular_check_val'] ) ) : '';

	$catalog_select       = isset( $_POST['catalog_select'] ) ? sanitize_text_field( wp_unslash( $_POST['catalog_select'] ) ) : '';
	$shipping_select      = isset( $_POST['shipping_select'] ) ? sanitize_text_field( wp_unslash( $_POST['shipping_select'] ) ) : '';
	$shipping_unit        = isset( $_POST['shipping_unit'] ) ? sanitize_text_field( wp_unslash( $_POST['shipping_unit'] ) ) : '';
	$shipping_unit_select = isset( $_POST['shipping_unit_select'] ) ? sanitize_text_field( wp_unslash( $_POST['shipping_unit_select'] ) ) : '';

	$stock_manage_select = isset( $_POST['stock_manage_select'] ) ? sanitize_text_field( wp_unslash( $_POST['stock_manage_select'] ) ) : '';
	$stock_status_select = isset( $_POST['stock_status_select'] ) ? sanitize_text_field( wp_unslash( $_POST['stock_status_select'] ) ) : '';

	$quantity_select = isset( $_POST['quantity_select'] ) ? sanitize_text_field( wp_unslash( $_POST['quantity_select'] ) ) : '';
	$quantity_text   = isset( $_POST['quantity_text'] ) ? sanitize_text_field( wp_unslash( $_POST['quantity_text'] ) ) : '';

	$backorder_select = isset( $_POST['backorder_select'] ) ? sanitize_text_field( wp_unslash( $_POST['backorder_select'] ) ) : '';
	$attribute_action = isset( $_POST['attribute_action'] ) ? sanitize_text_field( wp_unslash( $_POST['attribute_action'] ) ) : '';

	$length_select = isset( $_POST['length_select'] ) ? sanitize_text_field( wp_unslash( $_POST['length_select'] ) ) : '';
	$width_select  = isset( $_POST['width_select'] ) ? sanitize_text_field( wp_unslash( $_POST['width_select'] ) ) : '';
	$height_select = isset( $_POST['height_select'] ) ? sanitize_text_field( wp_unslash( $_POST['height_select'] ) ) : '';
	$weight_select = isset( $_POST['weight_select'] ) ? sanitize_text_field( wp_unslash( $_POST['weight_select'] ) ) : '';
	$length_text   = isset( $_POST['length_text'] ) ? sanitize_text_field( wp_unslash( $_POST['length_text'] ) ) : '';
	$width_text    = isset( $_POST['width_text'] ) ? sanitize_text_field( wp_unslash( $_POST['width_text'] ) ) : '';
	$height_text   = isset( $_POST['height_text'] ) ? sanitize_text_field( wp_unslash( $_POST['height_text'] ) ) : '';
	$weight_text   = isset( $_POST['weight_text'] ) ? sanitize_text_field( wp_unslash( $_POST['weight_text'] ) ) : '';

	$hide_price       = isset( $_POST['hide_price'] ) ? sanitize_text_field( wp_unslash( $_POST['hide_price'] ) ) : '';
	$hide_price_role  = ( isset( $_POST['hide_price_role'] ) && '' !== $_POST['hide_price_role'] ) ? sanitize_text_field( wp_unslash( $_POST['hide_price_role'] ) ) : '';
	$price_adjustment = isset( $_POST['price_adjustment'] ) ? sanitize_text_field( wp_unslash( $_POST['price_adjustment'] ) ) : '';
	$featured         = isset( $_POST['is_featured'] ) ? sanitize_text_field( wp_unslash( $_POST['is_featured'] ) ) : '';

	$description              = wp_kses( isset( $_POST['description'] ) ? wp_unslash( $_POST['description'] ) : '', $allowed_html );
	$description_action       = isset( $_POST['description_action'] ) ? sanitize_text_field( wp_unslash( $_POST['description_action'] ) ) : '';
	$short_description        = wp_kses( isset( $_POST['short_description'] ) ? wp_unslash( $_POST['short_description'] ) : '', $allowed_html );
	$short_description_action = isset( $_POST['short_description_action'] ) ? sanitize_text_field( wp_unslash( $_POST['short_description_action'] ) ) : '';

	$gallery_images        = ! empty( $_POST['gallery_images'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['gallery_images'] ) ) : array();
	$gallery_images_action = isset( $_POST['gallery_images_action'] ) ? sanitize_text_field( wp_unslash( $_POST['gallery_images_action'] ) ) : '';
	$main_image            = isset( $_POST['main_image'] ) ? sanitize_text_field( wp_unslash( $_POST['main_image'] ) ) : '';

	$delete_product_action = isset( $_POST['delete_product_action'] ) ? sanitize_text_field( wp_unslash( $_POST['delete_product_action'] ) ) : '';

	$tax_status_action = isset( $_POST['tax_status_action'] ) ? sanitize_text_field( wp_unslash( $_POST['tax_status_action'] ) ) : '';
	$tax_class_action  = isset( $_POST['tax_class_action'] ) ? sanitize_text_field( wp_unslash( $_POST['tax_class_action'] ) ) : '';

	$count_iteration = 0;

	foreach ( $selected_products as $pid => $temp ) {
		$index   = $pid;
		$pid     = $temp;
		$product = wc_get_product( $pid );
		switch ( $hide_price ) {
			case 'yes':
				elex_bep_update_meta_fn( $pid, 'product_adjustment_hide_price_unregistered', 'yes' );
				break;
			case 'no':
				elex_bep_update_meta_fn( $pid, 'product_adjustment_hide_price_unregistered', 'no' );
				break;
		}
		switch ( $price_adjustment ) {
			case 'yes':
				elex_bep_update_meta_fn( $pid, 'product_based_price_adjustment', 'yes' );
				break;
			case 'no':
				elex_bep_update_meta_fn( $pid, 'product_based_price_adjustment', 'no' );
				break;
		}
		if ( '' != $hide_price_role ) {
			elex_bep_update_meta_fn( $pid, 'eh_pricing_adjustment_product_price_user_role', $hide_price_role );
		}
		switch ( $shipping_unit_select ) {
			case 'add':
				$unit     = $product->get_meta( '_wf_shipping_unit' );
				$unit_val = number_format( $unit + $shipping_unit, 6, '.', '' );
				elex_bep_update_meta_fn( $pid, '_wf_shipping_unit', $unit_val );
				break;
			case 'sub':
				$unit     = $product->get_meta( $pid, '_wf_shipping_unit', true );
				$unit_val = number_format( $unit - $shipping_unit, 6, '.', '' );
				elex_bep_update_meta_fn( $pid, '_wf_shipping_unit', $unit_val );
				break;
			case 'replace':
				$unit = $product->get_meta( $pid, '_wf_shipping_unit', true );
				elex_bep_update_meta_fn( $pid, '_wf_shipping_unit', $shipping_unit );
				break;
			default:
				break;
		}

		$temp      = wc_get_product( $pid );
		$parent    = $temp;
		$parent_id = $pid;
		if ( ! empty( $temp ) && $temp->is_type( 'variation' ) ) {
			$parent_id = ( WC()->version < '2.7.0' ) ? $temp->parent->id : $temp->get_parent_id();
			$parent    = wc_get_product( $parent_id );
		}
		
		$temp_type  = ( WC()->version < '2.7.0' ) ? $temp->product_type : $temp->get_type();
		$temp_title = ( WC()->version < '2.7.0' ) ? $temp->post->post_title : $temp->get_title();
		if ( 'simple' == $temp_type  || 'variation' == $temp_type || 'variable' == $temp_type ) {
			$product_data                   = array();
			$product_data['type']           = 'simple';
			$product_data['title']          = $temp_title;
			$product_data['sku']            = $temp->get_sku();
			$product_data['catalog']        = ( WC()->version < '3.0.0' ) ? $temp->get_meta( '_visibility' ) : $temp->get_catalog_visibility();
			$ship_args                      = array( 'fields' => 'ids' );
			$product_data['shipping']       = current( wp_get_object_terms( $pid, 'product_shipping_class', $ship_args ) );
			$product_data['sale']           = (float) $temp->get_sale_price();
			$product_data['regular']        = (float) $temp->get_regular_price();
			$product_data['stock_manage']   = $temp->get_manage_stock();
			$product_data['stock_quantity'] = (float) $temp->get_stock_quantity();
			$product_data['backorder']      = $temp->get_backorders();
			$product_data['stock_status']   = $temp->get_stock_status();
			$product_data['length']         = (float) $temp->get_length();
			$product_data['width']          = (float) $temp->get_width();
			$product_data['height']         = (float) $temp->get_height();
			$product_data['weight']         = (float) $temp->get_weight();

			switch ( $title_select ) {
				case 'set_new':
					$temp->set_name( $title_text );
					$temp->save();
					break;
				case 'append':
					$temp->set_name( $product_data['title'] . $title_text );
					$temp->save();
					break;
				case 'prepand':
					$temp->set_name( $title_text . $product_data['title'] );
					$temp->save();
					break;
				case 'replace':
					$temp->set_name( str_replace( $replace_title_text, $title_text, $product_data['title'] ) );
					$temp->save();
					break;
				case 'regex_replace':
					if ( @preg_replace( '/' . $regex_replace_title_text . '/', $title_text, $product_data['title'] ) != false ) {
						$regex_flags = '';
						if ( ! empty( $_REQUEST['regex_flag_sele_title'] ) ) {
							$regex_flag_sele_title = sanitize_text_field(wp_unslash( $_REQUEST['regex_flag_sele_title'] ));  // phpcs:ignore WordPress.Security.NonceVerification
							foreach ( $regex_flag_sele_title as $reg_val ) {
								$regex_flags .= sanitize_text_field( $reg_val );
							}
						}
						$temp->set_name( preg_replace( '/' . $regex_replace_title_text . '/' . $regex_flags, $title_text, $product_data['title'] ) );
						$temp->save();
					}
					break;
			}
			switch ( $sku_select ) {
				case 'set_new':
					if ( 0 === $index ) {
						$sku_pad_number = 1;
						if ( empty($sku_padding)) {
							$sku_padding = 1;
						}
						$padded_num = str_pad($sku_padding, $sku_padding, '0', STR_PAD_LEFT);
					}
					$padding_reach = pow(10, $sku_padding-1);
					if ( $sku_pad_number == $padding_reach || $sku_pad_number > $padding_reach ) {
						$num = $sku_pad_number;
					} else {
						$num = preg_replace('/[1-9]/', $sku_pad_number, $padded_num);
					}
					if ( 'space' === $sku_delimeter) {
						$new_sku = $sku_text . ' ' . $num ;
					} else {
						$new_sku = $sku_text . $sku_delimeter . $num ;
					}
					//checking unique sku
					$unique = wc_get_product_id_by_sku($new_sku);
					if ( 0 === $unique ) {
						$temp->set_sku( $new_sku );
						$temp->save();
					}
					$sku_pad_number = $num + 1 ;
					break;
				case 'append':
					$sku_val = $product_data['sku'] . $sku_text;
					//checking unique sku
					$unique = wc_get_product_id_by_sku($sku_val);
					if ( 0 === $unique ) {
						$temp->set_sku( $sku_val );
						$temp->save();
					}
					break;
				case 'prepand':
					$sku_val = $sku_text . $product_data['sku'];
					//checking unique sku
					$unique = wc_get_product_id_by_sku($sku_val);
					if ( 0 === $unique ) {
						$temp->set_sku( $sku_val );
						$temp->save();
					}
					break;
				case 'replace':
					$sku_val = str_replace( $sku_replace_text, $sku_text, $product_data['sku'] );
					$unique  = wc_get_product_id_by_sku($sku_val);
					if ( 0 === $unique ) {
						$temp->set_sku( $sku_val );
						$temp->save();
					}
					break;
				case 'regex_replace':
					if ( @preg_replace( '/' . $regex_sku_replace_text . '/', $sku_text, $product_data['sku'] ) !== false ) {
						$regex_flags = '';
						if ( ! empty( $_REQUEST['regex_flag_sele_sku'] ) ) {
							foreach ( array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['regex_flag_sele_sku'] ) ) as $reg_val ) {
								$regex_flags .= $reg_val;
							}
						}
						$sku_val = preg_replace( '/' . $regex_sku_replace_text . '/' . $regex_flags, $sku_text, $product_data['sku'] );
						//checking unique sku
						$unique = wc_get_product_id_by_sku($sku_val);
						if ( 0 === $unique ) {
							$temp->set_sku( $sku_val );
							$temp->save();
						}
					}
					break;
			}
			// Set featured
			if ( isset( $_REQUEST['is_featured'] ) && ! empty( $_REQUEST['is_featured'] ) ) {
				$parent->set_featured( $featured );
				$parent->save();
			}
			$product_details = $temp->get_data();
			// Product description
			if ( isset( $description ) && '' != $description && '' != $description_action ) {
				if ( 'append' == $description_action ) {
					$desc = $product_details['description'] . $description;
				} elseif ( 'prepend' == $description_action ) {
					$desc = $description . $product_details['description'];
				} else {
					$desc = $description;
				}
				$temp->set_description( $desc );
				$temp->save();
			}
			// Product short description
			if ( isset( $short_description ) && '' != $short_description && '' != $short_description_action ) {
				if ( 'append' == $short_description_action ) {
					$short_desc = $product_details['short_description'] . $short_description;
				} elseif ( 'prepend' == $short_description_action ) {
					$short_desc = $short_description . $product_details['short_description'];
				} else {
					$short_desc = $short_description;
				}
				$temp->set_short_description( $short_desc );
				$temp->save();
			}
			// Main image
			$edit_data['main_image'] = '';
			if ( isset( $main_image ) && $main_image ) {
				$edit_data['main_image'] = $temp->get_image_id();
				$image_id                = attachment_url_to_postid( $main_image );
				$temp->set_image_id( $image_id );
				$temp->save();
			}
			// Gallery images
			if ( !empty( $gallery_images ) && '' != $gallery_images_action ) {
				$gallery_image_ids = array();
				foreach ( $gallery_images as $image_index => $image_url ) {
					$gallery_image_id = attachment_url_to_postid( $image_url );
					array_push( $gallery_image_ids, $gallery_image_id );
				}
				if ( 'add' == $gallery_images_action ) {
					$gallery_image_ids = array_merge( $gallery_image_ids, $temp->get_gallery_image_ids() );
				} elseif ( 'remove' == $gallery_images_action ) {
					$flag_array = array();
					if ( ! empty( $temp->get_gallery_image_ids() ) ) {
						foreach ( $temp->get_gallery_image_ids() as $i_ids ) {
							if ( ! in_array( $i_ids, $gallery_image_ids ) ) {
								array_push( $flag_array, $i_ids );
							}
						}
					}
					
					$gallery_image_ids = $flag_array;
					
				}
				$temp->set_gallery_image_ids( $gallery_image_ids );
				$temp->save();
			}
			// Delete
			if ( $temp ) {
				if ( isset( $delete_product_action ) && '' !== $delete_product_action ) {
					if ( 'move_to_trash' === $delete_product_action ) {
						$temp->delete( false );
					} else {
						$temp->delete( true );
					}
					continue; // Skip further processing for this product
				}
			}		
			if ( 'variation' != $temp_type ) {
				if ( WC()->version < '3.0.0' ) {
					$temp->set_catalog_visibility( $catalog_select );
					$temp->save();
				} else {
					$options        = array_keys( wc_get_product_visibility_options() );
					$catalog_select = wc_clean( $catalog_select );
					if ( in_array( $catalog_select, $options, true ) ) {
						$parent->set_catalog_visibility( $catalog_select );
						$parent->save();
					}
				}
			}

			if ( '' != $shipping_select ) {
				wp_set_object_terms( (int) $pid, (int) $shipping_select, 'product_shipping_class' );
			}

			/**
			 *  $sal_val for comparing with regular price, if $sal_val less than regular then only we are updating.
			 */
			if ( $sale_select ) {
				$sal_val = eh_bep_check_sale_price( $sale_select, $sale_text, $sale_round_text, $sale_round_select, $product_data['sale'], $regular_check_val );
			} else {
				$sal_val = $temp->get_sale_price();
			}
			switch ( $regular_select ) {
				case 'up_percentage':
					if ( '' !== $product_data['regular'] ) {
						$per_val = $product_data['regular'] * ( $regular_text / 100 );
						$cal_val = $product_data['regular'] + $per_val;
						if ( '' !== $regular_round_select ) {
							if ( '' === $regular_round_text ) {
								$regular_round_text = 1;
							}
							$got_regular = $cal_val;
							switch ( $regular_round_select ) {
								case 'up':
									$cal_val = eh_bep_round_ceiling( $got_regular, $regular_round_text );
									break;
								case 'down':
									$cal_val = eh_bep_round_ceiling( $got_regular, -$regular_round_text );
									break;
							}
						}
						$regular_val = wc_format_decimal( $cal_val, '', true );

						if ( 'variable' !== $temp_type && $sal_val < $regular_val ) {
							$temp->set_regular_price( $regular_val );
							$temp->save();
						} else {

							if ( 'variable' !== $temp_type ) {
								array_push( $sale_warning, 'Regular' );
								array_push( $sale_warning, $temp_type );
							}
						}
					}
					break;
				case 'down_percentage':
					if ( '' !== $product_data['regular'] ) {
						$per_val = $product_data['regular'] * ( $regular_text / 100 );
						$cal_val = $product_data['regular'] - $per_val;
						if ( '' !== $regular_round_select ) {
							if ( '' === $regular_round_text ) {
								$regular_round_text = 1;
							}
							$got_regular = $cal_val;
							switch ( $regular_round_select ) {
								case 'up':
									$cal_val = eh_bep_round_ceiling( $got_regular, $regular_round_text );
									break;
								case 'down':
									$cal_val = eh_bep_round_ceiling( $got_regular, -$regular_round_text );
									break;
							}
						}
						$regular_val = wc_format_decimal( $cal_val, '', true );
						
						if ( 'variable' !== $temp_type && $sal_val < $regular_val ) {
							$temp->set_regular_price( $regular_val );
							$temp->save();
						} else {
							if ( 'variable' !== $temp_type ) {
								array_push( $sale_warning, 'Regular' );
								array_push( $sale_warning, $temp_type );
							}
						}
					}
					break;
				case 'up_price':
					if ( '' !== $product_data['regular'] ) {
						$cal_val = $product_data['regular'] + $regular_text;
						if ( '' !== $regular_round_select ) {
							if ( '' === $regular_round_text ) {
								$regular_round_text = 1;
							}
							$got_regular = $cal_val;
							switch ( $regular_round_select ) {
								case 'up':
									$cal_val = eh_bep_round_ceiling( $got_regular, $regular_round_text );
									break;
								case 'down':
									$cal_val = eh_bep_round_ceiling( $got_regular, -$regular_round_text );
									break;
							}
						}
						$regular_val = wc_format_decimal( $cal_val, '', true );
						
						if ( 'variable' !== $temp_type && $sal_val < $regular_val ) {
							$temp->set_regular_price( $regular_val );
							$temp->save();
						} else {

							if ( 'variable' !== $temp_type ) {
								array_push( $sale_warning, 'Regular' );
								array_push( $sale_warning, $temp_type );
							}
						}
					}
					break;
				case 'down_price':
					if ( '' !== $product_data['regular'] ) {
						$cal_val = $product_data['regular'] - $regular_text;
						if ( '' !== $regular_round_select ) {
							if ( '' === $regular_round_text ) {
								$regular_round_text = 1;
							}
							$got_regular = $cal_val;
							switch ( $regular_round_select ) {
								case 'up':
									$cal_val = eh_bep_round_ceiling( $got_regular, $regular_round_text );
									break;
								case 'down':
									$cal_val = eh_bep_round_ceiling( $got_regular, -$regular_round_text );
									break;
							}
						}
						$regular_val = wc_format_decimal( $cal_val, '', true );
						
						if ( 'variable' !== $temp_type && $sal_val < $regular_val ) {
							$temp->set_regular_price( $regular_val );
							$temp->save();
						} else {
							if ( 'variable' !== $temp_type ) {
								array_push( $sale_warning, 'Regular' );
								array_push( $sale_warning, $temp_type );
							}
						}
					}
					break;
				case 'flat_all':
					$regular_val = wc_format_decimal( $regular_text, '', true );
					
					if ( 'variable' !== $temp_type && $sal_val < $regular_val ) {
						$temp->set_regular_price( $regular_val );
						$temp->save();
					} else {
						if ( 'variable' !== $temp_type ) {
							array_push( $sale_warning, 'Regular' );
							array_push( $sale_warning, $temp_type );
						}
					}
					break;
			}
			switch ( $sale_select ) {
				case 'up_percentage':
					if ( '' !== $product_data['sale'] ) {
						$collect_product_data['sale'] = $product_data['sale'];
						$sale_val                     = eh_bep_check_sale_price( $sale_select, $sale_text, $sale_round_text, $sale_round_select, $product_data['sale'], $regular_check_val );
						$reg_val                      = $temp->get_regular_price();
						if ( 'variable' !== $temp_type && $sale_val < $reg_val ) {
							$temp->set_sale_price( $sale_val );
							$temp->save();
						} else {

							if ( 'variable' !== $temp_type ) {
								array_push( $sale_warning, 'Sales' );
								array_push( $sale_warning, $temp_type );
							}
							if ( isset( $fields_and_values['regular_select'] ) ) {
								$temp->set_regular_price( $product_data['regular'] );
								$temp->save();
							}
						}
					}
					break;
				case 'down_percentage':
					if ( '' !== $product_data['sale'] || $regular_check_val ) {
						$sale_val = eh_bep_check_sale_price( $sale_select, $sale_text, $sale_round_text, $sale_round_select, $product_data['sale'], $regular_check_val );
						$reg_val  = $temp->get_regular_price();
						if ( $reg_val && $regular_check_val) {
							$per_val  = $reg_val - ( $reg_val * ( $sale_text / 100 ) );
							$sale_val = wc_format_decimal( $per_val, '', true );
							// leave sale price blank if sale price decreased by 100%.
							if ( 0 === intval( $sale_val ) || $sale_val < 0 ) {
								$sale_val = '';
							}
						}
						if ( 'variable' !== $temp_type && $sale_val < $reg_val ) {
							$temp->set_sale_price( $sale_val );
							$temp->save();
						} else {
							if ( 'variable' !== $temp_type ) {
								array_push( $sale_warning, 'Sales' );
								array_push( $sale_warning, $temp_type );
							}

							if ( isset( $fields_and_values['regular_select'] ) ) {
								$temp->set_regular_price( $product_data['regular'] );
								$temp->save();
							}
						}
					}
					break;
				case 'up_price':
					if ( '' !== $product_data['sale'] ) {
						$sale_val = eh_bep_check_sale_price( $sale_select, $sale_text, $sale_round_text, $sale_round_select, $product_data['sale'], $regular_check_val );
						$reg_val  = $temp->get_regular_price();
						if ( 'variable' !== $temp_type && $sale_val < $reg_val ) {
							$temp->set_sale_price( $sale_val );
							$temp->save();
						} else {
							if ( 'variable' !== $temp_type ) {
								array_push( $sale_warning, 'Sales' );
								array_push( $sale_warning, $temp_type );
							}

							if ( isset( $fields_and_values['regular_select'] ) ) {
								$temp->set_regular_price( $product_data['regular'] );
								$temp->save();
							}
						}
					}
					break;
				case 'down_price':
					if ( '' !== $product_data['sale'] || $regular_check_val ) {
						$collect_product_data['sale'] = $product_data['sale'];
						$sale_val                     = eh_bep_check_sale_price( $sale_select, $sale_text, $sale_round_text, $sale_round_select, $product_data['sale'], $regular_check_val );
						$reg_val                      = $temp->get_regular_price();
						if ( $reg_val && $regular_check_val) {
							$per_val  = $reg_val - $sale_text ;
							$sale_val = wc_format_decimal( $per_val, '', true );
							// leave sale price blank if sale price decreased by 100%.
							if ( 0 === intval( $sale_val ) || $sale_val < 0 ) {
								$sale_val = '';
							}
						}
						if ( 'variable' !== $temp_type && $sale_val < $reg_val ) {
							$temp->set_sale_price( $sale_val );
							$temp->save();
						} else {
							if ( 'variable' !== $temp_type ) {
								array_push( $sale_warning, 'Sales' );
								array_push( $sale_warning, $temp_type );
							}
							if ( isset( $fields_and_values['regular_select'] ) ) {
								$temp->set_regular_price( $product_data['regular'] );
								$temp->save();
							}
						}
					}
					break;
				case 'flat_all':
					$sale_val = eh_bep_check_sale_price( $sale_select, $sale_text, $sale_round_text, $sale_round_select, $product_data['sale'], $regular_check_val );
					$reg_val  = $temp->get_regular_price();
					if ( 'variable' !== $temp_type && $sale_val < $reg_val ) {
						$temp->set_sale_price( $sale_val );
						$temp->save();
					} else {
						if ( 'variable' !== $temp_type ) {
							array_push( $sale_warning, 'Sales' );
							array_push( $sale_warning, $temp_type );
						}

						if ( isset( $fields_and_values['regular_select'] ) ) {
							$temp->set_regular_price( $product_data['regular'] );
							$temp->save();
						}
					}
					break;
			}

			if ( $temp->get_sale_price() !== '' && $temp->get_regular_price() !== '' ) {
				$temp->set_price( $temp->get_sale_price() );
				$temp->save();
			} elseif ( $temp->get_sale_price() === '' && $temp->get_regular_price() !== '' ) {
				$temp->set_price( $temp->get_regular_price() );
				$temp->save();
			} elseif ( $temp->get_sale_price() !== '' && $temp->get_regular_price() === '' ) {
				$temp->set_price( $temp->get_sale_price() );
				$temp->save();
			} elseif ( $temp->get_sale_price() === '' && $temp->get_regular_price() === '' ) {
				$temp->set_price( '' );
				$temp->save();
			}
			switch ( $stock_manage_select ) {
				case 'yes':
					$temp->set_manage_stock( 'yes' );
					$temp->save();
					break;
				case 'no':
					$temp->set_manage_stock( 'no' );
					$temp->save();
					break;
			}
			switch ( $tax_status_action ) {
				case 'taxable':
					$temp->set_tax_status( $tax_status_action );
					$temp->save();
					break;
				case 'shipping':
					$temp->set_tax_status( $tax_status_action );
					$temp->save();
					break;
				case 'none':
					$temp->set_tax_status( $tax_status_action );
					$temp->save();
					break;
			}
			if ( 'default' == $tax_class_action ) {
					$temp->set_tax_class( '' );
					$temp->save();
			} else {
					$temp->set_tax_class( $tax_class_action );
					$temp->save();
			}
			switch ( $quantity_select ) {
				case 'add':
					$quantity_val = number_format( $product_data['stock_quantity'] + $quantity_text, 6, '.', '' );
					$temp->set_stock_quantity( $quantity_val );
					$temp->save();
					break;
				case 'sub':
					$quantity_val = number_format( $product_data['stock_quantity'] - $quantity_text, 6, '.', '' );
					$temp->set_stock_quantity( $quantity_val );
					$temp->save();
					break;
				case 'replace':
					$quantity_val = number_format( $quantity_text, 6, '.', '' );
					$temp->set_stock_quantity( $quantity_val );
					$temp->save();
					break;
			}
			switch ( $backorder_select ) {
				case 'no':
					$temp->set_backorders( 'no' );
					$temp->save();
					break;
				case 'notify':
					$temp->set_backorders( 'notify' );
					$temp->save();
					break;
				case 'yes':
					$temp->set_backorders( 'yes' );
					$temp->save();
					break;
			}
			switch ( $stock_status_select ) {
				case 'instock':
					$temp->set_stock_status( 'instock' );
					$temp->save();
					break;
				case 'outofstock':
					$temp->set_stock_status( 'outofstock' );
					$temp->save();
					break;
				case 'onbackorder':
					$temp->set_stock_status( 'onbackorder' );
					$temp->save();
					break;
			}
			switch ( $length_select ) {
				case 'add':
					$length_val = $product_data['length'] + $length_text;
					$temp->set_length( $length_val );
					$temp->save();
					break;
				case 'sub':
					$length_val = $product_data['length'] - $length_text;
					$temp->set_length( $length_val );
					$temp->save();
					break;
				case 'replace':
					$length_val = $length_text;
					$temp->set_length( $length_val );
					$temp->save();
					break;
			}
			switch ( $width_select ) {
				case 'add':
					$width_val = $product_data['width'] + $width_text;
					$temp->set_width( $width_val );
					$temp->save();
					break;
				case 'sub':
					$width_val = $product_data['width'] - $width_text;
					$temp->set_width( $width_val );
					$temp->save();
					break;
				case 'replace':
					$width_val = $width_text;
					$temp->set_width( $width_val );
					$temp->save();
					break;
			}
			switch ( $height_select ) {
				case 'add':
					$height_val = $product_data['height'] + $height_text;
					$temp->set_height( $height_val );
					$temp->save();
					break;
				case 'sub':
					$height_val = $product_data['height'] - $height_text;
					$temp->set_height( $height_val );
					$temp->save();
					break;
				case 'replace':
					$height_val = $height_text;
					$temp->set_height( $height_val );
					$temp->save();
					break;
			}
			switch ( $weight_select ) {
				case 'add':
					$weight_val = $product_data['weight'] + $weight_text;
					$temp->set_weight( $weight_val );
					$temp->save();
					break;
				case 'sub':
					$weight_val = $product_data['weight'] - $weight_text;
					$temp->set_weight( $weight_val );
					$temp->save();
					break;
				case 'replace':
					$weight_val = $weight_text;
					$temp->set_weight( $weight_val );
					$temp->save();
					break;
			}
			wc_delete_product_transients( $pid );
		}

				// Edit Attributes
		if ( 'variation' != $temp_type && ! empty( $_POST['attribute'] ) ) {
			$i                   = 0;
			$is_variation        = 0;
			$prev_value          = '';
			$_product_attributes = $temp->get_meta( '_product_attributes' );
			$attr_undo           = $_product_attributes;
			if ( ! empty( $attr_undo ) ) {

				foreach ( $attr_undo as $key => $val ) {
					$attr_undo[ $key ]['value'] = wc_get_product_terms( $pid, $key );
				}
	
			}
			if ( isset( $_POST['attribute_variation'] ) ) {
				$attribute_variation = sanitize_text_field( wp_unslash( $_POST['attribute_variation'] ) );
			
				switch ( $attribute_variation ) {
					case 'add':
						$is_variation = 1;
						break;
					case 'remove':
						$is_variation = 0;
						break;
				}
			}

			if ( ! empty( $_POST['attribute_value'] ) ) {
				$raw_values       = wp_unslash( $_POST['attribute_value'] );
				$attribute_values = array_map( 'sanitize_text_field', $raw_values );
				foreach ( $attribute_values as $key => $value ) {

					$value     = stripslashes( sanitize_text_field( $value ) );
					$value     = preg_replace( '/\'/', '', $value );
					$att_slugs = explode( ':', $value );
					if ( '' == $_POST['attribute_variation']  && isset( $_product_attributes[ $att_slugs[0] ] ) ) {
						$is_variation = $_product_attributes[ $att_slugs[0] ]['is_variation'];
					}
					if ( $prev_value != $att_slugs[0] ) {
						$i = 0;
					}
					$prev_value = $att_slugs[0];
					if ( 'replace' == sanitize_text_field(wp_unslash( $_POST['attribute_action'] )) && 0 == $i ) {
						wp_set_object_terms( $pid, $att_slugs[1], $att_slugs[0] );
						$i++;
					} else {
						wp_set_object_terms( $pid, $att_slugs[1], $att_slugs[0], true );
					}
					$thedata = array(
						$att_slugs[0] => array(
							'name'         => $att_slugs[0],
							'value'        => $att_slugs[1],
							'is_visible'   => '1',
							'is_taxonomy'  => '1',
							'is_variation' => $is_variation,
						),
					);
					if ( sanitize_text_field( wp_unslash($_POST['attribute_action'] )) == 'add' || sanitize_text_field( wp_unslash($_POST['attribute_action'] )) == 'replace' ) {
						$_product_attr = get_post_meta( $pid, '_product_attributes', true );
						if ( ! empty( $_product_attr ) ) {
							update_post_meta( $pid, '_product_attributes', array_merge( $_product_attr, $thedata ) );
						} else {
							update_post_meta( $pid, '_product_attributes', $thedata );
						}
					}
					if ( sanitize_text_field( wp_unslash($_POST['attribute_action'] )) == 'remove' ) {
						wp_remove_object_terms( $pid, $att_slugs[1], $att_slugs[0] );
					}
					$product = wc_get_product( $pid );           
					if ( class_exists(Automattic\WooCommerce\Internal\ProductAttributesLookup\LookupDataStore::class ) ) {             
						$data_store = new Automattic\WooCommerce\Internal\ProductAttributesLookup\LookupDataStore();          
						$data_store->create_data_for_product( $product );    
					}
				}
			}
			if ( ! empty( $_POST['new_attribute_values'] ) || '' != $_POST['new_attribute_values'] ) {
				$ar1 = explode( ',', sanitize_text_field(wp_unslash( $_POST['attribute'] )) );
				foreach ( $ar1 as $key => $value ) {

					foreach ( $_POST['new_attribute_values'] as $key_index => $value_slug ) {

						$att_s = 'pa_' . $value;

						if ( $prev_value != $att_s ) {
							$i = 0;
						}

						if ( isset($_POST['attribute_variation']) && '' == $_POST['attribute_variation']  && isset( $_product_attributes[ $att_s ] ) ) {
							$is_variation = $_product_attributes[ $att_s ]['is_variation'];
						}

						$prev_value = $att_s;
						if ( 'replace' == sanitize_text_field(wp_unslash( $_POST['attribute_action'] )) && 0 == $i ) {
							wp_set_object_terms( $pid, $value_slug, $att_s );
							$i++;
						} else {
							wp_set_object_terms( $pid, $value_slug, $att_s, true );
						}
						$thedata = array(
							$att_s => array(
								'name'         => $att_s,
								'value'        => $value_slug,
								'is_visible'   => '1',
								'is_taxonomy'  => '1',
								'is_variation' => $is_variation,
							),
						);
						if ( 'add' == sanitize_text_field( wp_unslash( $_POST['attribute_action'] ) ) || sanitize_text_field( wp_unslash( $_POST['attribute_action'] ) ) == 'replace' ) {
							$_product_attr = get_post_meta( $pid, '_product_attributes', true );
							if ( ! empty( $_product_attr ) ) {
								update_post_meta( $pid, '_product_attributes', array_merge( $_product_attr, $thedata ) );
							} else {
								update_post_meta( $pid, '_product_attributes', $thedata );
							}
							$product = wc_get_product( $pid );           
							if ( class_exists(Automattic\WooCommerce\Internal\ProductAttributesLookup\LookupDataStore::class ) ) {             
								$data_store = new Automattic\WooCommerce\Internal\ProductAttributesLookup\LookupDataStore();          
								$data_store->create_data_for_product( $product );    
							}
						}
					}
				}
			}
		}
				// category feature
		if ( isset( $_POST['categories_to_update'] ) &&  isset($_POST['category_update_option']) && sanitize_text_field(wp_unslash( $_POST['category_update_option']) ) != 'cat_none' ) {
			$existing_cat = wp_get_object_terms( $pid, 'product_cat' );
			if ( isset( $_POST['category_update_option'] ) && sanitize_text_field( wp_unslash($_POST['category_update_option'] )) == 'cat_add' ) {
				$temparr = array();
				foreach ( $existing_cat as $cat_key => $cat_val ) {
					array_push( $temparr, (int) $cat_val->term_id );
				}
				$categories_to_update_raw_val = wp_unslash( $_POST['categories_to_update'] );
				$categories_to_update         = array_map( 'sanitize_text_field', $categories_to_update_raw_val );
				foreach ( $categories_to_update as $key => $value ) {
					if ( ! in_array(  (int) $value, $temparr, true )) {
						array_push( $temparr, (int) $value);
					}
					wp_set_object_terms( $pid, $temparr, 'product_cat' );
				}
			} elseif (
				isset( $_POST['category_update_option'] ) && sanitize_text_field(wp_unslash( $_POST['category_update_option']) ) == 'cat_replace' ) {
				$temparr                      = array();
				$categories_to_update_raw_val = wp_unslash( $_POST['categories_to_update'] );
				$categories_to_update         = array_map( 'sanitize_text_field', $categories_to_update_raw_val );
				// Sanitize input
				$categories_to_update = array_map( 'intval', $categories_to_update );
				foreach ( $categories_to_update as $key => $val ) {
					array_push( $temparr, (int) $val );
				}
					wp_set_object_terms( $pid, $temparr, 'product_cat' );
			} elseif (
				 // phpcs:ignore WordPress.Security.NonceVerification
				 isset( $_POST['category_update_option'] ) && sanitize_text_field( wp_unslash ($_POST['category_update_option'])) == 'cat_remove' ) {
				$temparr_remove = array();
				foreach ( $existing_cat as $cat_rem_key => $cat_rem_val ) {
					
					if ( ! in_array( (int) $cat_rem_val->term_id, $_POST['categories_to_update'] ) ) {
						array_push( $temparr_remove, (int) $cat_rem_val->term_id );
					}
				}
				wp_set_object_terms( $pid, $temparr_remove, 'product_cat' );
			}	
		}
		++$count_iteration;
	wc_delete_product_transients( $pid );

	}
	if ( isset( $_POST['index_val'] ) && isset( $_POST['chunk_length'] ) && ( $_POST['index_val'] == $_POST['chunk_length'] - 1 ) ) {
		array_push( $sale_warning, 'done' );
		die( json_encode( $sale_warning ) );
	}
	die( json_encode( $sale_warning ) );
}

function elex_bep_update_meta_fn( $id, $key, $value ) {
	$product = wc_get_product( $id );
	$product->update_meta_data( $key, $value);
	$product->save();
}

function elex_bep_list_table_all_callback() {
	check_ajax_referer( 'ajax-eh-bep-nonce', '_ajax_eh_bep_nonce' );
	$obj = new Elex_DataTables();
	$obj->input();
	$obj->ajax_response( '1' );
}

function elex_bep_clear_all_callback() {
	check_ajax_referer( 'ajax-eh-bep-nonce', '_ajax_eh_bep_nonce' );
	update_option( 'eh_bulk_edit_choosed_product_id', elex_bep_get_first_products() );
	$obj = new Elex_DataTables();
	$obj->input();
	$obj->ajax_response();
}

/** List table. */
function eh_bep_list_table_all_callback() {
	check_ajax_referer( 'ajax-eh-bep-nonce', '_ajax_eh_bep_nonce' );
	$obj = new Eh_DataTables();
	$obj->input();
	$obj->ajax_response( '1' );
}

/** Search filter. */
function eh_bep_search_filter_callback() {
	set_time_limit( 300 );
	check_ajax_referer( 'ajax-eh-bep-nonce', '_ajax_eh_bep_nonce' );
	$obj_fil = new Elex_DataTables();
	$obj_fil->input();
	$obj_fil->ajax_response( '1' );
}

function elex_bep_get_selected_products( $table_obj = null ) {
	$sel_ids = array();
	 // phpcs:ignore WordPress.Security.NonceVerification
	if ( isset( $_REQUEST['count_products'] ) ) {
		$sel_ids = get_option( 'xa_bulk_selected_ids' );
		return $sel_ids;
	}
	delete_option( 'xa_bulk_selected_ids' );
	 // phpcs:ignore WordPress.Security.NonceVerification
	$page_no           = ! empty( $_REQUEST['paged'] ) ? sanitize_text_field(wp_unslash( $_REQUEST['paged'] )) : 1;
	$selected_products = array();
	$per_page          = ( get_option( 'eh_bulk_edit_table_row' ) ) ? get_option( 'eh_bulk_edit_table_row' ) : 20;
	$pid_to_include    = elex_bep_filter_products();

	update_option( 'xa_bulk_selected_ids', $pid_to_include );
	$sel_chunk = array_chunk( $pid_to_include, $per_page, true );
	if ( ! empty( $sel_chunk ) ) {
		$ids_per_page = $sel_chunk[ $page_no - 1 ];
		foreach ( $ids_per_page as $ids ) {
			$selected_products[ $ids ] = wc_get_product( $ids );
		}
	}

	$total_pages = count( $sel_chunk );
	 // phpcs:ignore WordPress.Security.NonceVerification
	if ( isset( $_REQUEST['page'] ) && ! empty( $table_obj ) && ( 1 ==  $total_pages ) ) {
		$total_pages++;
	}
	$ele_on_page = count( $pid_to_include );
	if ( ! empty( $table_obj ) ) {
		$table_obj->set_pagination_args(
			array(
				'total_items' => $ele_on_page,
				'per_page'    => $ele_on_page,
				'total_pages' => $total_pages,
			)
		);
	}

	if ( ! empty( $selected_products ) ) {
		return $selected_products;
	}
}

function elex_bep_get_categories( $categories, $subcat ) {
	$filter_categories   = array();
	$selected_categories = $categories;
	$t_arr               = array();
	if ( $subcat ) {
		while ( ! empty( $selected_categories ) ) {
			$slug_name = $selected_categories[0];
			$slug_name = trim( $slug_name, "\'" );
			array_push( $filter_categories, $slug_name );
			unset( $selected_categories[0] );
			$t_arr               = elex_bep_subcats_from_parentcat_by_slug( $slug_name );
			$selected_categories = array_merge( $selected_categories, $t_arr );
		}
	} else {
		foreach ( $categories as $category ) {
			array_push( $filter_categories, $category );
		}
	}
	return $filter_categories;
}

function elex_bep_filter_products( $data = '' ) {
	global $wpdb;
	$prefix = $wpdb->prefix;
	if ( empty( $data ) ) {
		$data_to_filter = $_REQUEST;
	} else {
		$data_to_filter = $data;
	}
	$query = elexBeBWPFluent()->table('posts')
	->select('posts.ID')
	->leftJoin('term_relationships', 'term_relationships.object_id', '=', 'posts.ID')
	->leftJoin('term_taxonomy', 'term_taxonomy.term_taxonomy_id', '=', 'term_relationships.term_taxonomy_id')
	->leftJoin('terms', 'terms.term_id', '=', 'term_taxonomy.term_id')
	->leftJoin('postmeta', 'postmeta.post_id', '=', 'posts.ID')
	->where('posts.post_type', '=', 'product')
	->where('posts.post_status', '=', 'publish')
	->where('term_taxonomy.taxonomy', '=', 'product_type')
	->whereIn('terms.slug', ['simple'])
	->groupBy('posts.ID');
	
	if ( isset( $data_to_filter['product_title_select'] ) && 'all' != sanitize_text_field( $data_to_filter['product_title_select'] ) && '' != $data_to_filter['product_title_text'] ) {
		switch ($data_to_filter['product_title_select']) {
			case 'starts_with':
				$query->where('posts.post_title', 'LIKE', $data_to_filter['product_title_text'] . '%');
				break;
			case 'ends_with':
				$query->where('posts.post_title', 'LIKE', '%' . $data_to_filter['product_title_text']);
				break;
			case 'contains':
				$query->where('posts.post_title', 'LIKE', '%' . $data_to_filter['product_title_text'] . '%');
				break;
			case 'title_regex':
				$query->where('posts.post_title', 'REGEXP', $data_to_filter['product_title_text']);
				break;
		}
	}
	$price_query  = '';
	$filter_range = ! empty( $data_to_filter['range'] ) ? $data_to_filter['range'] : '';
	if ( 'all' != $filter_range && ! empty( $filter_range ) ) {
		// Price filtering
		if ('|' != $filter_range) {
			$query->where('postmeta.meta_key', '=', '_regular_price')->where('postmeta.meta_value', $filter_range, (int) $data_to_filter['desired_price']);
		} else {
			$query->where('postmeta.meta_key', '=', '_regular_price')->whereBetween('postmeta.meta_value', $data_to_filter['minimum_price'], $data_to_filter['maximum_price']);
		}
	}

	$attr_condition  = '';
	$attribute_value = '';
	if ( ! empty( $data_to_filter['attribute_value_filter'] ) && is_array( $data_to_filter['attribute_value_filter'] ) ) {
		$filters = array_map(function( $item) {
			return trim($item, "'\\"); // removes both slashes and quotes
		}, $data_to_filter['attribute_value_filter']);
		$parsed  = [];
		foreach ($filters as $filter) {
			$parts    = explode(':', $filter);
			$taxonomy = isset($parts[0]) ? $parts[0] : null;
			$slug     = isset($parts[1]) ? $parts[1] : null;
			$parsed[] = ['taxonomy' => $taxonomy, 'slug' => $slug];
		}
		$attribute_value_filter_ids = elexBeBWPFluent()->table('posts')
		->leftJoin('term_relationships', 'posts.ID', '=', 'term_relationships.object_id')
		->leftJoin('term_taxonomy', 'term_relationships.term_taxonomy_id', '=', 'term_taxonomy.term_taxonomy_id')
		->leftJoin('terms', 'term_taxonomy.term_id', '=', 'terms.term_id')
		->where('posts.post_type', 'product')
		->where('posts.post_status', 'publish')
		->where(function ( $q) use ( $parsed) {
			foreach ($parsed as $pair) {
				$q->orWhere(function ( $subQ) use ( $pair) {
					$subQ->where('term_taxonomy.taxonomy', $pair['taxonomy'])
						->where('terms.slug', $pair['slug']);
				});
			}
		})
		->groupBy('posts.ID')
		->select('posts.ID')
		->get();
		$attribute_value_filter_ids = wp_list_pluck( $attribute_value_filter_ids, 'ID' );
	}
	if ( ! empty( $data_to_filter['attribute_value_and_filter'] ) && is_array( $data_to_filter['attribute_value_and_filter'] ) ) {
		$filters = array_map(function( $item) {
			return trim($item, "'\\"); // removes both slashes and quotes
		}, $data_to_filter['attribute_value_and_filter']);
		$parsed  = [];
		foreach ($filters as $filter) {
			$parts    = explode(':', $filter);
			$taxonomy = isset($parts[0]) ? $parts[0] : null;
			$slug     = isset($parts[1]) ? $parts[1] : null;
			$parsed[] = ['taxonomy' => $taxonomy, 'slug' => $slug];
		}
		$attribute_value_and_filter_ids = elexBeBWPFluent()->table('posts')
		->leftJoin('term_relationships', 'posts.ID', '=', 'term_relationships.object_id')
		->leftJoin('term_taxonomy', 'term_relationships.term_taxonomy_id', '=', 'term_taxonomy.term_taxonomy_id')
		->leftJoin('terms', 'term_taxonomy.term_id', '=', 'terms.term_id')
		->where('posts.post_type', 'product')
		->where('posts.post_status', 'publish')
		->where(function ( $q) use ( $parsed) {
			foreach ($parsed as $pair) {
				$q->orWhere(function ( $subQ) use ( $pair) {
					$subQ->where('term_taxonomy.taxonomy', $pair['taxonomy'])
						->where('terms.slug', $pair['slug']);
				});
			}
		})
		->groupBy('posts.ID')
		->select('posts.ID')
		->get();

		$attribute_value_and_filter_ids = wp_list_pluck( $attribute_value_and_filter_ids, 'ID' );
	}
	$category_condition = '';
	$filter_categories  = array();
	if (!empty($data_to_filter['category_filter'])) {
		$included = elex_get_categories( $data_to_filter['category_filter'], $data_to_filter['sub_category_filter'] );
	
		$include_query = elexBeBWPFluent()->table('posts')
			->leftJoin('term_relationships', 'posts.ID', '=', 'term_relationships.object_id')
			->where('posts.post_type', 'product')
			->where('posts.post_status', 'publish')
			->whereIn('term_relationships.term_taxonomy_id', $included)
			->groupBy('posts.ID')
			->select('posts.ID')
			->get();
	
		$include_categories_ids = wp_list_pluck($include_query, 'ID');


	}
	
	$exclude_categories     = array();
	$exclude_categories_ids = array();
	if (!empty($data_to_filter['exclude_categories'])) {
		$excluded      = elex_get_categories($data_to_filter['exclude_categories'], $data_to_filter['exclude_subcat_check']);
		$exclude_query = elexBeBWPFluent()->table('posts')
			->leftJoin('term_relationships', 'posts.ID', '=', 'term_relationships.object_id')
			->where('posts.post_type', 'product')
			->where('posts.post_status', 'publish')
			->whereIn('term_relationships.term_taxonomy_id', $excluded)
			->groupBy('posts.ID')
			->select('posts.ID')
			->get();

		$exclude_categories_ids = wp_list_pluck($exclude_query, 'ID');
		
	}
	
	$ids_simple = array();
	if ( empty( $data_to_filter['type'] ) || in_array( 'simple', $data_to_filter['type'] ) ) {
		$result     = $query->get();
		$ids_simple = wp_list_pluck( $result, 'ID' );
	}

	$res_id = $ids_simple;
	if ( ! empty( $exclude_categories_ids ) ) {
		$res_id = array_values( array_diff( $res_id, $exclude_categories_ids ) );
	}
	if ( ! empty( $data_to_filter['attribute_value_and_filter'] ) && is_array( $data_to_filter['attribute_value_and_filter'] ) ) {
		
		if ( empty( $attribute_value_and_filter_ids ) ) {
			$res_id = array();
		} else {
			$res_id = array_values( array_intersect( $res_id, $attribute_value_and_filter_ids ) );
		}
	}
	if ( ! empty( $data_to_filter['attribute_value_filter'] ) && is_array( $data_to_filter['attribute_value_filter'] ) ) {
		
		if ( empty( $attribute_value_filter_ids ) ) {
			$res_id = array();
		} else {
			$res_id = array_values( array_intersect( $res_id, $attribute_value_filter_ids ) );
		}
	}
	if ( ! empty( $data_to_filter['category_filter'] ) && is_array( $data_to_filter['category_filter'] ) ) {
		
		if ( empty( $include_categories_ids ) ) {
			$res_id = array();
		} else {
			$res_id = array_values( array_intersect( $res_id, $include_categories_ids ) );
		}
	}
	if (isset($_POST['_ajax_eh_bep_nonce'])) {
		$nonce = sanitize_text_field($_POST['_ajax_eh_bep_nonce']);
		if ( wp_verify_nonce($nonce, 'ajax-eh-bep-nonce')) {
			$enable_exclude_prods = isset($_POST['enable_exclude_prods']) ? sanitize_text_field($_POST['enable_exclude_prods']) : false;
		} else {
			$enable_exclude_prods = false;
		}
	} else {
		$enable_exclude_prods = false; 
	}
	if ( isset($enable_exclude_prods) && $enable_exclude_prods && ! empty( $res_id ) && ! empty( $data_to_filter['exclude_ids'] ) ) {
		foreach ( $res_id as $key => $val ) {
			if ( in_array( $val, $data_to_filter['exclude_ids'] ) ) {
				unset( $res_id[ $key ] );
			}
		}
		$res_id = array_values( $res_id );
	}
	global $wpdb;

	return $res_id;
}

// Get Subcategories
function elex_bep_subcats_from_parentcat_by_slug( $parent_cat_slug ) {
	$ID_by_slug     = get_term_by( 'slug', $parent_cat_slug, 'product_cat' );
	$product_cat_ID = $ID_by_slug->term_id;
	$args           = array(
		'hierarchical'     => 1,
		'show_option_none' => '',
		'hide_empty'       => 0,
		'parent'           => $product_cat_ID,
		'taxonomy'         => 'product_cat',
	);
	$subcats        = get_categories( $args );
	$temp_arr       = array();
	foreach ( $subcats as $sc ) {
		array_push( $temp_arr, $sc->slug );
	}
	return $temp_arr;
}
/** Get categories.
 *
 * @param array $categories categories.
 * @param array $subcat     subcategories.
 */
function elex_get_categories( $categories, $subcat ) {
	$filter_categories   = array();
	$selected_categories = $categories;
	$t_arr               = array();
	if ( $subcat ) {

		if ( ! empty( $selected_categories ) ) {
			foreach ( $selected_categories as $key => $selected_category_id ) {
				array_push( $filter_categories, $selected_category_id );
				unset( $selected_categories[ $key ] );
				$t_arr             = xa_subcats_from_parentcat_by_term_id( $selected_category_id );
				$filter_categories = array_merge( $filter_categories, $t_arr );
			}
		}
	} else {
		foreach ( $categories as $category ) {
			array_push( $filter_categories, $category );
		}
	}
	return $filter_categories;
}
/** Get Subcategories.
 *
 * @param int $parent_cat_term_id Parent Category Term ID.
 */
function xa_subcats_from_parentcat_by_term_id( $parent_cat_term_id ) {

	$args     = array(
		'hierarchical'     => 1,
		'show_option_none' => '',
		'hide_empty'       => 0,
		'parent'           => $parent_cat_term_id,
		'taxonomy'         => 'product_cat',
	);
	$subcats  = get_categories( $args );
	$temp_arr = array();
	foreach ( $subcats as $sc ) {
		array_push( $temp_arr, $sc->term_id );
		$subher = xa_filter_get_cat_hierarchy( $sc->term_id );
		if ( ! empty( $subher ) ) {
			$temp_arr = array_merge( $temp_arr, $subher );
		}
	}
	return $temp_arr;
}

/** Get Category Hierarchy.
 *
 * @param var $parent parent.
 */
function xa_filter_get_cat_hierarchy( $parent ) {
	$cat_args = array(
		'hide_empty'   => 0,
		'taxonomy'     => 'product_cat',
		'hierarchical' => 1,
		'orderby'      => 'name',
		'parent'       => $parent,
		'order'        => 'ASC',
	);
	$cats     = get_categories( $cat_args );
	$ret      = array();
	if ( ! empty( $cats ) ) {
		foreach ( $cats as $cat ) {
				$id = $cat->cat_ID;
				array_push( $ret, $id );
				$ret = array_merge( $ret, xa_filter_get_cat_hierarchy( $id ) );
		}
	}
	return $ret;
}
/**
 * Function for check sale price, when we are updating regular price and sale price together.
 */
function eh_bep_check_sale_price( $sale_select, $sale_text, $sale_round_text, $sale_round_select, $product_data_sale, $regular_check_val ) {
	$sale_val = '';
	switch ( $sale_select ) {
		case 'up_percentage':
			if ( '' !== $product_data_sale ) {
				$per_val = $product_data_sale * ( $sale_text / 100 );
				$cal_val = $product_data_sale + $per_val;
				if ( '' !== $sale_round_select ) {
					if ( '' === $sale_round_text ) {
						$sale_round_text = 1;
					}
					$got_sale = $cal_val;
					switch ( $sale_round_select ) {
						case 'up':
							$cal_val = eh_bep_round_ceiling( $got_sale, $sale_round_text );
							break;
						case 'down':
							$cal_val = eh_bep_round_ceiling( $got_sale, -$sale_round_text );
							break;
					}
				}
				$sale_val = wc_format_decimal( $cal_val, '', true );
				// leave sale price blank if sale price increased by -100%.
				if ( 0 == $sale_val ) {
					$sale_val = '';
				}
				
			}
			break;
		case 'down_percentage':
			if (!empty($product_data_sale) && 0 !== $product_data_sale ) {
				$per_val = $product_data_sale * ( $sale_text / 100 );
				$cal_val = $product_data_sale - $per_val;
				if ( '' === $sale_round_text ) {
					$sale_round_text = 1;
				}
					$got_sale = $cal_val;
				switch ( $sale_round_select ) {
					case 'up':
						$cal_val = eh_bep_round_ceiling( $got_sale, $sale_round_text );
						break;
					case 'down':
						$cal_val = eh_bep_round_ceiling( $got_sale, -$sale_round_text );
						break;
				}
				$sale_val = wc_format_decimal( $cal_val, '', true );
				// leave sale price blank if sale price decreased by 100%.
				if ( 0 === intval( $sale_val ) || $sale_val < 0 ) {
					$sale_val = '';
				}
				
			}
			break;
		case 'up_price':
			if ( '' !== $product_data_sale ) {
				$cal_val = $product_data_sale + $sale_text;
				if ( '' !== $sale_round_select ) {
					if ( '' === $sale_round_text ) {
						$sale_round_text = 1;
					}
					$got_sale = $cal_val;
					switch ( $sale_round_select ) {
						case 'up':
							$cal_val = eh_bep_round_ceiling( $got_sale, $sale_round_text );
							break;
						case 'down':
							$cal_val = eh_bep_round_ceiling( $got_sale, -$sale_round_text );
							break;
					}
				}
				$sale_val = wc_format_decimal( $cal_val, '', true );
				if ( $sale_val < 0 || 0 == $sale_val ) {
					$sale_val = '';
				}
				
			}
			break;
		case 'down_price':
			if (!empty($product_data_sale) && 0 !== $product_data_sale ) {
					$cal_val = $product_data_sale - $sale_text;
				if ( '' === $sale_round_text ) {
					$sale_round_text = 1;
				}
					$got_sale = $cal_val;
				switch ( $sale_round_select ) {
					case 'up':
						$cal_val = eh_bep_round_ceiling( $got_sale, $sale_round_text );
						break;
					case 'down':
						$cal_val = eh_bep_round_ceiling( $got_sale, -$sale_round_text );
						break;
				}
				$sale_val = wc_format_decimal( $cal_val, '', true );
			}
			break;
		case 'flat_all':
			$sale_val = wc_format_decimal( $sale_text, '', true );
			break;
	}
	return $sale_val;
}
