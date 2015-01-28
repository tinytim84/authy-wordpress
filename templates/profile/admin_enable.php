<h3><?php echo esc_html( AUTHY_PLUGIN_NAME ); ?></h3>
<table class="form-table">
  <tr>
      <p><?php _e( 'To enable Authy enter the country and cellphone number of the person who is going to use this account.', 'authy' )?></p>
      <th><label for="phone"><?php _e( 'Country', 'authy' ); ?></label></th>
      <td>
          <input type="text" id="authy-countries" class="small-text" name="<?php echo esc_attr( AUTHY_USERS_KEY ); ?>[country_code]" value="<?php echo esc_attr( $user_data['country_code'] ); ?>" />
      </td>
  </tr>
  <tr>
      <th><label for="phone"><?php _e( 'Phone number', 'authy' ); ?></label></th>
      <td>
          <input type="tel" class="regular-text" id="authy-cellphone" name="<?php echo esc_attr( AUTHY_USERS_KEY ); ?>[phone]" value="<?php echo esc_attr( $user_data['phone'] ); ?>" />
      </td>
      <?php wp_nonce_field( AUTHY_USERS_KEY . '_edit', "_{AUTHY_USERS_KEY}_wpnonce" ); ?>
  </tr>
  <tr>
      <th><?php _e( 'Force enable Authy', 'authy' ); ?></th>
      <td>
          <label for="force-enable">
              <input name="<?php echo esc_attr( AUTHY_USERS_KEY ); ?>[force_enable_authy]" type="checkbox" value="true" <?php if ($user_data['force_by_admin'] == 'true') echo 'checked="checked"'; ?> />
              <?php _e( 'Force this user to enable Authy Two-Factor Authentication on the next login.', 'authy' ); ?>
          </label>
      </td>
  </tr>
</table>