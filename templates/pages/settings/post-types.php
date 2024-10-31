<div class="push-monkey push-monkey-bootstrap">
    <div class="container-fluid">
        <?php if ( ! $signed_in ) { ?>
            <?php require_once( plugin_dir_path( __FILE__ ) . '/parts/log-in.php' ); ?>
        <?php } else { ?>
            <?php require_once( plugin_dir_path( __FILE__ ) . '/parts/header.php' ); ?>

              <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Post types that send Push Notifications</h3>
                </div>
                <div class="panel-body">
                  <p>
                    By default, all post types send web push notifications.
                  </p>
                </div>
                <div class="panel-body panel-body-table">
                    <form method="post" class="table-responsive">
                      <table class="table table-bordered table-striped table-actions">
                        <thead>
                            <tr>
                                <th>Post Type</th>
                                <th width="120">Send Push Notification?</th>
                            </tr>
                        </thead>
                        <tbody>
                      <?php foreach( $post_types as $post_type => $post_type_name ) { ?>
                            <tr>
                                <td><strong><?php echo $post_type_name; ?></td>
                                <td>
                                  <label class="switch">
                                      <input type="checkbox" class="switch" name="included_post_types[]" value="<?php echo $post_type; ?>" <?php if ( array_key_exists( $post_type, $set_post_types ) ) { echo 'checked="true" '; } ?>/>
                                      <span></span>
                                  </label>
                                </td>
                            </tr>
                      <?php }//foreach ?>
                            <tr>
                                <td colspan="2">
                                    <button type="submit" name="push_monkey_post_type_inclusion" class="btn btn-success btn-rounded">Update</button>
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