<?php

namespace Sukellos\WPFw\AdminBuilder\Fields;

use Sukellos\WPFw\AdminBuilder\Item_Type;

defined( 'ABSPATH' ) or exit;

/**
 * Text field
 *
 * @since 1.0
 * @type text
 */
class Text_Field extends Field {

    /**
     * Default settings specific for this field
     * @var array
     */
    private $default_specific_field_settings = array(

        'type' => Item_Type::TEXT,

        /**
         * (Optional) CSS rules to be used with this option. Only used when the option is placed in an admin page / tab
         *
         * @since 1.0.0
         * @var string
         * @see http://www.titanframework.net/generate-css-automatically-for-your-options/
         */
        'css' => '',

        /**
         * (Optional) The placeholder label shown when the input field is blank
         *
         * @since 1.0
         * @var string
         */
        'placeholder' => '',

        /**
         * (Optional) Set size of the field
         *
         * @since 1.9.3
         * @var string (large, regular, small)
         */
        'size' => 'regular',

        /**
         * (Optional) If true, the value of the input field will be hidden while typing.
         *
         * @since 1.0
         * @var boolean
         */
        'is_password' => false,

        /**
         * (Optional) The maximum character length allowed for the input field.
         *
         * @since 1.0
         * @var int
         */
        'maxlength' => '',

        /**
         * (Optional) An additional label, located immediately after the form field. Accepts alphanumerics and symbols. Potential applications include indication of the unit, especially if the field is used with numbers.
         *
         * @since 1.5.2
         * @var string
         * @example 'px' or '%'
         */
        'unit' => '',

        /**
         * Pro version compatibility
         * NUMBER
         */
        'min' => 0,
        'max' => 1000,
        'step' => 1,
        /**
         * Pro version compatibility
         * DATE
         */
        'date' => true,
        'time' => false,
        /**
         * Pro version compatibility
         * COLOR
         */
        'alpha' => false,
    );

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
		$is_password = $this->get_is_password() ? 'password' : 'text';
		$input_type = $this->get_hidden() ? 'hidden' : $is_password;

        $html_content = sprintf('<input class="%s-text" name="%s" placeholder="%s" maxlength="%s" id="%s" type="%s" value="%s" />%s',
			empty($this->get_size()) ? 'regular' : $this->get_size(),
			$this->get_id(),
			$this->get_placeholder(),
			$this->get_maxlength(),
			$this->get_id(),
            $input_type,
			esc_attr( $this->get_value() ),
			$this->get_hidden() ? '' : ' '.$this->get_unit()
		);

        if ( $echo ) {

            echo $html_content;
        } else {

            return $html_content;
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

        wp_enqueue_style( 'sk-admin-builder-text-field-style', plugins_url( '../../css/sk-field-text.css', __FILE__ ) );}

    /**
     * Load the javascript
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function init_scripts() {}
}
