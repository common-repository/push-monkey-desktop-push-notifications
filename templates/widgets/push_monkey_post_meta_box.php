<div class="push-monkey-bootstrap">
  <div class="preview-container">
    <?php if( ! $account_key ) { ?>
    <div class="error-message">
      <p>Sign In before you can use Push Monkey. Don't have an account yet? <a href="<?php echo $register_url; ?>">Click here to Sign Up</a>. <a href="http://www.getpushmonkey.com/help?source=plugin#gpm4">More info about this</a>.
      </p>
    </div>
    <?php } ?>
    <h4>Notification Preview</h4>
    <div class="notification">

    <img class="icon" src="<?php echo $this->endpointURL; ?>/clients/icon/<?php echo $account_key; ?>" />   

      <p>
        <strong id="push_monkey_preview_title"><?php echo $title; ?></strong> 
        <br /> 
        <span id="push_monkey_preview_content"><?php echo $body; ?></span>
      </p>

    </div>
  </div>


  <?php if ( $opt_out_disabled && $checked ) { ?>
  <input type="hidden" name="push_monkey_opt_out" value="on" />
  <?php } ?>
  <label class="check">
    <input 
      name="push_monkey_opt_out<?php if ( $opt_out_disabled && $checked ) { ?>_disabled<?php } ?>" 
      id="push_monkey_opt_out<?php if ( $opt_out_disabled && $checked ) { ?>_disabled<?php } ?>" 
      type="checkbox" 
      class="icheckbox" 
      <?php if ( $opt_out_disabled ) { ?>disabled="disabled"<?php } ?> 
      <?php echo $checked; ?> 
    />
    Do <strong>NOT</strong> send push notification for this post
  </label> 
  <p class="howto">
    Ticking this checkbox prevents this post from sending push notification, regardless
    of other settings.<a href="http://www.getpushmonkey.com/help#gpm9">Help? &#8594;</a>
  </p>


  <label class="check">
    <input name="push_monkey_force_send" id="push_monkey_force_send" type="checkbox" class="icheckbox" <?php echo $force_checked; ?> />
    Force send push notification
  </label>
  <p class="howto">
    Send a push notification regardless of any other exclusion settings.
  </p>
  

  <?php if ( !empty( $segments ) ) { ?>
    
    <h3>Segments</h3>
    <strong>NOTE: </strong><span> Leave un-checked to send to all subscribers.</span>
    <a href="http://www.getpushmonkey.com/help#gpm22">Help? &#8594;</a>
    <br/>
    <?php foreach( $segments as $segment ) { ?>
      <?php foreach ( $segment as $k => $v ) { ?>

        <label class="check">
          <input type="checkbox" name="push_monkey_post_segments[]" value="<?php echo $k; ?>" id="push_monkey_post_segments_<?php echo $k; ?>" class="icheckbox"/>
          <?php echo $v[0]; ?>
        </label>
        <br />
      <?php } ?>  
    <?php } ?>  
  <?php } ?>



  <?php if ( !empty( $locations ) ) { ?>
    
    <h3>Locations</h3>
    <strong>NOTE: </strong><span> Leave un-checked to send to all subscribers.</span>
    <a href="https://blog.getpushmonkey.com/2017/08/geolocation-targ…d-see-statistics/">Help? &#8594;</a>
    <br/>  
    <div class="scrolling-container">
    <?php if ( is_array( $locations ) )  {
      foreach( $locations as $location ) { ?>
        <label class="check">
          <input type="checkbox" name="push_monkey_post_locations[]" value="<?php echo $location[0]; ?>" id="push_monkey_post_locations_<?php echo $location[2]; ?>" class="icheckbox"/>
          <?php echo $location[0]; ?> (<?php echo $location[1]; ?>)
        </label>  
      <br/>
      <?php } ?>
    <?php } ?>
    </div>    
  <?php } ?>
</div>
