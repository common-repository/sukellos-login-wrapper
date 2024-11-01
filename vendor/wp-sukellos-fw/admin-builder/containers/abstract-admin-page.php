<?php

namespace Sukellos\WPFw\AdminBuilder;

use Sukellos\WPFw\Utils\WP_Helper;
use Sukellos\WPFw\Utils\WP_Log;

defined( 'ABSPATH' ) or exit;

/**
 * Admin_Page inherit from Container Class
 *
 * It is used to create a admin page
 * It can contain Tabs and Fields
 *
 * Only one Admin Page allowed for one classical Wordpress admin page
 *
 * @since 1.0.0
 */
abstract class Admin_Page extends Admin_Container {

    protected $default_admin_page_settings = array(
        'title' => '', // Title displayed on the top of the admin panel
        'capability' => 'manage_options', // User role
        'icon' => 'dashicons-admin-generic', // Menu icon for top level menus only http://melchoyce.github.io/dashicons/
        'use_tabs' => false, // Set true to get a tabbed admin page
    );

	// Keep current Wordpress menu page id
    protected $wp_menu_page_hook_suffix = null;

    /**
     * Constructor
     *
     * @param $settings array containing following params
     */
    public function __construct( $settings ) {

        parent::__construct( $settings );

        $priority = -1;
        if ( !is_null( $this->get_parent_id() ) ) {

            $priority = intval( $this->get_position() );
        }
        add_action( 'admin_menu', array( $this, 'action_admin_menu' ), $priority );
    }

    /**
     * Get the default item settings
     * @return array
     */
    protected function get_default_specific_admin_container_settings() {

        // Merge all settings
        $settings = array_merge(
            $this->default_admin_page_settings,
            $this->get_default_specific_admin_page_settings()
        );
        return $settings;
    }

    /**
     * Get the default field settings
     * @return array
     */
    abstract protected function get_default_specific_admin_page_settings();

    /**
     * May be call to add params in query url before redirect, in case of saving
     */
    abstract protected function add_query_args( &$url );

    /**
     * Verify a few things before saving fields
     * @return mixed
     */
    public function verify_security() {

        WP_Log::debug(__METHOD__, ['POST'=>$_POST]);
		if ( empty( $_POST ) || empty( $_POST['action'] ) ) {

            WP_Log::debug(__METHOD__.' - POST is empty, aborted', []);
			return false;
		}

        WP_Log::debug(__METHOD__.' - Before get_current_screen', []);
		$screen = get_current_screen();
        WP_Log::debug(__METHOD__.' - After get_current_screen', []);
		if ( $screen->id != $this->wp_menu_page_hook_suffix ) {

            WP_Log::debug(__METHOD__.' - Not the current screen ', ['screen'=>$screen, 'wp_menu_page_hook_suffix'=>$this->wp_menu_page_hook_suffix]);
			return false;
		}

		if ( ! current_user_can( $this->get_capability() ) ) {

            WP_Log::debug(__METHOD__.' - User not authorized ', ['capability'=>$this->get_capability()]);
			return false;
		}

		if ( ! check_admin_referer( $this->get_id(), self::NONCE_PREFIX . '_nonce' ) ) {

            WP_Log::debug(__METHOD__.' - Admin referer not good ', ['nonce'=>self::NONCE_PREFIX . '_nonce']);
			return false;
		}

        WP_Log::debug(__METHOD__.' - Accepted', []);
		return true;
	}

    /**
     * Use form if at least one field created here or in a sub container
     */
    public function get_need_form() {

        // Get all items
        $items = $this->get_items();

        foreach( $items as $item ) {

            if ( $item->get_need_form() ) {

                return true;
            }
        }
        // No item using form found
        return false;
    }

    /**
     *          ===============
     *      =======================
     *  ============ HOOKS ===========
     *      =======================
     *          ===============
     */

    /**
     * Sukellos Credits
     */
    public function action_add_credits() {

        WP_Log::debug('Admin_Page->action_add_credits called');

        add_filter( 'admin_footer_text', array( $this, 'filter_add_credit' ) );
    }

    /**
     * Sukellos Credits
     */
    public function filter_add_credit() {

        return WP_Helper::sk__( "<em>Admin Page Created with <a href='https://sukellos.com/wordpress-sukellos-fw-admin-builder/'>Sukellos Fw & Admin Builder</a></em>" );
    }

