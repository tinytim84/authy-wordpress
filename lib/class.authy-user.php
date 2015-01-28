<?php

if (!defined('ABSPATH')) exit();

require_once(AUTHY_PATH . 'lib/class.authy-api.php');
require_once(AUTHY_PATH . 'lib/class.authy-exceptions.php');
require_once(AUTHY_PATH . 'lib/class.authy-settings.php');

class AuthyUser {

    private $_data = array();
    private $api = null;
    private $api_key = null;
    private $settings = null;
    private $default = array(
    	'user_id'      => null,
        'email'        => null,
        'phone'        => null,
        'country_code' => '+1',
        'authy_id'     => null,
        'force_by_admin' => 'false',
	);

    protected $signature_key = 'user_signature';
    protected $data_temp_key = 'authy_data_temp';
    protected $response = null;

    public function __construct($user_id = null, $data = array()) {
    	$this->settings = AuthySettings::instance();
     	$this->api_key = $this->settings->get('api_key_production');
		$this->api = AuthyAPI::instance( $this->api_key, AUTHY_API_URL );
    	$this->load($user_id, $data);
    }

    /**
     * Allows set content for _data array as properties of the object
     *
     * @param string $property key to create/update into _data array
     * @param mixed $value
     * @return mixed
     */
    public function __set($property, $value){
        return $this->_data[$property] = $value;
    }

    /**
     * Allows access content of _data array as properties of the object
     *
     * @param string $property Existing key into the _data array
     * @return mixed
     */
    public function __get($property){
        return array_key_exists($property, $this->_data) ? $this->_data[$property] : null;
    }

    /**
     * Deletes any stored Authy connections for the user on WP.
     * Expected usage is somewhere where clearing is the known action.
     *
     * @param bool $temp Use temporary data key or final user key 
     * @uses delete_user_meta
     * @return (bool) True on success, false on failure.
     */
    public function clean($temp = false) {
    	return delete_user_meta($this->user_id, $this->get_data_key($temp));
    }

    /**
     * Retrieves user's data in array format
     *
     * @return array
     */
    public function data() {
    	return $this->_data;
    }

    public function get_errors() {
        $errors = array();
        if( $this->response && !empty($this->response->errors) ) {
            foreach ( $this->response->errors as $attr => $message ) {
                if ( $attr != 'message' ) {
                    array_push($errors, $this->get_error_message($attr, $message));
                }
            }
        }
        return $errors;
    }

    /**
     * Retrieves a user property if exists
     *
     * @param string $key
     * @return mixed
     */
    // public function get($key) {
    // 	return empty($this->{$key}) ? null : $this->{$key};
    // }


    public function get_signature($complete = false) {
        $signature = get_user_meta($this->user_id, $this->signature_key, true);
        if($complete) {
            return $signature;
        } else {
            return isset($signature[AUTHY_SIGNATURE_KEY]) ? $signature[AUTHY_SIGNATURE_KEY] : null;
        }
    }

    /**
     * Checks if user has an Authy ID set
     *
     * @return bool
     */
    public function has_authy_id() {
    	return (bool) $this->authy_id;
    }

    /**
     * Checks if user has wp ID set
     *
     * @return bool
     */
    public function has_user_id() {
    	return (bool) $this->user_id;
    }

    /**
    * Checks if user has Two factor authentication forced by admin
	*
    * @return bool
    */
    public function is_forced_by_admin() {
    	return $this->force_by_admin == 'true' || $this->force_by_admin === true;
    }

    public function load_temp_data() {
        return $this->load($this->user_id, array(), true);
    }

    /**
     * Registers the user on Authy and saves data for wp user account
     *
     * @uses this::validate, AuthyApi::register_user, this::save
     * @return bool
     */
    public function register($temp = false) {
    	$this->validate();
    	$this->response = $this->api->register_user( 
    		$this->email, 
    		$this->phone, 
    		$this->country_code
    	);
        if ( isset($this->response->user) && $this->response->user->id ) {
        	$this->authy_id = $this->response->user->id;
            return $this->save($temp);
        }
        return false;
    }

    public function request_sms($forced = false) {
        if(!$this->has_authy_id()) {
            throw new AuthyMissingFieldException("authy_id is required to send SMS", 1);
        }
        return $this->api->request_sms($this->authy_id, $forced);
    }

