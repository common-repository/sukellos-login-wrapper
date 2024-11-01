<?php

namespace Sukellos\WPFw\Utils;

defined( 'ABSPATH' ) or exit;

use Sukellos\WPFw\Singleton;

/**
 * Manages Sukellos Plugins
 *
 * @since 1.0.0
 */
class WP_Sukellos_Plugins_Manager {

    // Use Trait Singleton
    use Singleton;

    // The list of known Sukellos plugin : plugin base name => array( name, is_tool, description, button_label, url, is_active )
    // Keep the active plugin info from plugin data too
    private $sukellos_plugins = array(
        'sukellos-admin-builder/sukellos-admin-builder.php' => array(
            'name' => 'Sukellos - Admin Builder',
            'is_tool' => false,
            'description' => 'Easy Admin Pages. Magnify Options. Enrich Post Types And User Profiles. Automatic CSS generation.',
            'button_label' => 'DISCOVER',
            'url' => 'https://wp-adminbuilder.com',
            'is_active' => false,
            'is_full_version' => true,
            'is_licensed' => true,
        ),
        'sukellos-admin-builder-basic/sukellos-admin-builder-basic.php' => array(
            'name' => 'Sukellos - Admin Builder',
            'is_tool' => false,
            'description' => 'Easy Admin Pages. Magnify Options. Enrich Post Types And User Profiles. Automatic CSS generation.',
            'button_label' => 'FREE DOWNLOAD',
            'url' => 'https://wp-adminbuilder.com',
            'is_active' => false,
            'is_full_version' => false,
            'is_licensed' => true,
        ),
        'sukellos-dashboard-bar/sukellos-dashboard-bar.php' => array(
            'name' => 'Sukellos - Dashboard Bar',
            'is_tool' => true,
            'description' => 'Hide the admin dashboard bar. Can be applied to certain profiles only.',
            'button_label' => 'FREE DOWNLOAD', 
            'url' => 'https://wp-adminbuilder.com/dashboard-bar/',
            'is_active' => false,
            'is_full_version' => true,
            'is_licensed' => false,
        ),
        'sukellos-enable-classic-editor/sukellos-enable-classic-editor.php' => array(
            'name' => 'Sukellos - Enable Classic Editor',
            'is_tool' => true,
            'description' => 'Switch back to classic editor in post types and widgets.',
            'button_label' => 'FREE DOWNLOAD', 
            'url' => 'https://wp-adminbuilder.com/enable-classic-editor/',
            'is_active' => false,
            'is_full_version' => true,
            'is_licensed' => false,
        ),
        'sukellos-image-formats/sukellos-image-formats.php' => array(
            'name' => 'Sukellos - Image Formats',
            'is_tool' => true,
            'description' => 'Easily manage the image formats supported by Wordpress.',
            'button_label' => 'FREE DOWNLOAD', 
            'url' => 'https://wp-adminbuilder.com/image-formats/',
            'is_active' => false,
            'is_full_version' => true,
            'is_licensed' => false,
        ),
        'sukellos-logger/sukellos-logger.php' => array(
            'name' => 'Sukellos - Logger',
            'is_tool' => true,
            'description' => 'Add powerful logging functionality to Wordpress, usable from any plugin.',
            'button_label' => 'FREE DOWNLOAD', 
            'url' => 'https://wp-adminbuilder.com/logger/',
            'is_active' => false,
            'is_full_version' => true,
            'is_licensed' => true,
        ),
        'sukellos-login-style/sukellos-login-style.php' => array(
            'name' => 'Sukellos - Login Style',
            'is_tool' => true,
            'description' => 'Customize the Wordpress login page with colors and logo.',
            'button_label' => 'FREE DOWNLOAD', 
            'url' => 'https://wp-adminbuilder.com/login-style/',
            'is_active' => false,
            'is_full_version' => true,
            'is_licensed' => false,
        ),
        'sukellos-login-wrapper/sukellos-login-wrapper.php' => array(
            'name' => 'Sukellos - Login Wrapper',
            'is_tool' => true,
            'description' => 'Enrich the wordpress login with basic features (redirection, front end profile shortcode...)',
            'button_label' => 'FREE DOWNLOAD', 
            'url' => 'https://wp-adminbuilder.com/login-wrapper/',
            'is_active' => false,
            'is_full_version' => true,
            'is_licensed' => false,
        ),
        'sukellos-leo2-wooc/sukellos-leo2-wooc.php' => array(
            'name' => 'WooC LEO',
            'is_tool' => false,
            'description' => 'Synchronize LEO2 with WooCommerce. Exclusive Atoo LEO2 partner.',
            'button_label' => 'DISCOVER',
            'url' => 'https://woocleo.fr',
            'is_active' => false,
            'is_full_version' => true,
            'is_licensed' => true,
        ),
        'sukellos-email-tracker/sukellos-email-tracker.php' => array(
            'name' => 'Sukellos - Email Tracker',
            'is_tool' => true,
            'description' => 'Allows you to track email openings, clicks on links, etc.',
            'button_label' => 'FREE DOWNLOAD',
            'url' => 'https://wp-adminbuilder.com/email-tracker/',
            'is_active' => false,
            'is_full_version' => true,
            'is_licensed' => false,
        ),
        'sukellos-scrapio/sukellos-scrapio.php' => array(
            'name' => 'Sukellos - Scrapio',
            'is_tool' => true,
            'description' => 'Connects to Scrap.io\'s API to fetch Google Places based on criteria. Selected results are imported as Custom Post Types, with ACF fields generated from Scrap.io data.',
            'button_label' => 'FREE DOWNLOAD',
            'url' => 'https://wp-adminbuilder.com/scrapio/',
            'is_active' => false,
            'is_full_version' => true,
            'is_licensed' => false,
        ),
    );

