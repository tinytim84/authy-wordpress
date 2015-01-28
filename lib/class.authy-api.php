<?php
/**
 * AUTHY API CLASS
 *
 * Handles Authy API requests in a WordPress way.
 *
 * @package Authy
 * @since 1.0.0
 */

require_once(AUTHY_PATH . 'lib/class.authy-exceptions.php');

class AuthyAPI {
  /**
   * Class variables
   */

  // Oh look, a singleton
  private static $__instance = null;

  // Authy API
  protected $api_key = null;
  protected $api_endpoint = null;

  /**
   * Singleton implementation
   *
   * @uses this::setup
   * @return object
   */
  public static function instance( $api_key, $api_endpoint ) {
    if ( ! is_a( self::$__instance, 'AuthyAPI' ) || !self::$__instance->ready() ) {
      self::$__instance = new AuthyAPI;
      self::$__instance->api_key = $api_key;
      self::$__instance->api_endpoint = $api_endpoint;
      self::$__instance->setup();
    }
    return self::$__instance;
  }

  /**
   * Silence is golden.
   */
  private function __construct() {}

  /**
   * Really, silence is golden.
   */
  private function setup() {}

  /**
   * Make a request to Authy API
   *
   * @param string $url Site URL to retrieve
   * @param string $method
   * @param array $args
   * @return stdClass
   */
  public function request( $url, $args = array() ) {
    $args['user-agent'] = 'AuthyWordPress/'. AUTHY_VERSION. ' ('. PHP_OS. '; WordPress ' . $GLOBALS['wp_version'] . ')';

    $api_response = wp_remote_request($url, $args);
    error_log("[Authy] API response: " . print_r( $api_response, true ));
    $status_code = wp_remote_retrieve_response_code($api_response);

    $body = wp_remote_retrieve_body($api_response);
    $body = json_decode($body);

    $response = new stdClass;
    $response->status_code = $status_code;
    $response->body = $body;
    $response->success = isset($body->success) ? $body->success : null;

    return $response;
  }

  public function ready() {
    $is_empty_api_key = is_null( $this->api_key ) || empty( $this->api_key );
    $is_empty_api_endpoint = is_null( $this->api_endpoint ) || empty( $this->api_endpoint );
    return !$is_empty_api_key && !$is_empty_api_endpoint;
  }

  /**
   * Attempt to retrieve an Authy ID for a given request
   *
   * @param string $email
   * @param string $phone
   * @param string $country_code
   * @uses sanitize_email, add_query_arg, wp_remote_post, wp_remote_retrieve_response_code, wp_remote_retrieve_body
   * @return mixed
   */
  public function register_user( $email, $phone, $country_code ) {
    // Sanitize arguments
    $email = sanitize_email( $email );
    $phone = preg_replace( '#[^\d]#', '', $phone );
    $country_code = preg_replace( '#[^\d\+]#', '', $country_code );

    // Build API endpoint
    $endpoint = sprintf( '%s/protected/json/users/new', $this->api_endpoint );
    $endpoint = add_query_arg( array(
      'api_key' => rawurlencode($this->api_key),
      'user[email]' => rawurlencode($email),
      'user[cellphone]' => rawurlencode($phone),
      'user[country_code]' => rawurlencode($country_code)
    ), $endpoint );

    // Make API request and parse response
    $response = $this->request($endpoint, array('method' => 'POST'));
    if ( !empty($response->body) ) {
      return $response->body;
    }

    return false;
  }

  /**
   * Validate a given token and Authy ID
   *
   * @param int $id
   * @param string $token
   * @uses add_query_arg, wp_remote_head, wp_remote_retrieve_response_code
   * @return mixed
   */
  public function check_token( $id, $token ) {
    // Sanitize arguments
    $id = preg_replace( '#[^\d]#', '', $id );
    $token = preg_replace( '#[^\d]#', '', $token );

    // Validate the token length
    if ( strlen( $token ) < 6 && strlen( $token ) > 10 ) {
      return __( 'Invalid Authy Token.', 'authy' );
    }

    // Build API endpoint
    // Token must be a string because it can have leading zeros
    $endpoint = sprintf( '%s/protected/json/verify/%s/%d', $this->api_endpoint, $token, $id );
    $endpoint = add_query_arg( array(
      'api_key' => rawurlencode($this->api_key),
      'force' => 'true'
    ), $endpoint );

    // Make API request up to three times and check responding status code
    $response = $this->request($endpoint, array('method' => 'GET'));

    if ( $response->status_code == 200 && strtolower($response->body->token)  == 'is valid' ) {
      return true;
    } elseif ( $response->status_code == 401) {
      return __( 'Invalid Authy Token.', 'authy' );
    }

    return false;
  }

