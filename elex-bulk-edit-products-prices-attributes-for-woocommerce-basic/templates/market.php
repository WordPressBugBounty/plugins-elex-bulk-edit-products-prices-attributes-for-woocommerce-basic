<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<style>
	.woocommerce-save-button{
		display: none !important;
	}
	.box14{
		width: 100%;
		margin-top:2px;
		min-height: 310px;
		margin-right: 400px;
		padding:10px;
		z-index:1;
		right:0px;
		float:left;
		background: -webkit-gradient(linear, 0% 20%, 0% 92%, from(#fff), to(#f3f3f3), color-stop(.1,#fff));
		border: 1px solid #ccc;
		-webkit-border-radius: 60px 5px;
		-webkit-box-shadow: 0px 0px 35px rgba(0, 0, 0, 0.1) inset;
	}
	.box14_ribbon{
		position:absolute;
		top:0; right: 0;
		width: 130px;
		height: 40px;
		background: -webkit-gradient(linear, 555% 20%, 0% 92%, from(rgba(0, 0, 0, 0.1)), to(rgba(0, 0, 0, 0.0)), color-stop(.1,rgba(0, 0, 0, 0.2)));
		border-left: 1px dashed rgba(0, 0, 0, 0.1);
		border-right: 1px dashed rgba(0, 0, 0, 0.1);
		-webkit-box-shadow: 0px 0px 12px rgba(0, 0, 0, 0.2);
		-webkit-transform: rotate(6deg) skew(0,0) translate(-60%,-5px);
	}
	.box14 h3
	{
		text-align:center;
		margin:2px;
	}
	.box14 p
	{
		text-align:center;
		margin:2px;
		border-width:1px;
		border-style:solid;
		padding:5px;
		border-color: rgb(204, 204, 204);
	}
	.box14 span
	{
		background:#fff;
		padding:5px;
		display:block;
		box-shadow:green 0px 3px inset;
		margin-top:10px;
	}
	.box14 img {
		margin-top: 5px;
	}
	.table-box-main {
		box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
		transition: all 0.3s cubic-bezier(.25,.8,.25,1);
	}

	.table-box-main:hover {
		box-shadow: 0 14px 28px rgba(0,0,0,0.25), 0 10px 10px rgba(0,0,0,0.22);
	}
	span ul li{
		margin:4px;
	}
	.marketing_logos{
	width: 300px;
	height: 300px;
	border-radius: 10px;
	}
	.marketing_redirect_links{
		padding: 0px 2px !important;
		background-color: #337ab7 !important;
		color: white !important;
		height: 52px;
		font-weight: 600 !important;
		font-size: 18px !important;
		min-width: 210px;
	}
	.related_product_heading{
		background-color: #337ab7 !important;
		color: white !important;
	}
</style>
<div class="box14 table-box-main">
	
	<div class="elex_dp_wrapper">
		<center style="margin-top: 20px;">
			<div class="panel panel-default" style="margin: 20px;">
				<div class="panel-body">
					<div class="row">
						<div class="col-md-5">
	<img src="<?php echo esc_url( str_replace( '/templates', '', plugin_dir_url( __FILE__ ) . 'assets/images/prod-logo.png' ) ); ?>" class="marketing_logos">
	<h3><?php esc_html_e( 'ELEX WooCommerce Advanced Bulk Edit Products, Prices & Attributes', 'eh_bulk_edit' ); ?></h3>
	<br/> <center><a href="https://elextensions.com/plugin/bulk-edit-products-prices-attributes-for-woocommerce/" target="_blank" class="button button-primary">Go Premium!</a></center>
	 </div>
	<div class="col-md-5">
		<ul style="list-style-type:disc;">
			<p><?php esc_html_e( 'Note: Basic version only supports Simple Products.', 'eh_bulk_edit' ); ?></p>
			<p style="color:red;"><strong><?php esc_html_e( 'Your business is precious! Go Premium With Additional Features.', 'eh_bulk_edit' ); ?></strong></p>
			<p style="text-align:left">
				- <?php esc_html_e( 'Create WooCommerce Product Variations in Bulk.', 'eh_bulk_edit' ); ?><br>
				- <?php esc_html_e( 'Bulk Edit WooCommerce Product Types.', 'eh_bulk_edit' ); ?><br>
				- <?php esc_html_e( 'Bulk Edit WooCommerce Product Visibility.', 'eh_bulk_edit' ); ?><br>
				- <?php esc_html_e( 'Bulk Edit WooCommerce Variable Products.', 'eh_bulk_edit' ); ?><br>
				- <?php esc_html_e( 'Supports Bulk Edit Individual Variations of Variable Products.', 'eh_bulk_edit' ); ?><br>
				- <?php esc_html_e( 'Bulk Edit WooCommerce External Products.', 'eh_bulk_edit' ); ?><br>
				- <?php esc_html_e( 'Bulk Edit WooCommerce Product Attributes used for Creating Variation.', 'eh_bulk_edit' ); ?><br>
				- <?php esc_html_e( 'Schedule bulk update at a specific date & time.', 'eh_bulk_edit' ); ?><br>
				- <?php esc_html_e( 'Schedule recurring jobs for bulk update.', 'eh_bulk_edit' ); ?><br>
				- <?php esc_html_e( 'Revert back the bulk update after a specified scheduled time.', 'eh_bulk_edit' ); ?><br>
				- <?php esc_html_e( 'Bulk Edit Any Custom Product Fields.', 'eh_bulk_edit' ); ?><br>
				- <?php esc_html_e( 'Option to immediately undo the last WooCommerce bulk update.', 'eh_bulk_edit' ); ?><br>
				- <?php esc_html_e( 'Support for more filter options. (Description, Short Description, Stock Status, ...)', 'eh_bulk_edit' ); ?><br>
				- <?php esc_html_e( 'Select / Unselect Products after filtering for Update.', 'eh_bulk_edit' ); ?><br>
				- <?php esc_html_e( 'Timely compatibility updates and bug fixes.', 'eh_bulk_edit' ); ?><br>
				- <?php esc_html_e( 'Premium Support!', 'eh_bulk_edit' ); ?><br>
			</p>
		</ul>
		 <center> <a href="https://elextensions.com/knowledge-base/set-bulk-edit-products-prices-attributes-for-woocommerce-plugin/" target="_blank" class="button button-primary">Documentation</a></center>
						</div>
					</div>
				</div>
			</div>
		</center>
   
  </div>
   <div class="elex_dp_wrapper">
	<center style="margin-top: 20px;">
		<div class="panel panel-default" style="margin: 20px;">
		<div class="panel-heading related_product_heading">
			<h3 class="panel-title"><strong><?php esc_html_e( 'ELEXtensions Plugins You May Be Interested In...', 'eh_bulk_edit' ); ?></strong></h3>
		</div>
			<div class="panel-body">
				<div class="row">
					<div class="col-md-4">
						<div class="row">
							<div class="col-md-12">
								<img src="<?php echo esc_html( ELEX_BULK_EDIT_MAIN_URL_PATH . '/assets/images/amazon_payments.png' ); ?>" class="marketing_logos">
							</div>
						</div>
						<div class="row">
						<div class="col-md-12">
							<h5><a href="https://elextensions.com/plugin/amazon-payments-gateway-for-woocommerce/" target="_blank"><?php esc_html_e( 'ELEX Amazon Payments Gateway for WooCommerce', 'eh_bulk_edit' ); ?></a></h5>
						</div>
						</div>
					</div>
					<div class="col-md-4">
						<div class="row">
							<div class="col-md-12">
								<img src="<?php echo esc_html( ELEX_BULK_EDIT_MAIN_URL_PATH . '/assets/images/gpf.png' ); ?>" class="marketing_logos">
							</div>
						</div>
						<div class="row">
						<div class="col-md-12">
							<h5><a href="https://elextensions.com/plugin/woocommerce-google-product-feed-plugin/" target="_blank"><?php esc_html_e( 'ELEX WooCommerce Google Product Feed Plugin', 'eh_bulk_edit' ); ?></a></h5>
						</div>
						</div>
					</div>
					<div class="col-md-4">
						<div class="row">
							<div class="col-md-12">
								<img src="<?php echo esc_html( ELEX_BULK_EDIT_MAIN_URL_PATH . '/assets/images/wsdesk.png' ); ?>" class="marketing_logos">
							</div>
						</div>
						<div class="row">
						<div class="col-md-12">
							<h5><a href="https://elextensions.com/plugin/wsdesk-wordpress-support-desk-plugin/" target="_blank"><?php esc_html_e( 'WSDesk – ELEX WordPress Helpdesk Plugin', 'eh_bulk_edit' ); ?></a></h5>
						</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-4">
						<div class="row">
							<div class="col-md-12">
								<img src="<?php echo esc_html( ELEX_BULK_EDIT_MAIN_URL_PATH . '/assets/images/ship-calculator.png' ); ?>" class="marketing_logos">
							</div>
						</div>
						<div class="row">
						<div class="col-md-12">
							<h5><a href="https://elextensions.com/plugin/woocommerce-shipping-calculator-purchase-shipping-label-tracking-for-customers/" target="_blank"><?php esc_html_e( 'ELEX WooCommerce Shipping Calculator, Purchase Shipping Label & Tracking for Customers', 'eh_bulk_edit' ); ?></a></h5>
						</div>
						</div>
					</div>
					<div class="col-md-4">
						<div class="row">
							<div class="col-md-12">
								<img src="<?php echo esc_html( ELEX_BULK_EDIT_MAIN_URL_PATH . '/assets/images/ShipEngine.png' ); ?>" class="marketing_logos">
							</div>
						</div>
						<div class="row">
						<div class="col-md-12">
							<h5><a href="https://elextensions.com/plugin/elex-shipengine-multi-carrier-shipping-label-printing-plugin-for-woocommerce/" target="_blank"><?php esc_html_e( 'ELEX ShipEngine Multi-Carrier Shipping & Label Printing Plugin for WooCommerce', 'eh_bulk_edit' ); ?></a></h5>
						</div>
						</div>
					</div>
					<div class="col-md-4">
						<div class="row">
							<div class="col-md-12">
								<img src="<?php echo esc_html( ELEX_BULK_EDIT_MAIN_URL_PATH . '/assets/images/Stamps.com.png' ); ?>" class="marketing_logos">
							</div>
						</div>
						<div class="row">
						<div class="col-md-12">
							<h5><a href="https://elextensions.com/plugin/elex-woocommerce-stamps-com-usps-pickup-request-add-on/" target="_blank"><?php esc_html_e( 'ELEX WooCommerce Stamps.com-USPS Pickup Request Add-On', 'eh_bulk_edit' ); ?></a></h5>
						</div>
						</div>
					</div>
				</div>
				<div class="row">
				<div class="col-md-12">
					<input type="button" onclick='window.open("https://elextensions.com/product-category/plugins/", "_blank")' class="btn marketing_redirect_links" value="<?php esc_attr_e( 'Browse All ELEXtensions Plugins', 'eh_bulk_edit' ); ?>">
				</div>
				</div>
			</div>   
		</div>
	</center>
</div>  
</div>
