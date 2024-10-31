
<div class="row">
  <div class="col-md-4 col-md-offset-1">
    <!-- START PRIMARY PANEL -->
    <div class="panel panel-primary">
      <div class="panel-heading">
        <h3 class="panel-title">Mobile and Desktop Push Notifications</h3>
      </div>
      <div class="panel-body">
        <p><img style="float: left; margin: 2rem;" src="<?php echo $pluginPath . 'img/illustrations/64_smart phone computer screen.png'; ?>" />Automatically send push notifications directly to desktops or mobiles when new content is fresh from the oven.</p>
        <p>You would like a more personal touch? Sending custom notifications is just as easy.</p>
      </div>
    </div>
    <!-- END PRIMARY PANEL -->
    <!-- START SUCCESS PANEL -->
    <div class="panel panel-success">
      <div class="panel-heading">
        <h3 class="panel-title">Subscribers Insigths &amp; Statistics</h3>
      </div>
      <div class="panel-body">
        <p><img style="float: left; margin: 2rem;" src="<?php echo $pluginPath . 'img/illustrations/64_chart document.png'; ?>" />Relevant and powerful insights about your subscribers and their behaviour, right in your WordPress Dashboard.</p>
        <p>Easily track growth, platform and location.</p>
      </div>
    </div>
    <!-- END SUCCESS PANEL -->
    <!-- START WARNING PANEL -->
    <div class="panel panel-warning">
      <div class="panel-heading">
        <h3 class="panel-title">Best WordPress Integration</h3>
      </div>
      <div class="panel-body">
        <p><img style="float: left; margin: 2rem;" src="<?php echo $pluginPath . 'img/illustrations/64_resize.png'; ?>" />A deep integration with your entire WordPress setup ensure that push notifications are sent according to the content you publish.</p>
      </div>
    </div>
    <!-- END WARNING PANEL -->

  </div><!-- .col -->
  <div class="col-md-6">

    <div class="login-container login-v2">
      <div class="login-box animated fadeInDown">
        <!-- START JUSTIFIED TABS -->
        <div class="panel panel-default tabs">
          <ul class="nav nav-tabs nav-justified">
            <li class=" <?php if ( $sign_up ) { echo 'active'; } ?>"><a href="#tab8" data-toggle="tab" aria-expanded="true">Sign Up</a></li>
            <li class=" <?php if ( !$sign_up ) { echo 'active'; } ?>">
              <a href="#tab9" data-toggle="tab" aria-expanded="false">
                Have an account
                <span class="label label-warning">?</span>
              </a>
            </li>
          </ul>
          <div class="panel-body tab-content">
            <div class="tab-pane <?php if ( $sign_up ) { echo 'active'; } ?>" id="tab8">
              <div class="login-body">
                <div class="login-title"><strong>Welcome</strong>, Please sign up.</div>
                <form method="GET" action="<?php echo $register_url; ?>" class="form-horizontal" >
                  <input type="hidden" value="<?php echo $return_url; ?>" name="returnURL" />
                  <input type="hidden" value="<?php echo $website_name; ?>" name="websiteName" />
                  <input type="hidden" value="<?php echo $website_url; ?>" name="websiteURL" />
                  <input type="hidden" value="1" name="wordpress" />
                  <input type="hidden" value="1" name="registering" />
                  <div class="form-group">
                    <div class="col-md-12">
                      <div class="input-group">
                        <div class="input-group-addon">
                          <span class="fa fa-user"></span>
                        </div>
                        <input type="text" name="first_name" class="form-control" placeholder="First name" />
                      </div>
                    </div>
                  </div>
                  <div class="form-group">
                    <div class="col-md-12">
                      <div class="input-group">
                        <div class="input-group-addon">
                          <span class="fa fa-lock"></span>
                        </div>
                        <input type="email" name="email" class="form-control" placeholder="E-mail" />
                      </div>
                    </div>
                  </div>
                  <div class="form-group">
                    <div class="col-md-6">
                      <a href="javascript:;" id="have_an_account">Already have an account?</a>
                    </div>
                  </div>
                  <div class="form-group">
                    <div class="col-md-12">
                      <button class="btn btn-primary btn-lg btn-block" type="submit" name="submit">Sign Up</button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
            <div class="tab-pane <?php if ( !$sign_up ) { echo 'active'; } ?>" id="tab9">
              <div class="login-body">
                <div class="login-title"><strong>Welcome</strong>, Please login.</div>
                <form action="" class="form-horizontal" method="post">
                <div class="form-group">
                  <?php if ( isset( $sign_in_error ) ) { ?>
                  <div class="col-md-12">
                    <div class="alert alert-danger" role="alert">
                        <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
                        <?php echo $sign_in_error; ?>
                    </div>
                  </div>
                  <!-- .col -->
                  <?php } ?>
                  <div class="col-md-12">
                    <div class="input-group">
                      <div class="input-group-addon">
                        <span class="fa fa-user"></span>
                      </div>
                      <input type="text" name="username" class="form-control" placeholder="E-mail" />
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <div class="col-md-12">
                    <div class="input-group">
                      <div class="input-group-addon">
                        <span class="fa fa-lock"></span>
                      </div>
                      <input type="password" name="password" class="form-control" placeholder="Password" />
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <div class="col-md-6">
                    <a href="<?php echo $forgot_password_url; ?>">Forgot your password?</a>
                  </div>
                </div>
                <div class="form-group">
                  <div class="col-md-12">
                    <button type="submit" name="push_monkey_sign_in" class="btn btn-primary btn-lg btn-block">Sign In</button>
                  </div>
                </div>
                </form>
              </div>
            </div>
          </div>
        </div>
        <!-- END JUSTIFIED TABS -->
      </div>
    </div>

  </div>
</div>
