<div class="pm-loader" style="display: none;"></div>
<div class="push-monkey push-monkey-bootstrap">
<div class="container-fluid">
<?php if ( ! $signed_in ) { ?>
<?php require_once(plugin_dir_path(__DIR__).'settings/parts/log-in.php' );?>
<?php } else { ?>
<?php require_once(plugin_dir_path(__DIR__).'settings/parts/header.php' ); 
?>
<div class="push-monkey-bootstrap">
	<form method="post" class="form-horizontal" id="back_in_stock" enctype="multipart/form-data">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">Back In Stock Notification Settings</h3>
			</div>
			<div class="panel-body">
				<div class="form-group">
					<label class="col-md-3 control-label">Back In Stock Reminder Enabled</label>
					<div class="col-md-3">
						<label class="switch">
							<input type="checkbox" name="active">
							<span class="slider round"></span>
						</label>
						<span class="help-block">Allow your customers to subscribe for push notifications when products come back in stock.</span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 col-xs-12 control-label">Position</label>
					<div class="col-md-3 col-xs-12">
						<select name="position" id="position" class="form-control">
							<option value="left-top">Left Top</option>
							<option value="right-top">Right Top</option>
							<option value="bottom-left">Bottom Left</option>
							<option value="bottom-right">Bottom Right</option>
							<option value="left-bottom">Left Bottom</option>
							<option value="right-bottom">Right Bottom</option>
						</select>
						<span class="help-block">You can change dialog box position.</span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 col-xs-12 control-label">Notification Title</label>
					<div class="col-md-6 col-xs-12">
						<input type="text"class="form-control" name="notification_title" >
						<span class="help-block">You can use the [product-name] keyword as placeholder that will be dynamically replaced with the real value.</span>
					</div>
				</div>

				<div class="form-group">
					<label class="col-md-3 col-xs-12 control-label">Notification Message</label>
					<div class="col-md-6 col-xs-12">
						<input type="text"class="form-control" name="notification_message">
						<span class="help-block">You can use the [product-name] keyword as placeholder that will be dynamically replaced with the real value.</span>
					</div>
				</div>
				<!-- <div class="form-group">
					<label class="col-md-3 col-xs-12 control-label"></label>
					<div class="col-md-6 col-xs-12">
						<img src="<?php// echo $pluginPath . 'img/green floating label.png' ?>" name="image-swap" />
					</div>
				</div> -->

				<div class="form-group">
					<label class="col-md-3 col-xs-12 control-label">Title</label>
					<div class="col-md-6 col-xs-12">
						<input type="text" class="form-control" name="pop_up_title">
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 col-xs-12 control-label">Message</label>
					<div class="col-md-6 col-xs-12">
						<input type="text" class="form-control" name="pop_up_message">
						<span class="help-block">You can use the [product-name] keyword as placeholder that will be dynamically replaced with the real value.
						</span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 col-xs-12 control-label">Button</label>
					<div class="col-md-6 col-xs-12">
						<input type="text" class="form-control" name="button_text">
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 col-xs-12 control-label">Success Message</label>
					<div class="col-md-6 col-xs-12">
						<input type="text" class="form-control" name="success_message">
						<span class="help-block">You can use the [product-name] keyword as placeholder that will be dynamically replaced with the real value.
						</span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 col-xs-12 control-label">Color</label>
					<div class="col-md-6 col-xs-12">
						<div class="input-group color" id="colorpicker">
							<input type="text" name="color" class="form-control"/>
							<span class="input-group-addon"><i></i></span>
						</div>
						<span class="help-block">
							Default value is #2fcc70.
						</span>
					</div>
				</div>
			</div>
			<div class="panel-footer">
				<button type="submit" class="btn btn-primary pull-right">Save</button>
			</div>
		</div>
	</form>
</div>
			<?php require_once( plugin_dir_path(__DIR__) . 'settings/parts/footer.php' );
			?>
		<?php } ?>
	</div>
</div>