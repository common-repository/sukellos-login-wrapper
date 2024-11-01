<?php

namespace Sukellos\WPFw\AdminBuilder\Fields;

use Sukellos\WPFw\Utils\WP_Log;

defined( 'ABSPATH' ) or exit;

/**
 * Handler for option
 *
 * Used to manage load / save and more related to user_meta, post_meta or option specific behaviour
 * Allow polymorphism
 *
 * @since 1.0.0
 */
class Option_Handler extends Field_Handler {

    /**
     * Constructor
     *
     * @param $default default value
     */
    public function __construct( $field_id, $default ) {

        $this->field_id = $field_id;
        $this->default = $default;
    }

    /**
     * Save field value depending of its specific type
     */
    public function save() {

        WP_Log::notice( 'Option_Handler->save', ['POST'=>$_POST, 'field_id'=>$this->field_id]);

        // Verify that this is the good field id
        // Then value is contained in POST request
        if ( isset( $_POST[ ''.$this->field_id ] ) || isset( $_POST[ 'hidden_checkbox_'.$this->field_id ] ) || isset( $_POST[ 'hidden_multicheck_'.$this->field_id ] ) ) {

            if ( isset( $_POST[ ''.$this->field_id ] ) ) {
                $this->set_value($_POST['' . $this->field_id]);
//            $this->value = $_POST[ ''.$this->field_id ];
            }
            // Checkbox is not inserted in POST for value not checked, then check for hidden_checkbox_
            else if ( isset( $_POST[ 'hidden_checkbox_'.$this->field_id ] ) ) {

                // Specific case: if the handler is called without the value in POST, it may be a checkbox
                $this->set_value( FALSE );
            }
            // Multicheck is not inserted in POST but no value checked, then check for hidden_multicheck_
            else if ( isset( $_POST[ 'hidden_multicheck_'.$this->field_id ] ) ) {

                // Specific case: if the handler is called without the value in POST, it may be a multicheck
                $this->set_value( array() );
            }
            update_option( $this->field_id, $this->value );

            return true;
        }

        return false;
    }

    /**
     * Load field value depending of its specific type
     */
    public function load() {

        WP_Log::debug( 'Option_Handler->load', ['field_id'=>$this->field_id, 'default'=>$this->default]);

        $loaded_value = get_option( $this->field_id, false );

        // If no value retrieved, then set default, via set_value to force set cleaning into raw format
        if ( $loaded_value === FALSE ) {

            $this->set_value( $this->default );
        } else {

            // Already a ra value found in DB
            $this->value = $loaded_value;
        }

        WP_Log::debug( 'Option_Handler->load', ['value loaded'=>$this->value]);
        return $this->value;
    }

    /**
     * Reset field value depending of its specific type
     */
    public function reset() {

        WP_Log::debug( 'Option_Handler->save', ['POST'=>$_POST]);

        // Verify that this is the good field id
        // Then value is contained in POST request
        if ( isset( $_POST[ ''.$this->field_id ] ) ) {

            $this->set_value( $this->default );

            update_option( $this->field_id, $this->value );

            return true;
        }

        return false;
    }
}
