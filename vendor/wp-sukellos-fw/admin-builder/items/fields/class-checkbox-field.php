<?php

namespace Sukellos\WPFw\AdminBuilder\Fields;

use Sukellos\WPFw\Utils\WP_Log;
use Sukellos\WPFw\AdminBuilder\Item_Type;

defined( 'ABSPATH' ) or exit;

/**
 * Checkbox field
 *
 * @since 1.0
 * @type textarea
 */
class Checkbox_Field extends Field {

    /**
     * Default settings specific for this field
     * @var array
     */
    private $default_specific_field_settings = array(

        'type' => Item_Type::CHECKBOX,

        /**
         * Pro version compatibility
         * ENABLE
         */
        'enabled' => '',
        'disabled' => '',
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

        $checked_text = checked( $this->get_value(), 1, false );
        WP_Log::debug( 'Checkbox_Field->render_specific_body - Checkbox values ', ['value'=>$this->get_value(), 'checked text'=>$checked_text]);
	    $html_content = '
            <label for="'.$this->get_id().'">
                <input type="checkbox" name="'.$this->get_id().'" id="'.$this->get_id().'" '.$checked_text.' value="1">
                <input type="hidden" name="hidden_checkbox_'.$this->get_id().'" id="hidden_checkbox_'.$this->get_id().'" value="1">
                '.$this->get_descr( '' ).'
            </label>
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
     * Enqueue the scripts
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function enqueue_scripts() {

        wp_enqueue_style( 'sk-admin-builder-checkbox-field-style', plugins_url( '../../css/sk-field-checkbox.css', __FILE__ ) );
    }

    /**
     * Load the javascript
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function init_scripts() {}

    /**
     * Filter : sukellos_fw/admin_builder/field/set_cleaned_value_{field_id}
     * Used to clean up a value before updating it in field
     *
     * @param $value the raw value
     */
    public function filter_field_set_cleaned_value( $value ) {

        WP_Log::debug('Checkbox_Field->filter_field_set_cleaned_value', ['value'=>($value?'true':'false')]);
        return ($value?'1':'0');
    }
}
