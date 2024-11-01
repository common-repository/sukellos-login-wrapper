<?php

namespace Sukellos\WPFw\AdminBuilder\Fields;

use Sukellos\WPFw\AdminBuilder\EDD_Licensing_Manager;
use Sukellos\WPFw\AdminBuilder\Item_Type;
use Sukellos\WPFw\AdminBuilder\Inc\EDD_SL_Plugin_Updater;
use Sukellos\WPFw\Utils\WP_Log;
use Sukellos\WPFw\WP_PLoad;
use Sukellos\WPFw\Utils\WP_Helper;
use Sukellos\WPFw\Utils\WP_Admin_Notices_Manager;

defined( 'ABSPATH' ) or exit;

/**
 * EDD Licensing field
 * @see https://docs.easydigitaldownloads.com/article/1096-software-licensing---updater-implementation-for-wordpress-plugins
 *
 * @since 1.0
 * @type edd-licensing
 */
class Edd_Licensing_Field extends Field {

    // Text add to field id to store license status
    const LICENCE_STATUS_SUFFIX = '_status';
    const LICENCE_VALIDITY_SUFFIX = '_validity';

    const STATUS_ACTION_PREFIX = 'hidden_edd_licensing_';

    /**
     * Default settings specific for this field
     * @var array
     */
    private $default_specific_field_settings = array(

        'type' => Item_Type::EDD_LICENSING,

        /**
         * A unique ID for the option used to store the licence validity status.
         * This option can have following value : "valid" or "invalid" (after activation), "deactivated" or "failed" (after deactivation)
         * If this param is missing, a ID will be generated, adding '_validity' as suffix at the end of this field ID
         *
         * @since 1.0.0
         * @var string
         */
        'validity_id' => '',

        /**
         * This is the URL our updater / license checker pings. This should be the URL of the site with EDD installed
         *
         * @since 1.0.0
         * @var string
         */
        'store_url' => '',

        /**
         * The download ID for the product in Easy Digital Downloads
         *
         * @since 1.0
         * @var int
         */
        'download_id' => '',

        /**
         * The name of the product in Easy Digital Downloads
         *
         * @since 1.0.0
         * @var string
         */
        'download_name' => '',

        /**
         * The plugin file
         * This field is MANDATORY, and must contain the full plugin file, eg wp-sukellos-fw/wp-sukellos-fw.php
         * This parameter can be set using WP_PLoad->get_plugin_file()
         *
         * @since 1.0.0
         * @var string
         */
        'plugin_file' => '',

        /**
         * Used to override the Wordpress update checker
         *
         * @since 1.0.0
         * @var bool
         */
        'wp_override' => true,

        /**
         * Text for activate button
         *
         * @since 1.0.0
         */
        'activate' => 'Activate License',

        /**
         * Text for deactivate button
         *
         * @since 1.0.0
         */
        'deactivate' => 'Deactivate License',

        /**
         * If store url is behind a basic htaccess password protection
         * ATTENTION: update process cannot work using basic authorization, ONLY version check and license activation
         *
         * @since 1.0.0
         * @var bool
         */
        'use_basic_authorization' => false,

        /**
         * User name for basic htaccess password protection
         *
         * @since 1.0.0
         */
        'basic_authorization_name' => '',

        /**
         * Passwordfor basic htaccess password protection
         *
         * @since 1.0.0
         */
        'basic_authorization_password' => '',

    );

    /**
     * Constructor
     *
     * @param $settings
     */
    public function __construct( $settings, $handler ) {

        // Force need_form and global_form_independent
        $settings['need_form'] = true;
        $settings['global_form_independent'] = true;

        parent::__construct( $settings, $handler );

        EDD_Licensing_Manager::instance()->register_edd_license( $this->get_settings() );
    }

    /**
     * Get the default field settings
     * @return array
     */
    protected function get_default_specific_field_settings() {

        return $this->default_specific_field_settings;
    }

