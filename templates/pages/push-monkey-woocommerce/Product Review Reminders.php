<div class="pm-loader" style="display: none;"></div>
<div class="push-monkey push-monkey-bootstrap">
	<div class="container-fluid">
		<?php if ( ! $signed_in ) { ?>
			<?php require_once(plugin_dir_path(__DIR__).'settings/parts/log-in.php' );?>
		<?php } else { ?>
			<?php require_once(plugin_dir_path(__DIR__).'settings/parts/header.php' ); 
			?>
			<div class="push-monkey-bootstrap">
				<form method="post" class="form-horizontal" id="review_reminder" enctype="multipart/form-data">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title">Review Reminder Settings</h3>
						</div>
						<div class="panel-body">
							<div class="form-group">
								<label class="col-md-3 control-label">Review Reminder Enabled</label>
								<div class="col-md-3">
									<label class="switch">
										<input type="checkbox" name="active">
										<span class="slider round"></span>
									</label>
									<span class="help-block">Allow your customers to subscribe for review reminder push notifications.</span>
								</div>
							</div>

							<div class="form-group">
								<label class="col-md-3 col-xs-12 control-label">Notification Delay</label>
								<div class="col-md-6 col-xs-12">
									<input type="text" class="form-control" name="notification_delay" >
									<span class="help-block">Number of <strong>minutes</strong> after which the order has been paid.<span>
								</div>
							</div>

							<div class="form-group">
								<label class="col-md-3 col-xs-12 control-label">Notification Title</label>
								<div class="col-md-6 col-xs-12">
									<input type="text"class="form-control" name="notification_title">
									<span class="help-block">You can use the [product-name] keyword as placeholder that will be dynamically replaced with the real value.</span>
								</div>
							</div>

							<div class="form-group">
								<label class="col-md-3 col-xs-12 control-label">Notification Message</label>
								<div class="col-md-6 col-xs-12">
									<input type="text" class="form-control" name="notification_message">
									<span class="help-block">You can use the [product-name] keyword as placeholder that will be dynamically replaced with the real value.</span>
								</div>
							</div>
						</div>
						<div class="panel-footer">
							<button type="submit" class="btn btn-primary pull-right">Save</button>
						</div>
					</div> 
					<?php require_once(plugin_dir_path(__DIR__).'settings/parts/footer.php' ); 
					?>
				<?php } ?>
			</div>
		</div>