  /**
  * Request a valid token via SMS
  * @param string $id
  * @return mixed
  */
  public function request_sms($id, $force) {
    // Sanitize the arguments
    $id = preg_replace( '#[^\d]#', '', $id );

    $endpoint = sprintf( '%s/protected/json/sms/%d', $this->api_endpoint, $id );
    $arguments = array('api_key' => rawurlencode($this->api_key));

    if ($force == true) {
      $arguments['force'] = 'true';
    }

    $endpoint = add_query_arg( $arguments, $endpoint);
    $response = $this->request($endpoint, array('method' => 'GET'));

    if ( $response->status_code == 200 && $response->success == 'true' ) {
      return __( 'SMS token was sent. Please allow at least 1 minute for the text to arrive.', 'authy' );
    }

    return __( $response->body->message, 'authy' );
  }

  /**
  * Get application details
  * @return array
  */
  public function application_details() {
    if( !$this->ready() ) {
      return false;
    }

    $endpoint = sprintf( '%s/protected/json/app/details', $this->api_endpoint );
    $endpoint = add_query_arg( array('api_key' => rawurlencode($this->api_key)), $endpoint);

    $response = $this->request($endpoint, array('method' => 'GET'));

    if ( $response->status_code == 200) {
      return get_object_vars($response->body);
    }

    return array();
  }

  /**
  * Verify if the given signature is valid.
  * @return boolean
  */
  public function verify_signature($user_data, $signature) {
    if(!isset($user_data['authy_signature'])  || !isset($user_data['signed_at']) ) {
      return false;
    }

    if((time() - $user_data['signed_at']) <= 300 && $user_data['authy_signature'] === $signature ) {
      return true;
    }

    return false;
  }

  /**
  * Generates a signature
  * @return string
  */
  public function generate_signature() {
    return wp_generate_password(64, false, false);
  }

  /**
  * Sends onetouch approval request
  * 
  * @param string $id Authy user id
  * @param array $args Parameters sent to the approval request. Required 'message'. 
  * Check http://docs.authy.com/onetouch.html for additional info.
  * @return array
  */
  public function send_approval_request($id, $args = array()){
    // Sanitize the arguments
    $id = preg_replace( '#[^\d]#', '', $id );

    if( !isset($args['message']) ){
      throw new AuthyMissingFieldException("'message' is required to send a onetouch approval request", 1);      
    }

    $endpoint = sprintf( '%s/onetouch/json/users/%d/approval_requests?', $this->api_endpoint, $id );
    $endpoint .= http_build_query( array_merge( array('api_key' => $this->api_key), $args ) );

    $response = $this->request($endpoint, array('method' => 'POST'));
    if ( !empty($response->body) ) {
      return $response->body;
    }

    return false;
  }

  public function approval_request_status($uuid){
    $endpoint = sprintf( '%s/onetouch/json/approval_requests/%s', $this->api_endpoint, $uuid );
    $endpoint = add_query_arg( array('api_key' => rawurlencode($this->api_key)), $endpoint);
    $response = $this->request($endpoint, array('method' => 'GET'));
    if ( !empty($response->body) ) {
      return $response->body;
    }
    return false;
  }

  /**
  * Verify SSL certificates
  *
  * @return mixed
  */
  public function curl_ca_certificates() {
    $response = wp_remote_get('https://api.authy.com');

    $pattern = '/SSL certificate problem/';

    if ( isset($response->errors['http_request_failed']) ) {
      if ( preg_match($pattern, $response->errors['http_request_failed'][0]) ) {
        $message = "We can't verify the Authy SSL certificate with your current SSL certificates.";
        $message .= "<br> To fix the problem, please do the following:<br> 1. Download the file cacert.pem from <a href='http://curl.haxx.se/docs/caextract.html'>http://curl.haxx.se/docs/caextract.html</a>.";
        $message .= "<br> 2. Configure curl.cainfo in <strong>php.ini</strong> with the full path to the file downloaded in step 1, something like this: <strong>curl.cainfo=c:\php\cacert.pem</strong>";
        $message .= "<br> 3. Restart your web server.";
        return __($message, "authy");
      } else {
        return __($response->errors['http_request_failed'][0], 'authy');
      }
    }

    return true;
  }
}
