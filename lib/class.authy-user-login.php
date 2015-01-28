<?php

if (!defined('ABSPATH')) exit();

class AuthyUserLogin {

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
            add_filter( 'authenticate', array( $this, 'authenticate_user' ), 10, 3 );

            // Disable XML-RPC
            if ( $this->settings->get( 'disable_xmlrpc' ) == "true") {
                add_filter( 'xmlrpc_enabled', '__return_false' );
            }

            // Enable the user with no privileges to run action_request_sms() in AJAX
            add_action( 'wp_ajax_nopriv_request_sms_ajax', array( $this, 'request_sms_ajax' ) );
            add_action( 'wp_ajax_request_sms_ajax', array( $this, 'request_sms_ajax' ) );            
            // Enable hook for onetouch ajax
            add_action( 'wp_ajax_nopriv_onetouch_status_ajax', array( $this, 'onetouch_status_ajax' ) );            
            add_action( 'wp_ajax_onetouch_status_ajax', array( $this, 'onetouch_status_ajax' ) );            
        }
    }


    /**
    * @param mixed $user
    * @param string $username_login
    * @param string $password
    * @uses XMLRPC_REQUEST, APP_REQUEST, this::user_has_authy_id, this::get_user_authy_id, this::api::check_token
    * @return mixed
    */

    public function authenticate_user( $user = '', $username_login = '', $password = '' ) {
        // If XMLRPC_REQUEST is disabled stop
        if ( ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) || ( defined( 'APP_REQUEST' ) && APP_REQUEST ) ) {
            return $user;
        }

        $step = AuthyUtils::arr_get($_POST, 'step');
        $signature = AuthyUtils::arr_get($_POST, 'authy_signature');
        $authy_user_info = AuthyUtils::arr_get($_POST, 'authy_user');
        $remember_me = AuthyUtils::arr_get($_POST, 'rememberme');
        $redirect_to = AuthyUtils::arr_get($_POST, 'redirect_to');
        $authy_token = AuthyUtils::arr_get($_POST, 'authy_token');

        $username_for_authy = isset($_POST['username']) ? $_POST['username'] : null;
        $user_wp = $this->get_wp_user($username_for_authy, $username_login);

        if ( empty($username_for_authy) && !empty($user_wp) ){
            $username_for_authy = $user_wp->user_login;
        }

        if ( !empty( $username_login ) ) {
            return $this->validate_password( 
                $user, $user_wp, $password, $redirect_to, $remember_me 
            );
        }

        if( !empty($_POST) && !isset($signature) ) {
            return $this->auth_error('MISSING_CREDENTIALS');
        }

        if ( empty( $step ) && $authy_token ) {
            return $this->login_with_2FA( $user_wp, $signature, $authy_token, $redirect_to, $remember_me );
        } elseif ( $step == 'enable_authy' && $authy_user_info 
            && isset( $authy_user_info['country_code'] ) && isset( $authy_user_info['cellphone'] ) ) {
            // if step is enable_authy and have country_code and phone show the enable authy page
            $params = array(
                'username' => $username_for_authy,
                'signature' => $signature,
                'cellphone' => $authy_user_info['cellphone'],
                'country_code' => $authy_user_info['country_code'],
            );
            return $this->check_user_fields_and_redirect_to_verify_token( $user_wp, $params );
        } elseif ( $step == 'verify_installation' && $authy_token ) {
            // If step is verify_installation and have authy_token show the verify authy installation page.
            $params = array(
                'username' => $username_for_authy,
                'authy_token' => $authy_token,
                'signature' => $signature,
            );
            return $this->verify_authy_installation( $user_wp, $params );
        }
    }

    /**
     *
     */
    public function verify_authy_installation( $user_wp, $params ) {
        $authy_user = new AuthyUser($user_wp->ID);
        if ( !$authy_user->verify_signature($params['signature'], $simple = true) 
            || $authy_user->has_authy_id() 
            || !$authy_user->is_forced_by_admin() 
        ) {
            return $this->auth_error('AUTHENTICATION_FAILED');
        }

        // Get the temporal authy data
        $authy_user->load_temp_data();
        $token_response = $authy_user->verify_token($params['authy_token']);

        if ( $token_response === true ) {
            // Save authy data of user on database
            $authy_user->save();
            $authy_user->set_signature($reset=true);
            $authy_user->clean($temp=true);

            // Login user and redirect
            wp_set_auth_cookie( $user_wp->ID ); // token was checked so go ahead.
            wp_safe_redirect( admin_url() );
        } else {
            // Show the errors
            echo AuthyUtils::render_template('login/installation_form', array(
                'user' => $user_wp,
                'signature' => $authy_user->get_signature(),
                'errors' => $token_response,
                'installation_form' => true
            ));            
            exit();
        }
    }

    /**
     * Enable authy and go to verify installation page
     *
     * @param array $params
     * @return mixed
     */
    public function check_user_fields_and_redirect_to_verify_token($user_wp, $params ) {
        $authy_user = new AuthyUser($user_wp->ID, array(
            "email" => $user_wp->user_email,
            "phone" => $params['cellphone'],
            "country_code" => $params['country_code']
        ));

        if ( !$authy_user->verify_signature($params['signature'], $simple = true) 
            || $authy_user->has_authy_id() 
            || !$authy_user->is_forced_by_admin() 
        ) {
            return $this->auth_error('AUTHENTICATION_FAILED');
        }

        // Request an Authy ID with given user information
        if( $authy_user->register($temp = true) ) {
            echo AuthyUtils::render_template('login/installation_form', array(
                'user' => $user_wp,
                'signature' => $authy_user->get_signature(),
                'installation_form' => true
            ));
        } else {
            echo AuthyUtils::render_template('login/enable_authy', array(
                'user' => $user_wp,
                'signature' => $authy_user->get_signature(),
                'errors' => $authy_user->get_errors()
            ));
        }
        exit();
    }

    /**
     * Login user with Authy Two Factor Authentication
     *
     * @param mixed $user
     * @return mixed
     */
    public function login_with_2FA( $user, $signature, $authy_token, $redirect_to, $remember_me ) {
        $this->intercept_authentication();
        $authy_user = new AuthyUser($user->ID);
        error_log("[Authy] User {$user->ID} login with 2fa");

        if ( $authy_user->verify_signature($signature) ) {
            error_log("[Authy] Signature verified when login with 2fa for user {$user->ID} ");
            $authy_user->set_signature($reset = true);
            $token_response = $authy_user->verify_token($authy_token);
            error_log("[Authy] Token response for user {$user->ID}: " . print_r( $token_response, true ));
            if ( $token_response === true || $token_response === 1 ) {
                // If remember me is set the cookies will be kept for 14 days.
                $remember_me = ($remember_me == 'forever') ? true : false;
                wp_set_auth_cookie( $user->ID, $remember_me );
                error_log("[Authy] Redirecting user {$user->ID} to {$redirect_to}");
                wp_safe_redirect( $redirect_to );
                exit();
            } elseif ( is_string($token_response) ) {
                return $this->auth_error($token_response);
            }
        }
        error_log("[Authy] Error verifying signature when login with 2fa for user {$user->ID}");
        return $this->auth_error('TIMEOUT');
    }

    public function login_with_onetouch( $user, $signature, $remember_me ){
        $this->intercept_authentication();
        $authy_user = new AuthyUser($user->ID);
        if ( $authy_user->verify_signature($signature) ) {
            $authy_user->set_signature($reset = true);
            $remember_me = ($remember_me == 'forever') ? true : false;
            wp_set_auth_cookie( $user->ID, $remember_me );
        } else {
            return $this->auth_error('TIMEOUT');
        }
    }

    /**
     * Do password authentication and redirect to 2nd screen
     *
     * @param mixed $user
     * @param mixed $user_wp
     * @param string $password
     * @param string $redirect_to
     * @return mixed
     */
    public function validate_password($user, $user_wp, $password, $redirect_to, $remember_me) {
        // Don't bother if WP can't provide a user object.
        if ( !is_object($user_wp) || !property_exists($user_wp, 'ID') ) {
            return $user_wp;
        }
        $authy_user = new AuthyUser($user_wp->ID);

        if ( !$authy_user->has_authy_id() && !$authy_user->is_forced_by_admin() ) {
            return $user; // wordpress will continue authentication.
        }

        $this->intercept_authentication();

        $user = wp_authenticate_username_password( $user, $user_wp->user_login, $password );

        if ( is_wp_error($user) ) {
            return $user; // there was an error
        }

        $authy_user->set_signature();
        if ( $authy_user->is_forced_by_admin() && !$authy_user->has_authy_id() ) {
            echo AuthyUtils::render_template('login/enable_authy', array(
                'user' => $user_wp,
                'signature' => $authy_user->get_signature()
            ));            
        } else {
            $onetouch_request = $authy_user->send_approval_request();
            $onetouch_request = isset($onetouch_request->approval_request) ? 
                (array) $onetouch_request->approval_request : array();

            $authy_user->request_sms();
            echo AuthyUtils::render_template('login/token_form', array(
                'username' => $user_wp->user_login,
                'data' => $authy_user->data(),
                'signature' => $authy_user->get_signature($complete = true),
                'redirect' => $redirect_to,
                'remember_me' => $remember_me,
                'onetouch_request' => $onetouch_request
            ));
        }
        exit();
    }

    /**
     * Send SMS with Authy token via AJAX
     * @return string
     */
    public function request_sms_ajax() {
        $user_wp = get_user_by( 'login', $_GET['username'] );
        $authy_user = new AuthyUser($user_wp->ID);
        $authy_user->load_temp_data();
        $verified = $authy_user->verify_signature($_GET['signature'], $simple = true);
        $res = $verified ? $authy_user->request_sms($forced = true) : _e( 'Error', 'authy' );
        echo esc_attr( $res );
        die();
    }

    public function onetouch_status_ajax(){
        $user_wp = get_user_by( 'login', $_GET['username'] );
        $uuid = AuthyUtils::arr_get($_GET, 'onetouch_uuid');
        $signature = AuthyUtils::arr_get($_GET, 'authy_signature');
        $remember_me = AuthyUtils::arr_get($_GET, 'rememberme');
        $redirect_to = AuthyUtils::arr_get($_GET, 'redirect_to');

        $response = $this->api->approval_request_status($uuid);
        $status = isset($response->approval_request->status) ? $response->approval_request->status : "unknown";
        if($status == "approved") {
            $this->login_with_onetouch($user_wp, $signature, $remember_me);
        } elseif ($status == "denied" || $status == "expired" ) {
            $this->auth_error('AUTHENTICATION_FAILED');
            $redirect_to = wp_login_url();
        } 
        echo json_encode(array('status' => $status, 'redirect_to' => $redirect_to ));
        wp_die();
    }

    protected function get_wp_user($username_for_authy, $username_login) {
        $username = empty($username_for_authy) ? $username_login : $username_for_authy;
        $user_wp = get_user_by( 'login', $username );
        if( empty($user_wp) ) {
            $user_wp = get_user_by( 'email', $username );
        }
        return $user_wp;
    }

    /**
    * This method prevents WordPress from setting the authentication cookie and display errors.
    */
    protected function intercept_authentication() {
        // from here we take care of the authentication.
        remove_action( 'authenticate', 'wp_authenticate_username_password', 20 );
    }

    protected function auth_error($content) {
        $errors = array(
            'MISSING_CREDENTIALS' => __('Missing credentials', 'authy'),
            'AUTHENTICATION_FAILED' => __('Authentication failed', 'authy'),
            'TIMEOUT' => __('Authentication timed out. Please try again.', 'authy'),
            'DEFAULT' => $content
        );
        $error =  sprintf( '<strong>%1s</strong> %2s', __('ERROR:', 'authy'), 
            AuthyUtils::arr_get($errors, $content, $errors['DEFAULT']) );
        return new WP_Error( 'authentication_failed', $error  );
    }

    public static function instance() {
        if( ! is_a( self::$__instance, 'AuthyUserLogin' ) ) {
            self::$__instance = new AuthyUserLogin;
        }
        return self::$__instance;
    }

}

?>