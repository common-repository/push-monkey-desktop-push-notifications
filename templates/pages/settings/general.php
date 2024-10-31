<div class="push-monkey push-monkey-bootstrap">
    <div class="container-fluid">
        <?php if ( ! $signed_in ) { ?>
            <?php require_once( plugin_dir_path( __FILE__ ) . '/parts/log-in.php' ); ?>
        <?php } else { ?>
            <?php require_once( plugin_dir_path( __FILE__ ) . '/parts/header.php' ); ?>

            <form name="push_monkey_main_config" method="post" class="form-horizontal">
              <div class="panel panel-default">
                  <div class="panel-heading">
                      <h3 class="panel-title">General settings</h3>
                  </div>
                  <div class="panel-body">
                      <div class="form-group">
                          <label class="col-md-3 col-xs-12 control-label" for="<?php echo $website_name_key; ?>">Website name</label>
                          <div class="col-md-6 col-xs-12">
                              <div class="input-group">
                                  <span class="input-group-addon"><span class="fa fa-pencil"></span></span>
                                  <input type="text" value="<?php echo $website_name; ?>" name="<?php echo $website_name_key; ?>" id="<?php $website_name_key; ?>" class="form-control"/>
                              </div>
                              <span class="help-block">
                                By default the website name is the same as the setting in Wordpress. Use this
                                if you want to display a different name while sending push notifications.
                              </span>
                          </div>
                      </div>
                      <div class="form-group">
                        <label class="col-md-3 control-label">Send To One Signal Subscribers</label>
                        <div class="col-md-3">
                          <label class="switch">
                            <input type="checkbox" class="switch" name="one_signal_push"<?php checked( 'on', $one_signal_push ); ?>>
                            <span></span>
                          </label>
                          <span class="help-block">By default, this is disabled.</span>
                        </div>
                      </div>
                  </div>
                  <div class="panel-footer">
                      <button type="submit" name="push_monkey_main_config_submit" class="btn btn-primary pull-right">Save</button>
                  </div>
              </div>
            </form>

            <?php require_once( plugin_dir_path( __FILE__ ) . '/parts/footer.php' ); ?>
        <?php } ?>
    </div>
</div>