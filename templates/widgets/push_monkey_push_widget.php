<div class="container-fluid push-monkey-bootstrap">

	<?php if( $posted ) { ?>
	<div class="row">
		<div class="col-md-12">
			<div class="alert alert-global alert-success">
				<p>Push Notification Sent. Yay!</p>
			</div>
		</div><!-- .col -->
	</div><!-- .row -->
	<?php } ?>

	<?php if(!$account_key) { ?>
	<div class="error-message"> 
		<p>
			Sign in before you can use Push Monkey. Don't have an account yet? 
			<a href="<?php echo $settings_url; ?>">Click here to sign up</a>. 
			<a href="http://www.getpushmonkey.com/help?source=plugin#gpm16" target="_blank">More info about this &#8594;</a>
		</p>
	</div>	
	<?php } ?>	

	<div class="row">
		<form method="post" enctype="multipart/form-data"  class="form-horizontal">		

	    <div class="form-group">
  			<label class="col-md-3 col-xs-12 control-label"  for="pm_title">
					Title
				</label>
				<div class="col-md-9 col-xs-12">
          <input type="text" name="title" id="pm_title" maxlength="25" class="form-control"/>
  				<span class="help-block">of the push message. 25 characters or less.</span>
				</div>				
	    </div>

	    <div class="form-group">
  			<label class="col-md-3 col-xs-12 control-label"  for="pm_message">
					Messages
				</label>
				<div class="col-md-9 col-xs-12">
          <textarea class="form-control" name="message" id="pm_message" maxlength="120"></textarea>
  				<span class="help-block">120 characters or less.</span>
				</div>				
			</div>	    

	    <div class="form-group">
  			<label class="col-md-3 col-xs-12 control-label"  for="pm_url">
					URL
				</label>
				<div class="col-md-9 col-xs-12">
          <input type="text" name="url" id="pm_url" maxlength="200" class="form-control"/>
  				<span class="help-block">Where the reader will land after clicking on the notification.</span>
				</div>				
	    </div>

	    <div class="form-group">
  			<label class="col-md-3 col-xs-12 control-label"  for="pm_image">
					Image (optional)
				</label>
				<div class="col-md-9 col-xs-12">
          <input type="file" name="image" id="pm_image" class="fileinput btn-primary" title="Browse File"/>
  				<span class="help-block">To be displayed with the message. <a href="https://blog.getpushmonkey.com/2017/04/notifications-with-images/">Read more about images.</a></span>
				</div>				
	    </div>


	    <div class="form-group">
				<?php if ( count($segments) > 0 ) {?>
					<h3>Segments</h3>
				  <?php foreach( $segments as $segment ) { ?>
						<?php foreach ( $segment as $k => $v ) { ?>
						<div class="col-md-4">
							<label class="check">
								<input  name="push_monkey_post_segments[]"  value="<?php echo $k; ?>" type="checkbox" class="icheckbox" id="push_monkey_segment_<?php echo $k; ?>"/>
								<?php echo $v[0]; ?>
							</label> 
						</div><!-- .col -->						  
						<?php } ?>
				  <?php } ?>		
				<?php } else { ?>
					<p>
						<strong>OPTIONAL TIP:</strong> You have not set up any segments. 
						<a href="http://www.getpushmonkey.com/help#gpm22" target="_blank">What are segments?</a>
					</p>
				<?php } ?>	    	
	    </div>
	    <input type="hidden" name="push_monkey_push_submit" value="1" />

  		<div class="form-group text-right">
				<a class="btn btn-success push-monkey-push-widget-send" href="#">
				Send
				</a>
			</div>

		</form>
	</div><!-- .row -->    	

  <!-- Confirmation Modal -->
  <div class="modal" id="push_monkey_confirmation_modal" tabindex="-1" role="dialog" aria-labelledby="defModalHead" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">                    
        <div class="modal-body">
          <h3>Confirm</h3>
          <p>Are you sure that you want to send this custom push notification?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">No</button>
          <button type="button" class="btn btn-success push_monkey_submit"">Yes. Send it</button>
        </div>
      </div>
    </div>
  </div>  

</div>	 