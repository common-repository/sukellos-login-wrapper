<?php
/**
 * Plugin Name: Sukellos - Login Wrapper
 * Plugin URI: https://wp-adminbuilder.com/login-wrapper/
 * Description: Sukellos Framework Tools - Login Wrapper
 * Version: 1.1.8
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author: Sukellos
 * Author URI: https://wp-adminbuilder.com
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-sukellos-fw
 * Domain Path: /languages
 *
 * Copyright: (c) 2021-2022 Sukellos, SARL (ludovic.maillet@sukellos.fr)
 *
 *
 * @package   WP-Sukellos-Login-Wrapper
 * @author    Sukellos
 * @category  Admin
 * @copyright Copyright (c) 2021-2022 Sukellos, SARL
 * @license   Private
 */

namespace Sukellos;

defined( 'ABSPATH' ) or exit;

// Sukellos plugins share the same inclusion to allow regrouping whole Tools into one centralized admin page
// List authorized basenames for inclusion for security purpose
$authorized_sukellos_plugins = array(
    'sukellos-admin-builder',
    'sukellos-admin-builder-basic',
    'sukellos-dashboard-bar',
    'sukellos-email-tracker',
    'sukellos-enable-classic-editor',
    'sukellos-image-formats',
    'sukellos-logger',
    'sukellos-login-style',
    'sukellos-login-wrapper',
    'sukellos-leo2-wooc',
    'wp-sukellos-fw',
);
// Resolve Sukellos Fw inclusion
$wp_sukellos_fw_dir_ref = wp_cache_get( 'wp_sukellos_fw_dir_ref', 'wp_sukellos_fw' );
$authorized = false;
if ( FALSE !== $wp_sukellos_fw_dir_ref ) {

    // Ref must be one of authorized paths
    foreach ( $authorized_sukellos_plugins as $authorized_sukellos_plugin ) {

        $haystack_length = strlen( $wp_sukellos_fw_dir_ref );
        $needle_length = strlen( $authorized_sukellos_plugin );
        // Sukellos needle must be at the end of the haystack
        $needle_pos = strpos( $wp_sukellos_fw_dir_ref, $authorized_sukellos_plugin );
        $awaited_pos = $haystack_length - $needle_length;
        if ( ( FALSE !== $needle_pos ) && ( $needle_pos === $awaited_pos ) ) {

            $authorized = true;
        }
    }
}

// Ref was not present, or not authorized, then force current plugin ref on itself
if ( ( FALSE === $wp_sukellos_fw_dir_ref ) || ( !$authorized ) ) {

    // This one becomes the ref
    $wp_sukellos_fw_dir_ref = __DIR__;
    wp_cache_set( 'wp_sukellos_fw_dir_ref', $wp_sukellos_fw_dir_ref, 'wp_sukellos_fw' );
}

// Require vendor autoloads to be able to Use all frameworks namespaces
require_once $wp_sukellos_fw_dir_ref . '/vendor/autoload.php';
require_once $wp_sukellos_fw_dir_ref . '/vendor/wp-sukellos-fw/autoload.php';

// Require autoload for this current plugin
require_once __DIR__ . '/autoload.php';

// Sukellos Framework
use Sukellos\Admin\WP_Sukellos_Login_Wrapper_Admin;
use Sukellos\WPFw\Singleton;
use Sukellos\WPFw\WP_Sukellos_Fw_Loader;


/**
 * The loader class.
 *
 * @since 1.0.0
 */
final class WP_Sukellos_Login_Wrapper_Loader extends WP_Sukellos_Fw_Loader {

    // Use Trait Singleton
    use Singleton;

    /**
     * Default init method called when instance created
     * This method MUST be overridden in child
     *
     * @since 1.0.0
     * @access protected
     */
    public function init() {

        parent::init();
    }

    /**
     * Must be called in child Loader to get data from the file itself
     */
    public function get_plugin_file() {

        return __FILE__;
    }
    
    /**
     * Get the plugin instance
     *
     * @since 1.0.0
     *
     * @return The plugin main instance
     */
    public function get_plugin() {

        return WP_Sukellos_Login_Wrapper::instance();
    }

    /**
     * Gets the admin instance.
     *
     * @since 1.0.0
     *
     * @return admin instance, or null is no admin enabled
     */
    public function get_plugin_admin() {

        // Specific to include Tools into Sukellos Fw admin template
        parent::get_plugin_admin();

        return WP_Sukellos_Login_Wrapper_Admin::instance();
    }
}
WP_Sukellos_Login_Wrapper_Loader::instance();