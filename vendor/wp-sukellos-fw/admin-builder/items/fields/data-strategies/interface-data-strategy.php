<?php

namespace Sukellos\WPFw\AdminBuilder\Fields;

defined( 'ABSPATH' ) or exit;

/**
 * Data strategy interface
 *
 * Used to manage settings and build options for choice fields (eg. Select, Multicheck, Radio...)
 * Allow strategy pattern for choice fields
 *
 * @since 1.0.0
 */
interface Data_Strategy {

    /**
     * Get specific settings
     */
    public function get_settings();

    /**
     * Build options depending on specific datas and settings
     */
    public function build_options();
}
