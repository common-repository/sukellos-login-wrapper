<?php

namespace Sukellos\WPFw;

defined( 'ABSPATH' ) or exit;

use Sukellos\WPFw\AdminBuilder\EDD_Licensing_Manager;
use Sukellos\WPFw\Utils\WP_Log;
use Sukellos\WPFw\Utils\WP_Admin_Notices_Manager;
use Sukellos\WPFw\Utils\WP_Sukellos_Plugins_Manager;

/**
 * The abstract loader class.
 * Class name is shortened in order to simplify uses of pluginfo() method
 *
 * @since 1.0.0
 */
abstract class WP_PLoad {

    use License_Restriction;
    use Plugin_Action_Links;
    use Plugin_Activation_Control;

    const WP_SK_TEXT_DOMAIN = 'wp-sukellos-fw';
    const WP_SK_OPTIONS_SUFFIX_PARAM = 'wp_sukellos_fw';
    const WP_SK_NAME = 'Sukellos Fw';

    /** Parameters for plugin data
     *
     * [Name] => WP Sukellos Fw
     * [PluginURI] => https://sukellos.com
     * [Version] => 1.0.0
     * [Description] => WP Sukellos Framework
     * [Author] => Sukellos
     * [AuthorURI] => https://sukellos.com
     * [TextDomain] => wp-sukellos-fw
     * [DomainPath] => /languages
     * [Network] =>
     * [RequiresWP] =>
     * [RequiresPHP] =>
     * [UpdateURI] =>
     * [Title] => WP Sukellos Fw
     * [AuthorName] => Sukellos
     * [update-supported] =>
     */
    const PLUGIN_DATA = 'plugin_data';
    const NAME = 'Name';
    const PLUGIN_URI = 'PluginURI';
    const VERSION = 'Version';
    const DESCRIPTION = 'Description';
    const AUTHOR = 'Author';
    const AUTHOR_URI = 'AuthorURI';
    const TEXT_DOMAIN = 'TextDomain';
    const DOMAIN_PATH = 'DomainPath';
    const NETWORK = 'Network';
    const REQUIRES_WP = 'RequiresWP';
    const REQUIRES_PHP = 'RequiresPHP';
    const UPDATE_URI = 'UpdateURI';
    const TITLE = 'Title';
    const AUTHOR_NAME = 'AuthorName';
    const UPDATE_SUPPORTED = 'update-supported';
    const PLUGIN_DIR_URL = 'plugin_dir_url';
    const PLUGIN_DIR_PATH = 'plugin_dir_path';
    const PLUGIN_BASENAME = 'plugin_basename';
    const OPTIONS_SUFFIX_PARAM = 'options_suffix_param';

    /**
     * Init loader performs environnements checks
     * This method MUST be overridden in child
     */
    public function init() {

        // Init plugin infos
        $plugin_file = $this->get_plugin_file();

        // Init activation control
        // Use Trait Plugin_Activation_Control
        $this->init_activation_control( $plugin_file );

        // Filters the list of action links displayed for a specific plugin in the Plugins list table.
        // Use Trait Plugin_Action_Links
        $this->init_action_links( $plugin_file );

        // Order is: plugins_loaded, after_setup_theme, init, admin_menu, customize_register, admin_enqueue_scripts, wp_enqueue_scripts, admin_notices, wp_head, wp_ajax...
        // See https://codex.wordpress.org/Action_Reference#Actions_Run_During_a_Typical_Request
        add_action('init', array($this, 'action_init'), 10 );

        // This hook must be called BEFORE Admin Builder create fields (prio 100) to apply restriction before creating them
        add_action( 'init', array( $this, 'action_init_apply_license_restriction' ), 9 );

        // Fires once WordPress has loaded, allowing scripts and styles to be initialized for the plugin (admin scripts enqueued in admin classes)
        add_action('wp_enqueue_scripts', array($this, 'action_wp_enqueue_scripts'), 10 );

        // Notice manager
        WP_Admin_Notices_Manager::instance();

        // Init logging
        WP_Log::instance()->register_text_domain( $this->get_text_domain(), true );

        // Launch plugin and admin instances
        $plugin = $this->get_plugin();
        $this->get_plugin_admin();

        $this->register_plugin( $plugin );

        // Register plugin info to plugins manager
        WP_Sukellos_Plugins_Manager::instance()->register_plugin( $this->get_basename(), $plugin_file, $this->plugin_info );

        // Init EDD Licensing manager
        EDD_Licensing_Manager::instance();

        WP_Log::notice( 'WP_PLoad - Plugin init successful ', ['plugin info'=>$this->plugin_info], $this->get_text_domain() );
    }

