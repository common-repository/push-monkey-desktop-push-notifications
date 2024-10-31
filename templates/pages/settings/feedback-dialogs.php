<div class="push-monkey push-monkey-bootstrap">
    <div class="container-fluid">
        <?php if ( ! $signed_in ) { ?>
            <?php require_once( plugin_dir_path( __FILE__ ) . '/parts/log-in.php' ); ?>
        <?php } else { ?>
            <?php require_once( plugin_dir_path( __FILE__ ) . '/parts/header.php' ); ?>

            <form method="post" class="form-horizontal">
              <div class="panel panel-default">
                  <div class="panel-heading">
                      <h3 class="panel-title">Welcome Notification</h3>
                  </div>
                  <div class="panel-body">
                      <p>After readers subscribe to your website, they will receive a welcome notification.</p>
                  </div>
                  <div class="panel-body">
                    <div class="form-group">
                      <label class="col-md-3 control-label">Enabled</label>
                      <div class="col-md-3">
                        <label class="switch">
                          <input type="checkbox" class="switch" name="push_monkey_welcome_notification_enabled" <?php if ( $welcome_status_enabled === true ) {?> checked="true" <?php } ?>>
                          <span></span>
                        </label>
                        <span class="help-block">By default, this is enabled.</span>
                      </div>
                    </div>

                    <div class="form-group">
                      <label class="col-md-3 col-xs-12 control-label">Message</label>
                      <div class="col-md-6 col-xs-12">
                        <div class="input-group">
                          <span class="input-group-addon"><span class="fa fa-pencil"></span></span>
                            <input type="text" value="<?php echo $welcome_status_message; ?>" name="push_monkey_welcome_notification_message" class="form-control"/>
                          </div>
                          <span class="help-block">
                            The message inside the notification.
                          </span>
                        </div>
                    </div>
                  </div>
                  <div class="panel-footer">
                      <button type="submit" name="push_monkey_welcome_notification" class="btn btn-primary pull-right">Update</button>
                  </div>
              </div>

              <div class="panel panel-default">
                  <div class="panel-heading">
                      <h3 class="panel-title">Permission Dialog</h3>
                  </div>
                  <div class="panel-body">
                      <p>Appears before the default browser permission prompt. It is recommended to provide a better experience for your readers.</p>
                  </div>
                  <div class="panel-body">
                    <div class="form-group" <?php if ($domain_forced) { ?> style="opacity: 0.5"<?php } ?>>
                      <label class="col-md-3 control-label">Enabled</label>
                      <div class="col-md-3">
                        <label class="switch">
                          <input type="checkbox" class="switch" name="push_monkey_custom_prompt_enabled" <?php if ( $custom_prompt_enabled === true ) {?> checked="true" <?php } ?> 
                           <?php if ($domain_forced) { ?>disabled<?php } ?>>
                          <span></span>
                        </label>
                        <span class="help-block">By default, this is enabled.</span>
                      </div>
                    </div>
                    <?php if ($domain_forced) { ?>
                      <div class="form-group"> 
                        <div class="col-md-offset-3 col-md-6">
                          <p class="text-danger">This option can not be disabled while HTTP is turned on in the <strong>HTTP/HTTPS Settings</strong> page from the menu on the left. <a href="http://intercom.help/push-monkey/faq/http-vs-https" target="_blank">More info about this &#8594;</a>
                          </p>                          
                        </div>
                      </div>
                    <?php } ?>

                    <div class="form-group">
                      <label class="col-md-3 col-xs-12 control-label">Title</label>
                      <div class="col-md-6 col-xs-12">
                        <div class="input-group">
                          <span class="input-group-addon"><span class="fa fa-pencil"></span></span>
                            <input type="text" value="<?php echo $custom_prompt_title; ?>" name="push_monkey_custom_prompt_title" class="form-control"/>
                          </div>
                          <span class="help-block">
                            The title that appears in bold. Can be blank
                          </span>
                        </div>
                    </div>

                    <div class="form-group">
                      <label class="col-md-3 col-xs-12 control-label">Message</label>
                      <div class="col-md-6 col-xs-12">
                        <div class="input-group">
                          <span class="input-group-addon"><span class="fa fa-pencil"></span></span>
                            <input type="text" value="<?php echo $custom_prompt_message; ?>" name="push_monkey_custom_prompt_message" class="form-control"/>
                          </div>
                          <span class="help-block">
                            The message that appears under the title. Can be blank
                          </span>
                        </div>
                    </div>
                  </div>
                  <div class="panel-footer">
                      <button type="submit" name="push_monkey_custom_prompt" class="btn btn-primary pull-right">Update</button>
                  </div>
              </div>
            </form>

            <form method="post" class="form-horizontal">
              <div class="panel panel-default">
                  <div class="panel-heading">
                      <h3 class="panel-title">Segments Dialog</h3>
                  </div>
                  <div class="panel-body">
                    <p>
                        When readers subscribe to your website, a dialog will appear to show them the available segments.
                      <a href="http://www.getpushmonkey.com/help?source=plugin#gpm15" target="_blank">More info about this &#8594;</a>
                    </p>
                  </div>
                  <div class="panel-body">
                    <div class="form-group">
                      <label class="col-md-3 col-xs-12 control-label">Dialog Background Color</label>
                      <div class="col-md-6 col-xs-12">
                        <div class="input-group color" data-color="<?php echo $banner_color; ?>" id="colorpicker">
                            <input type="text" name="push_monkey_banner_color" value="<?php echo $banner_color; ?>" class="form-control"/>
                            <span class="input-group-addon"><i style="background-color: <?php echo $banner_color; ?>"></i></span>
                        </div>
                        <span class="help-block">
                          Default value is #2fcc70.
                        </span>
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="col-md-3 col-xs-12 control-label">Dialog Button Color</label>
                      <div class="col-md-6 col-xs-12">
                        <div class="input-group color" data-color="<?php echo $banner_subscribe_color; ?>" id="colorpicker2">
                            <input type="text" name="push_monkey_subscribe_color" value="<?php echo $banner_subscribe_color; ?>" class="form-control"/>
                            <span class="input-group-addon"><i style="background-color: <?php echo $banner_subscribe_color; ?>"></i></span>
                        </div>
                        <span class="help-block">
                          Default value is #2fcc70.
                        </span>
                      </div>
                    </div>
                  </div>
                  <div class="panel-footer">
                      <button type="submit" name="push_monkey_banner" class="btn btn-primary pull-right">Update</button>
                  </div>
              </div>
            </form>

            <?php require_once( plugin_dir_path( __FILE__ ) . '/parts/footer.php' ); ?>
        <?php } ?>
    </div>
</div>