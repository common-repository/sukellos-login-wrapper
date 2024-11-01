<?php

namespace Sukellos\WPFw\AdminBuilder;

use Sukellos\WPFw\AdminBuilder\Fields\Edd_Licensing_Field;
use Sukellos\WPFw\AdminBuilder\Inc\EDD_SL_Plugin_Updater;
use Sukellos\WPFw\Singleton;
use Sukellos\WPFw\Utils\WP_Admin_Notices_Manager;
use Sukellos\WPFw\Utils\WP_Log;
use Sukellos\WPFw\WP_PLoad;

defined( 'ABSPATH' ) or exit;

/**
 * EDD_Licensing_Manager - Manager EDD licensing updater for all EDD license fields created
 *
 * @since 1.0.0
 */
class EDD_Licensing_Manager {

    // Use Trait Singleton
    use Singleton;

    const EDD_LICENSES_OPTION = 'wp_sukellos_fw_edd_licenses';

    /**
     * Default init method called when instance created
     * This method can be overridden if needed.
     *
     * @since 1.0.0
     * @access protected
     */
    public function init() {

        // Initialize the updater. Hooked into `init` to work with the wp_version_check cron job, which allows auto-updates.
        add_action( 'init', array( $this, 'action_init' ) );
    }

    /**
     * Count licenses
     * @return array
     */
    public function count_edd_licenses() {

        $edd_licenses = $this->get_edd_licenses();
        $count = count( $edd_licenses );

        WP_Log::debug( 'EDD_Licensing_Manager->count_edd_licenses - License count ', ['$count'=>$count] );

        return $count;
    }

    /**
     * Retrieve licenses
     * @return array
     */
    public function get_edd_licenses() {

        $edd_licenses = get_option( self::EDD_LICENSES_OPTION, '' );

        // Sanitize
        if ( '' === $edd_licenses ) {

            $edd_licenses = array();
        }
        if ( !is_array( $edd_licenses ) ) {

            $edd_licenses = (array) $edd_licenses;
        }

        return $edd_licenses;
    }

    /**
     * Check validity of the EDD license for a plugin
     *
     * IF no EDD license field existing, then its valid
     * If there is an EDD license field associated to a plugin, then verify if its valid
     */
    public function check_edd_license_validity( $plugin_file ) {

        $valid = true;

        // Get edd licenses
        $edd_licences = $this->get_edd_licenses();
        WP_Log::debug( 'EDD_Licensing_Manager->check_edd_license_validity ', ['$edd_licences'=>$edd_licences] );

        foreach( $edd_licences as $edd_licence_id => $edd_license_setting ) {

            // Is the same plugin file ?
            $cplugin_file = $edd_license_setting[ 'plugin_file' ];
            WP_Log::debug( 'EDD_Licensing_Manager->check_edd_license_validity ', ['$cplugin_file found'=>$cplugin_file, '$plugin_file to check'=>$plugin_file] );

            if ( FALSE !== strpos( $cplugin_file, $plugin_file ) ) {

                // Check if this license id is valid
                $edd_license_id = $edd_license_setting[ 'id' ];
                WP_Log::debug( 'EDD_Licensing_Manager->check_edd_license_validity - Found a license field associated to this plugin', ['$edd_license_id'=>$edd_license_id] );

                // Format validity id
                if ( !isset( $edd_license_setting['validity_id'] ) || ( $edd_license_setting['validity_id'] === '' ) ) {

                    $edd_license_validity_id = $edd_license_id.Edd_Licensing_Field::LICENCE_VALIDITY_SUFFIX;
                } else {

                    $edd_license_validity_id = $edd_license_setting['validity_id'];
                }

                // Validity ?
                $validity = get_option( $edd_license_validity_id, '' );
                WP_Log::debug( 'EDD_Licensing_Manager->check_edd_license_validity - Found a validity license field associated to this plugin', ['$edd_license_validity_id'=>$edd_license_validity_id, '$validity'=>$validity] );
                if ( ( '' === $validity ) || ( 'valid' !== $validity ) ) {

                    $valid = false;
                }
                // Field has been found, then break the loop
                break;
            }
        }
        WP_Log::debug( 'EDD_Licensing_Manager->check_edd_license_validity ', ['valid?'=>($valid?'true':'false')] );

        return $valid;
    }

