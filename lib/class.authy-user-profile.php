<?php

if (!defined('ABSPATH')) exit();

class AuthyUserProfile {

  	private static $__instance = null;

    private function __construct() {
        require_once(AUTHY_PATH . 'lib/class.authy-settings.php');
        require_once(AUTHY_PATH . 'lib/class.authy-api.php');
        require_once(AUTHY_PATH . 'lib/class.authy-utils.php');
        require_once(AUTHY_PATH . 'lib/class.authy-user.php');

        $this->settings = AuthySettings::instance();
        $this->api = AuthyAPI::instance( $this->settings->get( 'api_key_production' ), AUTHY_API_URL );

        if ( $this->api->ready() ) {
	        // User settings
	        add_action( 'show_user_profile', array( $this, 'action_show_user_profile' ) );
	        add_action( 'edit_user_profile', array( $this, 'action_edit_user_profile' ) );
	        add_action( 'wp_ajax_' . AUTHY_USERS_PAGE, array( $this, 'get_user_modal_via_ajax' ) );

	        add_action( 'personal_options_update', array( $this, 'action_personal_options_update' ) );
	        add_action( 'edit_user_profile_update', array( $this, 'action_edit_user_profile_update' ) );
	        add_filter( 'user_profile_update_errors', array( $this, 'register_user_and_check_errors' ), 10, 3 );
        }
	}

    /**
    * USER SETTINGS PAGES
    */

    /**
     * Non-JS connection interface
     *
     * @param object $user
     * @uses this::get_authy_data, esc_attr,
     */
    public function action_show_user_profile( $user ) {
    	// var_dump($user);
    	$authy_user = new AuthyUser($user->ID);
        if ( $authy_user->has_authy_id() ) {
            if ( !$authy_user->is_forced_by_admin() ) {
                echo AuthyUtils::render_template('profile/disable_form');
            }
        } elseif ( $this->available_authy_for_role( $user ) ) {
            echo AuthyUtils::render_template('profile/register_form', array(
                'user_data' => $authy_user->data()
            ));
        }
    }


    /**
     * Allow sufficiently-priviledged users to disable another user's Authy service.
     *
     * @param object $user
     * @uses current_user_can, this::user_has_authy_id, get_user_meta, wp_parse_args, esc_attr, wp_nonce_field
     * @action edit_user_profile
     * @return string
     */
    public function action_edit_user_profile( $user_p ) {
        if ( !current_user_can( 'create_users' ) ) {
            return;
        }
    	$authy_user = new AuthyUser($user_p->ID);
        if ( $authy_user->has_authy_id() ) {
            echo AuthyUtils::render_template('profile/admin_disable');
        } else {
            echo AuthyUtils::render_template('profile/admin_enable', array(
                'user_data' => $authy_user->data()
            ));            
        }
    }


