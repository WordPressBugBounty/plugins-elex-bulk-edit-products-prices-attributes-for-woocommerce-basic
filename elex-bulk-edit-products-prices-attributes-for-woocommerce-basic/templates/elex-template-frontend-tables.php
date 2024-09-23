<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class='wrap table-box table-box-main' id='wrap_table' style="position:relative;display: none;">
	<?php
	elex_bep_list_table();
	?>
</div>
<div id='undo_update_html' style="padding: 10px 0px;"></div>
<?php
elex_bep_process_edit();
function elex_bep_list_table() {
	?>
	<?php
	$obj = new Elex_DataTables();
	$obj->input();
	$obj->prepare_items();
	$obj->search_box( 'search', 'search_id' );
	?>
	<!--<button id='process_edit' value='edit_products' style="background-color: green;color: white;" class='button button-large'><span class="update-text"><?php esc_html_e( 'Process Edit', 'eh_bulk_edit' ); ?></span><span class="edit"></span></button>-->
	<label for="display_count_order"><?php echo esc_html__( 'Items per page:', 'eh_bulk_edit' ); ?></label>
	<input id="display_count_order" style="width:75px" type="number" min="1" max="9999" maxlength="4" value="
	<?php
	$count = get_option( 'eh_bulk_edit_table_row' );
	if ( $count ) {
		echo esc_attr($count);
	}
	?>
	">
	<button id='save_dislay_count_order'class='button ' style='background-color:#f7f7f7; '><?php esc_html_e( 'Apply', 'eh_bulk_edit' ); ?></button>
	<form id="products-filter" method="get">
		<input type="hidden" name="action" value="all" />
		<input type="hidden" name="page" value="<?php echo isset($_REQUEST['page']) ? esc_attr( $_REQUEST['page'] ) : ''; ?>" />
		<?php $obj->display(); ?>
	</form>
	<button id='preview_back' value='edit_products' style="background-color: gray;color: white; width: 10%; " class='button button-large'><span class="update-text"><?php esc_html_e( 'Back', 'eh_bulk_edit' ); ?></span></button>
	<button id='preview_cancel' value='edit_products' style="background-color: gray;color: white; width: 10%; " class='button button-large'><span class="update-text"><?php esc_html_e( 'Cancel', 'eh_bulk_edit' ); ?></span></button>
	<button id='process_edit' value='edit_products' style="color: white;margin-bottom: 0%; float: right; width: 10%;" class='button button-primary button-large'><span class="update-text"><?php esc_html_e( 'Continue', 'eh_bulk_edit' ); ?></span></button>

	<?php
}

