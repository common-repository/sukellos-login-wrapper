<?php

namespace Sukellos\WPFw\AdminBuilder\Fields;

use Sukellos\WPFw\Utils\WP_Log;

defined( 'ABSPATH' ) or exit;

/**
 * Handler for field
 *
 * Used to manage load / save and more related to user_meta, post_meta or option specific behaviour
 * Allow polymorphism
 *
 * @since 1.0.0
 */
abstract class Field_Handler implements Handler {

    protected $value = null; // Raw format
    protected $default = ''; // Displayed format
    protected $field_id = '';

    /**
     * Get field id
     */
    public function get_field_id() {

        return $this->field_id;
    }

    /**
     * Set field id
     */
    public function set_field_id( $field_id ) {

        $this->field_id = $field_id;
    }

    /**
     * Get field value depending on its specific type
     * Return default value if null
     */
    public function get_value() {

        WP_Log::debug( 'Field_Handler->get_value', ['value'=>$this->value] );
        if ( is_null( $this->value ) ) {

            WP_Log::debug( 'Field_Handler->get_value - value found null', [] );
            return $this->default;
        }

        WP_Log::debug( 'Field_Handler->get_value - Before filtering', ['filter'=>'sukellos_fw/admin_builder/field/get_cleaned_value_'.$this->field_id, 'value'=>$this->value] );
        /**
         * Filter 1: sukellos_fw/admin_builder/field/get_cleaned_value_{field_id}
         * Filter 2: sukellos_fw/admin_builder/field/get_cleaned_value + param $field_id
         * Used to clean up a value before returning it to a caller
         *
         * @param $value the raw value
         */
        $cleaned_value = apply_filters( 'sukellos_fw/admin_builder/field/get_cleaned_value_'.$this->field_id, $this->value );
        WP_Log::debug( 'Field_Handler->get_value - After filtering get_cleaned_value_', ['filter'=>'sukellos_fw/admin_builder/field/get_cleaned_value_'.$this->field_id, 'value'=>$cleaned_value] );
        //$cleaned_value = apply_filters( 'sukellos_fw/admin_builder/field/get_cleaned_value', $this->field_id, $cleaned_value );
        WP_Log::debug( 'Field_Handler->get_value - After filtering get_cleaned_value', ['filter'=>'sukellos_fw/admin_builder/field/get_cleaned_value_'.$this->field_id, 'value'=>$cleaned_value] );

        return $cleaned_value;
    }

    /**
     * Set field value depending on its specific type
     */
    public function set_value( $value ) {

        WP_Log::debug( 'Field_Handler->set_value - Before filtering', ['filter'=>'sukellos_fw/admin_builder/field/set_cleaned_value_'.$this->field_id, 'value'=>$value] );
        /**
         * Filter1: sukellos_fw/admin_builder/field/set_cleaned_value_{field_id}
         * Filter2: sukellos_fw/admin_builder/field/set_cleaned_value + param $field_id
         * Used to clean up a value before updating it in field
         *
         * @param $value the raw value
         */
        $cleaned_value = apply_filters( 'sukellos_fw/admin_builder/field/set_cleaned_value_'.$this->field_id, $value );
        //$cleaned_value = apply_filters( 'sukellos_fw/admin_builder/field/set_cleaned_value', $this->field_id, $cleaned_value );
        WP_Log::debug( 'Field_Handler->set_value - After filtering', ['filter'=>'sukellos_fw/admin_builder/field/set_cleaned_value_'.$this->field_id, 'value'=>$cleaned_value] );

        $this->value = $cleaned_value;
    }

    /**
     * Save field value depending of its specific type
     */
    abstract public function save();

    /**
     * Load field value depending of its specific type
     */
    abstract public function load();


    /**
     * Reset field to its default value
     */
    abstract public function reset();
}
