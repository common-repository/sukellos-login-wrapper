<?php

namespace Sukellos\WPFw\AdminBuilder\Fields;

use Sukellos\WPFw\Utils\WP_Log;

defined( 'ABSPATH' ) or exit;

/**
 * Custom strategy is responsible to manage custom data for choice fields
 *
 * Strategy pattern for choice fields
 *
 * @since 1.0.0
 */
class Custom_Choices extends Data_Choices {

    /**
     * Default settings
     * @var array
     */
    protected $settings = array(

        /**
         * Associative array of value-label pairs containing option
         *
         * @since 1.0
         * @var array
         */
        'options' => array(),
    );


    /**
     * Build specific options depending on choice field context
     * @return mixed
     */
    protected function build_specific_options() {

        return $this->choice_field_context->get_options();
    }
}
