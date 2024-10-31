<div class="push-monkey push-monkey-bootstrap">
    <div class="container-fluid">
        <?php if ( ! $signed_in ) { ?>
            <?php require_once( plugin_dir_path( __FILE__ ) . '/parts/log-in.php' ); ?>
        <?php } else { ?>
            <?php require_once( plugin_dir_path( __FILE__ ) . '/parts/header.php' ); ?>


            <form method="post" class="form-horizontal">
              <div class="panel panel-default">
                  <div class="panel-heading">
                      <h3 class="panel-title">Subdomain for HTTP</h3>
                  </div>
                  <div class="panel-body">
                      <p>If your website is not accessed over HTTPS, a subdomain is required.</p>
                  </div>

                  <div class="form-group" <?php if ($has_https) { ?> style="opacity: 0.5" <?php } ?>>
                    <label class="col-md-3 control-label">Use HTTP</label>
                    <div class="col-md-3">
                        <label class="switch">
                            <input type="checkbox" class="switch" name="push_monkey_subdomain_forced" <?php if ( $domain_forced && $has_https ) {?> checked="true" <?php } ?>
                            <?php if ($has_https) { ?> disabled <?php } ?>>
                            <span></span>
                        </label>
                        <span class="help-block">By default, this is enabled.</span>
                    </div>
                  </div>
                  <?php if ($has_https) { ?>
                    <div class="form-group"> 
                      <div class="col-md-offset-3 col-md-6">
                        <p class="text-danger">This option can not be disabled until you install a SSL Certificate so you domain can be securly accessed via HTTPS. <a href="http://intercom.help/push-monkey/faq/http-vs-https" target="_blank">More info about this &#8594;</a>
                        </p>                          
                      </div>
                    </div>
                  <?php } ?>                  
                  <div class="panel-footer">
                      <button type="submit" name="push_monkey_subdomain_settings" class="btn btn-primary pull-right">Save</button>
                  </div>
              </div>
            </form>

            <?php require_once( plugin_dir_path( __FILE__ ) . '/parts/footer.php' ); ?>
        <?php } ?>
    </div>
</div>