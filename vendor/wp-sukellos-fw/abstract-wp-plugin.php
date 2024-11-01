<?php

namespace Sukellos\WPFw;

defined( 'ABSPATH' ) or exit;

/**
 * Wordpress Generic Plugin
 *
 * @version 1.0.0
 */
abstract class WP_Plugin {

    use License_Restriction;

    /** @var bool whether admin is enabled */
    private $admin_enabled = false;

    /**
     * Checks if admin is enabled in settings.
     *
     * @since 1.0.0
     *
     * @return bool
     */
    public function admin_enabled() {

        return $this->admin_enabled;
    }
    
    /**
     * Enable admin to plug admin hooks
     *
     * @since 1.0.0
     *
     * @return bool
     */
    public function enable_admin() {

        $this->admin_enabled = true;
    }

    /**
     * Plugin activated method. Perform any activation tasks here.
     * Note that this _does not_ run during upgrades.
     *
     * @since 1.0.0
     * @deprecated 5.2.0
     */
    public function activate() {

        wc_deprecated_function( __METHOD__, '5.2.0' );
    }


    /**
     * Plugin deactivation method. Perform any deactivation tasks here.
     *
     * @since 1.0.0
     * @deprecated 5.2.0
     */
    public function deactivate() {

        wc_deprecated_function( __METHOD__, '5.2.0' );
    }

    /**
     * Initializes the custom post types.
     * Called on init and on activation hooks
     * 
     * Must be override to create custom post types for the plugin
     *
     * @since 1.0.0
     */
    abstract public function init_custom_post_types();
}
