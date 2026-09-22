<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php
$cat_args        = array(
	'hide_empty' => false,
	'order'      => 'ASC',
);
$attributes      = wc_get_attribute_taxonomies();
$attribute_value = get_terms( array_merge(
	array( 'taxonomy' => 'pa_size' ),
	$cat_args
) );
?>
<style>
		.select2-container--default .select2-results__option--highlighted.select2-results__option--selectable{
		color: #000 !important;
	}
	.select2-results__option--highlighted:hover{
		background-color: grey  !important;
	}
</style>
<div class="loader"></div>

<div class='wrap postbox table-box table-box-main' id="top_filter_tag" style='padding:5px 20px;'>
	<h2>
	<?php
		echo esc_html__( 'Filter the Products', 'eh_bulk_edit' ) . '<br> <span style="color:green;font-size:16px">' . esc_html__( 'This basic version only supports Simple Products.', 'eh_bulk_edit' ) . '<button id="go_premium_link" style="color:red; background:white; border:white; text-decoration: underline;">' . esc_html__( 'Go Premium! ', 'eh_bulk_edit' ) . '</button>' . esc_html__( 'for Variable products.', 'eh_bulk_edit' ) . '</span>';
	?>
		<span style="float: right;" id="remove_undo_update_button_top" ><span class='woocommerce-help-tip tooltip' id='add_undo_button_tooltip' style="padding:0px 15px" data-tooltip='<?php esc_attr_e( 'Click to undo the last update you have done', 'eh_bulk_edit' ); ?>'></span><button id='undo_display_update_button' style="margin-bottom: 2%;" class='button button-primary button-large' disabled="disabled"><span class="update-text"><?php esc_html_e( 'Undo Last Update', 'eh_bulk_edit' ); ?></span></button></span>
	</h2>
	<hr>
	<table class='eh-content-table' id='data_table' style="width:100%">

		<tr>
			<td class='eh-content-table-left'>
				<?php esc_html_e( 'Product Title', 'eh_bulk_edit' ); ?>
			</td>
			<td class='eh-content-table-middle'>
				<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_attr_e( 'Select a condition from the drop-down and enter a product title', 'eh_bulk_edit' ); ?>'></span>
			</td>
			<td class='eh-content-table-input-td' colspan="2" >
				<select id='product_title_select' style="width:21%;">
					<option value = 'all'><?php esc_html_e( 'All', 'eh_bulk_edit' ); ?></option>
					<option value = 'starts_with'><?php esc_html_e( 'Starts With', 'eh_bulk_edit' ); ?></option>
					<option value = 'ends_with'><?php esc_html_e( 'Ends With', 'eh_bulk_edit' ); ?></option>
					<option value = 'contains'><?php esc_html_e( 'Contains', 'eh_bulk_edit' ); ?></option>
					<option value = 'title_regex'><?php esc_html_e( 'Regex Match', 'eh_bulk_edit' ); ?></option>
				</select>
				<span id='product_title_text'></span>
			</td>
			<td class='eh-content-table-right' id='regex_flags_field'>
				<span class='select-eh'><select data-placeholder='<?php esc_attr_e( 'Select Flags (Optional)', 'eh_bulk_edit' ); ?>' id='regex_flags_values' multiple class='category-chosen' >
				<?php
				{
					echo "<option value='A'>" . esc_html__( 'Anchored (A)', 'eh_bulk_edit' ) . '</option>';
					echo "<option value='D'>" . esc_html__( 'Dollors End Only (D)', 'eh_bulk_edit' ) . '</option>';
					echo "<option value='x'>" . esc_html__( 'Extended (x)', 'eh_bulk_edit' ) . '</option>';
					echo "<option value='X'>" . esc_html__( 'Extra (X)', 'eh_bulk_edit' ) . '</option>';
					echo "<option value='i'>" . esc_html__( 'Insensitive (i)', 'eh_bulk_edit' ) . '</option>';
					echo "<option value='J'>" . esc_html__( 'Jchanged (J)', 'eh_bulk_edit' ) . '</option>';
					echo "<option value='m'>" . esc_html__( 'Multi Line (m)', 'eh_bulk_edit' ) . '</option>';
					echo "<option value='s'>" . esc_html__( 'Single Line (s)', 'eh_bulk_edit' ) . '</option>';
					echo "<option value='u'>" . esc_html__( 'Unicode (u)', 'eh_bulk_edit' ) . '</option>';
					echo "<option value='U'>" . esc_html__( 'Ungreedy (U)', 'eh_bulk_edit' ) . '</option>';
				}
				?>
		</select></span>
			</td>
			<td class='eh-content-table-help_link' id='regex_help_link'>
				<a href="https://elextensions.com/understanding-regular-expression-regex-pattern-matching-bulk-edit-products-prices-attributes-woocommerce-plugin/" target="_blank">Help</a>
			</td>
		</tr>


		<tr>
			<td class='eh-content-table-left'>
				<?php esc_html_e( 'Product Types', 'eh_bulk_edit' ); ?>
			</td>
			<td class='eh-content-table-middle'>
				<span class='woocommerce-help-tip tooltip' data-tooltip=' <?php esc_attr_e( 'Select the product type(s) for which the filter has to be applied', 'eh_bulk_edit' ); ?> '></span>
			</td>
			<td>

				<span class='select-eh'><select data-placeholder='<?php esc_attr_e( 'Select Product Types', 'eh_bulk_edit' ); ?>' id='product_type' multiple class='category-chosen' >
						<?php
						{

							echo "<option value='simple'>" . esc_html__( 'Simple', 'eh_bulk_edit' ) . '</option>';

						}
						?>
					</select></span>
			</td>
		</tr>
		<tr>
			<td class='eh-content-table-left'>
				<?php esc_html_e( 'Product Categories', 'eh_bulk_edit' ); ?>
			</td>
			<td class='eh-content-table-middle'>
				<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Select the category(s) for which the filter has to be applied. The products added to any of the selected categories will be filtered. Enable the checkbox to include subcategories', 'eh_bulk_edit' ); ?>'></span>
			</td>

			<td class='eh-edit-tab-table-input-td'>
				<select data-placeholder='<?php esc_html_e( 'Select Categories', 'eh_bulk_edit' ); ?>' class ="elex-select-categories" id='elex_select_include_categories'  multiple style="width: 100%;"></select>
			</td>
			<td class='eh-content-table-right'>
				<input type="checkbox" id="subcat_check">
				<?php echo esc_html__( 'Include Subcategories', 'eh_bulk_edit' ); ?>
			</td>
			<td class='eh-content-table-right'>
						
			</td>
		</tr>
		<tr>
			<td class='eh-content-table-left'>
				<?php esc_html_e( 'Product Regular Price', 'eh_bulk_edit' ); ?>
			</td>
			<td class='eh-content-table-middle'>
				<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_attr_e( 'Select a condition and specify a price', 'eh_bulk_edit' ); ?>'></span>
			</td>
			<td class='eh-content-table-input-td'>
				<select id='regular_price_range_select' style="width: 45%;">
					<option value='all'><?php esc_html_e( 'All', 'eh_bulk_edit' ); ?></option>
					<option value='>='>>=</option>
					<option value='<='><=</option>
					<option value='='>==</option>
					<option value='|'>|| <?php esc_html_e( 'Between', 'eh_bulk_edit' ); ?></option>
				</select>
				<span id='regular_price_range_text'></span>
			</td>
		</tr>
		<tr>
			<td class='eh-content-table-left'>
		<h3><?php esc_html_e( 'Attributes', 'eh_bulk_edit' ); ?></h3>
		<hr>
			</td>
	</tr>
		<tr id='attribute_types'>
			<td class='eh-content-table-left' style="vertical-align: baseline;">
				<?php esc_html_e( 'Product Attributes (Group with OR)', 'eh_bulk_edit' ); ?>
			</td>
			<td class='eh-content-table-middle' style="vertical-align: baseline;">
				<span class="woocommerce-help-tip tooltip" data-tooltip="<?php esc_html_e( "The products will be filtered when any one of the attributes and it's corresponding values are present", 'eh_bulk_edit' ); ?>"></span>
			</td>
			<td style="vertical-align: baseline;" colspan='3'>
			<div style="display:flex;flex-wrap:wrap;margin-bottom: 10px;">
				<?php
				global $wpdb;
				// Get Global attributes.
				if ( count( $attributes ) > 0 ) {
					foreach ( $attributes as $key => $value ) {
						echo "<span id='attrib_name' class='attribute-checkbox'><input type='checkbox' name='attrib_name' value='" . esc_attr( $value->attribute_name ) . "' id='" . esc_attr( $value->attribute_name ) . "'>" . esc_html( $value->attribute_label ) . '</span>';
					}
				} else {
					echo "<span id='attrib_name' class='attribute-checkbox'>" . esc_html__( 'No attributes found.', 'eh_bulk_edit' ) . '</span>';
				}
				?>
			</div>
			</td>
		</tr>
		<tr id='attribute_types_and'>
			<td class='eh-content-table-left' style="vertical-align: baseline;">
				<?php esc_html_e( 'Product Attributes (Group with AND)', 'eh_bulk_edit' ); ?>
			</td>
			<td class='eh-content-table-middle' style="vertical-align: baseline;">
				<span class="woocommerce-help-tip tooltip" data-tooltip="<?php esc_html_e( "The products will be filtered only when both attributes and it's corresponding values are present", 'eh_bulk_edit' ); ?>"></span>
			</td>
			<td style="vertical-align: baseline;" colspan='3'>
			<div style="display:flex;flex-wrap:wrap;margin-bottom: 10px;">
					<?php
					global $wpdb;
					// Get Global attributes.
					if ( count( $attributes ) > 0 ) {
						foreach ( $attributes as $key => $value ) {
							echo filter_var( "<span id='attrib_name_and' class='attribute-checkbox'><input type='checkbox' name='attrib_name_and' value='" . $value->attribute_name . "' id='" . $value->attribute_name . "'>" . $value->attribute_label . '</span>' );
						}
					} else {
						echo "<span id='attrib_name_and' class='attribute-checkbox'>" . esc_html__( 'No attributes found.', 'eh_bulk_edit' ) . '</span>';
					}
					?>
				</div>
			</td>

		</tr>
		

	</table>
	<h2 >
		<?php echo '<span style="padding-right:1em; font-size:20px;">' . esc_html__( 'Exclusions', 'eh_bulk_edit' ) . '</span>'; ?>
		<input type="checkbox" id ="enable_exclude_products"><span style="font-weight:normal;font-size: 14px;"><?php esc_html_e( 'Enable', 'eh_bulk_edit' ); ?></span>
	</h2>
	<hr align="left" width="20%" >
	<table class='eh-content-table' id="exclude_products">
		<tr>
			<td class='eh-content-table-left'>
				<?php esc_html_e( 'Exclude by IDs', 'eh_bulk_edit' ); ?>
			</td>
			<td class='eh-content-table-middle'>
				<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Enter the Product IDs to exclude from getting updated (separate IDs by comma).', 'eh_bulk_edit' ); ?>'></span>
			</td>
			<td class='eh-content-table-input-td'>
				<textarea rows="4" cols="50" id="exclude_ids"></textarea>
			</td>
		</tr>
		<tr>
			<td class='eh-content-table-left'>
				<?php esc_html_e( 'Exclude by Categories', 'eh_bulk_edit' ); ?>
			</td>
			<td class='eh-content-table-middle'>
				<span class='woocommerce-help-tip tooltip' data-tooltip="<?php esc_html_e( "Select the categories to exclude products from getting updated. All the subcategories under a parent category will be excluded if you enable 'Include Subcategories' checkbox.", 'eh_bulk_edit' ); ?>"></span>
			</td>
			<td class='eh-edit-tab-table-input-td'>
				<select data-placeholder='<?php esc_html_e( 'Select Categories', 'eh_bulk_edit' ); ?>' class ="elex-select-categories" id='elex_select_exclude_categories'  multiple style="width: 100%;"></select>
			</td>
			<td class='eh-content-table-right'>
				<input type="checkbox" id ="exclude_subcat_check">Include Subcategories
			</td>
		</tr>
	</table>
	<button id='clear_filter_button' value='clear_products' style='margin:5px 2px 2px 2px; color: white; width:15%; background-color: gray;' class='button button-large'><?php esc_html_e( 'Reset Filter', 'eh_bulk_edit' ); ?></button>
	<button id='filter_products_button' value='filter_products' style='margin:5px 2px 2px 2px; float: right; ' class='button button-primary button-large'><?php esc_html_e( 'Preview Filtered Products', 'eh_bulk_edit' ); ?></button>        
