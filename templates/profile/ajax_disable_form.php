<?php echo AuthyUtils::render_template('ajax_header', false, $sanitize=false); ?>
<body <?php body_class( 'wp-admin wp-core-ui authy-user-modal' ); ?>>
  <div class="wrap">
    <h2><?php echo esc_html( AUTHY_PLUGIN_NAME ); ?></h2>
    <form action="<?php echo AuthyUtils::get_ajax_url(); ?>" method="post">
	  <p><?php _e( 'Authy is enabled for this account.', 'authy' ); ?></p>
	  <p><?php printf( __( 'Click the button below to disable Two-Factor Authentication for <strong>%s</strong>', 'authy' ), $user_wp->user_login ); ?></p>

	  <p class="submit">
	    <input name="Disable" type="submit" value="<?php esc_attr_e( 'Disable Authy', 'authy' );?>" class="button-primary">
	  </p>
	  <input type="hidden" name="authy_step" value="disable" />

	  <?php wp_nonce_field( AUTHY_USERS_KEY . '_ajax_disable' ); ?>
    </form>
  </div>
</body>
