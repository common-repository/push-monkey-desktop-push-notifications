<div class="push-monkey push-monkey-bootstrap">
    <div class="container-fluid">
        <?php if ( ! $signed_in ) { ?>
            <?php require_once( plugin_dir_path( __FILE__ ) . '/parts/log-in.php' ); ?>
        <?php } else { ?>
            <?php require_once( plugin_dir_path( __FILE__ ) . '/parts/header.php' ); ?>

              <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Segments</h3>
                </div>
                <div class="panel-body">
                  <p>
                    By default, the notifications are sent to all subscribers.
                  </p>
                </div>
                <div class="panel-body panel-body-table">
                    <form method="post" class="table-responsive">
                      <table class="table table-bordered table-striped table-actions">
                        <thead>
                            <tr>
                                <th>Segment Name</th>
                                <th width="100">Subscribers</th>
                                <th width="120">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach( $segments as $segment ) { ?>
                          <?php foreach ( $segment as $k => $v ) { ?>
                            <tr id="trow_1">
                                <td><strong><?php echo $v[0]; ?></strong></td>
                                <td><?php echo $v[1]; ?></td>
                                <td>
                                    <a href="<?php echo $segment_delete_url . $k; ?>" class="btn btn-danger btn-rounded btn-condensed btn-sm"><span class="fa fa-times"></span></a>
                                </td>
                            </tr>
                          <?php } ?>
                        <?php } ?>
                            <tr>
                                <td>
                                  <div class="form-group">
                                      <input type="text" class="form-control" name="push_monkey_new_segment" placeholder ="Please enter the name of a new segment">
                                  </div>
                                </td>
                                <td colspan="2">
                                    <button type="submit" name="push_monkey_add_segment" class="btn btn-success btn-rounded btn-block">Add</button>
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