    /**
     * Register a license
     */
    public function register_edd_license( $edd_license_settings ) {

        // Get edd licenses
        $edd_licences = $this->get_edd_licenses();

        // Get license field id
        $edd_license_id = $edd_license_settings[ 'id' ];
        $edd_licences[ ''.$edd_license_id ] = $edd_license_settings;

        update_option( self::EDD_LICENSES_OPTION, $edd_licences );
    }

    /**
     * Unregister licenses
     */
    public function unregister_edd_license( $plugin_file ) {

        $edd_licenses = $this->get_edd_licenses();
        $new_edd_licenses = array();
        foreach ( $edd_licenses as $edd_license_id => $edd_license_setting ) {

            // Search for the plugin file
            $cplugin_file = $edd_license_setting['plugin_file'];
            if ( 0 < strpos( $cplugin_file, $plugin_file ) ) {

                // This is the good one

                // Format validity id
                $edd_license_id = $edd_license_setting[ 'id' ];
                if ( !isset( $edd_license_setting['validity_id'] ) || ( $edd_license_setting['validity_id'] === '' ) ) {

                    $edd_license_validity_id = $edd_license_id.Edd_Licensing_Field::LICENCE_VALIDITY_SUFFIX;
                } else {

                    $edd_license_validity_id = $edd_license_setting['validity_id'];
                }

                // Delete validity
                delete_option( $edd_license_validity_id );

                // Delete all licenses
                delete_option( $edd_license_id );
            } else {

                $new_edd_licenses[ ''.$edd_license_id ] = $edd_license_setting;
            }
        }

        update_option( self::EDD_LICENSES_OPTION, $new_edd_licenses );
    }

    /**
     *          ===============
     *      =======================
     *  ============ HOOKS ===========
     *      =======================
     *          ===============
     */


    /**
     * Initialize the updater. Hooked into `init` to work with the
     * wp_version_check cron job, which allows auto-updates.
     */
    public function action_init() {

        // To support auto-updates, this needs to run during the wp_version_check cron job for privileged users.
        $doing_cron = defined( 'DOING_CRON' ) && DOING_CRON;
        WP_Log::debug( 'EDD_Licensing_Manager->action_init ', ['DOING_CRON?'=>(! $doing_cron?'false':'true')] );
        if ( ! current_user_can( 'manage_options' ) && ! $doing_cron ) {

            return;
        }

        // Get edd licenses
        $edd_licences = $this->get_edd_licenses();
        WP_Log::debug( 'EDD_Licensing_Manager->action_init ', ['$edd_licences'=>$edd_licences] );

        foreach ( $edd_licences as $edd_licence_id => $edd_licence_settings ) {

            // Retrieve our license key from the DB
            $license_key = get_option( $edd_licence_id );
            WP_Log::debug( 'EDD_Licensing_Manager->action_init ', ['$license_key'=>$license_key] );
            $store_url = $edd_licence_settings[ 'store_url' ];
            $plugin_file = $edd_licence_settings[ 'plugin_file' ];
            $download_id = $edd_licence_settings[ 'download_id' ];
            $wp_override = $edd_licence_settings[ 'wp_override' ];

            if( !function_exists('get_plugin_data') ) {

                require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            }
            $plugin_info = get_plugin_data( $plugin_file );
            $author = $plugin_info[ WP_PLoad::AUTHOR_NAME ];
            $version = $plugin_info[ WP_PLoad::VERSION ];

            // setup the updater
            $edd_updater = new EDD_SL_Plugin_Updater(
                $store_url,
                $plugin_file,
                array(
                    'version' => $version,                    // current version number
                    'license' => $license_key,             // license key (used get_option above to retrieve from DB)
                    'item_id' => $download_id,       // ID of the product
                    'author'  => $author, // author of this plugin
                    'beta'    => false,
                )
            );
        }


    }
}
