<h3><?php echo esc_html( AUTHY_PLUGIN_NAME ); ?></h3>
<table class="form-table" id="<?php echo esc_attr( AUTHY_USERS_KEY ); ?>">
<tr>
  <th>
	<label for="<?php echo esc_attr( AUTHY_USERS_KEY ); ?>_disable">
		<?php _e( 'Disable Two Factor Authentication?', 'authy' ); ?>
	</label>
  </th>
  <td>
    <input type="checkbox" id="<?php echo esc_attr( AUTHY_USERS_KEY ); ?>_disable" name="<?php echo esc_attr( AUTHY_USERS_KEY ); ?>[disable_own]" value="1" />
    <label for="<?php echo esc_attr( AUTHY_USERS_KEY ); ?>_disable">
    	<?php _e( 'Yes, disable Authy for your account.', 'authy' ); ?>
    </label>
    <?php wp_nonce_field( AUTHY_USERS_KEY . 'disable_own', AUTHY_USERS_KEY . '[nonce]' ); ?>
  </td>
</tr>
</table>