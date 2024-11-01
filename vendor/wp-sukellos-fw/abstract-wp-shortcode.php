<?php

namespace Sukellos\WPFw;

defined( 'ABSPATH' ) or exit;

/**
 * Wordpress Generic Shortcode 
 *
 *
 * @version 5.5.0
 */
abstract class WP_Shortcode {

    protected $shortcode_slug;

    /**
     * Init shortcode : self shortcode and hooks
     * Called on hook plugins_loaded
     */
    public function init() {

        // Add the [witti_product_datasheet] shortcode
        add_shortcode( $this->shortcode_slug, array( $this, 'render_shortcode' ) );
    }

    /**
    * Main method : Render the shortcode
    *
    * @since 1.0.0
    * @param array $atts shortcode attributes
    */
    abstract public function render_shortcode( $atts );

    /**
     * Get description for admin purpose for example
     */
    abstract public function get_description();
}
