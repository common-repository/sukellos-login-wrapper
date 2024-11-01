<?php

namespace Sukellos\WPFw\AdminBuilder;

use Sukellos\WPFw\AdminBuilder\Fields\Sanitize_Manager;
use Sukellos\WPFw\Singleton;
use Sukellos\WPFw\Utils\WP_Admin_Notices_Manager;
use Sukellos\WPFw\Utils\WP_Log;

defined( 'ABSPATH' ) or exit;

/**
 * Admin Builder Class - Used to create admin pages, tabs, options...
 *
 * Init hook 'sukellos_fw/admin_builder/build_admin' on wordpress hook 'init'
 * Only one Admin Builder instance that will contains all containers
 * Manages containers creation (AdminPage, UserProfile, Metabox...)
 *
 * @since 1.0.0
 */
class Admin_Builder {

    // Use Trait Singleton
    use Singleton;

    const ADMIN_PAGE = 'admin-page';

    /**
     * All main containers (admin pages, meta boxes, user profile section)
     *
     * Foreach specific containers : <id> => Container
     */
    private $containers = array(
        self::ADMIN_PAGE => array(),
    );

    /**
     * Default init method called when instance created
     * This method can be overridden if needed.
     *
     * @since 1.0.0
     * @access protected
     */
    public function init() {

        // Create the admin page
        // Order is: plugins_loaded, after_setup_theme, init, admin_menu, admin_enqueue_scripts, wp_enqueue_scripts, admin_notices, wp_head, wp_ajax...

        // This hook must be called AFTER all other init to allow using Wordpress info while building admin...
        add_action( 'init', array( $this, 'action_init' ), 100 );

        add_action( 'admin_enqueue_scripts', array( $this, 'action_admin_enqueue_scripts' ) );

        WP_Log::info( 'Admin_Builder->init OK');
    }

    /**
     * Create an admin page
     *
     * @since 1.0.0
     *
     * @param array $settings The arguments for creating the admin page.
     *
     * @return Admin_Page_Simple The created admin page
     */
    public function create_admin_page( $settings ) {

        $settings['type'] = self::ADMIN_PAGE;
        $container = $this->create_container( $settings );

        return $container;
    }

    /**
     * Create a meta box
     *
     * @since 1.0.0
     *
     * @param array $settings The arguments for creating the meta box.
     *
     * @return Meta_Box The created meta box
     */
    public function create_meta_box( $settings ) {

        // Not supported in Basic version
        return null;
    }

    /**
     * Create a user profile
     *
     * @since 1.0.0
     *
     * @param array $settings The arguments for creating the user profile.
     *
     * @return User_profile The created meta box
     */
    public function create_user_profile( $settings ) {

        // Not supported in Basic version
        return null;
    }

    /**
     * Creates a container (e.g. admin page, meta box, user profile section) depending
     * on the `type` parameter given in $settings
     *
     * @since 1.0.0
     *
     * @param array $settings The arguments for creating the container.
     */
    public function create_container( $settings ) {

        WP_Log::debug('Admin_Builder->create_container', ['settings' => $settings, 'POST' => $_POST, 'REQUEST'=> $_REQUEST]);

        // Verify that mandatories settings are presents
        $mandatory_settings = array( 'type', 'id', 'name' );
        foreach ( $mandatory_settings as $mandatory_setting ) {

            if ( !array_key_exists( ''.$mandatory_setting, $settings ) || is_null( $settings[''.$mandatory_setting] ) || ( $settings[''.$mandatory_setting] === '' ) ) {

                WP_Admin_Notices_Manager::instance()->add_admin_notice( 'create_container', WP_Admin_Notices_Manager::TYPE_ERROR, sprintf(
                    '%s needs a %s parameter.',
                    '<code>' . __FUNCTION__ . '</code>',
                    '<code>'.$mandatory_setting.'</code>'
                ) );
                return null;
            }
        }

        // Verify id unicity
        $container_id = $settings[ 'id' ];
        foreach ( $this->containers as $type => $specific_containers ) {

            if ( array_key_exists( $container_id, $specific_containers ) ) {

                WP_Admin_Notices_Manager::instance()->add_admin_notice( 'create_container', WP_Admin_Notices_Manager::TYPE_ERROR, sprintf(
                    '%s must be unique.',
                    '<code>id</code>'
                ) );
                return null;
            }
        }

        // Container
        $container = null;
        $type = strtolower( $settings['type'] );
        switch ( $type ) {

            case self::ADMIN_PAGE:

                // Type is depending on 'use_tabs' setting
                if ( array_key_exists( 'use_tabs', $settings ) && ( $settings[ 'use_tabs' ] === TRUE ) ) {

                    $container = new Admin_Page_Simple( $settings );
                } else {

                    $container = new Admin_Page_Simple( $settings );
                }
                break;
            default:
                WP_Admin_Notices_Manager::instance()->add_admin_notice( 'create_container', WP_Admin_Notices_Manager::TYPE_ERROR, sprintf(
                    'Container of type %s, does not exist.',
                    '<code>' . $type . '</code>'
                ) );
                return null;
        }

        // Store the container
        $this->containers[ ''.$type ][] = $container;

        return $container;
    }