	/**
	 * Display for options and meta
     * @param bool $echo Whether to display or return string, default true
     */
    public function render_specific_body( $echo=true ) {

		// If hidden, takes precedence over password field.
		$input_type = $this->get_hidden() ? 'hidden' : 'text';

        $html_content = sprintf('<input class="regular-text" name="%s" id="%s" type="%s" value="%s" />',
			$this->get_id(),
			$this->get_id(),
            $input_type,
			esc_attr( $this->get_value() )
		);

        // Add status
        $status  = get_option( $this->get_id().self::LICENCE_STATUS_SUFFIX, '' );
        if ( $status !== '' ) {

            $class = 'status-valid';
            if ( 'Valid' !== $status ) {

                $class = 'status-invalid';
            }

            $html_content .= '<p class="description '.$class.'">'.$status.'</p>';

            // Special case : Deactivate is displayed only once
            if ( WP_Helper::sk__( 'Deactivated' ) === $status ) {

                update_option( $this->get_id().self::LICENCE_STATUS_SUFFIX, '' );
            }
            // Deactivate message status for the next time if field is empty
            if ( '' === $this->get_value() ) {

                update_option( $this->get_id().self::LICENCE_STATUS_SUFFIX, '' );
            }
        }

        if ( $echo ) {

            echo $html_content;
        } else {

            return $html_content;
        }
	}


    /**
     * Add activate / deactivate button
     * Overridden to customize button depending on current license status
     *
     * @param false $use_br
     * @return string
     */
    protected function render_buttons() {

        // Get the status related to this license id
        $field_validity_id = $this->get_validity_id();
        $validity  = get_option( $field_validity_id );
        WP_Log::debug( 'Edd_Licensing_Field->render_buttons ', ['$status'=>$validity] );

        // Init label and action depending on status
        $status_action = 'edd_license_deactivate';
        $status_label = $this->get_deactivate();

        if ( 'valid' !== $validity ) {

            $status_action = 'edd_license_activate';
            $status_label = $this->get_activate();
        }

        // Display button
        $html_content = '
                <input type="hidden" name="'.self::STATUS_ACTION_PREFIX.$this->get_id().'" id="'.self::STATUS_ACTION_PREFIX.$this->get_id().'" value="'.$status_action.'">
                <div class="sk-admin-builder-submit-buttons">
                    <button name="action" value="save_fields" class="button button-primary">
                            '.$status_label.'
                    </button>
                ';


        $html_content .= '
                </div>
            ';

        return $html_content;
    }

    /**
     * Override settings bean getter method for validity_id
     * @return mixed|string
     */
    public function get_validity_id() {

        $settings = $this->get_settings();

        // Format validity id
        if ( !isset( $settings['validity_id'] ) || ( $settings['validity_id'] === '' ) ) {

            return $this->get_id().self::LICENCE_VALIDITY_SUFFIX;
        } else {

            return $settings['validity_id'];
        }
    }

    /**
     * Save field value depending on its specific type
     * Handler implementation
     * Overridden to trigger activate / deactivate actions
     */
    public function save() {

        WP_Log::debug( 'Edd_Licensing_Field->save ', ['$_POST'=>$_POST] );

        // Check for specific status action
        if ( !isset( $_POST[ ''.$this->get_id() ] )
            || !isset( $_POST[ self::STATUS_ACTION_PREFIX.$this->get_id() ] )
            || ( '' === $_POST[ ''.$this->get_id() ] )
        ) {

            return;
        }
        $license = $_POST[ ''.$this->get_id() ];
        $status_action = $_POST[ self::STATUS_ACTION_PREFIX.$this->get_id() ];
//        update_option( $this->get_id().self::LICENCE_STATUS_SUFFIX, $message );

        // Format validity id
        $field_validity_id = $this->get_validity_id();

        // Activate ou deactivate, before saving field into options table
        if ( 'edd_license_activate' === $status_action ) {

            // $license_data->license will be either "valid" or "invalid"
            $activate_result = $this->edd_activate_license( $license );
            WP_Log::debug( 'Edd_Licensing_Field->save - edd_activate_license', ['result'=>$activate_result] );
            if ( !$activate_result ) {

                update_option( $field_validity_id, 'invalid' );
            } else {

                update_option( $field_validity_id, 'valid' );
            }

        } else if ( 'edd_license_deactivate' === $status_action ) {

            $deactivate_result = $this->edd_deactivate_license( $license );
            WP_Log::debug( 'Edd_Licensing_Field->save - edd_deactivate_license', ['result'=>$deactivate_result] );

            // $license_data->license will be either "deactivated" or "failed"
            if ( !$deactivate_result ) {

                update_option( $field_validity_id, 'failed' );
            } else {

                update_option( $field_validity_id, 'deactivated' );
            }

            // Reset the value
            $_POST[ ''.$this->get_id() ] = '';

        } else {

            return;
        }

        if ( is_null( $this->handler ) ) {

            return;
        }

        $this->handler->save();

        /**
         * Action: sukellos_fw/admin_builder/field/field_saved_{$field_id}
         * Called just after field has been saved
         *
         * @param $value the raw value
         */
        do_action( 'sukellos_fw/admin_builder/field/field_saved_'.$this->get_id(), $this->get_value() );
    }


