<?php

class AuthyUtils {

    /**
     * Runs esc_html on strings. Leaves input untouched if it's not a string.
     *
     * @return mixed
     */
    private static function escape_string($maybe_string) {
        $escaped = $maybe_string;
        if (is_string($maybe_string)) {
            $escaped = esc_html($maybe_string);
        }
        return $escaped;
    }
    
    /**
    * Normalize phone number
    * given a phone number return the normal form
    * 17654305034 -> 765-430-5034
    * normal form: 10 digits, {3}-{3}-{4}
    * @param string $phone_number
    * @return string
    */
    public static function normalize_phone_number( $phone_number ) {
        $phone_number = substr( $phone_number, 0, -4 ) . '-' 
            . substr( $phone_number, -4 );
        if ( strlen( $phone_number ) - 5 > 3 ) {
            $phone_number = substr( $phone_number, 0, -8 ) . '-' 
                . substr( $phone_number, -8 );
        }
        return $phone_number;
    }

    /**
     * Renders the specified template, giving it access to $variables.
     * Strings are escaped.
     *
     * @param string $name The name (with no .php extension) of a file in
     *   templates/.
     * @param array $variables A list of variables to be used in the
     *   template.
     * @return string
     */
    public static function render_template($name, $variables=false, $sanitize=true) {
        if ($variables) {
            $escaped_variables = $variables;
            if ($sanitize) {
                $escaped_variables = array_map(array(__CLASS__, 'escape_string'), $variables);
            }
            extract($escaped_variables, EXTR_SKIP);
        }
        ob_start();
        require(AUTHY_TEMPLATE_PATH . $name . '.php');
        return ob_get_clean();
    }

    /**
     * Renders the specified template to be used in a modal window, giving it access to $variables.
     * Strings are escaped.
     *
     * @param string $name The name (with no .php extension) of a file in
     *   templates/.
     * @param array $variables A list of variables to be used in the
     *   template.
     * @return string
     */
    public static function render_modal_form_template($name, $variables=false, $sanitize=true) {
        $content = AuthyUtils::render_template($name, $variables, $sanitize);
        $response = AuthyUtils::render_template('profile/ajax_form_layout', array(
            'content' => $content
        ), false);
        return $response;
    }

    /**
     * Build Ajax URL for users' connection management
     *
     * @uses add_query_arg, wp_create_nonce, admin_url
     * @return string
     */
    public static function get_ajax_url() {
        return add_query_arg( array(
            'action' => AUTHY_USERS_PAGE,
            'nonce' => wp_create_nonce( AUTHY_USERS_KEY . '_ajax' ),
        ), admin_url( 'admin-ajax.php' ) );
    }

    public static function arr_get($array, $key, $default = null) {
        return isset($array[$key]) ? $array[$key] : $default;
    }

}

?>