    /**
     * Saves Authy data to the user account
     *
     * @uses this::has_user_id, this::has_authy_id, update_user_meta
     * @return bool
     */
    public function save($temp = false) {
        if((!$this->has_user_id() || !$this->has_authy_id()) && !empty($this->phone)) {
        	throw new AuthyMissingFieldException("user_id and authy_id are required fields for saving user object", 1);
        }
        if($temp) {
            $this->clean(true);
        }
        return update_user_meta( $this->user_id, $this->get_data_key($temp), array( $this->api_key => $this->data() ) );
    }


    public function send_approval_request() {
        $user_wp = get_user_by('id', $this->user_id);

        $message = sprintf( __('Login request to your WordPress site %s', 'authy'), $this->settings->get_application_name() );
        $ip_address = empty($_SERVER['REMOTE_ADDR']) ? __('UNKNOWN', 'authy') : $_SERVER['REMOTE_ADDR'];

        $details = array(
            __('Username', 'authy') => $user_wp->user_login,
            __('IP Address', 'authy') => $ip_address
        );
        if ( !empty($user_wp->user_email) ) {
            $details[__('Email', 'authy')] = $user_wp->user_email;
        }
        if ( !empty($user_wp->display_name) ) {
            $details[__('Name', 'authy')] = $user_wp->display_name;
        }

        return $this->api->send_approval_request($this->authy_id, array(
            'message' => $message,
            'details' => $details
        ));
    }


    public function set_signature($reset = false) {
        if (!$this->has_user_id()) {
            throw new AuthyMissingFieldException("user_id is required field to set signature", 1);
        }
        $signed_at = $reset ? null : time();
        return update_user_meta( 
            $this->user_id, 
            $this->signature_key, 
            array(AUTHY_SIGNATURE_KEY => $this->generate_signature(), 'signed_at' => $signed_at) 
        );
    }

    /**
    * Verify if the given signature is valid.
    * @return boolean
    */
    public function verify_signature($signature_entered, $simple = false) {
        $signature = $this->get_signature($complete = true);
        if ( empty($signature) 
            || !isset($signature[AUTHY_SIGNATURE_KEY])  
            || (!isset($signature['signed_at']) && !$simple)
        ) {
            return false;
        }
        $in_timelapse = $simple ? true : (time() - $signature['signed_at']) <= 300;
        return $in_timelapse && $signature[AUTHY_SIGNATURE_KEY] === $signature_entered;
    }

    public function verify_token($token) {
        return $this->api->check_token( $this->authy_id, $token );        
    }

    /**
    * Generates a signature
    * @return string
    */
    protected function generate_signature() {
        return wp_generate_password(64, false, false);
    }  

    protected function get_error_message($key, $message) {
        $messages = array(
            'country_code' => 'Country code is invalid'
        );
        return AuthyUtils::arr_get($messages, $key, $key.' '.$message);
    }

    /**
     * Loads authy user data, or the default data in case don't exist 
     * into the _data array to be accessed as object properties 
     *
     * @param int $user_id Internal wordpress user id. Default null
     * @param array $new_data New data to populate the object
     * @uses get_user_meta, wp_parse_args
     * @return null
     */
    protected function load($user_id, $new_data = array(), $temp = false) {
    	$current_data = get_user_meta($user_id, $this->get_data_key($temp), true);
    	if($this->api_key && is_array($current_data) 
    			&& array_key_exists($this->api_key, $current_data)) {
    		$current_data = $current_data[$this->api_key];
    	} else {
    		$current_data = array();
    	}
    	if($user_id && !isset($current_data['user_id'])) {
    		$current_data['user_id'] = $user_id;
    	}
    	// Merge current data with default values
	    $data = wp_parse_args($current_data, $this->default);
    	// Merge new data with previous merged data
	    $this->_data = wp_parse_args($new_data, $data);
    }

    /**
     * Validates Authy data has required fields 
     *
     * @return bool
     */
    protected function validate() {
    	$not_required = array('authy_id');
    	$required = array_diff(array_keys($this->data()), $not_required);
    	$missing_fields = array();
    	foreach($required as $property) {
    		if(is_null($this->{$property})) {
    			$missing_fields[] = $property;
    		}
    	}
        if ( !empty($missing_fields) ) {
        	throw new AuthyMissingFieldException("Missing fields: " . implode(", ", $missing_fields), 1);
        }
        return true;
    }

    protected function get_data_key($temp = false){
        return $temp ? $this->data_temp_key : AUTHY_USERS_KEY;
    }

}

?>
