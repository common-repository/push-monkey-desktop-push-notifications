<div class="pm-loader" style="display: none;"></div>
<div class="push-monkey push-monkey-bootstrap">
	<div class="container-fluid">
		<?php
		if ( ! $signed_in ) {
			require_once(plugin_dir_path(__DIR__).'settings/parts/log-in.php' );
		} else {
			require_once(plugin_dir_path(__DIR__).'settings/parts/header.php' );
			?>
			<div class="push-monkey-bootstrap">
				<form enctype="multipart/form-data" method="post" id="pm_abandoned_cart" class="form-horizontal">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title">Abandoned Cart Settings</h3>
						</div>
						<div class="panel-body">
							<div class="form-group">
								<label class="col-md-3 control-label">Abandoned Cart Reminder Enabled</label>
								<div class="col-md-3">
									<label class="switch">
										<input type="checkbox" name="active">
										<span class="slider round"></span>
									</label>
									<span class="help-block">Allow your customers to subscribe for push notifications when abandoned cart not added.</span>
								</div>
							</div>

							<div class="form-group">
								<label class="col-md-3 col-xs-12 control-label">First notification sent after</label>
								<div class="col-md-3 col-sm-5 col-xs-12">
									<input type="text"  class="form-control" name="first_notification_sent_after" >
								</div>
								<div class="col-sm-3 col-xs-12"><label><p class="text-muted">minutes = 0 day(s) 0 hour(s) 50 minute(s)</p></label></div>
							</div>

							<div class="form-group">
								<label class="col-md-3 col-xs-12 control-label">Second notification sent after</label>
								<div class="col-md-3 col-sm-5 col-xs-12">
									<input type="text"  class="form-control" name="second_notification_sent_after" >
								</div>
								<div class="col-sm-3" >
									<label><p class="text-muted">minutes = 0 day(s) 1 hour(s) 40 minute(s)</p></label>
								</div>
								<div class="col-sm-1" ><label>Active?</label></div>
								<div class="col-sm-1" > 
									<label class="switch">
										<input type="checkbox" name="second_notification_sent_after_status">
										<span class="slider round"></span>
									</label>
								</div>
							</div>

							<div class="form-group">
								<label class="col-md-3 col-xs-12 control-label">Third notification sent after</label>
								<div class="col-md-3 col-sm-6 col-xs-12">
									<input type="text"  class="form-control" name="third_notification_sent_after" >
								</div>
								<div class="col-sm-3" ><label><span class="text-muted">minutes = 2 day(s) 0 hour(s) 0 minute(s)</span></label></div>
								<div class="col-sm-1" ><label>Active?</label></div>
								<div class="col-sm-1" >
									<label class="switch">
										<input type="checkbox" name="third_notification_sent_after_status">
										<span class="slider round"></span>
									</label>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 col-xs-12 control-label">Notification Title</label>
								<div class="col-md-6 col-xs-12">
									<input type="text" class="form-control" name="notification_title" >
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 col-xs-12 control-label">Notification Message</label>
								<div class="col-md-6 col-xs-12">
									<input type="text"class="form-control" name="notification_message">
									<span class="help-block">You can use the [product-name] and [price] keywords as placeholders that will be dynamically replaced with the real values.</span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 col-xs-12 control-label">Notification Image</label>
								<div class="col-md-6 col-xs-12">
									<input type="file" class="fileinput btn-primary" id="notification_image" value="24" name="notification_image">
									<span class="help-block">This image is sent together with the reminder notification. Recommended size 675x506 pixelsd</span>
								</div>
							</div>
							<div class="form-group" id="preview_image" style="display: none;">
								<label class="col-md-3 col-xs-12 control-label"></label>
								<div class="col-md-6 col-xs-12">
									<img class="img-responsive back-in-stock-label-preview back-in-stock-label-blue" width="280">
								</div>
							</div>
						</div>
						<div class="panel-footer">
							<button type="submit" class="btn btn-primary pull-right">Save</button>
						</div>
					</div>
				</form>
			</div>
			<?php require_once( plugin_dir_path( __DIR__ ) . 'settings/parts/footer.php' ); ?>
		<?php } ?>
	</div>
</div>
