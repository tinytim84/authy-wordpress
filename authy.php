<?php
/**
 * Plugin Name: Authy Two Factor Authentication
 * Plugin URI: https://github.com/authy/authy-wordpress
 * Description: Add <a href="http://www.authy.com/">Authy</a> two-factor authentication to WordPress, the easiest and fastest way to add strong 2 Factor Authentication to your blog.
 * Author: Authy Inc
 * Version: 3.0.1
 * Author URI: https://www.authy.com
 * License: GPL2+
 * Text Domain: authy

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if (!defined('ABSPATH')) exit();

if (!class_exists('Authy')) {

    class Authy {
        private static $__instance = null;

        private function __construct() {
            $this->define_constants();

            require_once(AUTHY_PATH . 'lib/class.authy-admin.php');
            require_once(AUTHY_PATH . 'lib/class.authy-user-profile.php');
            require_once(AUTHY_PATH . 'lib/class.authy-user-login.php');

            // Load translations
            load_plugin_textdomain( 'authy', false,
                dirname( AUTHY_BASENAME ) . '/languages' );

            add_action('plugins_loaded', array('AuthyAdmin', 'instance'));
            add_action('plugins_loaded', array('AuthyUserProfile', 'instance'));
            add_action('plugins_loaded', array('AuthyUserLogin', 'instance'));
        }

        private function define_constants() {
            if (!defined('AUTHY_DEBUG')) define('AUTHY_DEBUG', false);
            define('AUTHY_VERSION', '3.0.1');
            define('AUTHY_PLUGIN_NAME', 'Authy Two-Factor Authentication');
            define('AUTHY_PATH', plugin_dir_path(__FILE__));
            define('AUTHY_URL', plugin_dir_url(__FILE__));
            define('AUTHY_BASENAME', plugin_basename(__FILE__));
            define('AUTHY_TEMPLATE_PATH', AUTHY_PATH . 'templates/');
            define('AUTHY_API_URL', 'https://api.authy.com');
            define('AUTHY_SETTINGS_PAGE', 'authy');
            define('AUTHY_SETTINGS_KEY', 'authy');
            define('AUTHY_SETTINGS_APP_NAME_KEY', AUTHY_SETTINGS_KEY . '_application_name' );
            define('AUTHY_USERS_PAGE', 'authy-user');
            define('AUTHY_USERS_KEY', 'authy_user');
            define('AUTHY_SIGNATURE_KEY', 'authy_signature');
            define('AUTHY_BASEURL', 'https://www.authy.com');
            define('AUTHY_DASHBOARD_URL', 'https://dashboard.authy.com/');
            define('AUTHY_JS_URL', AUTHY_BASEURL . '/form.authy.min.js');
            define('AUTHY_CSS_URL', AUTHY_BASEURL . '/form.authy.min.css');
        }

        public static function instance() {
            if( ! is_a( self::$__instance, 'Authy' ) ) {
                self::$__instance = new Authy;
            }
            return self::$__instance;
        }
    }

}

Authy::instance();

?>
