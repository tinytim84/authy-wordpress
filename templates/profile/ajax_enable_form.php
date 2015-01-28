<?php echo AuthyUtils::render_template('ajax_header', false, $sanitize=false); ?>
<body <?php body_class( 'wp-admin wp-core-ui authy-user-modal' ); ?>>
  <div class="wrap">
    <h2><?php echo esc_html( AUTHY_PLUGIN_NAME ); ?></h2>
    <form action="<?php echo AuthyUtils::get_ajax_url(); ?>" method="post">

<p><?php printf( __( 'Authy is not yet configured for <strong>%s</strong> account.', 'authy' ), $user_wp->user_login ); ?></p>

<p><?php _e( 'Complete the form below to enable Two-Factor Authentication for this account.', 'authy' ); ?></p>

<?php if ( !empty($errors) ): ?>
  <div class='error'>
    <?php foreach ($errors as $error): ?>
        <p><?php echo $error; ?></p>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<table class="form-table" id="<?php echo esc_attr( AUTHY_USERS_KEY ); ?>-ajax">
  <tr>
    <th><label for="phone"><?php _e( 'Country', 'authy' ); ?></label></th>
    <td>
      <input type="text" id="authy-countries" class="small-text" name="authy_country_code" value="<?php echo esc_attr( $user_data['country_code'] ); ?>" required />
    </td>
  </tr>
  <tr>
    <th><label for="phone"><?php _e( 'Phone number', 'authy' ); ?></label></th>
    <td>
      <input type="tel" id="authy-cellphone" class="regular-text" name="authy_phone" value="<?php echo esc_attr( $user_data['phone'] ); ?>" style="width:140px;" />
    </td>
  </tr>
</table>

<input type="hidden" name="authy_step" value="" />
<?php wp_nonce_field( AUTHY_USERS_KEY . '_ajax_check' ); ?>

<p class="submit">
  <input name="Continue" type="submit" value="<?php esc_attr_e( 'Continue', 'authy' );?>" class="button-primary">
</p>

    </form>
  </div>
</body>
