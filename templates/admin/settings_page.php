<div class="wrap">
  <?php screen_icon(); ?>
  <h2><?php echo esc_attr( $plugin_name ); ?></h2>

  <?php if ( $ready ): ?>
    <p><?php _e( 'Enter your Authy API key (get one on authy.com/signup). You can select which users can enable authy by their WordPress role. Users can then enable Authy on their individual accounts by visting their user profile pages.', 'authy' ); ?></p>
    <p><?php _e( 'You can also enable and force Two-Factor Authentication by editing the user on the Users page, and then clicking "Enable Authy" button on their settings.', 'authy' ); ?></p>
  <?php else:  ?>
    <p><?php printf( __( 'To use the Authy service, you must register an account at <a href="%1$s"><strong>%1$s</strong></a> and create an application for access to the Authy API.', 'authy' ), AUTHY_BASEURL ); ?></p>
    <p><?php _e( "Once you've created your application, enter your API keys in the fields below.", 'authy' ); ?></p>
    <p><?php printf( __( 'Until your API keys are entered, the %s plugin will not work.', 'authy' ), $plugin_name ); ?></p>
  <?php endif; ?>

  <form action="options.php" method="post">
      <?php settings_fields( AUTHY_SETTINGS_PAGE ); ?>
      <?php do_settings_sections( AUTHY_SETTINGS_PAGE ); ?>

      <p class="submit">
          <input name="Submit" type="submit" value="<?php esc_attr_e( 'Save Changes', 'authy' );?>" class="button-primary">
      </p>
  </form>

  <?php if ( !empty( $details ) ): ?>
    <h2><?php _e( 'Application Details', 'authy' ); ?></h2>

    <table class='widefat' style="width:400px;">
        <tbody>
            <tr>
                <th><?php printf( __( 'Application name', 'authy' ) ); ?></th>
                <td><?php print esc_attr( $details['app']->name ); ?></td>
            </tr>
        </tbody>
    </table>

    <?php if ( $details['app']->plan === 'sandbox' ): ?>
        <strong style='color: #bc0b0b;'><?php _e( "Warning: text-messages won't work on the current plan. Upgrade for free to the Starter plan on your authy.com dashboard to enable text-messages.", 'authy' ); ?></strong>
    <?php endif; ?>

    <?php include AUTHY_TEMPLATE_PATH . 'admin/onetouch.php'; ?>

  <?php endif; ?>
</div>