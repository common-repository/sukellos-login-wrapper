<?php

namespace Sukellos\WPFw\AdminBuilder\Fields;

defined( 'ABSPATH' ) or exit;

/**
 * Handler interface
 *
 * Used to manage load / save and more related to user_meta, post_meta or option specific behaviour
 * Allow polymorphism
 *
 * @since 1.0.0
 */
interface Handler {

    /**
     * Get field value depending on its specific type
     */
    public function get_value();

    /**
     * Set field value depending on its specific type
     */
    public function set_value( $value );

    /**
     * Save field value depending on its specific type
     */
    public function save();

    /**
     * Load field value depending on its specific type
     */
    public function load();

    /**
     * Reset field to its default value
     */
    public function reset();
}
