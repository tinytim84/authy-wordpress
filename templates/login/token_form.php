<html>
  <?php $is_onetouch = isset($onetouch_request) && !empty($onetouch_request); ?>
  <?php include AUTHY_TEMPLATE_PATH . 'header.php'; ?>
  <body class='login wp-core-ui'>
    <div id="login">
      <h1>
        <a href="http://wordpress.org/" title="Powered by WordPress"><?php echo get_bloginfo( 'name' ); ?></a>
      </h1>
      <h3 style="text-align: center; margin-bottom:10px;">Authy Two-Factor Authentication</h3>
      <?php if( $is_onetouch): ?>
      <p class="message">
        <?php _e( "Check your Authy mobile app and approve the OneTouch request to login. Click the button below if you are having issues getting the notification or still want to enter manually the token from the mobile app or SMS.", 'authy' ); ?>
      </p>
      <?php else: ?>
      <p class="message">
        <?php _e( "You can get this token from the Authy mobile app. If you are not using the Authy app we've automatically sent you a token via SMS message to your phone number: ", 'authy' ); ?>
        <strong>
          <?php
            $phone_number = AuthyUtils::normalize_phone_number( $data['phone'] );
            $phone_number = preg_replace( "/^\d{1,3}\-/", 'XXX-', $phone_number );
            $phone_number = preg_replace( "/\-\d{1,3}\-/", '-XXX-', $phone_number );

            echo esc_attr( $phone_number );
          ?>
        </strong>
      </p>
    <?php endif; ?>

      <form method="POST" id="authy" action="<?php echo wp_login_url(); ?>" <?php if($is_onetouch): ?>style="display:none;"<?php endif; ?>>
        <label for="authy_token">
          <?php _e( 'Authy Token', 'authy' ); ?>
          <br>
          <input type="text" name="authy_token" id="authy-token" class="input" value="" size="20" autofocus="true" />
        </label>
        <input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect ); ?>"/>
        <input type="hidden" name="username" value="<?php echo esc_attr( $username ); ?>"/>
        <input type="hidden" name="rememberme" value="<?php echo esc_attr( $remember_me ); ?>"/>
        <?php if ( isset( $signature[AUTHY_SIGNATURE_KEY] ) && isset( $signature['signed_at'] ) ): ?>
          <input type="hidden" name="authy_signature" value="<?php echo esc_attr( $signature[AUTHY_SIGNATURE_KEY] ); ?>"/>
        <?php endif; ?>
        <?php if( $is_onetouch ): ?>
          <input type="hidden" class="onetouch-request" name="onetouch_uuid" value="<?php echo esc_attr( $onetouch_request['uuid'] ); ?>"/>          
        <?php endif; ?>

        <p class="submit">
          <input type="submit" value="<?php echo esc_attr_e( 'Login', 'authy' ) ?>" id="wp_submit" class="button button-primary button-large" />
        </p>
      </form>

      <form id="authy-onetouch" <?php if(!$is_onetouch): ?>style="display:none;"<?php endif; ?>>
          <h3 style="text-align: center; margin-bottom:10px;"><?php echo _e('Waiting for approval...', 'authy'); ?></h3>
          <p><?php echo _e('Open Authy mobile app and approve the login request.', 'authy'); ?></p>
          <input value="<?php echo _e('Enter token manually', 'authy'); ?>" type="button" id="authy-onetouch-btn" class="button button-primary button-large" style="text-align: center; margin-top:10px;"/>
      </form>
    </div>
  </body>
</html>