    /**
     * Activates the license key.
     *
     * @return void false if an error occurred, else "valid" or "invalid"
     */
    private function edd_activate_license( $license ) {

        // data to send in our API request
        $api_params = array(
            'edd_action'  => 'activate_license',
            'license'     => $license,
            'item_id'     => $this->get_download_id(),
            'item_name'   => rawurlencode( $this->get_download_name() ), // the name of our product in EDD
            'url'         => home_url(),
            'environment' => function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production',
        );
        WP_Log::debug( 'Edd_Licensing_Field->edd_activate_license', ['$api_params'=>$api_params] );

        // Check is basic authorization needed
        $use_basic_auth = $this->get_use_basic_authorization();
        $auth_header = array();
        if ( $use_basic_auth ) {

            $auth_header[ 'Authorization' ] = 'Basic ' . base64_encode( $this->get_basic_authorization_name().':'.$this->get_basic_authorization_password() );
        }

        // Call the custom API.
        $response = wp_remote_post(
            $this->get_store_url(),
            array(
                'timeout'   => 15,
                'sslverify' => false,
                'body'      => $api_params,
                'headers' => $auth_header,
            )
        );
        WP_Log::debug( 'Edd_Licensing_Field->edd_activate_license', ['$response'=>$response] );

        // make sure the response came back okay
        if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

            if ( is_wp_error( $response ) ) {
                $message = $response->get_error_message();
            } else {
                $message = WP_Helper::sk__( 'An error occurred, please try again.' );
            }
        } else {

            $license_data = json_decode( wp_remote_retrieve_body( $response ) );

            if ( false === $license_data->success ) {

                switch ( $license_data->error ) {

                    case 'expired':
                        $message = sprintf(
                        /* translators: the license key expiration date */
                            __( 'Your license key expired on %s.' ),
                            date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
                        );
                        break;

                    case 'disabled':
                    case 'revoked':
                        $message = WP_Helper::sk__( 'Your license key has been disabled.' );
                        break;

                    case 'missing':
                        $message = WP_Helper::sk__( 'Invalid license.' );
                        break;

                    case 'invalid':
                    case 'site_inactive':
                        $message = WP_Helper::sk__( 'Your license is not active for this URL.' );
                        break;

                    case 'item_name_mismatch':
                        /* translators: the plugin name */
                        $message = sprintf( WP_Helper::sk__( 'This appears to be an invalid license key for %s.' ), $this->get_download_name() );
                        break;

                    case 'no_activations_left':
                        $message = WP_Helper::sk__( 'Your license key has reached its activation limit.' );
                        break;

                    default:
                        $message = WP_Helper::sk__( 'An error occurred, please try again.' );
                        break;
                }
            }
        }
        WP_Log::debug( 'Edd_Licensing_Field->edd_activate_license', ['$message'=>$message] );

        // Check if anything passed on a message constituting a failure
        if ( ! empty( $message ) ) {

            update_option( $this->get_id().self::LICENCE_STATUS_SUFFIX, $message );
            return false;
        }

