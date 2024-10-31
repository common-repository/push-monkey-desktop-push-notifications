<div class="pm-loader"></div>
<div class="push-monkey push-monkey-bootstrap">
	<div class="container-fluid">
		<?php if ( ! $signed_in ) { ?>
			<?php require_once( plugin_dir_path(__DIR__). 'settings/parts/log-in.php' ); ?>
		<?php } else { ?>
			<?php require_once( plugin_dir_path(__DIR__). 'settings/parts/header.php' ); ?>
		</div>
		<div class="panel-body">

			<?php
			if ( ! $woocommerce_is_active ) { ?>

				<h3>Did you know Push Monkey works seamlessly with WooCommerce?</h3>
				<p>
					Install and activate WooCommerce to take full advantage of this feature.
				</p>
			<?php } else { ?>

				<h3>The WooCommerce feature is now active.</h3>  
			<?php }?>
		<?php } ?>
	</div>

	<?php if ( $woocommerce_is_active ) { ?>
		<div class="pm-feature-active-list">

		<div class="pm-feature-active-item" id="abandoned_cart_status">
			<div class="card text-dark bg-light">
				<div class="card-header">
					<h2>Abandoned Cart Notification</h2>     
					<div><mark class="text-light bg-success pm-woo-status-text" data-action-text="Active" data-inaction-text="Inactive">Active</mark></div>
				</div>
				<div class="card-body ">
					<div class="pm-icon"><i class="fa fa-shopping-cart"></i></div>
					<p class="card-text text-center" >Turn abandoned carts into sales by sending push notification reminders
					straight to your consumers.
					</p>
				</div>
				<div class="card-footer border-warning text-center">
					<a class="btn btn-danger btn-sm pm-woo-status" data-key="abandoned_cart_status" data-enable="Enable" data-disable="Disable"><i class="fa fa-remove"></i><b>Disable</b></a>
					<a class="btn btn-info btn-sm" href="<?php $localpath[0] ?>admin.php?page=push_monkey_woo_abandoned_cart"><i class="fa fa-cogs"></i><b>Configure</b></a>
				</div>
			</div>
		</div>  
		<div class="pm-feature-active-item" id="back_in_stock_status">
			<div class="card text-dark bg-light"> 
				<div class="card-header">
					<h2>Back in Stock Notification</h2>
					<div><mark class="text-light bg-success pm-woo-status-text" data-action-text="Active" data-inaction-text="Inactive">Active</mark></div>
				</div>
				<div class="card-body ">
					<div class="pm-icon"><i class="fa fa-mail-reply"></i></div>
					<p class="card-text text-center">Avoid missed sales for products that are out of stock by notifying consumers when desired product available again.</p>
				</div>
				<div class="card-footer border-warning text-center">
					<a class="btn btn-danger btn-sm pm-woo-status" data-key="back_in_stock_status" data-enable="Enable" data-disable="Disable"><i class="fa fa-remove"></i><b>Disable</b></a>
					<a class="btn btn-info btn-sm" name="BackInStockConfigure" href="<?php $localpath[0] ?>admin.php?page=push_monkey_woo_back_in_stock"><i class="fa fa-cogs"></i><b>Configure</b></a>
				</div>
			</div>
		</div>
		<div class="pm-feature-active-item" id="price_drop_notifications_status">
			<div class="card text-dark bg-light">
				<div class="card-header">
					<h2>Price Drop Notification</h2>
					<div><mark class="text-light bg-success pm-woo-status-text" data-action-text="Active" data-inaction-text="Inactive">Active</mark></div>
				</div>
				<div class="card-body ">
					<div class="pm-icon"><i class="fa fa-flash"></i></div>
					<p class="card-text text-center" >A second chance to get the attention of possible customers by automatically sending the price of a product changes.</p>
				</div>
				<div class="card-footer border-warning text-center">
					<a class="btn btn-danger btn-sm pm-woo-status" data-key="price_drop_notifications_status" data-enable="Enable" data-disable="Disable"><i class="fa fa-remove"></i><b>Disable</b></a>
					<a class="btn btn-info btn-sm" name="PriceDropConfigure" href="<?php $localpath[0] ?>admin.php?page=push_monkey_woo_price_drop_notification"><i class="fa fa-cogs"></i><b>Configure</b></a>
				</div>
			</div>
		</div>
		<div class="pm-feature-active-item" id="product_review_reminders_status">
			<div class="card text-dark bg-light">
				<div class="card-header">
					<h2>Product Review Reminders</h2>
					<div><mark class="text-light bg-success pm-woo-status-text" data-action-text="Active" data-inaction-text="Inactive">Active</mark></div>
				</div>
				<div class="card-body ">
					<div class="pm-icon"><i class="fa fa-star-half-o"></i></div>
					<p class="card-text text-center" >Increase sales by receiving relevant reviews from your previous customers. Sent automatically.</p>
				</div>
				<div class="card-footer border-warning text-center">
					<a class="btn btn-danger btn-sm pm-woo-status" data-key="product_review_reminders_status" data-enable="Enable" data-disable="Disable"><i class="fa fa-remove"></i><b>Disable</b></a>
					<a class="btn btn-info btn-sm" name="ProductReviewConfigure" href="<?php $localpath[0] ?>admin.php?page=push_monkey_woo_product_review_reminders"><i class="fa fa-cogs"></i><b>Configure</b></a>
				</div>
			</div>
		</div>
		<div class="pm-feature-active-item" id="welcome_discounts_status">
			<div class="card text-dark bg-light">
				<div class="card-header">
					<h2>Welcome Discounts</h2>
					<div><mark class="text-light bg-success pm-woo-status-text" data-action-text="Active" data-inaction-text="Inactive">Active</mark></div>
				</div>
				<div class="card-body ">
					<div class="pm-icon"><i class="fa fa-check-square-o"></i></div>
					<p class="card-text text-center" >The very first notification after subscribing lets the customer get familiar with push notifications.</p>
				</div>
				<div class="card-footer border-warning text-center">
					<a class="btn btn-danger btn-sm pm-woo-status" data-key="welcome_discounts_status" data-enable="Enable" data-disable="Disable"><i class="fa fa-remove"></i><b>Disable</b></a>
					<a class="btn btn-info btn-sm" name="WelcomeNotificationConfigure" href="<?php $localpath[0] ?>admin.php?page=push_monkey_woo_welcome_notification"><i class="fa fa-cogs"></i><b>Configure</b></a>
				</div>
			</div>
		</div>
	<?php }?>
	<?php require_once( plugin_dir_path( __FILE__ ) . '/parts/footer.php' ); ?>
</div></div>