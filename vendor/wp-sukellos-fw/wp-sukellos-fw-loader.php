<?php

namespace Sukellos\WPFw;

defined( 'ABSPATH' ) or exit;


/**
 * The WP Sukellos Fw loader class.
 *
 * @since 1.0.0
 */
abstract class WP_Sukellos_Fw_Loader extends WP_PLoad {

    /**
     * Default init method called when instance created
     * This method MUST be overridden in child
     *
     * @since 1.0.0
     * @access protected
     */
    public function init() {

        $this->sukellos_fw_required = false;

        parent::init();
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
        return WP_Sukellos_Fw_Admin::instance();
    }

    /**
     * Gets the plugin update URL
     * This is used to link user when plugin need to be updated
     *
     * @since 1.0.0
     *
     * @return string plugin update URL
     */
    public function get_update_url() {

        return $this->get_plugin_uri();
    }

    /**
     * This is used to build actions list in plugins page
     * Leave blank ('') to disable
     *
     * @since 1.0.0
     *
     * @return string plugin settings URL
     */
    public function get_settings_url() {

        return $this->get_plugin_admin()->get_settings_url();
    }


    /**
     * This is used to build actions list in plugins page
     * Leave blank ('') to disable
     *
     * @since 1.0.0
     *
     * @return string documentation URL
     */
    public function get_documentation_url() {

        return 'https://wp-adminbuilder.com/tutorial/sukellos-fw-minimum-requirements/';
    }


    /**
     * This is used to build actions list in plugins page
     * Leave blank ('') to disable
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_support_url() {

        return $this->plugin_info( WP_PLoad::AUTHOR_URI ).'/contact';
    }


    /**
     * This is used to build actions list in plugins page
     * Leave blank ('') to disable
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_sales_page_url() {

        return $this->get_plugin_uri();
    }

    /**
     *          ===============
     *      =======================
     *  ============ HOOKS ===========
     *      =======================
     *          ===============
     */

    /**
     * This hook must be called AFTER Admin Builder create fields (prio 100) to allow using EDD Licensing fields to apply restriction
     */
    public function action_init_apply_license_restriction() {

        parent::action_init_apply_license_restriction();

        // Used to apply restriction on Sukellos internal admin pages, too
        $admin = WP_Sukellos_Fw_Admin::instance();

        $is_license_restricted = $this->is_license_restricted();
        $admin->apply_license_restriction( $is_license_restricted, $this->get_basename() );

        // Get sub admins
        $sub_admins = $admin->get_plugin_sub_admins();
        foreach ( $sub_admins as $sub_admin ) {

            $sub_admin->apply_license_restriction( $is_license_restricted, $this->get_basename() );
        }
    }

    /**
     * Used to enqueue styles and scripts
     */
    public function action_wp_enqueue_scripts() {

        // CSS
        //wp_enqueue_style('sukellos_fw_css', $this->get_plugin_dir_url().'assets/css/plugin.css', array(), '1.0', 'all');
    }
}