        // $license_data->license will be either "valid" or "invalid"
        update_option( $this->get_id().self::LICENCE_STATUS_SUFFIX, WP_Helper::sk__( 'Valid' ) );
        return $license_data->license;
    }


    /**
     * Deactivates the license key.
     * This will decrease the site count.
     *
     * @return void
     */
    private function edd_deactivate_license( $license ) {

        // data to send in our API request
        $api_params = array(
            'edd_action'  => 'deactivate_license',
            'license'     => $license,
            'item_id'     => $this->get_download_id(),
            'item_name'   => rawurlencode( $this->get_download_name() ), // the name of our product in EDD
            'url'         => home_url(),
            'environment' => function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production',
        );

        // Check is basic authorization needed
        $use_basic_auth = $this->get_use_basic_authorization();
        $auth_header = array();
        if ( $use_basic_auth ) {

            $auth_header[ 'Authorization' ] = 'Basic ' . base64_encode( $this->get_basic_authorization_name().':'.$this->get_basic_authorization_password() );
        }

        // Call the custom API.
        $response = wp_remote_post(
            $this->get_store_url(),
            array(
                'timeout'   => 15,
                'sslverify' => false,
                'body'      => $api_params,
                'headers' => $auth_header,
            )
        );

        // make sure the response came back okay
        if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

            if ( is_wp_error( $response ) ) {

                $message = $response->get_error_message();
            } else {

                $message = WP_Helper::sk__( 'An error occurred, please try again.' );
            }

            // Check if anything passed on a message constituting a failure
            if ( ! empty( $message ) ) {

                update_option( $this->get_id().self::LICENCE_STATUS_SUFFIX, $message );
                return false;
            }
        }

        // decode the license data
        $license_data = json_decode( wp_remote_retrieve_body( $response ) );

        // $license_data->license will be either "deactivated" or "failed"
        update_option( $this->get_id().self::LICENCE_STATUS_SUFFIX, WP_Helper::sk__( 'Deactivated' ) );
        return $license_data->license;
    }


    /**
     * Checks if a license key is still valid.
     * The updater does this for you, so this is only needed if you want
     * to do something custom.
     *
     * @return void false if error occured, else 'valid', or 'invalid'
     */
    private function edd_check_license( $license ) {

        $api_params = array(
            'edd_action'  => 'check_license',
            'license'     => $license,
            'item_id'     => $this->get_download_id(),
            'item_name'   => rawurlencode( $this->get_download_name() ),
            'url'         => home_url(),
            'environment' => function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production',
        );

        // Check is basic authorization needed
        $use_basic_auth = $this->get_use_basic_authorization();
        $auth_header = array();
        if ( $use_basic_auth ) {

            $auth_header[ 'Authorization' ] = 'Basic ' . base64_encode( $this->get_basic_authorization_name().':'.$this->get_basic_authorization_password() );
        }

        // Call the custom API.
        $response = wp_remote_post(
            $this->get_store_url(),
            array(
                'timeout'   => 15,
                'sslverify' => false,
                'body'      => $api_params,
                'headers' => $auth_header,
            )
        );

        if ( is_wp_error( $response ) ) {

            return false;
        }

        $license_data = json_decode( wp_remote_retrieve_body( $response ) );
        return $license_data->license;
    }

    /**
     *          ===============
     *      =======================
     *  ============ HOOKS ===========
     *      =======================
     *          ===============
     */

    /**
     * Filter : sukellos_fw/admin_builder/field/set_cleaned_value_{field_id}
     * Used to clean up a value before updating it in field
     *
     * @param $value the raw value
     */
    public function filter_field_set_cleaned_value( $value ) {
        return $value;
    }

    /**
     * Enqueue the scripts
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function enqueue_scripts() {

        wp_enqueue_style( 'sk-admin-builder-edd-licensing-field-style', plugins_url( '../../css/sk-field-edd-licensing.css', __FILE__ ) );
    }

    /**
     * Load the javascript
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function init_scripts() {}
}