    /**
     * Get various information about the Plugin
     *
     * @param string $name name of information, one of PLUGIN_DATA_FILE, PLUGIN_DATA_PLUGIN_URL ...
     *
     * @return string, blank if unknown info
     */
    public function plugin_info( $name = null ) {

        switch ( $name ) {
            case self::PLUGIN_DATA:
            case self::NAME:
            case self::PLUGIN_URI:
            case self::VERSION:
            case self::DESCRIPTION:
            case self::AUTHOR:
            case self::AUTHOR_URI:
            case self::TEXT_DOMAIN:
            case self::DOMAIN_PATH:
            case self::NETWORK:
            case self::REQUIRES_WP:
            case self::REQUIRES_PHP:
            case self::UPDATE_URI:
            case self::TITLE:
            case self::AUTHOR_NAME:
            case self::UPDATE_SUPPORTED:
            case self::PLUGIN_DIR_URL:
            case self::PLUGIN_DIR_PATH:
            case self::PLUGIN_BASENAME:
            case self::OPTIONS_SUFFIX_PARAM:
                if ( array_key_exists( $name, $this->plugin_info )) {
                    return $this->plugin_info[ $name ];
                }
                break;
        }
        return '';
    }

    /**
     * Getters on plugin infos
     */
    public function get_name() {
        return $this->plugin_info( self::NAME );
    }
    public function get_version() {
        return $this->plugin_info( self::VERSION );
    }
    public function get_text_domain() {
        return $this->plugin_info( self::TEXT_DOMAIN );
    }
    public function get_plugin_uri() {
        return $this->plugin_info( self::PLUGIN_URI );
    }
    public function get_plugin_dir_url() {
        return $this->plugin_info( self::PLUGIN_DIR_URL );
    }
    public function get_plugin_dir_path() {
        return $this->plugin_info( self::PLUGIN_DIR_PATH );
    }
    public function get_basename() {
        return $this->plugin_info( self::PLUGIN_BASENAME );
    }
    public function get_options_suffix_param() {
        return $this->plugin_info( self::OPTIONS_SUFFIX_PARAM );
    }
    public function get_requires_wp() {
        return $this->plugin_info( self::REQUIRES_WP );
    }
    public function get_requires_php() {
        return $this->plugin_info( self::REQUIRES_PHP );
    }
    public function get_update_uri() {
        return $this->plugin_info( self::UPDATE_URI );
    }

    /**
     * Must be called in child Loader to get data from the first 8KB of the current file (cf get_file_data)
     * Template method
     */
    abstract public function get_plugin_file();

    /**
     * Get the plugin instance
     *
     * @since 1.0.0
     *
     * @return \WP_Plugin The extension main instance
     */
    abstract public function get_plugin();

    /**
     * Gets the admin instance.
     *
     * @since 1.0.0
     *
     * @return admin instance, or null is no admin enabled
     */
    abstract public function get_plugin_admin();

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

        // Check if a license restriction is applied to this plugin
        $valid_license = EDD_Licensing_Manager::instance()->check_edd_license_validity( $this->get_plugin_file() );
        WP_Log::debug( 'WP_PLoad - Licence validity check ', ['Licence valid?'=>($valid_license?'true':'false'), 'plugin base name' => $this->get_basename()], $this->get_text_domain() );
        $this->apply_license_restriction( !$valid_license, $this->get_basename() );

        // Launch plugin and admin instances
        $plugin = $this->get_plugin();
        $plugin_admin = $this->get_plugin_admin();

        // Apply restrictions on plugins & admins
        $plugin->apply_license_restriction( !$valid_license, $this->get_basename() );
        $plugin_admin->apply_license_restriction( !$valid_license, $this->get_basename() );

        // Get sub admins
        $sub_admins = $plugin_admin->get_plugin_sub_admins();
        foreach ( $sub_admins as $sub_admin ) {

            $sub_admin->apply_license_restriction( !$valid_license, $this->get_basename() );
        }
    }

    public function action_init() {

        if ( is_null( $this->get_plugin() ) ) return;

        //
        // Init Custom Post Types
        // Flushing Rewrite on Activation #Flushing Rewrite on Activation
        // To get permalinks to work when you activate the plugin use the following example, paying attention to how my_cpt_init() is called in the register_activation_hook callback
        // See https://developer.wordpress.org/reference/functions/register_post_type/
        //
        $this->get_plugin()->init_custom_post_types();
    }

    /** Abstract used to constraint child class */
    abstract public function action_wp_enqueue_scripts();

}