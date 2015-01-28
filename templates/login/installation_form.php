<html>
  <?php include AUTHY_TEMPLATE_PATH . 'header.php'; ?>
  <body class='login wp-core-ui'>
    <div id="authy-verify">
      <h1><a href="http://wordpress.org/" title="Powered by WordPress"><?php echo get_bloginfo( 'name' ); ?></a></h1>
      <?php if ( !empty( $errors ) ): ?>
          <div id="login_error"><strong><?php echo esc_attr_e( 'ERROR:', 'authy' ); ?> </strong><?php echo esc_attr_e( $errors, 'authy' ); ?></div>
      <?php endif; ?>
      <form method="POST" id="authy" action="wp-login.php">
        <p><?php echo esc_attr_e( 'To activate your account you need to setup Authy Two-Factor Authentication.', 'authy' ); ?></p>

        <div class='step'>
          <div class='description-step'>
            <span class='number'>1.</span>
            <span><?php printf( __( 'On your phone browser go to <a href="%1$s" alt="install authy" style="padding-left: 18px;">%1$s</a>.', 'authy' ), 'https://www.authy.com/install' ); ?></span>
          </div>
          <img src="<?php echo plugins_url( '../assets/images/step1-image.png', dirname(__FILE__) ); ?>" alt='installation' />
        </div>

        <div class='step'>
          <div class='description-step'>
            <span class='number'>2.</span>
            <span><?php printf( __('Open the App and register.', 'authy' ) ) ?></span>
          </div>
          <img src="<?php echo plugins_url( '../assets/images/step2-image.png', dirname(__FILE__) ); ?>" alt='smartphones' style='padding-left: 22px;' />
        </div>

        <p class='italic-text'>
          <?php echo esc_attr_e( 'If you don’t have an iPhone or Android ', 'authy' ); ?>
          <a href="#" class="request-sms-link"
            data-username="<?php echo esc_attr( $user->user_login );?>"
            data-signature="<?php echo esc_attr( $signature ); ?>"><?php echo esc_attr_e( 'click here to get the Token as a Text Message.', 'authy' ); ?>
          </a>
        </p>

        <label for="authy_token">
          <?php _e( 'Authy Token', 'authy' ); ?>
          <br>
          <input type="text" name="authy_token" id="authy-token" class="input" value="" size="20" />
        </label>
        <input type="hidden" name="username" value="<?php echo esc_attr( $user->user_login ); ?>"/>
        <input type="hidden" name="step" value="verify_installation"/>
        <?php if ( isset( $signature ) ) { ?>
          <input type="hidden" name="authy_signature" value="<?php echo esc_attr( $signature ); ?>"/>
        <?php } ?>

        <input type="submit" value="<?php echo esc_attr_e( 'Verify Token', 'authy' ) ?>" id="wp_submit" class="button button-primary">
        <div class="rsms">
          <img src="<?php echo plugins_url( '../assets/images/phone-icon.png', dirname(__FILE__) ); ?>" alt="cellphone">
          <a href="#" class='request-sms-link' data-username="<?php echo esc_attr( $user->user_login );?>" data-signature="<?php echo esc_attr( $signature ); ?>">
            <?php echo esc_attr_e( 'Get the token via SMS', 'authy' ); ?>
          </a>
        </div>
      </form>
    </div>
  </body>
</html>