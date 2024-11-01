<?php

namespace Sukellos\WPFw;

use Sukellos\WPFw\AdminBuilder\EDD_Licensing_Manager;
use Sukellos\WPFw\Utils\WP_Helper;
use Sukellos\WPFw\AdminBuilder\Admin_Builder;
use Sukellos\WPFw\Utils\WP_Log;
use Sukellos\WPFw\Utils\WP_Sukellos_Plugins_Manager;

defined( 'ABSPATH' ) or exit;

/**
 * Sukellos Licenses Admin class used as admin licenses container for all Sukellos plugins
 *
 * @since 1.0.0
 */
class WP_Sukellos_Fw_Admin_Licenses extends WP_Plugin_Admin {

    // Use Trait Singleton
    use Singleton;

    /**
     * Default init method called when instance created
     * This method can be overridden if needed.
     *
     * @since 1.0.0
     * @access protected
     */
    public function init() {

        parent::init();
    }

    /**
     *          ===============
     *      =======================
     *  ============ HOOKS ===========
     *      =======================
     *          ===============
     */

    /***
     * Adding CSS and JS into header
     * Default add assets/admin.css and assets/admin.js
     */
    public function admin_enqueue_scripts() {
        // Nothing to enqueue
    }

    /***
     * Admin page
     */
    public function create_items() {

        WP_Log::debug('create_items, counting licensed plugins ', ['plugins'=>WP_Sukellos_Plugins_Manager::instance()->count_licensed_plugins()]);

        // Check if there activated Sukellos licensed plugins
        if ( ( 0 < WP_Sukellos_Plugins_Manager::instance()->count_licensed_plugins() ) ) {

            // Admin page.
            $admin_page = Admin_Builder::instance()->create_admin_page(
                array(
                    'name' => WP_Helper::sk__( 'Licenses' ),
                    'title' => WP_Helper::sk__( 'Licenses' ),
                    'id' => WP_PLoad::WP_SK_OPTIONS_SUFFIX_PARAM.'_licenses',
                    'capability' => 'manage_options',
                    'parent_id' => WP_PLoad::WP_SK_OPTIONS_SUFFIX_PARAM.'_dashboard',
                    'icon' => 'dashicons-smiley', // Menu icon for top level menus only https://developer.wordpress.org/resource/dashicons/#editor-paste-word
                    'position' => 12,
                    'desc' => 'All licenses',
                    'use_reset' => true,
                    'save' => WP_Helper::sk__( 'Save' ), // Text for save button
                    'reset' => WP_Helper::sk__( 'Reset' ), // Text for reset button
                    'reset_question' => WP_Helper::sk__( 'Do you really want to reset form?' ), // Question asked to confirm reset
                    'use_tabs' => false,
                )
            );

            // Check if license restriction applied
            if ( $this->is_license_restricted() ) {

                // Create a note
                $admin_page->create_note(
                    array(
                        // Common
                        'id' => WP_PLoad::WP_SK_OPTIONS_SUFFIX_PARAM.'_note_license_invalid',
                        'name' => 'ATTENTION',
                        'desc' => WP_Helper::sk__( 'Thank you to validate your license, in order to be able to use all the functionalities of this plugin. If you do not yet have a license key, you can get one <a href="https://sukellos.com" target="_blank">here</a>' ),
                        'context_color' => '#bf935e',
                        'sukellos_rule_exception' => true,
                    )
                );
            }

            // Create all licenses fields
            do_action( 'sukellos_fw/admin/create_licenses_fields', $admin_page );

        }
    }
}