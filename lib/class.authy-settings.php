<?php

class AuthySettings {

	private static $__instance = null;

    private $settings = null;
    protected $settings_fields = null;
    protected $settings_field_defaults = array(
        'label'    => null,
        'type'     => 'text',
        'sanitizer' => 'sanitize_text_field',
        'section'  => 'default',
        'class'    => null,
    );

    private function __construct() {
    	$this->settings_fields = array(
	        array(
	            'name'      => 'api_key_production',
	            'label'     => __( 'Authy Production API Key', 'authy' ),
	            'type'      => 'text',
	            'sanitizer' => 'alphanumeric',
	        ),
	        array(
	            'name'      => 'disable_xmlrpc',
	            'label'     => __( "Disable external apps that don't support Two-factor Authentication", 'authy' ),
	            'type'      => 'checkbox',
	            'sanitizer' => null,
	        )
	    );
    }

    public function get($key) {
        if ( is_null( $this->settings ) || !is_array( $this->settings ) ) {
            $this->settings = get_option( AUTHY_SETTINGS_KEY );
            $this->settings = wp_parse_args( $this->settings, array(
                'api_key_production'  => '',
                'environment'         => apply_filters( 'authy_environment', 'production' ),
                'disable_xmlrpc'      => "true",
            ) );
        }
        return isset( $this->settings[ $key ] ) ? $this->settings[ $key ] : null;
    }

    public function register() {
        register_setting( AUTHY_SETTINGS_PAGE, AUTHY_SETTINGS_KEY, array( $this, 'validate' ) );
        register_setting( AUTHY_SETTINGS_PAGE, 'authy_roles', array( $this, 'select_only_system_roles' ) );    	
    }

    /**
     * Validate plugin settings
     *
     * @param array $settings
     * @uses check_admin_referer, wp_parse_args, sanitize_text_field
     * @return array
     */
    public function validate( $settings ) {
        check_admin_referer( AUTHY_SETTINGS_PAGE . '-options' );

        $settings_validated = array();

        foreach ( $this->settings_fields as $field ) {
            $field = wp_parse_args( $field, $this->settings_field_defaults );

            if ( !isset( $settings[ $field['name'] ] ) && $field['type'] != 'checkbox' ) {
                continue;
            }

            if ( $field['type'] === "text" && $field['sanitizer'] === 'alphanumeric' ) {
                $value = preg_replace( '#[^a-z0-9]#i', '', $settings[ $field['name' ] ] );
            } elseif ( $field['type'] == "checkbox" ) {
                $value = $settings[ $field['name'] ];

                if ( $value != "true" ) {
                    $value = "false";
                }
            } else {
                $value = sanitize_text_field( $settings[ $field['name'] ] );
            }

            if ( isset( $value ) && !empty( $value ) ) {
                $settings_validated[ $field['name'] ] = $value;
            }
        }
        return $settings_validated;
    }

    /**
    * Select the system roles present in $roles
    * @param array $roles
    * @uses $wp_roles
    * @return array
    */
    public function select_only_system_roles( $roles ) {
        if ( !is_array( $roles ) || empty( $roles ) ) {
            return array();
        }
        global $wp_roles;
        $system_roles = $wp_roles->get_names();
        foreach ( $roles as $role ) {
            if ( !in_array( $roles, $system_roles ) ) {
                unset( $roles[$role] );
            }
        }
        return $roles;
    }

    /**
     * GENERAL OPTIONS PAGE
     */

    /**
     * Populate settings page's sections
     *
     * @uses add_settings_field
     * @return null
     */
    public function register_settings_page_sections() {
        add_settings_field( 'api_key_production', __( 'Authy Production API Key', 'authy' ), array( $this, 'add_settings_api_key' ), AUTHY_SETTINGS_PAGE, 'default' );
        add_settings_field( 'authy_roles', __( 'Allow Authy for the following roles', 'authy' ), array( $this, 'add_settings_for_roles' ), AUTHY_SETTINGS_PAGE, 'default' );
        add_settings_field( 'disable_xmlrpc', __( "Disable external apps that don't support Two-factor Authentication", 'authy' ), array( $this, 'add_settings_disable_xmlrpc' ), AUTHY_SETTINGS_PAGE, 'default' );
    }

    /**
     * Render settings api key
     *
     * @uses this::get_setting, esc_attr
     * @return string
     */
    public function add_settings_api_key() {
        $value = $this->get( 'api_key_production' );
        ?>
            <input type="text" name="<?php echo esc_attr( AUTHY_SETTINGS_KEY ); ?>[api_key_production]"
              class="regular-text" id="field-api_key_production" value="<?php echo esc_attr( $value ); ?>" />
        <?php
    }

    /**
    * Render settings roles
    * @uses $wp_roles
    * @return string
    */
    public function add_settings_for_roles() {
        global $wp_roles;

        $roles = $wp_roles->get_names();
        $roles_to_list = array();

        foreach ( $roles as $key => $role ) {
            $roles_to_list[before_last_bar( $key )] = before_last_bar( $role );
        }

        $selected = get_option( 'authy_roles', $roles_to_list );

        foreach ( $wp_roles->get_names() as $role ) {
            $checked = in_array( before_last_bar( $role ), $selected );
            $role_name = before_last_bar( $role );
            // html block
            ?>
                <input name='authy_roles[<?php echo esc_attr( strtolower( $role_name ) ); ?>]' type='checkbox'
                  value='<?php echo esc_attr( $role_name ); ?>'<?php if ( $checked ) echo 'checked="checked"'; ?> /><?php echo esc_attr( $role_name ); ?><br/>
            <?php
        }
    }

    /**
    * Render settings disable XMLRPC
    *
    * @return string
    */
    public function add_settings_disable_xmlrpc() {
    	$value = $this->get( 'disable_xmlrpc' ) != "false"; 
        ?>
            <label for='<?php echo esc_attr( AUTHY_SETTINGS_KEY ); ?>[disable_xmlrpc]'>
                <input name="<?php echo esc_attr( AUTHY_SETTINGS_KEY ); ?>[disable_xmlrpc]" type="checkbox" value="true" <?php if ($value) echo 'checked="checked"'; ?> >
                <span style='color: #bc0b0b;'><?php _e( 'Ensure Two-factor authentication is always respected.' , 'authy' ); ?></span>
            </label>
            <p class ='description'><?php _e( "WordPress mobile app's don't support Two-Factor authentication. If you disable this option you will be able to use the apps but it will bypass Two-Factor Authentication.", 'authy' ); ?></p>
        <?php
    }

    /**
     * Return settings application name
     *
     * @return string
     */
    public function get_application_name() {
        $app_name = get_option( AUTHY_SETTINGS_APP_NAME_KEY );
        $api = $this->api();
        if( empty($app_name) && !empty($api) ) {
            $details = $api->application_details();
            if(!empty($details)) {
                $app_name = $details['app']->name;
                $this->set_application_name($app_name);
            }
        }
        return $app_name;
    }  

    /**
     * Set settings application name
     *
     * @return bool
     */
    public function set_application_name($app_name) {
        return update_option(AUTHY_SETTINGS_APP_NAME_KEY, $app_name);
    }

    private function api() {
        $api_key = $this->get( 'api_key_production' );
        if ( empty($api_key) ) {
            return null;
        }
        require_once(AUTHY_PATH . 'lib/class.authy-api.php');
        return AuthyAPI::instance( $api_key, AUTHY_API_URL );        
    }

    public static function instance() {
        if( ! is_a( self::$__instance, 'AuthySettings' ) ) {
            self::$__instance = new AuthySettings;
        }
        return self::$__instance;
    }

}