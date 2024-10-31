<div class="push-monkey push-monkey-bootstrap">
  <div class="container-fluid">
    <?php if ( ! $signed_in ) { ?>
      <?php require_once( plugin_dir_path( __FILE__ ) . '/parts/log-in.php' ); ?>
    <?php } else { ?>
      <?php require_once( plugin_dir_path( __FILE__ ) . '/parts/header.php' ); ?>

          <!-- START WIDGETS -->
          <div class="row">
            <div class="col-md-3">

              <!-- START WIDGET SUBSCRIBERS -->
              <div class="widget widget-success widget-item-icon">
                <div class="widget-item-left">
                  <span class="fa fa-group"></span>
                </div>
                <div class="widget-data">
                  <div class="widget-int num-count"><?php echo $output->subscribers; ?></div>
                  <div class="widget-title">Subscribers</div>
                  <div class="widget-subtitle">out of <?php echo $output->total_subscribers;?></div>
                </div>
              </div>
              <!-- END WIDGET SUBSCRIBERS -->
            </div>
            <div class="col-md-3">

              <!-- START WIDGET SUBSCRIBERS YESTERDAY -->
              <div class="widget widget-default widget-item-icon">
                <div class="widget-item-left">
                  <span class="fa fa-angle-double-left"></span>
                </div>
                <div class="widget-data">
                  <div class="widget-int num-count"><?php echo $output->subscribers_yesterday; ?></div>
                  <div class="widget-title">subscribers</div>
                  <div class="widget-subtitle">were new yesterday</div>
                </div>
                <div class="widget-controls">
                </div>
              </div>
              <!-- END WIDGET SUBSCRIBERS YESTERDAY -->

            </div>
            <div class="col-md-3">

              <!-- START WIDGET SUBSCRIBERS TODAY -->
              <div class="widget widget-default widget-item-icon">
                <div class="widget-item-left">
                  <span class="fa fa-calendar"></span>
                </div>
                <div class="widget-data">
                  <div class="widget-int num-count"><?php echo $output->subscribers_today; ?></div>
                  <div class="widget-title">Subscribers</div>
                  <div class="widget-subtitle">are new today</div>
                </div>
                <div class="widget-controls">
                </div>
              </div>
              <!-- END WIDGET SUBSCRIBERS TODAY -->

            </div>
            <div class="col-md-3">

              <!-- START WIDGET MESSAGES -->
              <div class="widget widget-default widget-item-icon">
                <div class="widget-item-left">
                  <span class="fa  fa-check-square"></span>
                </div>
                <div class="widget-data">
                  <div class="widget-int num-count"><?php echo $output->sent_notifications; ?></div>
                  <div class="widget-title">Notifications</div>
                  <div class="widget-subtitle">sent this month (including "Welcome Notifications")</div>
                </div>
                <div class="widget-controls">
                </div>
              </div>
              <!-- END WIDGET MESSAGES -->

            </div>
          </div>
          <!-- END WIDGETS -->

      <div class="row">
        <div class="col-md-5">

          <!-- START SENT NOTIFICATIONS -->
          <div class="panel panel-default">
            <div class="panel-heading">
              <div class="panel-title-box">
                <h3>Sent notifications</h3>
                <span>number of sent notifications</span>
              </div>
            </div>
            <div class="panel-body padding-0">
              <div class="chart-holder" id="push-monkey-dashboard-line-1" style="height: 200px;"></div>
            </div>
          </div>
          <!-- END SENT NOTIFICATIONS -->

        </div>

        <div class="col-md-4">

          <!-- START CLICKS BLOCK -->
          <div class="panel panel-default">
            <div class="panel-heading">
              <div class="panel-title-box">
                <h3>Opened notifications</h3>
                <span>number of opened notifications</span>
              </div>
            </div>
            <div class="panel-body padding-0">
              <div class="chart-holder" id="push-monkey-dashboard-line-2" style="height: 200px;"></div>
            </div>
          </div>
          <!-- END CLICKS BLOCK -->

        </div>
        <div class="col-md-3">

          <!-- START CLICKS BLOCK -->
          <div class="panel panel-default">
            <div class="panel-heading">
              <div class="panel-title-box">
                <h3>Browsers</h3>
                <span>usage amongs your subscribers</span>
              </div>
            </div>
            <div class="panel-body">

              <h5>Mozilla Firefox</h5>
              <div class="progress progress-small">
                <div class="progress-bar progress-bar-warning" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $output->subscribers_firefox; ?>%">
                </div>
              </div>
              <h5>Google Chrome</h5>
              <div class="progress progress-small">
                <div class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $output->subscribers_chrome; ?>%">
                </div>
              </div>
              <h5>Apple Safari</h5>
              <div class="progress progress-small">
                <div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $output->subscribers_safari; ?>%">
                </div>
              </div>
            </div>
          </div>
          <!-- END CLICKS BLOCK -->

        </div>
      </div>

      <div class="row">
        <div class="col-md-7">

          <!-- START MAP BLOCK -->
          <div class="panel panel-default">
            <div class="panel-heading">
              <div class="panel-title-box">
                <h3>Top 5 - Map</h3>
                <span>of subscribers by country of origin</span>
              </div>
            </div>
            <div class="panel-body">
              <div class="row stacked">
                <div class="col-md-4">
                   <table class="table table-striped table-condensed">
                    <thead>
                      <tr>
                        <th>Country</th>
                        <th>Subscribers</th>
                      </tr>
                    </thead>
                    <tbody>
                    <?php
                    $countries = (array) $output->top_countries;
                    if ( count( $countries ) ) {
                      foreach ($countries as $row) {
                      $r = (array)$row;
                    ?>
                      <tr>
                        <td><?php echo $r['country_name']; ?></td>
                        <td><?php echo $r['count']; ?></td>
                      </tr>
                    <?php
                      }
                    } else {
                    ?>
                    <tr>
                      <td colspan="2" class="text-center">No countries yet.</td>
                    </tr>
                    <?php
                    }
                    ?>
                    </tbody>
                  </table>
                </div>
                <div class="col-md-8">
                  <div id="dashboard-map-seles" style="width: 100%; height: 200px"></div>
                </div>
              </div>
            </div>
          </div>
          <!-- END MAP BLOCK -->

        </div>
      </div>

      <?php require_once( plugin_dir_path( __FILE__ ) . '/parts/footer.php' ); ?>
    <?php } ?>
  </div>
</div>