<?php

namespace Sukellos\WPFw\AdminBuilder;

use Sukellos\WPFw\Utils\WP_Log;

defined( 'ABSPATH' ) or exit;

/**
 * Bean based on settings parameters
 *
 * @since 1.0.0
 */
trait Settings_Bean {

    /**
     * Default settings
     * @var array
     */
    private $settings = array(

        /**
         * A unique ID
         * @since 1.0.0
         * @var string
         */
        'id' => '',
    );

    /**
     * Getter / setter: May be called to get a setting value
     * Eg.: get_name( 'defaultName' )
     *
     * @param $name name of the settings
     * @param $args default value
     * @return mixed|string the value of the setting
     */
    public function __call( $name, $args ) {

//        WP_Log::debug('Settings_Bean __call', ['name' => $name, 'args' => $args]);

        // Check if method is 'get_'
        if ( stripos( $name, 'get_' ) === 0 ) {

            // Default, if one param passed
            $default = ( is_array( $args ) && count( $args ) ) ? $args[0] : '';

            $setting = strtolower( substr( $name, 4 ) );
            return !array_key_exists( ''.$setting, $this->settings ) ? $default : $this->settings[ ''.$setting ];
        }

        // Check if method is 'set_'
        if ( stripos( $name, 'set_' ) === 0 ) {

            // Default, if one param passed
            $value = ( is_array( $args ) && count( $args ) ) ? $args[0] : '';

            $setting = strtolower( substr( $name, 4 ) );

            if ( array_key_exists( ''.$setting, $this->settings ) ) {

                $this->settings[ ''.$setting ] = $value;
            }

            return;
        }
    }

    /**
     * Add specific settings to default ones (ony called by specific child class when instance created
     */
    protected function update_settings( $settings ) {

        if ( !is_null( $settings ) && is_array( $settings ) ) {

            $this->settings = array_merge( $this->settings, $settings );
        }
    }
    /**
     * Get settings array
     */
    public function get_settings() {

        return $this->settings;
    }

    /**
     * Return true if the settings exists
     *
     * @param $setting the setting name
     */
    public function exists_setting( $setting ) {

        if ( array_key_exists( ''.$setting, $this->settings ) ) {

            return true;
        }
        return false;
    }
}
