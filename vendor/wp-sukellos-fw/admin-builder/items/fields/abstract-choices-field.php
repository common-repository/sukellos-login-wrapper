<?php

namespace Sukellos\WPFw\AdminBuilder\Fields;

use Sukellos\WPFw\AdminBuilder\Item_Type;

defined( 'ABSPATH' ) or exit;

/**
 * Choices field is an abstract class used as context for select, multicheck, radio...
 * It use Strategy pattern to allow sub choices to work independently with any type of datas (users, posts, custom, font-family...)
 *
 * @since 1.0
 * @type choices
 */
abstract class Choices_Field extends Field {

    private $data_choices_strategy = null;

    /**
     * Constructor
     *
     * @param $settings
     * @param $handler is responsible for storage as user_meta, options, or post_meta
     * @param $data_choices_strategy is responsible for building specific data options (users, posts, custom...)
     */
    public function __construct( $settings, $handler, $data_choices_strategy=null ) {

        // Set strategy
        $this->data_choices_strategy = $data_choices_strategy;
        $this->data_choices_strategy->set_choice_field_context( $this );

        parent::__construct( $settings, $handler );
    }

    /**
     * Get the default field settings
     * @return array
     */
    protected function get_default_specific_field_settings() {

        // Merge all settings
        $settings = array_merge(
            $this->data_choices_strategy->get_settings(),
            $this->get_default_choice_field_settings()
        );
        return $settings;
    }

    /**
     * Get the default field settings
     * @return array
     */
    abstract protected function get_default_choice_field_settings();

    /**
     * Build options
     */
    public function build_options() {

        return $this->data_choices_strategy->build_options();
    }

    /**
     * Get field value depending on its specific type
     * Handler implementation
     */
    public function get_value() {

        if ( is_null( $this->handler ) ) {

            return array();
        }
        $value = $this->handler->get_value();
        if ( !is_array( $value ) ) {

            return array();
        }
        return $this->handler->get_value();
    }

    /**
     *          ===============
     *      =======================
     *  ============ HOOKS ===========
     *      =======================
     *          ===============
     */

}
