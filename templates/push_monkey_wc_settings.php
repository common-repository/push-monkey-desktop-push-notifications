<div class="push-monkey">
  <div class="container-fluid">
    <?php if ( ! $signed_in ) { ?>

      <div class="row header">
        <div class="col-md-12">
          <img src="<?php echo plugins_url( 'img/push-monkey-logo-small.png', dirname( __FILE__ ) ); ?>" alt="Push Monkey Logo" />
        </div><!-- .col --> 
      </div><!-- .row --> 
      <div class="row content">
        <div id="sign-in-up-carousel" class="carousel slide" data-ride="carousel" data-interval="false">  
          <div class="carousel-inner" role="listbox">
            <div class="item <?php if( ! $sign_up ) { echo 'active'; } ?>">
              <div class="col-md-4 col-md-offset-2">
                <div class="login-box">
                  <form method="POST" action="">
                    <div class="row text-center">
                      <?php if ( isset( $sign_in_error ) ) { ?>
                      <div class="col-md-10 col-md-offset-1 text-left">
                        <p class="bg-danger"><?php echo $sign_in_error; ?></p>
                      </div><!-- .col -->
                      <?php } ?>
                      <div class="col-md-10 col-md-offset-1 text-left">
                        <label class="first">E-mail</label>
                        <input type="text" class="form-control" name="username">
                      </div><!-- .col -->
                      <div class="col-md-10 col-md-offset-1 text-left">
                        <label>Password</label>
                        <input type="password" class="form-control section-start" name="password">
                      </div><!-- .col -->
                      <input type="submit" value="Sign In" class="btn btn-lg btn-success" name="push_monkey_sign_in">
                      <br /> 
                      <a class="btn btn-primary" href="<?php echo $forgot_password_url; ?>">Forgot<br /> Password?</a>
                    </div><!-- .row -->
                  </form>
                </div><!-- .login-box -->
              </div><!-- .col --> 
              <div class="col-md-3">
                <div class="text-center new-account-box">
                  <p>Don't have an account yet? Sign up now to start sending Desktop Push Notifications</p>
                  <a class="btn btn-lg btn-success" href="#sign-in-up-carousel" role="button" data-slide="next">Sign Up</a>
                </div>
              </div><!-- .col -->
            </div><!-- .item -->
            <div class="item <?php if( $sign_up ) { echo 'active'; } ?>">
              <div class="col-md-4 col-md-offset-<?php if($is_subscription_version) { echo '2'; } else { echo '3'; } ?>">
                <div class="login-box">
                  <form method="GET" action="<?php echo $register_url; ?>">
                    <div class="row text-center">
                      <div class="col-md-10 col-md-offset-1 text-left">
                        <label class="first">First Name</label>
                        <input type="text" class="form-control" name="first_name">
                      </div><!-- .col -->
                      <div class="col-md-10 col-md-offset-1 text-left">
                        <label>E-mail</label>
                        <input type="text" class="form-control section-start" name="email">
                      </div><!-- .col -->
                      <input type="hidden" value="<?php echo $return_url; ?>" name="returnURL"  />
                      <input type="hidden" value="<?php echo $website_name; ?>" name="websiteName"  />
                      <input type="hidden" value="<?php echo $website_url; ?>" name="websiteURL"  />
                      <input type="hidden" value="1" name="wordpress"  />                   
                      <input type="hidden" value="1" name="registering"  />
                      <input type="submit" value="Sign Up" class="btn btn-lg btn-success" name="submit">
                      <br /> 
                      <a class="btn btn-primary" href="#sign-in-up-carousel" role="button" data-slide="prev">Already have <br /> an account?</a>
                    </div><!-- .row -->
                  </form>
                </div><!-- .login-box -->
              </div><!-- .col --> 
              <?php if ( $is_subscription_version ) { ?>
              <div class="col-md-3">
                  <br /><br /><br /><br /> <br /> <br />
                  <img src="<?php echo $img_free_trial_src; ?>"/>
              </div><!-- .col -->
              <?php } ?>
            </div><!-- .item -->
          </div><!-- .carousel-inner -->
        </div><!-- .carousel -->
      </div><!-- .row -->
      <div class="row content hidden">
      </div><!-- .row -->
      <div class="row footer">
        <div class="col-md-3 text-center">
          <img src="<?php echo $img_notifs_src; ?>" class="" />
          <p>Send website notifications directly to the desktop when new content 
            is fresh from the oven.</p>
        </div><!-- .col -->
        <div class="col-md-3 col-md-offset-1 text-center">
          <img src="<?php echo $img_stats_src; ?>" class="" />
          <p>Beautiful and easy-to-understand statistics for the Push Monkey 
            performance are available directly in your Wordpress Dashboard</p>
        </div><!-- .col -->
        <div class="col-md-3 col-md-offset-1 text-center">
          <img src="<?php echo $img_filter_src; ?>" class="" />
          <p>With <strong>Granular Filtering</strong> you can select which post 
            categories don't send push notifications, as easy as you can say 
            ba-na-na. No spam around here!</p>
        </div><!-- .col -->
      </div><!-- .row -->
    <?php } else { ?> <!-- if !$signed_in -->

      <div class="row header">
        <div class="col-md-6">
          <img src="<?php echo plugins_url( 'img/push-monkey-logo-small.png', dirname( __FILE__ ) ); ?>" alt="Push Monkey Logo" />
        </div><!-- .col --> 
        <div class="col-md-4 text-right">
          <span><?php echo $email; ?></span> 
          <a href="<?php echo $logout_url; ?>">Sign Out</a>
          <?php if ( $plan_name ) { ?>
          <br /><br />
          <div>
            <p>You're rocking the <strong><?php echo $plan_name; ?></strong> plan.</p>
            <?php if ( $plan_can_upgrade ) { ?>
            <a class="btn btn-success btn-xs" href="<?php echo $upgrade_url; ?>" target="_blank">Upgrade Now?</a> 
            <?php } ?>
          </div>
          <?php } else if ( $plan_expired ) { ?>
          <br /><br />
          <div> 
            <p class="text-danger">Your plan expired.</p>
            <?php if ( $plan_can_upgrade ) { ?>
            <a class="btn btn-danger btn-xs" href="<?php echo $upgrade_url; ?>" target="_blank">Upgrade Now</a> 
            <?php } ?>
          </div>
          <?php } ?>
        </div><!-- .col -->
      </div><!-- .row --> 
      <?php if( $registered ) { ?>
      <div class="row">
        <div class="col-md-10 text-left">
          <p class="bg-success">Welcome to Push Monkey! May your push notifications be merry and your readers happy!</p>
          <br /><br />
        </div><!-- .col -->
      </div><!-- .row -->
      <?php } ?>
      <div class="row stats-container">
        <div class="col-md-2">
          <div class="number-box box-green">
            <p class="box-label">Sent Notifications</p>
            <p class="box-value">
              <?php echo $output->sent_notifications; ?>
            </p>
          </div><!-- .number-box -->
          <div class="number-box box-light-orange">
            <p class="box-label">Subscribers</p>
            <p class="box-value">
              <?php echo $output->subscribers; ?>
            </p>
          </div><!-- .number-box -->
          <div class="number-box box-orange">
            <p class="box-label">Posts</p>
            <p class="box-value">
              <?php echo $output->notifications; ?>
            </p>
          </div><!-- .number-box -->
        </div><!-- .col -->
        <div class="col-md-3">
          <div class="doughnut-chart-wrapper">
            <canvas id="doughnut-chart"></canvas>
            <div class="legend-dataset-two"> Sent Notifications </div>
            <div class="legend-dataset-three"> Remaining Notifications </div>
          </div>
        </div><!-- .col -->
        <div class="col-md-5">
          <canvas id="chart"></canvas>
          <div class="col col-chart-legend">
            <div class="legend-dataset-two"> Sent Notifications </div>
          </div>
          <div class="col col-chart-legend">
            <div class="legend-dataset-one"> Opened Notifications </div>
          </div>
        </div><!-- .col -->
      </div><!-- .row -->

<?php } ?>
