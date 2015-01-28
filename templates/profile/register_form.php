<h3><?php echo esc_html( AUTHY_PLUGIN_NAME ); ?></h3>
<table class="form-table" id="<?php echo esc_attr( AUTHY_USERS_KEY ); ?>">
<tr>
  <th><label for="phone"><?php _e( 'Country', 'authy' ); ?></label></th>
  <td>
    <input type="text" id="authy-countries" class="small-text" name="<?php echo esc_attr( AUTHY_USERS_KEY ); ?>[country_code]" value="<?php echo esc_attr( $user_data['country_code'] ); ?>" />
  </td>
</tr>
<tr>
  <th><label for="phone"><?php _e( 'Phone number', 'authy' ); ?></label></th>
  <td>
    <input type="tel" id="authy-cellphone" class="regular-text" name="<?php echo esc_attr( AUTHY_USERS_KEY ); ?>[phone]" value="<?php echo esc_attr( $user_data['phone'] ); ?>" />

    <?php wp_nonce_field( AUTHY_USERS_KEY . 'edit_own', AUTHY_USERS_KEY . '[nonce]' ); ?>
  </td>
</tr>
</table>