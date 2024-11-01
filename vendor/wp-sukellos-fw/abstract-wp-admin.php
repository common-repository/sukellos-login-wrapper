<?php

namespace Sukellos\WPFw;

use Sukellos\WPFw\AdminBuilder\Admin_Builder;

defined( 'ABSPATH' ) or exit;

/**
 * Abstract Admin class.
 * 
 * This class embed all admin purpose. No WP hook needed, only plugin_loaded to initiate Admin Builder
 * 
 * @since 1.0.0
 */
abstract class WP_Plugin_Admin {

    use License_Restriction;

    // Must be set before instance() call to operate
    static protected $priority = 10;

    /**
     * Init admin
     */
    public function init() {

        // Init Admin builder Factory
        Admin_Builder::instance();
                
        add_action('admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), self::$priority );
        
        // Admin Builder will create TOP level admin menu

        add_action( 'sukellos_fw/admin_builder/create_items', array( $this, 'create_items' ), self::$priority );
    }

    /**
     * Get sub admin instances.
     * Must be used ONLY in main admin, to init sub admin instances correctly
     *
     * @return array of WP_Plugin_Admin instances
     */
    public function get_plugin_sub_admins() {

        return array();
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
    abstract public function admin_enqueue_scripts();

    /***
     * Admin Builder hook used to compose the admin page
     */
    abstract public function create_items();

    /**
     * Gets the plugin configuration URL
     * This is used to build actions list in plugins page
     * Leave blank ('') to disable
     * Sould be overidden by main admin instance
     *
     * @since 1.0.0
     *
     * @return string plugin settings URL
     */
    public function get_settings_url() {

        return '';
    }
}