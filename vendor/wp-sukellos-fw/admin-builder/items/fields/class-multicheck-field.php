<?php

namespace Sukellos\WPFw\AdminBuilder\Fields;

use Sukellos\WPFw\AdminBuilder\Item_Type;
use Sukellos\WPFw\Utils\WP_Helper;

defined( 'ABSPATH' ) or exit;

/**
 * Multicheck field
 *
 * @since 1.0
 * @type multicheck
 */
class Multicheck_Field extends Choices_Field {

    /**
     * Default settings specific for this field
     * @var array
     */
    private $default_choice_field_settings = array(

        'type' => Item_Type::MULTICHECK,
        'generic_type' => Item_Type::MULTICHECK,

        /**
         * Display a select all checkbox if true, or a string which define the label
         *
         * @since 1.0.0
         * @var bool
         */
        'select_all' => false,
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

        $html_content = '<fieldset>';

        $value = $this->get_value();

        if ( !empty( $this->get_select_all() ) ) {

            $select_all_label = WP_Helper::sk__( 'Select All' );

            if ( is_string(  $this->get_select_all() ) ) {

                $select_all_label = $this->get_select_all();
            }
            $html_content .= sprintf('<label style="margin-bottom: 1em !important;"><input class="sk_admin_builder_checkbox_selectall" type="checkbox" /> %s </label><br>',
                esc_html( $select_all_label )
            );
        }

        $options = $this->build_options();

        foreach ( $options as $ovalue => $olabel ) {

            $html_content .= sprintf('<label for="%s"><input id="%s" type="checkbox" name="%s[]" value="%s" %s/> %s</label><br>',
                $this->get_id().$ovalue,
                $this->get_id().$ovalue,
                $this->get_id(),
                esc_attr( $ovalue ),
                checked( in_array( $ovalue, $value ), true, false ),
                $olabel
            );
        }

        $html_content .= '
                </fieldset>
                <input type="hidden" name="hidden_multicheck_'.$this->get_id().'" id="hidden_checkbox_'.$this->get_id().'" value="1">
                ';

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

        // Set a serialized array of values
        if ( empty( $value ) ) {

            return array();
        }
        if ( is_serialized( $value ) ) {

            return $value;
        }
        // CSV
        if ( is_string( $value ) ) {

            $value = explode( ',', $value );
        }
        return serialize( $value );
    }

    /**
     * Enqueue the scripts
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function enqueue_scripts() {

        wp_enqueue_style( 'sk-admin-builder-multicheck-field-style', plugins_url( '../../css/sk-field-multicheck.css', __FILE__ ) );

        wp_enqueue_script( 'sk-admin-builder-multicheck-select-all', plugins_url( '../../js/multicheck-select-all.js', __FILE__ ), array( 'jquery' ) );
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
