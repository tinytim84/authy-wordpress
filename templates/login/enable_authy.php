<html>
  <?php include AUTHY_TEMPLATE_PATH . 'header.php'; ?>
  <body class='login wp-core-ui'>
    <div id="login">
      <h1><a href="http://wordpress.org/" title="Powered by WordPress"><?php echo get_bloginfo( 'name' ); ?></a></h1>
      <h3 style="text-align: center; margin-bottom:10px;"><?php _e('Enable Authy Two-Factor Authentication', 'authy')?></h3>
      <?php
        if ( !empty( $errors ) ) {
          $message = '';
          foreach ( $errors as $msg ) {
            $message .= '<strong>ERROR: </strong>' . $msg . '<br>';
          }
          ?><div id="login_error"><?php echo _e( $message, 'authy' ); ?></div><?php
        }
      ?>
      <p class="message"><?php _e( 'Your administrator has requested that you add Two-Factor Authentication to your account, please enter your cellphone below to enable.', 'authy' ); ?></p>
      <form method="POST" id="authy" action="wp-login.php">
        <label for="authy_user[country_code]"><?php _e( 'Country', 'authy' ); ?></label>
        <input type="text" name="authy_user[country_code]" id="authy-countries" class="input" />

        <label for="authy_user[cellphone]"><?php _e( 'Phone number', 'authy' ); ?></label>
        <input type="tel" name="authy_user[cellphone]" id="authy-cellphone" class="input" />
        <input type="hidden" name="username" value="<?php echo esc_attr( $user->user_login ); ?>"/>
        <input type="hidden" name="step" value="enable_authy"/>
        <input type="hidden" name="authy_signature" value="<?php echo esc_attr( $signature ); ?>"/>

        <p class="submit">
          <input type="submit" value="<?php echo esc_attr_e( 'Enable', 'authy' ) ?>" id="wp_submit" class="button button-primary button-large">
        </p>
      </form>
    </div>
  </body>
</html>