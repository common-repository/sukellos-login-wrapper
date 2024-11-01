<?php

namespace Sukellos\WPFw\AdminBuilder\Fields;

defined( 'ABSPATH' ) or exit;

/**
 * Strategy pattern for choice fields
 *
 * @since 1.0.0
 */
abstract class Data_Choices implements Data_Strategy {

    protected $choice_field_context = null;

    /**
     * Default settings
     * @var array
     */
    protected $settings = array();

    /**
     * Must be linked to a choice field context, otherwise its nonsense
     *
     * @param $choice_field_context the choice field
     */
    public function set_choice_field_context( $choice_field_context ) {

        // Set context
        $this->choice_field_context = $choice_field_context;
    }

    /**
     * Get specific settings
     */
    public function get_settings() {

        return $this->settings;
    }

    /**
     * Build options depending on specific datas and settings
     */
    public function build_options() {

        if ( is_null( $this->choice_field_context ) ) {

            return array();
        }

        return $this->build_specific_options();
    }

    /**
     * Build specific options depending on choice field context
     * @return mixed
     */
    abstract protected function build_specific_options();
}
