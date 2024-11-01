<?php

namespace Sukellos\WPFw\AdminBuilder\Fields;

use Sukellos\WPFw\AdminBuilder\Item_Type;

defined( 'ABSPATH' ) or exit;

/**
 * Select field
 *
 * @since 1.0
 * @type select
 */
class Select_Field extends Choices_Field {

    /**
     * Default settings specific for this field
     * @var array
     */
    private $default_choice_field_settings = array(

        'type' => Item_Type::SELECT,
        'generic_type' => Item_Type::SELECT,

        /**
         * Set to true to use advanced multiple selection
         *
         * @since 1.0
         * @var array
         */
        'multiple' => false,
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

        $multiple = $this->get_multiple() ? 'multiple' : '';

        $name = $this->get_id();
        $val  = $this->get_value();

        if ( ! empty( $multiple ) ) {
            $name = "{$name}[]";
        }

        $html_content = '<select name="'.$name.'" '.$multiple.'>';

        $options = $this->build_options();

        // Make sure the current value is an array (for multiple select).
        foreach ( $options as $value => $label ) {

            // This is if we have option groupings.
            if ( is_array( $label ) ) {

                $html_content .= '<optgroup label="'.$value.'">';

                foreach ( $label as $sub_value => $sub_label ) {

                    $html_content .= sprintf( '<option value="%s" %s %s>%s</option>',
                        $sub_value,
                        in_array( $sub_value, $val ) ? 'selected="selected"' : '',
                        disabled( stripos( $sub_value, '!' ), 0, false ),
                        $sub_label
                    );
                }
                $html_content .= '</optgroup>';

                // This is for normal list of options.
            } else {

                $html_content .= sprintf( '<option value="%s" %s %s>%s</option>',
                    $value,
                    in_array( $value, $val ) ? 'selected="selected"' : '',
                    disabled( stripos( $value, '!' ), 0, false ),
                    $label
                );
            }
        }

        $html_content .= '</select>';

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

        wp_enqueue_script( 'sk-admin-builder-select2', plugins_url( '../../js/select2/select2.min.js', __FILE__ ), array( 'jquery' ) );


        wp_enqueue_style( 'sk-admin-builder-select2-style', plugins_url( '../../css/select2/select2.min.css', __FILE__ ) );
        wp_enqueue_style( 'sk-admin-builder-select-field-style', plugins_url( '../../css/sk-field-select.css', __FILE__ ) );
    }

    /**
     * Load the javascript
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function init_scripts() {
        ?>
        <script>
            jQuery( document ).ready( function () {
                'use strict';

                /**
                 * Select2
                 * @see https://select2.github.io/
                 */
                if ( jQuery().select2 ) {
                    jQuery( 'select.sk-admin-builder-select, [class*="sk-admin-builder-select"] select' ).select2();
                }
            });
        </script>
        <?php
    }
}
