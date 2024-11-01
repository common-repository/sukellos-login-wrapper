<?php

namespace Sukellos\WPFw;

use Sukellos\WPFw\Utils\WP_Helper;
use Sukellos\WPFw\AdminBuilder\Admin_Builder;
use Sukellos\WPFw\Utils\WP_Sukellos_Plugins_Manager;

defined( 'ABSPATH' ) or exit;

/**
 * Sukellos Tools Admin class used as admin tools container for all Sukellos plugins
 *
 * @since 1.0.0
 */
class WP_Sukellos_Fw_Admin_Tools extends WP_Plugin_Admin {

    const IMAGE_SIZE_PREFIX = 'image_size_';

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

        // Check if there activated Sukellos Tools
        if ( ( 0 < WP_Sukellos_Plugins_Manager::instance()->count_activated_tools() ) ) {

            // Admin page.
            $admin_page = Admin_Builder::instance()->create_admin_page(
                array(
                    'name' => WP_Helper::sk__( 'Tools' ),
                    'title' => WP_Helper::sk__( 'Tools' ),
                    'id' => WP_PLoad::WP_SK_OPTIONS_SUFFIX_PARAM.'_tools',
                    'capability' => 'manage_options',
                    'parent_id' => WP_PLoad::WP_SK_OPTIONS_SUFFIX_PARAM.'_dashboard',
                    'icon' => 'dashicons-smiley', // Menu icon for top level menus only https://developer.wordpress.org/resource/dashicons/#editor-paste-word
                    'position' => 11,
                    'desc' => 'Some handy tools for website design',
                    'use_reset' => true,
                    'save' => WP_Helper::sk__( 'Save' ), // Text for save button
                    'reset' => WP_Helper::sk__( 'Reset' ), // Text for reset button
                    'reset_question' => WP_Helper::sk__( 'Do you really want to reset form?' ), // Question asked to confirm reset
                    'use_tabs' => false,
                )
            );

            // Create all tools fields
            do_action( 'sukellos_fw/admin/create_tools_fields', $admin_page );

        }
    }
}