    /**
     * Decorator function on Wordpress get_user_meta, used to sanitize a raw value, depending on field type
     *
     * @param int $user_id User ID.
     * @param string $user_meta The meta key to retrieve
     * @param Item_Type $field_type the type of the field value
     * @param bool $single Optional. Whether to return a single value. This parameter has no effect if `$key` is not specified. Default false.
     * @return mixed depending on the field type
     */
    public static function get_user_meta( $user_id, $user_meta, $field_type = Item_Type::TEXT, $single = false ) {

        $raw_value = get_user_meta( $user_id, $user_meta, $single );
        if ( FALSE === $raw_value ) {

            return $raw_value;
        }
        // Return the sanitized value
        return Sanitize_Manager::get_sanitized_value( $raw_value, $field_type );
    }

    /**
     * Decorator function on Wordpress get_user_meta, used to sanitize a raw value, depending on field type
     *
     * @param int $post_id Post ID.
     * @param string $post_meta The meta key to retrieve
     * @param Item_Type $field_type the type of the field value
     * @param bool $single Optional. Whether to return a single value. This parameter has no effect if `$key` is not specified. Default false.
     * @return mixed depending on the field type
     */
    public static function get_post_meta( $post_id, $post_meta, $field_type = Item_Type::TEXT, $single = false ) {

        $raw_value = get_post_meta( $post_id, $post_meta, $single );
        if ( FALSE === $raw_value ) {

            return $raw_value;
        }
        // Return the sanitized value
        return Sanitize_Manager::get_sanitized_value( $raw_value, $field_type );
    }

    /**
     * Decorator function on Wordpress get_option, used to sanitize a raw value, depending on field type
     *
     * @param string $option  Name of the option to retrieve. Expected to not be SQL-escaped.
     * @param Item_Type $field_type the type of the field value
     * @param mixed  $default Optional. Default value to return if the option does not exist.
     * @return mixed depending on the field type
     */
    public static function get_option( $option, $field_type = Item_Type::TEXT, $default = false ) {

        $raw_value = get_option( $option, $default );
        if ( FALSE === $raw_value ) {

            return $raw_value;
        }
        // Return the sanitized value
        return Sanitize_Manager::get_sanitized_value( $raw_value, $field_type );
    }

    /**
     *          ===============
     *      =======================
     *  ============ HOOKS ===========
     *      =======================
     *          ===============
     */

	/**
	 * Trigger the creation of the options
	 *
	 * @since 1.0.0
	 */
	public function action_init() {

		/**
         * Action: sukellos_fw/admin_builder/create_items
		 * Triggers the creation of options. Hook into this action and use the various create methods.
		 *
		 * @since 1.0.0
		 */
		do_action( 'sukellos_fw/admin_builder/create_items' );

		/**
         * Action: sukellos_fw/admin_builder/fields_created
		 * Fires immediately after options are created.
		 *
		 * @since 1.0.0
		 */
		do_action( 'sukellos_fw/admin_builder/fields_created' );
	}

    /**
     * Loads all the admin scripts used by Admin Builder
     *
     * @since 1.0.0
     *
     * @param string $hook The slug of admin page that called the enqueue.
     *
     * @return void
     */
    public function action_admin_enqueue_scripts( $hook ) {

        wp_enqueue_media();
        wp_enqueue_script( 'sk-admin-builder-serialize', plugins_url( 'js/min/serialize-min.js', __FILE__ ) );
        wp_enqueue_script( 'sk-admin-builder-styling', plugins_url( 'js/min/admin-styling-min.js', __FILE__ ) );
        wp_enqueue_style( 'sk-admin-builder-admin-styles', plugins_url( 'css/sk-admin-builder.css', __FILE__ ) );
    }
}
