<?php

namespace Sukellos\WPFw;

use Sukellos\WPFw\AdminBuilder\EDD_Licensing_Manager;
use Sukellos\WPFw\Utils\WP_Admin_Notices_Manager;
use Sukellos\WPFw\Utils\WP_Helper;
use Sukellos\WPFw\Utils\WP_Log;
use Sukellos\WPFw\Utils\WP_Sukellos_Plugins_Manager;

defined( 'ABSPATH' ) or exit;

/**
 * Plugin activation Controler
 * @see https://www.php.net/manual/fr/language.oop5.traits.php
 *
 * @since 1.0.0
 */
trait Plugin_Activation_Control {

    private $plugin_file = null;
    private $plugin = null;

    // Plugin info
    private $plugin_info = array();

    protected $woocommerce_required = false;
    protected $sukellos_fw_required = true;
    protected $sukellos_fw_admin_builder_pro_denied = false;

    /***
     * Minimum PHP / WP / WC versions required by plugin
     * Values may be overridden for each plugin need
     */
    protected $min_wc_version = '4.9.1';
    protected $min_sukellos_fw_version = '1.0.0';

    /**
     * Init activation control (hooks)
     */
    public function init_activation_control( $plugin_file ) {

        $this->plugin_file = $plugin_file;

        // Respecting hook triggering order
        register_activation_hook( $plugin_file, array( $this, 'activate' ) );
        register_deactivation_hook( $plugin_file, array( $this, 'deactivate' ) );
        register_uninstall_hook( $plugin_file, array( $this, 'uninstall' ) );

        // Init plugin info
        $default_headers = array(
            'Plan'        => 'Plan',
            'Name'        => 'Plugin Name',
            'PluginURI'   => 'Plugin URI',
            'Version'     => 'Version',
            'Description' => 'Description',
            'Author'      => 'Author',
            'AuthorURI'   => 'Author URI',
            'TextDomain'  => 'Text Domain',
            'DomainPath'  => 'Domain Path',
            'Network'     => 'Network',
            'RequiresWP'  => 'Requires at least',
            'RequiresPHP' => 'Requires PHP',
            'UpdateURI'   => 'Update URI',
            // Site Wide Only is deprecated in favor of Network.
            '_sitewide'   => 'Site Wide Only',
        );
        $this->plugin_info = get_file_data( $plugin_file, $default_headers, 'plugin' );

        // Init advanced plugin infos
        $this->plugin_info[ WP_PLoad::PLUGIN_DIR_URL ] = plugin_dir_url( $plugin_file );
        $this->plugin_info[ WP_PLoad::PLUGIN_DIR_PATH ] = plugin_dir_path( $plugin_file );
        $this->plugin_info[ WP_PLoad::PLUGIN_BASENAME ] = plugin_basename( $plugin_file );
        $this->plugin_info[ WP_PLoad::OPTIONS_SUFFIX_PARAM ] = str_replace('-', '_', $this->plugin_info[ WP_PLoad::TEXT_DOMAIN ]);

        // Order is: plugins_loaded, after_setup_theme, init, admin_menu, customize_register, admin_enqueue_scripts, wp_enqueue_scripts, admin_notices, wp_head, wp_ajax...
        // See https://codex.wordpress.org/Action_Reference#Actions_Run_During_a_Typical_Request

        // Wordpress / admin_init: Fires as an admin screen or script is being initialized.
        // Here, checks the environment on loading WordPress, just in case the plugin is activated in a weird way, or the versions change after activation.
        add_action('admin_init', array($this, 'action_admin_init'), 10 );
    }

    /**
     * Plugin must be registered as soon as possible
     * @param $plugin
     */
    private function register_plugin( $plugin ) {

        $this->plugin = $plugin;
    }


    /** Getters */
    protected function get_min_php_version() { return $this->plugin_info[ WP_PLoad::REQUIRES_PHP ]; }
    protected function get_min_wp_version() { return $this->plugin_info[ WP_PLoad::REQUIRES_WP ]; }
    protected function get_min_wc_version() { return $this->min_wc_version; }
    protected function get_min_sukellos_fw_version() { return $this->min_sukellos_fw_version; }