    /**
     * Default init method called when instance created
     * This method can be overridden if needed.
     *
     * @since 1.0.0
     * @access public
     */
    public function init() {

    }

    /**
     * Return true if currently referencing the Admin Builder Pro will full features
     */
    public function is_sukellos_fw_pro() {

        return false;
    }

    /**
     * Register an active Sukellos Fw plugin
     *
     * @param $wp_plugin_name short plugin name, eg wp-sukellos-admin-builder
     * @param $wp_plugin_file full plugin file
     * @param $wp_plugin_info all plugin nifos retrieved from plugin headers
     */
    public function register_plugin( $wp_plugin_name, $wp_plugin_file, $wp_plugin_info ) {

        WP_Log::debug( 'WP_Sukellos_Plugins_Manager->register_plugin', ['$wp_plugin_name' => $wp_plugin_name, '$wp_plugin_file'=>$wp_plugin_file, '$wp_plugin_info'=>$wp_plugin_info ] );
        if ( array_key_exists( $wp_plugin_name, $this->sukellos_plugins ) ) {

            // Keep infos
            $this->sukellos_plugins[ ''.$wp_plugin_name ][ 'plugin_file' ] = $wp_plugin_file;
            $this->sukellos_plugins[ ''.$wp_plugin_name ][ 'plugin_info' ] = $wp_plugin_info;
            $this->sukellos_plugins[ ''.$wp_plugin_name ][ 'is_active' ] = true;

            if ( FALSE === $this->sukellos_plugins[ ''.$wp_plugin_name ][ 'is_full_version' ] ) {

                if ( isset( $wp_plugin_info[ 'Plan' ] ) && ( 'Basic' === $wp_plugin_info[ 'Plan' ] ) )  {

                    $this->sukellos_plugins[ ''.$wp_plugin_name ][ 'button_label' ] = 'DISCOVER PRO';
                } else {

                    $this->sukellos_plugins[ ''.$wp_plugin_name ][ 'is_full_version' ] = true;
                }
            }
        }
        WP_Log::debug( 'WP_Sukellos_Plugins_Manager->register_plugin - sukellos_plugins', ['sukellos_plugins' => $this->sukellos_plugins ] );
    }

    /**
     * Check if a tool is activated (may be used to add Tools admin page)
     *
     * @return the number of activated Sukellos Tools
     */
    public function count_activated_tools() {

        $count_activated_tools = 0;

        foreach ( $this->sukellos_plugins as $wp_plugin_name => $sukellos_plugin ) {

            if ( WP_Helper::is_plugin_active( $wp_plugin_name ) && ( TRUE === $sukellos_plugin['is_tool'] ) ) {

                WP_Log::debug( 'WP_Sukellos_Plugins_Manager->count_activated_tools - Activated Sukellos plugin found', ['plugin name' => $wp_plugin_name, 'plugin' => $sukellos_plugin] );
                $count_activated_tools++;
            }
        }
        return $count_activated_tools;
    }

    /**
     * Check if a plugin is activated with a license needed
     *
     * @return the number of activated Sukellos plugins with licenses
     */
    public function count_licensed_plugins() {

        WP_Log::debug( 'WP_Sukellos_Plugins_Manager->count_licensed_plugins - sukellos_plugins', ['sukellos_plugins' => $this->sukellos_plugins ] );

        $count_licensed_plugins = 0;

        foreach ( $this->sukellos_plugins as $wp_plugin_name => $sukellos_plugin ) {
            WP_Log::debug( 'WP_Sukellos_Plugins_Manager->count_licensed_plugins - Sukellos plugin found', ['plugin name' => $wp_plugin_name, 'plugin' => $sukellos_plugin] );

            if ( WP_Helper::is_plugin_active( $wp_plugin_name ) && ( TRUE === $sukellos_plugin['is_licensed'] ) ) {

                WP_Log::debug( 'WP_Sukellos_Plugins_Manager->count_licensed_plugins - Activated Sukellos plugin found', ['plugin name' => $wp_plugin_name, 'plugin' => $sukellos_plugin] );
                $count_licensed_plugins++;
            }
        }
        return $count_licensed_plugins;
    }


    /**
     * Get all plugins actualized with activation status
     *
     * @return array
     */
    public function get_plugin_infos( $base_name=null ) {

        WP_Log::debug( 'WP_Sukellos_Plugins_Manager->get_addons_status - Sukellos plugins found', ['plugins' => $this->sukellos_plugins] );
        if ( !is_null( $base_name ) && ( array_key_exists( $base_name, $this->sukellos_plugins ) ) ) {

            return $this->sukellos_plugins[ ''.$base_name ];
        } else {

            $plugin_infos = $this->sukellos_plugins;
            unset( $plugin_infos[ 'sukellos-admin-builder-basic/sukellos-admin-builder-basic.php' ] );

            if ( !$this->sukellos_plugins[ 'sukellos-admin-builder/sukellos-admin-builder.php' ][ 'is_active' ] && !$this->sukellos_plugins[ 'sukellos-admin-builder-basic/sukellos-admin-builder-basic.php' ][ 'is_active' ] ) {

                $plugin_infos[ 'sukellos-admin-builder/sukellos-admin-builder.php' ][ 'button_label' ] = 'FREE DOWNLOAD';
            }
            return $plugin_infos;
        }
    }

    /**
     *          ===============
     *      =======================
     *  ============ HOOKS ===========
     *      =======================
     *          ===============
     */

}