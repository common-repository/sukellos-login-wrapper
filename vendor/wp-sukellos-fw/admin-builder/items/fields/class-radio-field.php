<?php

namespace Sukellos\WPFw\AdminBuilder\Fields;

use Sukellos\WPFw\AdminBuilder\Item_Type;

defined( 'ABSPATH' ) or exit;

/**
 * Radio field
 *
 *
 * @since 1.0
 * @type radio
 */
class Radio_Field extends Choices_Field {

    /**
     * Default settings specific for this field
     * @var array
     */
    private $default_choice_field_settings = array(

        'type' => Item_Type::RADIO,
        'generic_type' => Item_Type::RADIO,

        /**
         * (Optional) CSS rules to be used with this option. Only used when the option is placed in an admin page / tab
         *
         * @since 1.0.0
         * @var string
         * @see http://www.titanframework.net/generate-css-automatically-for-your-options/
         */
        'css' => '',

        /**
         * To display inline, or one per line
         *
         * @since 1.0.0
         * @var string
         */
        'inline' => false,
    );


    /**
     * Get the default field settings
     * @return array
     */
    protected function get_default_choice_field_settings() {

        return $this->default_choice_field_settings;
    }

	/**
	 * Display for options and meta
     * @param bool $echo Whether to display or return string, default true
     */
    public function render_specific_body( $echo=true ) {

        $options = $this->build_options();

        $inline = $this->get_inline();

        $html_content = '';
        $br = '';

        if ( !$inline ) {

            $html_content = '<fieldset>';
            $br = '<br>';
        }

        foreach ( $options as $value => $label ) {

            $html_content .= sprintf('<label for="%s"><input id="%s" type="radio" name="%s" value="%s" %s/> %s</label>'.$br,
                $this->get_id() . $value,
                $this->get_id() . $value,
                $this->get_id(),
                esc_attr( $value ),
                checked( $this->get_value(), $value, false ),
                $label
            );
        }

        if ( !$inline ) {

            $html_content .= '</fieldset>';
        }

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

        wp_enqueue_style( 'sk-admin-builder-radio-field-style', plugins_url( '../../css/sk-field-radio.css', __FILE__ ) );
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
