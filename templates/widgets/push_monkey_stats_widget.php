<div class="container-fluid push-monkey-bootstrap">
	<div class="row">
		<div class="col-md-12">
			<?php if ( $notice ) { $notice->render(); } ?>
		</div><!-- .col -->
	</div><!-- .row -->

	<!-- START COUNTERS -->
	<div class="row">
		<div class="col-md-12">

		  <!-- START WIDGET SUBSCRIBERS -->
      <div class="widget widget-success widget-item-icon">
        <div class="widget-item-left">
          <span class="fa fa-group"></span>
        </div>
        <div class="widget-data">
          <div class="widget-int num-count"><?php echo $output->subscribers; ?></div>
          <div class="widget-title">Subscribers</div>
          <div class="widget-subtitle">out of <?php echo $output->total_subscribers; ?></div>
        </div>
      </div>
      <!-- END WIDGET SUBSCRIBERS -->

		</div><!-- .col -->
		<div class="col-md-12">
			
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

		</div><!-- .col -->
	</div><!-- .row -->
	<!-- END COUNTERS -->

	<div class="row">
		<div class="col-md-12">

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

		</div><!-- .col -->		
	</div><!-- .row -->

	<div class="row">
		<div class="col-md-12">
			
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

		</div><!-- .col -->
	</div><!-- .row -->


</div>