function elex_bep_process_edit() {
	global $woocommerce;
	$attributes = wc_get_attribute_taxonomies();
	?>
	<div class='wrap postbox table-box table-box-main' id="update_logs" style='padding:0px 20px;display: none'>
		<h1> <?php esc_html_e( 'Updating the products. Do not refresh...', 'eh_bulk_edit' ); ?></h1>
		<div id='logs_val' ></div>
		<div id='logs_loader' ></div><br><br>

		<button id='finish_cancel' value='edit_products' style="background-color: gray; margin-bottom: 1%; color: white; width: 10%;" class='button button-large'><span class="update-text"><?php esc_html_e( 'Cancel', 'eh_bulk_edit' ); ?></span></button>
		<button id='update_finished' value='edit_products' style=' background-color: #006799; margin-bottom: 1%; color: white; float: right; width: 10%;height: 37px;' class='button button-large'><span class="update-text"><?php esc_html_e( 'Continue', 'eh_bulk_edit' ); ?></span></button>

	</div>
	

	<div class='wrap postbox table-box table-box-main' id="edit_product" style='padding:0px 20px;display: none'>
		<h2>
			<?php esc_html_e( 'Update the Products', 'eh_bulk_edit' ); ?><span style="font-size: 14 !important; float: right;"><span class='woocommerce-help-tip tooltip' id='add_undo_now_tooltip' style="padding:0px 5px" data-tooltip='<?php echo esc_attr_e( 'Enable this Undo option to revert back your current update. But it will override your previous update.', 'eh_bulk_edit' ); ?>'></span><input type="checkbox" id="add_undo_now" disabled="disabled" value="ok"><?php echo esc_html__('Enable Undo Update', 'eh_bulk_edit') . ' <span style="vertical-align: super;color:red;font-size:12px">' . esc_html__('Premium', 'eh_bulk_edit') . '</span>'; ?>
		</h2>
		<hr>
		<table class='eh-edit-table' id='update_general_table'>
			<tr>
				<td class='eh-edit-tab-table-left'>
					<?php esc_html_e( 'Title', 'eh_bulk_edit' ); ?>
				</td>
				<td class='eh-edit-tab-table-middle'>
					<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_attr_e( 'Select a condition to edit the title, and enter the relevant text', 'eh_bulk_edit' ); ?>'></span>
				</td>
				<td class='eh-edit-tab-table-input-td'>
					<select id='title_action' style="width: 26%;">
						<option value=''><?php esc_html_e( '< No Change >', 'eh_bulk_edit' ); ?></option>
						<option value='set_new'><?php esc_html_e( 'Set New', 'eh_bulk_edit' ); ?></option>
						<option value='append'><?php esc_html_e( 'Append', 'eh_bulk_edit' ); ?></option>
						<option value='prepand'><?php esc_html_e( 'Prepend', 'eh_bulk_edit' ); ?></option>
						<option value='replace'><?php esc_html_e( 'Replace', 'eh_bulk_edit' ); ?></option>
						<option value='regex_replace'><?php esc_html_e( 'RegEx Replace', 'eh_bulk_edit' ); ?></option>
					</select>
					<span id='title_text'></span>
				</td>
				<td class='eh-edit-tab-table-right' id='regex_flags_field_title'>
					<span class='select-eh'><select data-placeholder='<?php esc_attr_e( 'Select Flags (Optional)', 'eh_bulk_edit' ); ?>' id='regex_flags_values_title' multiple class='category-chosen regex-flags-edit-table' >
					<?php
						{
							echo "<option value='A'>" . esc_html__( 'Anchored (A)', 'eh_bulk_edit' ) . '</option>';
							echo "<option value='D'>" . esc_html__( 'Dollars End Only (D)', 'eh_bulk_edit' ) . '</option>';
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
				<td class='eh-edit-tab-table-help' id='regex_help_link_title'>
					<a href="https://elextensions.com/understanding-regular-expression-regex-pattern-matching-bulk-edit-products-prices-attributes-woocommerce-plugin/" target="_blank">Help</a>
				</td>
			</tr>
			<tr>
				<td class='eh-edit-tab-table-left'>
					<?php esc_html_e( 'SKU', 'eh_bulk_edit' ); ?>
				</td>
				<td class='eh-edit-tab-table-middle'>
					<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_attr_e( 'Select a condition to edit the SKU, and enter the relevant text', 'eh_bulk_edit' ); ?>'></span>
				</td>
				<td class='eh-edit-tab-table-input-td'>
					<select id='sku_action' style="width: 26%;">
						<option value=''><?php esc_html_e( '< No Change >', 'eh_bulk_edit' ); ?></option>
						<option value='set_new'><?php esc_html_e( 'Set New', 'eh_bulk_edit' ); ?></option>
						<option value='append'><?php esc_html_e( 'Append', 'eh_bulk_edit' ); ?></option>
						<option value='prepand'><?php esc_html_e( 'Prepend', 'eh_bulk_edit' ); ?></option>
						<option value='replace'><?php esc_html_e( 'Replace', 'eh_bulk_edit' ); ?></option>
						<option value='regex_replace'><?php esc_html_e( 'RegEx Replace', 'eh_bulk_edit' ); ?></option>
					</select>
					<span id='sku_text'></span>
				</td>
				<td class='eh-edit-tab-table-right' id='regex_flags_field_sku'>
					<span class='select-eh'><select data-placeholder='<?php esc_attr_e( 'Select Flags (Optional)', 'eh_bulk_edit' ); ?>' id='regex_flags_values_sku' multiple class='category-chosen regex-flags-edit-table' >
					<?php
						{
							echo "<option value='A'>" . esc_html__( 'Anchored (A)', 'eh_bulk_edit' ) . '</option>';
							echo "<option value='D'>" . esc_html__( 'Dollars End Only (D)', 'eh_bulk_edit' ) . '</option>';
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
				<td class='eh-edit-tab-table-right' id='sku_show' style="height:28px;width:20%;vertical-align:top;padding-top:20px;">
					<div class="sku_ex text-secondary text-nowrap"><?php esc_html_e( 'Preview :', 'eh_bulk_edit' ); ?>
				</td>
				<td class='eh-edit-tab-table-help' id='regex_help_link_sku'>
					<a href="https://elextensions.com/understanding-regular-expression-regex-pattern-matching-bulk-edit-products-prices-attributes-woocommerce-plugin/" target="_blank">Help</a>
				</td>
			</tr>
			<tr>
				<td class='eh-edit-tab-table-left'>
					<?php esc_html_e( 'Product Visiblity', 'eh_bulk_edit' ); ?>
				</td>
				<td class='eh-edit-tab-table-middle'>
					<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_attr_e( 'Choose which all shop pages the product will be listed on', 'eh_bulk_edit' ); ?>'></span>
				</td>
				<td class='eh-edit-tab-table-input-td'>
					<select id='catalog_action' style="width: 26%;">
						<option value=''><?php esc_html_e( '< No Change >', 'eh_bulk_edit' ); ?></option>
						<option value='visible'><?php esc_html_e( 'Shop and Search', 'eh_bulk_edit' ); ?></option>
						<option value='catalog'><?php esc_html_e( 'Shop', 'eh_bulk_edit' ); ?></option>
						<option value='search'><?php esc_html_e( 'Search', 'eh_bulk_edit' ); ?></option>
						<option value='hidden'><?php esc_html_e( 'Hidden', 'eh_bulk_edit' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td class='eh-edit-tab-table-left'>
					<?php esc_html_e( 'Featured Product', 'eh_bulk_edit' ); ?>
				</td>
				<td class='eh-edit-tab-table-middle'>
					<span class='woocommerce-help-tip tooltip' data-tooltip='
					<?php esc_html_e( 'Select an option to make the product(s) Featured or not.', 'eh_bulk_edit' ); ?>
			'></span>
				</td>
				<td class='eh-edit-tab-table-input-td'>
					<select id='is_featured' style="width: 26%;">
						<option value=''><?php esc_html_e( '< No Change >', 'eh_bulk_edit' ); ?></option>
						<option value='yes'><?php esc_html_e( 'Yes', 'eh_bulk_edit' ); ?></option>
						<option value='no'><?php esc_html_e( 'No', 'eh_bulk_edit' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td class='eh-edit-tab-table-left'>
					<?php esc_html_e( 'Shipping Class', 'eh_bulk_edit' ); ?>
				</td>
				<td class='eh-edit-tab-table-middle'>
					<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_attr_e( 'Select a shipping class that will be added to all the filtered products', 'eh_bulk_edit' ); ?>'></span>
				</td>
				<td class='eh-edit-tab-table-input-td'>
					<select id='shipping_class_action' style="width: 26%;">
						<?php
						$ship = $woocommerce->shipping->get_shipping_classes();
						if ( count( $ship ) > 0 ) {
							?>
							<option value=''><?php esc_html_e( '< No Change >', 'eh_bulk_edit' ); ?></option>
							<option value='-1'><?php esc_html_e( 'No Shipping Class', 'eh_bulk_edit' ); ?></option>
							<?php
							foreach ( $ship as $key => $value ) {
								echo "<option value='" . esc_attr( $value->term_id ) . "'>" . esc_html( $value->name ) . '</option>';
							}
						} else {
							?>
							<option value=''><?php esc_html_e( '< No Shipping Class >', 'eh_bulk_edit' ); ?></option>
							<?php
						}
						?>
					</select>
					<span id='shipping_class_check_text'></span>
				</td>
			</tr>
			<tr>
				<td class='eh-edit-tab-table-left'>
					<?php esc_html_e( 'Description Action', 'eh_bulk_edit' ); ?>
				</td>
				<td class='eh-edit-tab-table-middle'>
					<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Select a condition to edit or add the description, and enter the relevant text.', 'eh_bulk_edit' ); ?>'></span>
				</td>
				<td class='eh-edit-tab-table-input-td'>
				<select id="description_action" style="width: 26%;">
					<option value=""><?php esc_html_e( 'No Change', 'eh_bulk_edit' ); ?></option>
					<option value="append"><?php esc_html_e( 'Append', 'eh_bulk_edit' ); ?></option>
					<option value="prepend"><?php esc_html_e( 'Prepend', 'eh_bulk_edit' ); ?></option>
					<option value="set_new"><?php esc_html_e( 'Set new', 'eh_bulk_edit' ); ?></option>
				</select>
				</td>
			</tr>
			<tr id="description_tr">
				<td class='eh-edit-tab-table-left'>
					<?php esc_html_e( 'Description', 'eh_bulk_edit' ); ?>
				</td>
				<td class='eh-edit-tab-table-middle'>
					<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Prepend - Enter the text you want to add at the beginning of the current description. Append - Enter the text you want to add at the end of the current description. Set new - Enter the text to replace the current description.', 'eh_bulk_edit' ); ?>'></span>
				</td>
				<td class='eh-edit-tab-table-input-td'>
					<?php wp_editor( '', 'elex_product_description' ); ?>
				</td>
			</tr>
			<tr>
				<td class='eh-edit-tab-table-left'>
					<?php esc_html_e( 'Short Description Action', 'eh_bulk_edit' ); ?>
				</td>
				<td class='eh-edit-tab-table-middle'>
					<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Select a condition to edit or add the short description, and enter the relevant text.', 'eh_bulk_edit' ); ?>'></span>
				</td>
				<td class='eh-edit-tab-table-input-td'>
				<select id="short_description_action" style="width: 26%;">
					<option value=""><?php esc_html_e( 'No Change', 'eh_bulk_edit' ); ?></option>
					<option value="append"><?php esc_html_e( 'Append', 'eh_bulk_edit' ); ?></option>
					<option value="prepend"><?php esc_html_e( 'Prepend', 'eh_bulk_edit' ); ?></option>
					<option value="set_new"><?php esc_html_e( 'Set new', 'eh_bulk_edit' ); ?></option>
				</select>
				</td>
			</tr>
			<tr id="short_description_tr">
				<td class='eh-edit-tab-table-left'>
					<?php esc_html_e( 'Short Description', 'eh_bulk_edit' ); ?>
				</td>
				<td class='eh-edit-tab-table-middle'>
					<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Prepend - Enter the text you want to add at the beginning of the current short description. Append - Enter the text you want to add at the end of the current short description. Set new - Enter the text to replace the current short description.', 'eh_bulk_edit' ); ?>'></span>
				</td>
				<td class='eh-edit-tab-table-input-td'>
					<?php wp_editor( '', 'elex_product_short_description' ); ?>
				</td>
			</tr>
			<tr>
				<td class='eh-edit-tab-table-left'>
					<?php esc_html_e( 'Product Image', 'eh_bulk_edit' ); ?>
				</td>
				<td class='eh-edit-tab-table-middle'>
					<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Specify an image url to add or replace the product image.', 'eh_bulk_edit' ); ?>'></span>
				</td>
				<td class='eh-edit-tab-table-input-td'>
					<input type="text" id="elex_product_main_image" style="width: 26%;"/>
				</td>
			</tr>
			<tr>
				<td class='eh-edit-tab-table-left'>
					<?php esc_html_e( 'Product Gallery Images Action', 'eh_bulk_edit' ); ?>
				</td>
				<td class='eh-edit-tab-table-middle'>
					<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Select a condition to modify product gallery images.', 'eh_bulk_edit' ); ?>'></span>
				</td>
				<td class='eh-edit-tab-table-input-td'>
					<select id="gallery_image_action">
						<option value=""><?php esc_html_e( 'No Change', 'eh_bulk_edit' ); ?></option>
						<option value="add"><?php esc_html_e( 'Add', 'eh_bulk_edit' ); ?></option>
						<option value="remove"><?php esc_html_e( 'Remove', 'eh_bulk_edit' ); ?></option>
						<option value="replace"><?php esc_html_e( 'Replace', 'eh_bulk_edit' ); ?></option>
					</select>
				</td>
			</tr>
			<tr id="gallery_images_tr">
				<td class='eh-edit-tab-table-left'>
					<?php esc_html_e( 'Product Gallery Images', 'eh_bulk_edit' ); ?>
				</td>
				<td class='eh-edit-tab-table-middle'>
					<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Specify the urls (separated by comma) to add, remove or replace images from product gallery.', 'eh_bulk_edit' ); ?>'></span>
				</td>
				<td class='eh-edit-tab-table-input-td'>
					<textarea id="elex_product_gallery_images" style="width: 26%;"></textarea>
				</td>
			</tr>
		</table>
		<h2>
			<?php esc_html_e( 'Price', 'eh_bulk_edit' ); ?>
		</h2>
		<hr>
		<table class='eh-edit-table' id="update_price_table">
			<tr>
				<td class='eh-edit-tab-table-left'>
					<?php esc_html_e( 'Regular Price', 'eh_bulk_edit' ); ?>
				</td>
				<td class='eh-edit-tab-table-middle'>
					<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_attr_e( 'Select a condition to adjust the price and enter the value. You can also choose an option to round it to the nearest value', 'eh_bulk_edit' ); ?>'></span>
				</td>
				<td class='eh-edit-tab-table-input-td'>
					<select id='regular_price_action' style="width: 26%;">
						<option value=''><?php esc_html_e( '< No Change >', 'eh_bulk_edit' ); ?></option>
						<option value='up_percentage'><?php esc_html_e( 'Increase by Percentage ( + %)', 'eh_bulk_edit' ); ?></option>
						<option value='down_percentage'><?php esc_html_e( 'Decrease by Percentage ( - %)', 'eh_bulk_edit' ); ?></option>
						<option value='up_price'><?php esc_html_e( 'Increase by Price ( + $)', 'eh_bulk_edit' ); ?></option>
						<option value='down_price'><?php esc_html_e( 'Decrease by Price ( - $)', 'eh_bulk_edit' ); ?></option>
						<option value='flat_all'><?php esc_html_e( 'Flat Price for All', 'eh_bulk_edit' ); ?></option>

					</select>
					<span id='regular_price_text'></span>
				</td>
			</tr>
			<tr>
				<td class='eh-edit-tab-table-left'>
					<?php esc_html_e( 'Sale Price', 'eh_bulk_edit' ); ?>
				</td>
				<td class='eh-edit-tab-table-middle'>
					<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_attr_e( 'Select a condition to adjust the price and enter the value. You can also choose an option to round it to the nearest value', 'eh_bulk_edit' ); ?>'></span>
				</td>
				<td class='eh-edit-tab-table-input-td'>
					<select id='sale_price_action' style="width: 26%;">
						<option value=''><?php esc_html_e( '< No Change >', 'eh_bulk_edit' ); ?></option>
						<option value='up_percentage'><?php esc_html_e( 'Increase by Percentage ( + %)', 'eh_bulk_edit' ); ?></option>
						<option value='down_percentage'><?php esc_html_e( 'Decrease by Percentage ( - %)', 'eh_bulk_edit' ); ?></option>
						<option value='up_price'><?php esc_html_e( 'Increase by Price ( + $)', 'eh_bulk_edit' ); ?></option>
						<option value='down_price'><?php esc_html_e( 'Decrease by Price ( - $)', 'eh_bulk_edit' ); ?></option>
						<option value='flat_all'><?php esc_html_e( 'Flat Price for All', 'eh_bulk_edit' ); ?></option>

					</select>
					<span id='sale_price_text'></span>
				</td>
			</tr>
			<tr id="regular_checkbox">
				<td class='eh-edit-tab-table-left'>
					<?php esc_html_e( 'Use Regular Price to set Sale Price', 'eh_bulk_edit' ); ?>
				</td>
				<td class='eh-edit-tab-table-middle'>
					<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_attr_e( 'Enable this option to set the Sale Price based on the the Regular Price.', 'eh_bulk_edit' ); ?>'></span>
				</td>
				<td class='eh-edit-tab-table-input-td'>
					<input type="checkbox" id="regular_val_check">Enable
					<span id='regular_price_text'></span>
				</td>
			</tr>
			
		</table>
		<h2>
			<?php esc_html_e( 'Stock', 'eh_bulk_edit' ); ?>
		</h2>
		<hr>
		<table class='eh-edit-table' id='update_stock_table'>
			<tr>
				<td class='eh-edit-tab-table-left'>
					<?php esc_html_e( 'Manage Stock', 'eh_bulk_edit' ); ?>
				</td>
				<td class='eh-edit-tab-table-middle'>
					<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_attr_e( 'Enable or Disable manage stock for products or variations', 'eh_bulk_edit' ); ?>'></span>
				</td>
				<td class='eh-edit-tab-table-input-td'>
					<select id='manage_stock_action' style="width: 26%;">
						<option value=''><?php esc_html_e( '< No Change >', 'eh_bulk_edit' ); ?></option>
						<option value='yes'><?php esc_html_e( 'Enable', 'eh_bulk_edit' ); ?></option>
						<option value='no'><?php esc_html_e( 'Disable', 'eh_bulk_edit' ); ?></option>
					</select>
					<span id='manage_stock_check_text'></span>
				</td>
			</tr>
			<tr>
				<td class='eh-edit-tab-table-left'>
					<?php esc_html_e( 'Stock Quantity', 'eh_bulk_edit' ); ?>
				</td>
				<td class='eh-edit-tab-table-middle'>
					<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_attr_e( 'Choose an option to update stock quantity and enter the value', 'eh_bulk_edit' ); ?>'></span>
				</td>
				<td class='eh-edit-tab-table-input-td'>
					<select id='stock_quantity_action' style="width: 26%;">
						<option value=''><?php esc_html_e( '< No Change >', 'eh_bulk_edit' ); ?></option>
						<option value='add'><?php esc_html_e( 'Increase', 'eh_bulk_edit' ); ?></option>
						<option value='sub'><?php esc_html_e( 'Decrease', 'eh_bulk_edit' ); ?></option>
						<option value='replace'><?php esc_html_e( 'Replace', 'eh_bulk_edit' ); ?></option>
					</select>
					<span id='stock_quantity_text'></span>
				</td>
			</tr>
			<tr>
				<td class='eh-edit-tab-table-left'>
					<?php esc_html_e( 'Allow Backorders', 'eh_bulk_edit' ); ?>
				</td>
				<td class='eh-edit-tab-table-middle'>
					<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_attr_e( 'Choose how you want to handle backorders', 'eh_bulk_edit' ); ?>'></span>
				</td>
				<td class='eh-edit-tab-table-input-td'>
					<select id='allow_backorder_action' style="width: 26%;">
						<option value=''><?php esc_html_e( '< No Change >', 'eh_bulk_edit' ); ?></option>
						<option value='no'><?php esc_html_e( 'Do not Allow', 'eh_bulk_edit' ); ?></option>
						<option value='notify'><?php esc_html_e( 'Allow, but Notify the Customer', 'eh_bulk_edit' ); ?></option>
						<option value='yes'><?php esc_html_e( 'Allow', 'eh_bulk_edit' ); ?></option>
					</select>
					<span id='backorder_text'></span>
				</td>
			</tr>
			<tr>
				<td class='eh-edit-tab-table-left'>
					<?php esc_html_e( 'Stock Status', 'eh_bulk_edit' ); ?>
				</td>
				<td class='eh-edit-tab-table-middle'>
					<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_attr_e( 'Choose an option to update  the stock status', 'eh_bulk_edit' ); ?>'></span>
				</td>
				<td class='eh-edit-tab-table-input-td'>
					<select id='stock_status_action' style="width: 26%;">
						<option value=''><?php esc_html_e( '< No Change >', 'eh_bulk_edit' ); ?></option>
						<option value='instock'><?php esc_html_e( 'In Stock', 'eh_bulk_edit' ); ?></option>
						<option value='outofstock'><?php esc_html_e( 'Out of Stock', 'eh_bulk_edit' ); ?></option>
						<option value='onbackorder'><?php esc_html_e( 'On Backorder', 'eh_bulk_edit' ); ?></option>
					</select>
				</td>
			</tr>
		</table>
		<h2>
			<?php esc_html_e( 'Weight & Dimensions', 'eh_bulk_edit' ); ?>
		</h2>
		<hr>
		<table class='eh-edit-table' id='update_properties_table'>
			<tr>
				<td class='eh-edit-tab-table-left'>
					<?php esc_html_e( 'Length', 'eh_bulk_edit' ); ?>
					<span style="float:right;">
						<?php
						$dimension_unit = strtolower( get_option( 'woocommerce_dimension_unit' ) );
						/* translators: Dimension unit */
						printf( esc_html__( '(%s)', 'eh_bulk_edit' ), esc_html( $dimension_unit ) );
						?>
					</span>
				</td>
				<td class='eh-edit-tab-table-middle'>
					<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_attr_e( 'Choose an option to update length and enter the value', 'eh_bulk_edit' ); ?>'></span>
				</td>
				<td class='eh-edit-tab-table-input-td'>
					<select id='length_action' style="width: 26%;">
						<option value=''><?php esc_html_e( '< No Change >', 'eh_bulk_edit' ); ?></option>
						<option value='add'><?php esc_html_e( 'Increase', 'eh_bulk_edit' ); ?></option>
						<option value='sub'><?php esc_html_e( 'Decrease', 'eh_bulk_edit' ); ?></option>
						<option value='replace'><?php esc_html_e( 'Replace', 'eh_bulk_edit' ); ?></option>
					</select>
					<span id='length_text'></span>
				</td>
			</tr>
			<tr>
				<td class='eh-edit-tab-table-left'>
					<?php esc_html_e( 'Width', 'eh_bulk_edit' ); ?>
					<span style="float:right;"><?php esc_html_e( '(' . strtolower( get_option( 'woocommerce_dimension_unit' ) ) . ')' ); ?></span>
				</td>
				<td class='eh-edit-tab-table-middle'>
					<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_attr_e( 'Choose an option to update width and enter the value', 'eh_bulk_edit' ); ?>'></span>
				</td>
				<td class='eh-edit-tab-table-input-td'>
					<select id='width_action' style="width: 26%;">
						<option value=''><?php esc_html_e( '< No Change >', 'eh_bulk_edit' ); ?></option>
						<option value='add'><?php esc_html_e( 'Increase', 'eh_bulk_edit' ); ?></option>
						<option value='sub'><?php esc_html_e( 'Decrease', 'eh_bulk_edit' ); ?></option>
						<option value='replace'><?php esc_html_e( 'Replace', 'eh_bulk_edit' ); ?></option>
					</select>
					<span id='width_text'></span>
				</td>
			</tr>
			<tr>
				<td class='eh-edit-tab-table-left'>
					<?php esc_html_e( 'Height', 'eh_bulk_edit' ); ?>
					<span style="float:right;"><?php esc_html_e( '(' . strtolower( get_option( 'woocommerce_dimension_unit' ) ) . ')' ); ?></span>
				<td class='eh-edit-tab-table-middle'>
					<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_attr_e( 'Choose an option to update height and enter the value', 'eh_bulk_edit' ); ?>'></span>
				</td>
				<td class='eh-edit-tab-table-input-td'>
					<select id='height_action' style="width: 26%;">
						<option value=''><?php esc_html_e( '< No Change >', 'eh_bulk_edit' ); ?></option>
						<option value='add'><?php esc_html_e( 'Increase', 'eh_bulk_edit' ); ?></option>
						<option value='sub'><?php esc_html_e( 'Decrease', 'eh_bulk_edit' ); ?></option>
						<option value='replace'><?php esc_html_e( 'Replace', 'eh_bulk_edit' ); ?></option>
					</select>
					<span id='height_text'></span>
				</td>
			</tr>
			<tr>
				<td class='eh-edit-tab-table-left'>
					<?php esc_html_e( 'Weight', 'eh_bulk_edit' ); ?>
					<span style="float:right;"><?php esc_html_e( '(' . strtolower( get_option( 'woocommerce_weight_unit' ) ) . ')' ); ?></span>
				</td>
				<td class='eh-edit-tab-table-middle'>
					<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_attr_e( 'Choose an option to update weight and enter the value', 'eh_bulk_edit' ); ?>'></span>
				</td>
				<td class='eh-edit-tab-table-input-td'>
					<select id='weight_action' style="width: 26%;">
						<option value=''><?php esc_html_e( '< No Change >', 'eh_bulk_edit' ); ?></option>
						<option value='add'><?php esc_html_e( 'Increase', 'eh_bulk_edit' ); ?></option>
						<option value='sub'><?php esc_html_e( 'Decrease', 'eh_bulk_edit' ); ?></option>
						<option value='replace'><?php esc_html_e( 'Replace', 'eh_bulk_edit' ); ?></option>
					</select>
					<span id='weight_text'></span>
				</td>
			</tr>
		</table>

		<h2>
			<?php esc_html_e( 'Tax', 'eh_bulk_edit' ); ?>
		</h2>
		<hr>
		<table class='eh-edit-table' id='update_tax_table'>

			<tr id="tax_status_add_edit">
				<td class='eh-edit-tab-table-left'>
					<?php esc_html_e( 'Tax status', 'eh_bulk_edit' ); ?>
				</td>
				<td class='eh-edit-tab-table-middle'>
					<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Select an option to determine whether you want to display the selected attribute on the product page.', 'eh_bulk_edit' ); ?>'></span>
				</td>
				<td class='eh-edit-tab-table-input-td'>
					<select id='tax_status_action' style="width: 210px;">
						<option value=''><?php esc_html_e( '< No Change >', 'eh_bulk_edit' ); ?></option>
						<option value='taxable'><?php esc_html_e( 'Taxable', 'eh_bulk_edit' ); ?></option>
						<option value='shipping'><?php esc_html_e( 'Shipping', 'eh_bulk_edit' ); ?></option>
						<option value='none'><?php esc_html_e( 'None', 'eh_bulk_edit' ); ?></option>
					</select>
				</td>
			</tr>

			<tr id="tax_class_add_edit">
				<td class='eh-edit-tab-table-left'>
					<?php esc_html_e( 'Tax Class', 'eh_bulk_edit' ); ?>
				</td>
				<td class='eh-edit-tab-table-middle'>
					<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Select an option to determine whether you want to display the selected attribute on the product page.', 'eh_bulk_edit' ); ?>'></span>
				</td>
				<td class='eh-edit-tab-table-input-td'>

					<select id='tax_class_action' style="width: 210px;">
						<?php
						$tax_classes              = WC_Tax::get_tax_classes();
						$classes_names            = array();
						$classes_names['default'] = 'Standard';
						if ( ! empty( $tax_classes ) ) {
							foreach ( $tax_classes as $class ) {
								$classes_names[ sanitize_title( $class ) ] = esc_html( $class );
							}
						}
						if ( count( $tax_classes ) > 0 ) {
							?>
							<option value=''><?php esc_html_e( '< No Change >', 'eh_bulk_edit' ); ?></option>
							<?php
							foreach ( $classes_names as $key => $value ) {
								echo "<option value='" . filter_var( $key ) . "'>" . filter_var( $value ) . '</option>';
							}
						} else {
							?>
							<option value=''><?php esc_html_e( '<No change >', 'eh_bulk_edit' ); ?></option>
							<?php
						}
						?>
					</select>
				</td>
			</tr>
		</table>

		<h2>
			<?php esc_html_e( 'Attributes', 'eh_bulk_edit' ); ?>
		</h2>
		<hr>
		<table class='eh-edit-table' id='update_attribute_table'>

			<tr id="attr_add_edit">
				<td class='eh-edit-tab-table-left'>
					<?php esc_html_e( 'Attribute Actions', 'eh_bulk_edit' ); ?>
	<!--                    <span style="float:right;"><?php esc_html_e( '(' . strtolower( get_option( 'woocommerce_weight_unit' ) ) . ')' ); ?></span>-->
				</td>
				<td class='eh-edit-tab-table-middle'>
					<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_attr_e( 'Select an option to make changes to your attribute values', 'eh_bulk_edit' ); ?>'></span>
				</td>
				<td class='eh-edit-tab-table-input-td'>
					<select id='attribute_action' style="width: 210px;">
						<option value=''><?php esc_html_e( '< No Change >', 'eh_bulk_edit' ); ?></option>
						<option value='add'><?php esc_html_e( 'Add New Values', 'eh_bulk_edit' ); ?></option>
						<option value='remove'><?php esc_html_e( 'Remove Existing Values', 'eh_bulk_edit' ); ?></option>
						<option value='replace'><?php esc_html_e( 'Overwrite Existing Values', 'eh_bulk_edit' ); ?></option>
					</select>
				</td>
			</tr>
			<tr id="attr_names" >
				<td class='eh-edit-tab-table-left'>
					<?php esc_html_e( 'Attributes to Update', 'eh_bulk_edit' ); ?>
	<!--                    <span style="float:right;"><?php esc_html_e( '(' . strtolower( get_option( 'woocommerce_weight_unit' ) ) . ')'); ?></span>-->
				</td>
				<td class='eh-edit-tab-table-middle'>
					<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_attr_e( 'Select the attribute(s) for which you want to change the values', 'eh_bulk_edit' ); ?>'></span>
				</td>
				<td class= 'eh-edit-tab-table-input-td'>
					<?php
					if ( count( $attributes ) > 0 ) {
						foreach ( $attributes as $key => $value ) {
							echo "<span id='attribu_name' class='checkbox-eh'><input type='checkbox' name='attribu_name' value='" . esc_attr( $value->attribute_name ) . "' id='" . esc_attr( $value->attribute_name ) . "'>" . esc_html( $value->attribute_label ) . '</span>';
						}
					} else {
						echo "<span id='attribu_name' class='checkbox-eh'>" . esc_html__( 'No attributes found.', 'eh_bulk_edit' ) . '</span>';
					}
					?>
				</td>
	<!--                    <span id='weight_text'></span>-->

			</tr>
			<tr id="new_attr">

			</tr>

			<tr id ="variation_select">

			</tr>





		</table>
		<h2>
			<?php esc_html_e( 'Categories', 'eh_bulk_edit' ); ?>
		</h2>
		<hr>
		<table class='eh-edit-table' id='update_category_table'>

			<tr id="cat_update">
				<td class='eh-edit-tab-table-left'>
					<?php esc_html_e( 'Category Actions', 'eh_bulk_edit' ); ?>
				</td>
				<td class='eh-edit-tab-table-middle'>
					<span class='woocommerce-help-tip tooltip' data-tooltip="<?php esc_attr_e( "Select an action to re-assign categories. 'Add' will append and 'Remove' will take out categories. 'Overwrite' will remove all the existing categories and assign the selected categories", 'eh_bulk_edit' ); ?>"></span>
				</td>
				<td class='eh-edit-tab-table-input-td'>
					<input type="radio" id="cat_update_none" name="edit_category" value="cat_none" checked /><label><?php esc_html_e( 'None', 'eh_bulk_edit' ); ?></label>
					<input type="radio" id="cat_update_add" name="edit_category" value="cat_add" /><label><?php esc_html_e( 'Add', 'eh_bulk_edit' ); ?></label>
					<input type="radio" id="cat_update_remove" name="edit_category" value="cat_remove" /><label><?php esc_html_e( 'Remove', 'eh_bulk_edit' ); ?></label>
					<input type="radio" id="cat_update_replace" name="edit_category" value="cat_replace" /><label><?php esc_html_e( 'Overwrite', 'eh_bulk_edit' ); ?></label>
				</td>

			</tr>
			<tr id="cat_select">
				<td class='eh-edit-tab-table-left'>
					<?php esc_html_e( 'Select Categories', 'eh_bulk_edit' ); ?>
				</td>
				<td class='eh-edit-tab-table-middle'>
					<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_attr_e( 'Select a category to perform the action you have chosen.', 'eh_bulk_edit' ); ?>'></span>
				</td>
				<td class='eh-edit-tab-table-input-td'>
					<?php
					$cat_args      = array(
						'hide_empty'   => 0,
						'taxonomy'     => 'product_cat',
						'hierarchical' => 1,
						'orderby'      => 'name',
						'order'        => 'ASC',
						'child_of'     => 0,
					);
					$cat_hierarchy = elex_bep_get_cat_hierarchy( 0, $cat_args );
					$cat_rows      = elex_bep_category_rows( $cat_hierarchy, 0 );
					?>
					<div id="product_cat-all" class="tabs-panel">
						<ul id="product_catchecklist">
							<?php
							$allowed_html          = wp_kses_allowed_html( 'post' );
							$allowed_html['input'] = array(
									'type'      => true,
									'name'      => true,
									'value'     => true,
									'checked'   => false,
							);
							echo wp_kses( $cat_rows, $allowed_html );
							?>
						</ul></div>
				</td>

			</tr>



		</table>

		<?php
		if ( in_array( 'pricing-discounts-by-user-role-woocommerce/pricing-discounts-by-user-role-woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			?>
			<h2>
				<?php esc_html_e( 'Role Based Pricing', 'eh_bulk_edit' ); ?>
			</h2>
			<hr>
			<table class='eh-edit-table' id='update_general_table'>
				<tr>
					<td class='eh-edit-tab-table-left'>
						<?php esc_html_e( 'Hide price', 'eh_bulk_edit' ); ?>
					</td>
					<td class='eh-edit-tab-table-middle'>
						<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_attr_e( 'Select option to hide price for unregistered users.', 'eh_bulk_edit' ); ?>'></span>
					</td>
					<td class='eh-edit-tab-table-input-td'>
						<select id='visibility_price'>
							<option value=''><?php esc_html_e( '< No Change >', 'eh_bulk_edit' ); ?></option>
							<option value='no'><?php esc_html_e( 'Show Price', 'eh_bulk_edit' ); ?></option>
							<option value='yes'><?php esc_html_e( 'Hide Price', 'eh_bulk_edit' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<td class='eh-edit-tab-table-left'>
						<?php esc_html_e( 'Hide product price based on user role', 'eh_bulk_edit' ); ?>
					</td>
					<td class='eh-edit-tab-table-middle'>
						<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_attr_e( 'For selected user role, hide the product price', 'eh_bulk_edit' ); ?>'></span>
					</td>
					<td class='eh-edit-tab-table-input-td'>
						<span class='select-eh'>
							<select data-placeholder='<?php esc_attr_e( 'User Role', 'eh_bulk_edit' ); ?>' id='hide_price_role_select' multiple class='hide-price-role-select-chosen' >
								<?php
								global $wp_roles;
								$roles = $wp_roles->role_names;
								foreach ( $roles as $key => $value ) {
									echo "<option value='" . esc_attr( $key ) . "'>" . esc_html( $value ) . '</option>';
								}
								?>
							</select>
						</span>
					</td>
				</tr>
				<?php
				$enabled_roles = get_option( 'eh_pricing_discount_product_price_user_role' );
				if ( is_array( $enabled_roles ) ) {
					if ( ! in_array( 'none', $enabled_roles ) ) {
						?>
						<tr>
							<td class='eh-edit-tab-table-left'>
								<?php esc_html_e( 'Enforce product price adjustment', 'eh_bulk_edit' ); ?>
							</td>
							<td class='eh-edit-tab-table-middle'>
								<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_attr_e( 'Select option to enforce indvidual price adjustment', 'eh_bulk_edit' ); ?>'></span>
							</td>
							<td class='eh-edit-tab-table-input-td'>
								<select id='price_adjustment_action'>
									<option value=''><?php esc_html_e( '< No Change >', 'eh_bulk_edit' ); ?></option>
									<option value='yes'><?php esc_html_e( 'Enable', 'eh_bulk_edit' ); ?></option>
									<option value='no'><?php esc_html_e( 'Disable', 'eh_bulk_edit' ); ?></option>
								</select>
							</td>
						</tr>
						<?php
					}
				}
				?>
			</table>
			<?php
		}

		if ( in_array( 'per-product-addon-for-woocommerce-shipping-pro/woocommerce-per-product-shipping-addon-for-shipping-pro.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			?>

			<h2>
				<?php esc_html_e( 'Shipping Pro', 'eh_bulk_edit' ); ?>
			</h2>
			<hr>
			<table class='eh-edit-table' id='update_properties_table'>
				<tr>
					<td class='eh-edit-tab-table-left'>
						<?php esc_html_e( 'Shipping Unit', 'eh_bulk_edit' ); ?>
					</td>
					<td class='eh-edit-tab-table-middle'>
						<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_attr_e( 'Update Shipping Unit', 'eh_bulk_edit' ); ?>'></span>
					</td>
					<td class='eh-edit-tab-table-input-td'>
						<select id='shipping_unit_action'>
							<option value=''><?php esc_html_e( '< No Change >', 'eh_bulk_edit' ); ?></option>
							<option value='add'><?php esc_html_e( 'Add', 'eh_bulk_edit' ); ?></option>
							<option value='sub'><?php esc_html_e( 'Subtract', 'eh_bulk_edit' ); ?></option>
							<option value='replace'><?php esc_html_e( 'Replace', 'eh_bulk_edit' ); ?></option>
						</select>
						<span id='shipping_unit_text'></span>
					</td>
				</tr>
			</table>
			<?php
		}
		?>
		</table>
		<h2>
			<?php esc_html_e( 'Delete Products', 'eh_bulk_edit' ); ?>
		</h2>
		<hr>
		<table class='eh-edit-table' id='delete_products_table'>
			<tr>
				<td class='eh-edit-tab-table-left'>
					<?php esc_html_e( 'Delete Action', 'eh_bulk_edit' ); ?>
				</td>
				<td class='eh-edit-tab-table-middle'>
					<span class='woocommerce-help-tip tooltip' data-tooltip='<?php esc_html_e( 'Select how you want to delete products.', 'eh_bulk_edit' ); ?>'></span>
				</td>
				<td class='eh-edit-tab-table-input-td'>
					<select id="delete_product_action">
						<option value=""><?php esc_html_e( '< No Change >', 'eh_bulk_edit' ); ?></option>
						<option value="move_to_trash"><?php esc_html_e( 'Move to trash', 'eh_bulk_edit' ); ?></option>
						<option value="delete_permanently"><?php esc_html_e( 'Delete Permanently', 'eh_bulk_edit' ); ?></option>
					</select>
				</td>
			</tr>
		</table>
		<button id='edit_back' value='cancel_update_button' style="margin-bottom: 1%; background-color: gray; color: white; width: 10%; " class='button button-large'><span class="update-text"><?php esc_html_e( 'Back', 'eh_bulk_edit' ); ?></span></button>
		<button id='edit_cancel' value='cancel_update_button' style="margin-bottom: 1%; background-color: gray; color: white; width: 10%; " class='button button-large'><span class="update-text"><?php esc_html_e( 'Cancel', 'eh_bulk_edit' ); ?></span></button>
		<button id='reset_update_button' value='reset_update_button' style="margin-bottom: 1%; background-color: gray; color: white; width: 10%;" class='button button-large'><span class="update-text"><?php esc_html_e( 'Reset Values', 'eh_bulk_edit' ); ?></span></button>
		<button id='update_button' value='update_button' style="margin-bottom: 1%; float: right; color: white; width: 12%;" class='button button-primary button-large'><span class="update-text"><?php esc_html_e( 'Update Products', 'eh_bulk_edit' ); ?></span></button>
	</div>    
	<?php
}
?>
<?php
add_action( 'admin_footer', 'elex_bep_variation_pop' );
require_once ELEX_BEP_TEMPLATE_PATH . '/elex-template-frontend-settings-tab-fields.php';

function elex_bep_variation_pop() {
	$page = ( isset( $_GET['page'] ) ) ? esc_attr( $_GET['page'] ) : false;
	if ( 'eh-bulk-edit-product-attr' != $page ) {
		return;
	}
	?>
	<div class="popup" data-popup="popup-1" id='main_var_disp'>
		<div class="popup-inner" >
			<center><h3><?php esc_html_e( 'Product variations', 'eh_bulk_edit' ); ?></h3></center>
			<div id='vari_disp' style="overflow-y: auto; height: 80%; position:relative;">
			</div>
			<span class="popup-close " data-popup-close="popup-1" id='pop_close' style="cursor:pointer;">x</span>
		</div>
	</div>
	<?php
}

function elex_bep_get_cat_hierarchy( $parent, $args ) {
	$cats = get_categories( $args );
	$ret  = new stdClass();
	foreach ( $cats as $cat ) {
		if ( $cat->parent == $parent ) {
			$id                 = $cat->cat_ID;
			$ret->$id           = $cat;
			$ret->$id->children = elex_bep_get_cat_hierarchy( $id, $args );
		}
	}
	return $ret;
}

function elex_bep_category_rows( $categories, $level ) {
	$html_code       = '';
	$level_indicator = '';
	for ( $i = 0; $i < $level; $i++ ) {
		$level_indicator .= '- ';
	}
	if ( $categories ) {
		foreach ( $categories as $category ) {
			$html_code .= '<li><label><input value=' . $category->term_id . " type='checkbox' name='cat_update'>" . $level_indicator . $category->name . '</label></li>';
			if ( $category->children && count( (array) $category->children ) > 0 ) {
				$html_code .= elex_bep_category_rows( $category->children, $level + 1 );
			}
		}
	} else {
		$html_code .= esc_html__( 'No categories found.', 'eh_bulk_edit' );
	}
	return $html_code;
}
