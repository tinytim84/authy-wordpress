<?php echo AuthyUtils::render_template('ajax_header', false, $sanitize=false); ?>
<body <?php body_class( 'wp-admin wp-core-ui authy-user-modal' ); ?>>
  <div class="wrap">
    <h2><?php echo esc_html( AUTHY_PLUGIN_NAME ); ?></h2>
	<p><?php echo esc_attr_e( 'Authy was disabled', 'authy' );?></p>
	<p>
	  <a class="button button-primary" href="#" onClick="self.parent.tb_remove();return false;">
	    <?php _e( 'Return to your profile', 'authy' ); ?>
	  </a>
	</p>
  </div>
</body>
