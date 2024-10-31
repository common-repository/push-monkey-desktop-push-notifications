<div class="push-monkey push-monkey-bootstrap">
    <div class="container-fluid">
        <?php if ( ! $signed_in ) { ?>
            <?php require_once( plugin_dir_path( __FILE__ ) . '/parts/log-in.php' ); ?>
        <?php } else { ?>
            <?php require_once( plugin_dir_path( __FILE__ ) . '/parts/header.php' ); ?>

              <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Excluded categories</h3>
                </div>
                <div class="panel-body">
                  <p>
                    Standard Posts which have the following categories will not send push notifications when they are posted.
                    By default, all standard posts send push notifications.
                    If standard post types are configured to not send Desktop Push Notifications (from the switches above),
                    category exclusion is disabled.
                  </p>
                </div>
                <div class="panel-body panel-body-table">
                    <form method="post" class="table-responsive">
                      <table class="table table-bordered table-striped table-actions">
                        <thead>
                            <tr>
                                <th>Category Name</th>
                                <th width="120">Exclude from Push Monkey Notifications?</th>
                            </tr>
                        </thead>
                        <tbody>
                       <?php
                          foreach ( $post_types as $key => $post_name ) {
                           $taxonomy_objects = get_object_taxonomies( $key, 'names' );
                           if ( $taxonomy_objects ) { ?>
                            <tr align="center"><th colspan="2"><h4><?php echo ucfirst( $post_name ); ?></h4></th></tr>
                            <?php $terms = get_terms( $taxonomy_objects , 
                              array(
                                'hide_empty' => false,
                                'order' => 'ASC' ) 
                              ); 
                           	foreach ( $terms as $term_value ) { ?>
	                            <tr>
	                              <td><strong><?php echo $term_value->name; ?></strong></td>
	                              <td>
	                                <label class="switch">
	                                    <input type="checkbox" class="switch" name="excluded_categories[]" value="<?php echo $term_value->term_id; ?>" <?php if ( in_array( $term_value->term_id, $options ) ) { echo 'checked="true" '; } ?>>
	                                    <span></span>
	                                </label>
	                              </td>
	                            </tr>
                          	<?php  
                            }
                           }
                          } 
                        ?>
	                        <tr>
	                        	<td colspan="2">
	                        		<button type="submit" name="push_monkey_category_exclusion" class="btn btn-success btn-rounded">Update</button>
	                        	</td>
	                        </tr>
                        </tbody>
                      </table>
                    </form>
                </div>
              </div>

            <?php require_once( plugin_dir_path( __FILE__ ) . '/parts/footer.php' ); ?>
        <?php } ?>
    </div>
</div>