<div class="header">
  <div class="header-row">
    <img src="<?php echo $pluginPath . 'img/logo@2x.png' ?>" alt="Push Monkey Logo" class="header-logo" />

    <div class="header-box text-right">
      <p>
        <?php echo $email; ?>
      </p>

      <div class="header-info">
        <?php if ( $plan_name ) { ?>
          <p>You're rocking the <strong><?php echo $plan_name; ?></strong> plan.</p>
          <?php if ( $plan_can_upgrade ) { ?>
          <a class="btn btn-success" href="<?php echo $upgrade_url; ?>" target="_blank">Upgrade Now?</a>
          <?php } ?>
        <?php } else if ( $plan_expired ) { ?>
          <p class="text-danger">Your plan expired.</p>
          <?php if ( $plan_can_upgrade ) { ?>
          <a class="btn btn-danger" href="<?php echo $upgrade_url; ?>" target="_blank">Upgrade Now</a>
          <?php } ?>
        <?php } ?>
        <a class="btn btn-default" href="<?php echo $logout_url; ?>">Sign Out</a>
      </div>
    </div>
  </div>

  <?php if ( $registered ) { ?>
    <div class="header-row">
      <div class="alert alert-success" role="alert">
        <p>Welcome to Push Monkey! May your push notifications be merry and your readers happy!</p>
      </div>
    </div>
  <?php } ?>

  <?php if ( $notice ) { ?>
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-8 col-md-offset-2">
        <div class="panel panel-default">
          <div class="panel-body">
            <br>
            <?php $notice->render(); ?>
          </div>
        </div>
      </div><!-- .col -->
    </div><!-- .row -->
  </div>
  <?php } ?>

</div>