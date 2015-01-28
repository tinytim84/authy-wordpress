<?php

if (!defined('ABSPATH')) exit();

class AuthyAdmin {

  	private static $__instance = null;

    // Some plugin info
    protected $api = null;

    private $settings = null;

    private function __construct() {
        require_once(AUTHY_PATH . 'lib/class.authy-settings.php');
        require_once(AUTHY_PATH . 'lib/class.authy-api.php');
        require_once(AUTHY_PATH . 'lib/class.authy-utils.php');

        $this->settings = AuthySettings::instance();
        $this->api = AuthyAPI::instance( $this->settings->get( 'api_key_production' ), AUTHY_API_URL );

        // Plugin settings
        add_action( 'admin_init', array( $this->settings, 'register' ) );
        add_action( 'admin_menu', array( $this, 'action_admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'action_admin_enqueue_scripts' ) );

        add_filter( 'plugin_action_links_' . AUTHY_BASENAME, array( $this, 
            'filter_plugin_action_links' ), 10, 2 );

        if ($this->api->ready()) {
            // Display notices
            add_action( 'admin_notices', array( $this, 'action_admin_notices' ) );
        }
    }

    /**
     * Add settings link to plugin row actions
     *
     * @param array $links
     * @uses get_admin_url, __
     * @filter plugin_action_links
     * @return array
     */
    public function filter_plugin_action_links( $links ) {
        $links['settings'] = sprintf('<a href="%s">%s</a>', 
        	get_admin_url(null, 'options-general.php?page=' . AUTHY_SETTINGS_PAGE), __( 'Settings', 'authy' ));
        return $links;
    }

    /**
     * Register plugin settings page and page's sections
     *
     * @uses add_options_page, add_settings_section
     * @action admin_menu
     * @return null
     */
    public function action_admin_menu() {
        $can_admin_network = is_plugin_active_for_network( AUTHY_BASENAME ) && current_user_can( 'network_admin' );

        if ( $can_admin_network || current_user_can( 'manage_options' ) ) {
            add_options_page( AUTHY_PLUGIN_NAME, 'Authy', 'manage_options', AUTHY_SETTINGS_PAGE, array( $this, 'plugin_settings_page' ) );
            add_settings_section( 'default', '', array( $this->settings, 'register_settings_page_sections' ), AUTHY_SETTINGS_PAGE );
        }
    }

    /**
     * Enqueue admin script for connection modal
     *
     * @uses get_current_screen, wp_enqueue_script, plugins_url, wp_localize_script, this::get_ajax_url, wp_enqueue_style
     * @action admin_enqueue_scripts
     * @return null
     */
    public function action_admin_enqueue_scripts() {
        if ( ! $this->api->ready() ) {
            return;
        }
        global $current_screen;
        if ( $current_screen->base === 'profile' ) {
            wp_enqueue_script( 'authy-profile', plugins_url( 'assets/authy-profile.js', dirname(__FILE__) ), array( 'jquery', 'thickbox' ), 1.01, true );
            wp_enqueue_script( 'form-authy-js', AUTHY_JS_URL, array(), false, true );
            wp_localize_script( 'authy-profile', 'Authy', array(
                'ajax' => AuthyUtils::get_ajax_url(),
                'th_text' => __( 'Two-Factor Authentication', 'authy' ),
                'button_text' => __( 'Enable/Disable Authy', 'authy' ),
            ) );

            wp_enqueue_style( 'thickbox' );
            wp_enqueue_style( 'form-authy-css', AUTHY_CSS_URL, array(), false, 'screen' );
        } elseif ( $current_screen->base === 'user-edit' ) {
            wp_enqueue_script( 'form-authy-js', AUTHY_JS_URL, array(), false, true );
            wp_enqueue_style( 'form-authy-css', AUTHY_CSS_URL, array(), false, 'screen' );
        }
    }

    /**
     * Render settings page
     *
     * @uses screen_icon, esc_html, get_admin_page_title, settings_fields, do_settings_sections
     * @return string
     */
    public function plugin_settings_page() {
        $details = $this->api->application_details();
        $this->sync_application_name($details);
        echo AuthyUtils::render_template('admin/settings_page', array(
            'plugin_name' => get_admin_page_title(),
            'ready' => $this->api->ready(),
            'details' => $details
        ));
    }

    /**
    * Display an admin notice when the server doesn't installed a cert bundle.
    */
    public function action_admin_notices() {
        $response = $this->api->curl_ca_certificates();
        if ( is_string( $response ) ) {
            ?><div id="message" class="error"><p><strong>Error:</strong><?php echo $response; ?></p></div><?php
        }
    }

    public static function instance() {
        if( ! is_a( self::$__instance, 'AuthyAdmin' ) ) {
            self::$__instance = new AuthyAdmin;
        }
        return self::$__instance;
    }

    private function sync_application_name($application_details) {
        if ( empty($application_details) ) {
            return;
        }
        $authy_app_name = $application_details['app']->name;
        $wp_current_name = $this->settings->get_application_name();
        if ( $wp_current_name != $authy_app_name ) {
            $this->settings->set_application_name($authy_app_name);
        }
    }

}

?>