    /**
    * Check if Two factor authentication is available for role
    * @param object $user
    * @uses wp_roles, get_option
    * @return boolean
    */
    public function available_authy_for_role( $user ) {
        global $wp_roles;
        $wordpress_roles = $wp_roles->get_names();
        $authy_roles = get_option( 'authy_roles', $wordpress_roles );

        foreach ( $user->roles as $role ) {
            if ( array_key_exists( $role, $authy_roles ) ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Handle non-JS changes to users' own connection
     *
     * @param int $user_id
     * @uses check_admin_referer, wp_verify_nonce, get_userdata, is_wp_error, this::register_authy_user, this::clear_authy_data,
     * @return null
     */
    public function action_personal_options_update( $user_id ) {
        check_admin_referer( 'update-user_' . $user_id );

        // Check if we have data to work with
        $authy_data = isset( $_POST[ AUTHY_USERS_KEY ] ) ? $_POST[ AUTHY_USERS_KEY ] : false;

        // Parse for nonce and API existence
        if ( !is_array( $authy_data ) || !array_key_exists( 'nonce', $authy_data ) ) {
            return;
        }

        $is_editing = wp_verify_nonce( $authy_data['nonce'], AUTHY_USERS_KEY . 'edit_own' );
        $is_disabling = wp_verify_nonce( $authy_data['nonce'], AUTHY_USERS_KEY . 'disable_own' ) && isset( $authy_data['disable_own'] );

        if ( $is_editing ) {
            // Email address
            $userdata = get_userdata( $user_id );
            if ( is_object( $userdata ) && ! is_wp_error( $userdata ) ) {
                $email = $userdata->data->user_email;
            } else {
                $email = null;
            }

            // Phone number
            $phone = preg_replace( '#[^\d]#', '', $authy_data['phone'] );
            $country_code = preg_replace( '#[^\d\+]#', '', $authy_data['country_code'] );

            $authy_user = new AuthyUser($user_id, array(
                "email" => $email,
                "phone" => $phone,
                "country_code" => $country_code,
                "force_by_admin" => false,
            ));
            $authy_user->register();
        } elseif ( $is_disabling ) {
            // Delete Authy usermeta if requested
            $authy_user = new AuthyUser($user_id);
            $authy_user->clean();
        }
    }


    /**
     * Updates/Clears a user's Authy configuration if an allowed user requests it.
     *
     * @param int $user_id
     * @uses wp_verify_nonce, this::clear_authy_data
     * @action edit_user_profile_update
     * @return null
     */
    public function action_edit_user_profile_update( $user_id ) {
    	$wpnonce = sprintf("_%s_wpnonce", AUTHY_USERS_KEY);
        $is_disabling_user = isset($_POST[$wpnonce]) && wp_verify_nonce($_POST[$wpnonce], AUTHY_USERS_KEY . '_disable');

        if (!isset($_POST[ AUTHY_USERS_KEY ]) ) {
        	if($is_disabling_user) {
	            $authy_user = new AuthyUser($user_id);
	            $authy_user->clean();
        	}
            return;
        }

        $authy_user_info = $_POST[AUTHY_USERS_KEY];
        $cellphone = $authy_user_info['phone'];
        $country_code = $authy_user_info['country_code'];
        $force_enable_authy = 'false';
        if(!empty( $authy_user_info['force_enable_authy'] ) && $authy_user_info['force_enable_authy'] == 'true'){
        	$force_enable_authy = 'true';
        }

        if ( !empty( $country_code ) && !empty( $cellphone ) ) {
            $email = $_POST['email'];
            $authy_user = new AuthyUser($user_id, array(
              "email" => $email,
              "phone" => $cellphone,
              "country_code" => $country_code,
              "force_by_admin" => 'true'
            ));
            $authy_user->register();
        } else {
            $authy_user = new AuthyUser($user_id, array('force_by_admin' => $force_enable_authy));	
            $authy_user->save();
        }
    }


    /**
     * Ajax handler for users' connection manager
     *
     * @uses wp_verify_nonce, get_current_user_id, get_userdata, this::get_authy_data, wp_print_scripts, wp_print_styles, body_class, esc_url, this::get_ajax_url, this::user_has_authy_id, _e, __, wp_nonce_field, esc_attr, this::clear_authy_data, wp_safe_redirect, sanitize_email, this::register_authy_user
     * @action wp_ajax_{$this->users_page}
     * @return string
     */
    public function get_user_modal_via_ajax() {
        // If nonce isn't set, bail
        if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], AUTHY_USERS_KEY . '_ajax' ) ) {
            echo '<script type="text/javascript">self.parent.tb_remove();</script>';
            exit();
        }

        $authy_user = new AuthyUser(get_current_user_id());
        $user_wp = get_user_by('id', $authy_user->user_id);
 
        $errors = array();

        // Step
        $step = isset( $_REQUEST['authy_step'] ) ? preg_replace( '#[^a-z0-9\-_]#i', '', $_REQUEST['authy_step'] ) : false;

        $is_enabling = isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], AUTHY_USERS_KEY . '_ajax_check' );
        $is_disabling = $step == 'disable' && isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], AUTHY_USERS_KEY . '_ajax_disable' );

        if( $is_disabling ) {
            $authy_user->clean();
            echo AuthyUtils::render_template('profile/ajax_disable_confirmation', array(
                'user_wp' => $user_wp
            ));
            exit();
        }

        if( $authy_user->has_authy_id() ){
            echo AuthyUtils::render_template('profile/ajax_disable_form', array(
                'user_wp' => $user_wp
            ));
            exit();
        } elseif ( $is_enabling ){
            $authy_user->email = sanitize_email( $user_wp->user_email );
            $authy_user->phone = isset( $_POST['authy_phone'] ) ? preg_replace( '#[^\d]#', '', $_POST['authy_phone'] ) : false;
            $authy_user->country_code = isset( $_POST['authy_country_code'] ) ? preg_replace( '#[^\d]#', '', $_POST['authy_country_code'] ) : false;
            if( $authy_user->register() ) {
                echo AuthyUtils::render_template('profile/ajax_enable_confirmation', array(
                    'user_wp' => $user_wp,
                    'user_data' => $authy_user->data()
                ));
            } else {
                echo AuthyUtils::render_template('profile/ajax_enable_form', array(
                    'user_wp' => $user_wp,
                    'user_data' => $authy_user->data(),
                    'errors' => $authy_user->get_errors()
                ));
            }
            exit();
        }
        echo AuthyUtils::render_template('profile/ajax_enable_form', array(
            'user_wp' => $user_wp,
            'user_data' => $authy_user->data()
        ));
        exit();
    }

    /**
    * Add errors when editing another user's profile
    *
    */
    public function register_user_and_check_errors( &$errors, $update, &$user ) {
        if( !$update || empty( $_POST[AUTHY_USERS_KEY]['phone'] ) ) {
            // Ignore if it's not updating an authy user.
            return;
        }
        $authy_user = new AuthyUser($user->ID, array(
            "email" => $_POST['email'],
            "phone" => $_POST[AUTHY_USERS_KEY]['phone'],
            "country_code" => $_POST[AUTHY_USERS_KEY]['country_code']
        ));
        if( !$authy_user->register() ) {
            foreach ( $authy_user->get_errors() as $message ) {
                $errors->add( 'authy_error', '<strong>Authy Error:</strong> ' . $message );
            }
        }
    }

    public static function instance() {
        if( ! is_a( self::$__instance, 'AuthyUserProfile' ) ) {
            self::$__instance = new AuthyUserProfile;
        }
        return self::$__instance;
    }
}

?>
