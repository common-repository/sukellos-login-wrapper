<?php

namespace Sukellos\WPFw\AdminBuilder\Fields;

use Sukellos\WPFw\AdminBuilder\Item_Type;

defined( 'ABSPATH' ) or exit;

/**
 * Textarea field
 *
 * Creates a Textarea field
 *
 * @since 1.0
 * @type textarea
 */
class Textarea_Field extends Field {

    /**
     * Default settings specific for this field
     * @var array
     */
    private $default_specific_field_settings = array(

        'type' => Item_Type::TEXTAREA,

        /**
         * (Optional) The placeholder label shown when the input field is blank
         *
         * @since 1.0
         * @var string
         */
        'placeholder' => '',

        /**
         * (Optional) If true, a more code-like font will be used
         *
         * @since 1.0
         * @var string
         */
        'is_code' => false,

        /**
         * Pro version compatibility
         * WYSIWYG
         */
        'editor_settings' => array(), // WP_Editors settings, see https://developer.wordpress.org/reference/classes/_wp_editors/parse_settings/
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

        $html_content = sprintf("<textarea class='large-text %s' name=\"%s\" placeholder=\"%s\" id=\"%s\" rows='10' cols='50'>%s</textarea>",
            $this->get_is_code() ? 'code' : '',
            $this->get_id(),
            $this->get_placeholder(),
            $this->get_id(),
            esc_textarea( stripslashes( $this->get_value() ) )
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

        wp_enqueue_style( 'sk-admin-builder-textarea-field-style', plugins_url( '../../css/sk-field-textarea.css', __FILE__ ) );
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
