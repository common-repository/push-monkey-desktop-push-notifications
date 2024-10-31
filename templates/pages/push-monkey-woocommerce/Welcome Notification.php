<div class="pm-loader" style="display: none;"></div>
<div class="push-monkey push-monkey-bootstrap">
	<div class="container-fluid">
		<?php if ( ! $signed_in ) { ?>
			<?php require_once(plugin_dir_path(__DIR__).'settings/parts/log-in.php' );?>
		<?php } else { ?>
			<?php require_once(plugin_dir_path(__DIR__).'settings/parts/header.php' ); 
			?>
			<div class="push-monkey-bootstrap">
				<form class="push_monkey_woo_settings form-horizontal" id="welcome_notification" enctype="multipart/form-data" method="post" class="form-horizontal">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title">Welcome Discount</h3>
						</div>
						<div class="panel-body">
							<div class="form-group">
								<label class="col-md-3 control-label">Welcome Notification Active</label>
								<div class="col-md-3">
									<label class="switch">
										<input type="checkbox" name="active">
										<span class="slider round"></span>
									</label>
									<span class="help-block">By default, this is enabled.</span>
								</div>
							</div>

							<div class="form-group">
								<label class="col-md-3 col-xs-12 control-label">Custom Message</label>
								<div class="col-md-6 col-xs-12">
									<input type="text" class="form-control" name="custom_message">
								</div>
							</div>

							<div class="form-group">
								<label class="col-md-3 col-xs-12 control-label">Welcome Link</label>
								<div class="col-md-6 col-xs-12">
									<input type="text"class="form-control"name="welcome_link">
								</div>
							</div>

							<div class="form-group">
								<label class="col-md-3 col-xs-12 control-label">Welcome Image</label>
								<div class="col-md-6 col-xs-12">
									<input type="file" class="fileinput btn-primary" name="welcome_image">
									<span class="help-block">The welcome message can include an image. Recommended size 675x506 pixels.</span>
								</div>
							</div>
							<div class="form-group" id="preview_image" style="display: none;">
								<label class="col-md-3 col-xs-12 control-label">Current Image</label>
								<div class="col-md-6 col-xs-12">
									<img class="img-responsive back-in-stock-label-preview back-in-stock-label-blue" src="">
								</div>
							</div>
						</div>
						<div class="panel-footer">
							<button type="submit" class="btn btn-primary pull-right">Save</button>
						</div>
					</div> 
				</form>
			</div>
		<?php require_once(plugin_dir_path(__DIR__).'settings/parts/footer.php' ); 
		?>
	<?php } ?>
</div>
</div>