    /**
     * Checks all versions compatibilities
     *
     * @return boolean
     */
    private function is_environment_compatible() {

        if ( $this->sukellos_fw_admin_builder_pro_denied ) {

            if ( in_array( 'sukellos-admin-builder/sukellos-admin-builder.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

                WP_Admin_Notices_Manager::instance()->add_admin_notice( '', WP_Admin_Notices_Manager::TYPE_ERROR, sprintf(
                    WP_Helper::sk__( '%s is not active, because Sukellos Fw & Admin Builder Pro is already activated.' ),
                    '<strong>' . $this->plugin_info[ WP_PLoad::NAME ] . '</strong>'
                ) );
                return false;
            }
        }
        if ( $this->sukellos_fw_required ) {

            if ( !in_array( 'sukellos-admin-builder/sukellos-admin-builder.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

                WP_Admin_Notices_Manager::instance()->add_admin_notice( '', WP_Admin_Notices_Manager::TYPE_ERROR, sprintf(
                    WP_Helper::sk__( '%s is not active, as it requires Sukellos Fw to be installed. Please install Sukellos Fw with version %s or higher.' ),
                    '<strong>' . $this->plugin_info[ WP_PLoad::NAME ] . '</strong>',
                    $this->get_min_sukellos_fw_version()
                ) );
                return false;
            }

            if ( version_compare( '1.0.0', $this->get_min_sukellos_fw_version() ) === -1 ) {

                WP_Admin_Notices_Manager::instance()->add_admin_notice( 'Update required', WP_Admin_Notices_Manager::TYPE_ERROR, sprintf(
                    WP_Helper::sk__( '%s is not active, as it requires Sukellos Fw version %s or higher. Please %supdate Sukellos Fw &raquo;%s' ),
                    '<strong>' . $this->plugin_info[ WP_PLoad::NAME ] . '</strong>',
                    $this->get_min_sukellos_fw_version(),
                    '<a href="' . esc_url( $this->get_update_url() ) . '">', '</a>'
                ) );
                return false;
            }
        }
        if ( $this->woocommerce_required ) {

            if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

                WP_Admin_Notices_Manager::instance()->add_admin_notice( 'install_woocommerce', WP_Admin_Notices_Manager::TYPE_ERROR, sprintf(
                    WP_Helper::sk__( '%s is not active, as it requires WooCommerce to be installed. Please install WooCommerce with version %s or higher.' ),
                    '<strong>' . $this->plugin_info[ WP_PLoad::NAME ] . '</strong>',
                    $this->get_min_wc_version()
                ) );
                return false;
            }

            if ( defined('WC_VERSION') && version_compare( WC_VERSION, $this->get_min_wc_version(), '<' ) ) {

                WP_Admin_Notices_Manager::instance()->add_admin_notice( 'update_woocommerce', WP_Admin_Notices_Manager::TYPE_ERROR, sprintf(
                    WP_Helper::sk__( '%s is not active, as it requires WooCommerce version %s or higher. Please %supdate WooCommerce &raquo;%s' ),
                    '<strong>' . $this->plugin_info[ WP_PLoad::NAME ] . '</strong>',
                    $this->get_min_wc_version(),
                    '<a href="' . esc_url( admin_url( 'update-core.php' ) ) . '">', '</a>'
                ) );
                return false;
            }
        }

        if ( version_compare( PHP_VERSION, $this->get_min_php_version(), '<' ) ) {

            WP_Admin_Notices_Manager::instance()->add_admin_notice( 'update_php', WP_Admin_Notices_Manager::TYPE_ERROR, sprintf(
                WP_Helper::sk__( '%s is not active, as it requires PHP version %s or higher. Please update PHP' ),
                '<strong>' . $this->plugin_info[ WP_PLoad::NAME ] . '</strong>',
                $this->get_min_php_version()
            ) );
            return false;
        }

        if ( version_compare( get_bloginfo( 'version' ), $this->get_min_wp_version(), '<' ) ) {

            WP_Admin_Notices_Manager::instance()->add_admin_notice( 'update_wordpress', WP_Admin_Notices_Manager::TYPE_ERROR, sprintf(
                WP_Helper::sk__( '%s is not active, as it requires WordPress version %s or higher. Please %supdate WordPress &raquo;%s' ),
                '<strong>' . $this->plugin_info[ WP_PLoad::NAME ] . '</strong>',
                $this->get_min_wp_version(),
                '<a href="' . esc_url( admin_url( 'update-core.php' ) ) . '">', '</a>'
            ) );
            return false;
        }


        return true;
    }


    /**
     * Deactivates the plugin.
     *
     * @since 1.0.0
     */
    private function deactivate_plugin() {

        deactivate_plugins( $this->plugin_info[ WP_PLoad::PLUGIN_BASENAME ] );

        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }
    }

    /**
     *          ===============
     *      =======================
     *  ============ HOOKS ===========
     *      =======================
     *          ===============
     */

    /**
     * Checks the server environment and other factors and deactivates plugins as necessary.
     *
     * Based on http://wptavern.com/how-to-prevent-wordpress-plugins-from-activating-on-sites-with-incompatible-hosting-environments
     *
     * @since 1.0.0
     */
    public function activate() {

        if ( ! $this->is_environment_compatible() ) {

            $this->deactivate_plugin();
//            wp_die( WP_Basic_Plugin_DISPLAYED_NAME . ' could not be activated. ' . $this->get_environment_message() );
        }

        if ( is_null( $this->plugin ) ) return;

        //
        // Init Custom Post Types
        // Flushing Rewrite on Activation #Flushing Rewrite on Activation
        // To get permalinks to work when you activate the plugin use the following example, paying attention to how my_cpt_init() is called in the register_activation_hook callback
        // See https://developer.wordpress.org/reference/functions/register_post_type/
        //
        $this->plugin->init_custom_post_types();

        // ATTENTION: This is *only* done during plugin activation hook in this example!
        // You should *NEVER EVER* do this on every page load!!
        flush_rewrite_rules();
    }
    /**
     * Deactivate plugin can be used to purge data, drop tables...
     *
     * @since 1.0.0
     */
    public function deactivate() {

        WP_Log::instance()->deactivate();

        EDD_Licensing_Manager::instance()->unregister_edd_license( $this->plugin_info[ WP_PLoad::PLUGIN_BASENAME ] );
    }
    /**
     * Uninstall plugin can be used to purge data, drop tables...
     *
     * @since 1.0.0
     */
    public function uninstall() {
    }

    public function action_init() {

        if ( is_null( $this->plugin ) ) return;

        //
        // Init Custom Post Types
        // Flushing Rewrite on Activation #Flushing Rewrite on Activation
        // To get permalinks to work when you activate the plugin use the following example, paying attention to how my_cpt_init() is called in the register_activation_hook callback
        // See https://developer.wordpress.org/reference/functions/register_post_type/
        //
        $this->plugin->init_custom_post_types();
    }

    /**
     * Checks the environment on loading WordPress, just in case the environment changes after activation.
     *
     * @since 1.0.0
     */
    public function action_admin_init() {

        $plugin_base_name = $this->plugin_info[ WP_PLoad::PLUGIN_BASENAME ];
        if ( ! $this->is_environment_compatible() && is_plugin_active( $plugin_base_name ) ) {

            $this->deactivate_plugin();
        }
    }
}