    /**
     * admin_menu hook to build Wordpress admin menu
     */
    public function action_admin_menu() {

        WP_Log::debug('Admin_Page->action_admin_menu',
            [
                'get_name' => $this->get_name(),
                'get_parent_id' => (is_null( $this->get_parent_id())?'null':$this->get_parent_id()),
                'get_title' => $this->get_title(),
                'get_capability' => $this->get_capability(),
                'get_id' => $this->get_id(),
                'get_icon' => $this->get_icon(),
                'get_position' => (is_null( $this->get_position())?'null':$this->get_position()),
                'POST' => $_POST
            ]);

        // Parent menu
        if ( is_null( $this->get_parent_id() ) ) {

            $this->wp_menu_page_hook_suffix = add_menu_page( $this->get_name(),
                $this->get_title(),
                $this->get_capability(),
                $this->get_id(),
                array( $this, 'render' ),
                $this->get_icon(),
                $this->get_position() );

            WP_Log::debug('Admin_Page->action_admin_menu add_menu_page OK', ['wp_menu_page_hook_suffix' => $this->wp_menu_page_hook_suffix]);
        }
        // Sub menu
        else {

            $this->wp_menu_page_hook_suffix = add_submenu_page( $this->get_parent_id(),
                $this->get_name(),
                $this->get_title(),
                $this->get_capability(),
                $this->get_id(),
                array( $this, 'render' ) );

            WP_Log::debug('Admin_Page->action_admin_menu add_submenu_page OK', ['wp_menu_page_hook_suffix' => $this->wp_menu_page_hook_suffix]);
        }

        // Before rendering page, eventually save fields contained in $_POST request...
        add_action( 'load-' . $this->wp_menu_page_hook_suffix, array( $this, 'action_save_fields' ), 5 );
        add_action( 'load-' . $this->wp_menu_page_hook_suffix, array( $this, 'action_reset_fields' ), 5 );

        // ... and after that loading current values
        add_action( 'load-' . $this->wp_menu_page_hook_suffix, array( $this, 'action_load_fields' ), 10 );

        // Add hook to display Sukellos credits
        add_action( 'load-' . $this->wp_menu_page_hook_suffix, array( $this, 'action_add_credits' ) );

        WP_Log::debug('Admin_Page->action_admin_menu finished');
    }

    /**
     * Load fields
     */
    public function action_load_fields()
    {

        WP_Log::debug('Admin_Page->action_load_fields called', ['name' => $this->get_name()]);

        do_action( 'sukellos_fw/admin_builder/admin_page/load_field' );

        /**
         * Action : sukellos_fw/admin_builder/admin_page/fields_loaded
         * Called after the fields ar loaded
         */
        do_action( 'sukellos_fw/admin_builder/admin_page/fields_loaded' );
    }

    /**
     * Reset fields
     */
    public function action_reset_fields() {

        WP_Log::debug('Admin_Page->action_reset_fields called', ['name' => $this->get_name()]);

        if ( empty( $_POST ) || empty( $_POST['action'] ) ) {

            return;
        }

        if ( $_POST['action'] !== 'reset_fields' ) {

            return;
        }

        if ( ! $this->verify_security() ) {
            return;
        }

        $message = '';

        do_action( 'sukellos_fw/admin_builder/admin_page/reset_field' );

        $message = 'reset';

        /*
         * Redirect to prevent refresh saving
         */
        $url = wp_get_referer();

        //  urlencode to allow special characters in the url
        $url = add_query_arg( 'page', urlencode( $this->get_id() ), $url );

        if ( ! empty( $message ) ) {

            $url = add_query_arg( 'message', $message, $url );
        }

        $this->add_query_args( $url );

        /**
         * Action : sukellos_fw/admin_builder/admin_page/fields_reseted
         * Called after the fields ar reseted
         */
        do_action( 'sukellos_fw/admin_builder/admin_page/fields_reseted' );

        wp_redirect( esc_url_raw( $url ) );
    }

    /**
     * Save fields
     */
    public function action_save_fields() {

        WP_Log::debug(__METHOD__, ['name' => $this->get_name(), 'GET'=>$_GET, 'POST'=>$_POST]);

        // Is the container concerned ?
        if ( empty( $_GET ) || !isset( $_GET['page'] ) || ( $_GET['page'] != $this->get_id() ) ) {

            WP_Log::debug(__METHOD__.' - Container is not concerned by this action_save_fields hook', ['name' => $this->get_name(), 'GET'=>$_GET, 'id'=>$this->get_id()]);
            return;
        }

        if ( empty( $_POST ) || empty( $_POST['action'] ) ) {

            return;
        }
        if ( $_POST['action'] !== 'save_fields' ) {

            WP_Log::debug(__METHOD__.' - Not save_fields then not treated', []);
            return;
        }

        if ( ! $this->verify_security() ) {

            WP_Log::debug(__METHOD__.' - verify_security not OK, abort', []);
            return;
        }

        /*
         *  Save
         */
        do_action( 'sukellos_fw/admin_builder/admin_page/save_field' );

        /**
         * Action : sukellos_fw/admin_builder/admin_page/fields_saved
         * Called after the fields are saved
         */
        do_action( 'sukellos_fw/admin_builder/admin_page/fields_saved' );

        $message = apply_filters( 'sukellos_fw/admin_builder/admin_page/get_message', 'saved' );
        WP_Log::debug( __METHOD__, ['message'=>$message]);

        /*
         * Redirect to prevent refresh saving
         */
        $url = wp_get_referer();

        //  urlencode to allow special characters in the url
        $url = add_query_arg( 'page', urlencode( $this->get_id() ), $url );

        if ( ! empty( $message ) ) {

            $url = add_query_arg( 'message', $message, $url );
        }
        WP_Log::debug( __METHOD__, ['$url'=>$url]);

        $this->add_query_args( $url );


        wp_redirect( esc_url_raw( $url ) );
    }
}
