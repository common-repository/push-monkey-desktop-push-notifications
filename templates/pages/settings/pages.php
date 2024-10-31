<div class="push-monkey push-monkey-bootstrap">
	<div class="container-fluid">
		<?php if ( ! $signed_in ) { ?>
		<?php require_once( plugin_dir_path( __FILE__ ) . '/parts/log-in.php' ); ?>
		<?php } else { ?>
		<?php require_once( plugin_dir_path( __FILE__ ) . '/parts/header.php' ); ?>

		<div class="panel panel-default ">
			<div class="accordion">
				<div class="accordion-group">
					<form method="post" class="table-responsive">
						<div class="panel-heading">
							<h3 class="panel-title">Pages that display the <strong>Permission Dialog</strong></h3>
						</div>
						<div class="panel-body">
							<p>
								The <strong>Permission Dialog</strong> allows your visitors to subscribe to web push notifications. If this dialog does not appear, visitors can not subscribe.
							</p>
							<p>
								This settings page allows you select which of the pages within you WordPress instance display this Permission Dialog.
							</p>
							<p>
								By default, <strong>all</strong> pages display the permission dialog, this means that all the switches from below are turned off (red).
							</p>
						</div>
						<div class="accordion-heading country">
							<table class="table table-bordered table-striped table-actions">
								<thead>
									<tr colspan="2">
										<th><a class="accordion-toggle" data-toggle="collapse" href="#pages"><span class="glyphicon glyphicon-plus"></span> Pages</a></th>
									</tr>
								</thead>
							</table>
						</div>
						<div id="pages" class="accordion-body collapse">
							<div class="accordion-inner">
								<div class="panel-body panel-body-table">
									<table class="table table-bordered table-striped table-actions">
										<thead>
											<tr>
												<th>Page Name</th>
												<th width="120">Show permission dialog?</th>
											</tr>
										</thead>
										<tbody>
											<?php foreach( $all_pages as $all_pages_key => $all_pages_name ) { ?>
											<tr>
												<td><strong><?php echo $all_pages_name->post_title; ?></strong></td>
												<td>
													<label class="switch">
														<input type="checkbox" class="switch" name="included_allow_pages[]" value="<?php echo $all_pages_name->ID; ?>" <?php if ( in_array( $all_pages_name->ID, $set_allow_pages ) ) { echo 'checked="true" '; } ?>/>
														<span></span>
													</label>
												</td>
											</tr>
											<?php }//foreach ?>
											<tr>
												<td colspan="2">
													<button type="submit" name="push_monkey_pages" class="btn btn-success btn-rounded">Update</button>
												</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
						</div>
						<div class="accordion-heading country">
							<table class="table table-bordered table-striped table-actions">
								<thead>
									<tr colspan="2">
										<th><a class="accordion-toggle" data-toggle="collapse" href="#post-type"><span class="glyphicon glyphicon-plus"></span> Post Types</a></th>
									</tr>
								</thead>
							</table>
						</div>
						<div id="post-type" class="accordion-body collapse">
							<div class="accordion-inner">
								<div class="panel-body panel-body-table">
									<table class="table table-bordered table-striped table-actions">
										<thead>
											<tr>
												<th>Post Type Name</th>
												<th width="120">Show permission dialog?</th>
											</tr>
										</thead>
										<tbody>
											<?php foreach( $pages_post_type as $post_key => $page_post_name ) { ?>
											<tr>
												<?php $page_post_name = $post_key == "post" ? $page_post_name : $page_post_name->name; ?>
												<td><strong><?php echo $page_post_name; ?></strong></td>
												<td>
													<label class="switch">
														<input type="checkbox" class="switch" name="included_allow_pages[]" value="<?php echo $post_key; ?>" <?php if ( in_array( $post_key, $set_allow_pages ) ) { echo 'checked="true" '; } ?>/>
														<span></span>
													</label>
												</td>
											</tr>
											<?php }//foreach ?>
											<tr>
												<td colspan="2">
													<button type="submit" name="push_monkey_pages" class="btn btn-success btn-rounded">Update</button>
												</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
						</div>
						<div class="accordion-heading country">
							<table class="table table-bordered table-striped table-actions">
								<thead>
									<tr colspan="2">
										<th><a class="accordion-toggle" data-toggle="collapse" href="#taxonomies"><span class="glyphicon glyphicon-plus"></span> Taxonomies</a></th>
									</tr>
								</thead>
							</table>
						</div>
						<div id="taxonomies" class="accordion-body collapse">
							<div class="accordion-inner">
								<div class="panel-body panel-body-table">
									<table class="table table-bordered table-striped table-actions">
										<thead>
											<tr>
												<th>Taxonomie Name</th>
												<th width="120">Show permission dialog?</th>
											</tr>
										</thead>
										<tbody>
											<?php foreach( $pages_taxonomies as $taxonomies_key => $taxonomies_name ) { ?>
											<tr>
												<?php $taxonomies_name = $taxonomies_key == "category" ? $taxonomies_name : $taxonomies_name->labels->name; ?>
												<td><strong><?php echo $taxonomies_name; ?></strong></td>
												<td>
													<label class="switch">
														<input type="checkbox" class="switch" name="included_allow_pages[]" value="<?php echo $taxonomies_key; ?>" <?php if ( in_array( $taxonomies_key, $set_allow_pages ) ) { echo 'checked="true" '; } ?>/>
														<span></span>
													</label>
												</td>
											</tr>
											<?php }//foreach ?>
											<tr>
												<td colspan="2">
													<button type="submit" name="push_monkey_pages" class="btn btn-success btn-rounded">Update</button>
												</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
		<?php require_once( plugin_dir_path( __FILE__ ) . '/parts/footer.php' ); ?>
		<?php } ?>
	</div>
</div>