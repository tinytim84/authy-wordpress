<h3><?php echo esc_html( AUTHY_PLUGIN_NAME ); ?></h3>
<table class="form-table">
  <tr>
      <th><label for="<?php echo esc_attr( AUTHY_USERS_KEY ); ?>"><?php _e( 'Two Factor Authentication', 'authy' ); ?></label></th>
      <td>
          <input type="checkbox" id="<?php echo esc_attr( AUTHY_USERS_KEY ); ?>" name="<?php echo esc_attr( AUTHY_USERS_KEY ); ?>" value="1" checked/>
      </td>
  </tr>
  <?php wp_nonce_field( AUTHY_USERS_KEY . '_disable', sprintf("_%s_wpnonce", AUTHY_USERS_KEY) ); ?>
</table>