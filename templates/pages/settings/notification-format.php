<div class="push-monkey push-monkey-bootstrap">
    <div class="container-fluid">
        <?php if ( ! $signed_in ) { ?>
            <?php require_once( plugin_dir_path( __FILE__ ) . '/parts/log-in.php' ); ?>
        <?php } else { ?>
            <?php require_once( plugin_dir_path( __FILE__ ) . '/parts/header.php' ); ?>

            <form method="post">
              <div class="panel panel-default">
                  <div class="panel-heading">
                      <h3 class="panel-title">Notifications Format</h3>
                  </div>
                  <div class="panel-body">
                      <p>You can customise how your readers see the notifications they receive, currently in 2 formats. Choose one of the options bellow.</p>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                      <div class="col-md-4">
                        <label for="radio-standard" class="selection-box-wrapper">
                          <input type="radio" name="push_monkey_notification_format" value="standard" class="" id="radio-standard" <?php if ( ! $notification_is_custom ) { echo 'checked="checked"'; }?> />
                          <div class="selection-box">
                            <div class="selection-inner">
                              <img src="<?php echo $notification_format_image; ?>" class="img-responsive"/>
                              <div class="notification">
                                <img class="icon" src="<?php echo $this->endpointURL; ?>/clients/icon/<?php echo $account_key; ?>" />
                                <p>
                                  <strong>Fairly short post title</strong>
                                  <br />
                                  <span>Post body that would fit inside the notification</span>
                                </p>
                              </div>
                            </div>
                            <div class="checkmark">
                              <span class="glyphicon glyphicon-ok"></span>
                            </div>
                          </div>
                        </label>
                      </div><!-- .col -->
                      <div class="col-md-4">
                        <label for="radio-static-title" class="selection-box-wrapper">
                          <input type="radio" name="push_monkey_notification_format" value="static-title" class="" id="radio-static-title" <?php if ( $notification_is_custom ) { echo 'checked="checked"'; }?> />
                          <div class="selection-box">
                            <div class="selection-inner">
                              <img src="<?php echo $notification_format_image; ?>" class="img-responsive"/>
                              <div class="notification">
                                <img class="icon" src="<?php echo $this->endpointURL; ?>/clients/icon/<?php echo $account_key; ?>" />
                                <p>
                                  <strong id="push_monkey_preview_title"><?php echo $notification_custom_text; ?></strong>
                                  <br />
                                  <span id="push_monkey_preview_content">Longer post title that fits this are better</span>
                                </p>
                              </div>
                            </div>
                            <div class="checkmark">
                              <span class="glyphicon glyphicon-ok"></span>
                            </div>
                          </div>
                        </label>
                      </div><!-- .col -->
                    </div><!-- .row -->

                    <div class="row form-group">
                      <div class="col-md-4 col-md-offset-4">
                        <div class="form-group">
                            <label for="custom-text">Custom Text</label>
                            <input name="custom-text" id="custom-text" class="form-control" <?php if ( ! $notification_is_custom ) { echo 'disabled'; }?> value="<?php echo $notification_custom_text; ?>"/>
                        </div>
                      </div><!-- .col -->
                    </div><!-- .row -->
                  </div>
                  <div class="panel-footer">
                      <button type="submit" name="push_monkey_notification_config" class="btn btn-primary pull-right">Update</button>
                  </div>
              </div>
            </form>

            <?php require_once( plugin_dir_path( __FILE__ ) . '/parts/footer.php' ); ?>
        <?php } ?>
    </div>
</div>