</div>

 <?php
	require_once 'market.php';

	require_once ELEX_BEP_TEMPLATE_PATH . '/elex-template-frontend-tables.php';

	function elex_bep_filter_get_cat_hierarchy( $parent, $args ) {
		$cats = get_categories( $args );
		$ret  = new stdClass();
		foreach ( $cats as $cat ) {
			if ( $cat->parent == $parent ) {
				$id                 = $cat->cat_ID;
				$ret->$id           = $cat;
				$ret->$id->children = elex_bep_filter_get_cat_hierarchy( $id, $args );
			}
		}
		return $ret;
	}

	function elex_bep_filter_category_rows( $categories, $level, $name ) {
		$html_code       = '';
		$level_indicator = '';
		for ( $i = 0; $i < $level; $i++ ) {
			$level_indicator .= '- ';
		}
		if ( $categories ) {
			foreach ( $categories as $category ) {
				$html_code .= '<li><label><input value=' . $category->slug . " type='checkbox' name=" . $name . '>' . $level_indicator . $category->name . '</label></li>';
				if ( $category->children && count( (array) $category->children ) > 0 ) {
					$html_code .= elex_bep_filter_category_rows( $category->children, $level + 1, $name );
				}
			}
		} else {
			$html_code .= esc_html__( 'No categories found.', 'eh_bulk_edit' );
		}
		return $html_code;
	}
