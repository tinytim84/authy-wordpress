<?php echo AuthyUtils::render_template('ajax_header', false, $sanitize=false); ?>
<body <?php body_class( 'wp-admin wp-core-ui authy-user-modal' ); ?>>
  <div class="wrap">
    <h2><?php echo esc_html( AUTHY_PLUGIN_NAME ); ?></h2>
<?php if ( $user_data['authy_id'] ) : ?>
  <p>
    <?php printf( __( 'Congratulations, Authy is now configured for <strong>%s</strong> user account.', 'authy' ), $user_wp->user_login ); ?>
  </p>
  <p>
    <?php _e( "We've sent an email, and an SMS message with instructions on how to install the Authy App. If you do not install the app, we'll automatically send an SMS message to your phone number ", 'authy' ); ?>
    <strong><?php echo esc_attr( $user_data['phone'] ); ?></strong>
    <?php _e( 'with the token that you need to use when logging in.', 'authy' ); ?>
  </p>
  <p><a class="button button-primary" href="#" onClick="self.parent.tb_remove();return false;"><?php _e( 'Return to your profile', 'authy' ); ?></a></p>
<?php else : ?>
  <p><?php printf( __( 'Authy could not be activated for the <strong>%s</strong> user account.', 'authy' ), $user_wp->user_login ); ?></p>
  <p><?php _e( 'Please try again later.', 'authy' ); ?></p>
  <p>
    <a class="button button-primary" href="<?php echo esc_url( AuthyUtils::get_ajax_url() ); ?>">
      <?php _e( 'Try again', 'authy' ); ?>
    </a>
  </p>
<?php endif; ?>
  </div>
</body>
