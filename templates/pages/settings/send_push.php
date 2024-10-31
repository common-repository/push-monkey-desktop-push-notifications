<div class="push-monkey push-monkey-bootstrap">
    <div class="container-fluid">
        <?php if ( ! $signed_in ) { ?>
            <?php require_once( plugin_dir_path( __FILE__ ) . '/parts/log-in.php' ); ?>
        <?php } else { ?>
            <?php require_once( plugin_dir_path( __FILE__ ) . '/parts/header.php' ); ?>

            <form id="push-monkey-send-push-form" method="post" enctype="multipart/form-data" class="form-horizontal">
              <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Send a Custom Push Notification</h3>
                </div>
                <div class="panel-body">
                  <p>
                    The Push Monkey WordPress plugin automatically send push notifications when you publish a post, of course depending on your settings.
                  </p>
                  <p>
                    Sometimes you might want to send a custom push notification, without publishing a post. This page allows you to do so.
                  </p>
                </div>
                <div class="panel-body">

                  <div class="form-group">
                    <h3>Push Notification</h3>
                    <label class="col-md-2 col-xs-12 control-label"  for="pm_title">
                      Title
                    </label>
                    <div class="col-md-7 col-xs-12">
                      <input type="text" name="title" id="pm_title" maxlength="25" class="form-control"/>
                      <span class="help-block">of the push message. 25 characters or less.</span>
                    </div>        
                  </div>

                  <div class="form-group">
                    <label class="col-md-2 col-xs-12 control-label"  for="pm_message">
                      Messages
                    </label>
                    <div class="col-md-7 col-xs-12">
                      <textarea class="form-control" name="message" id="pm_message" maxlength="120"></textarea>
                      <span class="help-block">120 characters or less.</span>
                    </div>        
                  </div>      

                  <div class="form-group">
                    <label class="col-md-2 col-xs-12 control-label"  for="pm_url">
                      URL
                    </label>
                    <div class="col-md-7 col-xs-12">
                      <input type="text" name="url" id="pm_url" maxlength="200" class="form-control"/>
                      <span class="help-block">Where the reader will land after clicking on the notification.</span>
                    </div>        
                  </div>

                  <div class="form-group">
                    <label class="col-md-2 col-xs-12 control-label" for="pm_action_btn">
                      Action Button Title
                    </label>
                    <div class="col-md-9 col-xs-12">
                      <input type="text" name="pm_action_btn" id="pm_action_btn"  class="form-control" value="OK" />
                      <span class="help-block">Title of Action button that appears on notification. Default Value: OK</span>
                    </div>        
                  </div>
                  <div class="col-md-12 col-xs-12 control-label text-left" style="display: flex; margin: 10px 0;">
                    <h3>Actions (Optional) :</h3><p>&nbsp;&nbsp;This feature is only supported in Chrome browser.</p>
                  </div>
                  <div class="form-group">
                    <label class="col-md-2 col-xs-12 control-label"  for="pm_action2_btn">
                      Additional CTA text
                    </label>
                    <div class="col-md-9 col-xs-12">
                      <input type="text" name="pm_action2_btn" id="pm_action2_btn"  class="form-control"/>
                    </div>        
                  </div>

                  <div class="form-group" style="margin-bottom: 50px;">
                    <label class="col-md-2 col-xs-12 control-label"  for="pm_action2_url">
                      Additional CTA url
                    </label>
                    <div class="col-md-9 col-xs-12">
                      <input type="text" name="pm_action2_url" id="pm_action2_url"class="form-control"/>
                    </div>        
                  </div>

                  <div class="form-group">
                    <label class="col-md-2 col-xs-12 control-label"  for="pm_image">
                      Image (optional)
                    </label>
                    <div class="col-md-7 col-xs-12">
                      <input type="file" name="image" id="pm_image" class="fileinput btn-primary" title="Browse File"/>
                      <span class="help-block">To be displayed with the message. <a href="https://blog.getpushmonkey.com/2017/04/notifications-with-images/">Read more about images.</a></span>
                    </div>        
                  </div>

                  <div class="form-group">
                    <?php if ( count($segments) > 0 ) {?>
                      <h3>Segments</h3>
                      <?php foreach( $segments as $segment ) { ?>
                        <?php foreach ( $segment as $k => $v ) { ?>
                        <div class="col-md-2 col-xs-16">
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
                  <input type="hidden" name="push_monkey_push_submit_page" value="1" />                  

                </div>
                <div class="panel-footer text-right">
                  <a class="btn btn-success push-monkey-send-push" href="#">
                    Send
                  </a>
                </div>
              </div>
            </form>

            <?php require_once( plugin_dir_path( __FILE__ ) . '/parts/footer.php' ); ?>
        <?php } ?>
    </div>


  <!-- Confirmation Modal -->
  <div class="modal" id="push_monkey_confirmation_modal" tabindex="-1" role="dialog" aria-labelledby="defModalHead" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">                    
        <div class="modal-body">
          <h3>Confirm</h3>
          <p>Are you sure that you want to send this custom push notification?</p>
          <p class="text-center">
            <img src="https://thumbs.gfycat.com/TightAmusedKakarikis-max-1mb.gif" />
          </p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">No</button>
          <button type="button" class="btn btn-success push_monkey_submit"">Yes. Send it</button>
        </div>
      </div>
    </div>
  </div>  


</div>