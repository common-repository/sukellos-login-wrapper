<?php

namespace Sukellos\WPFw;

use Sukellos\WPFw\Utils\WP_Log;

defined( 'ABSPATH' ) or exit;

/**
 * License restriction
 * @see https://www.php.net/manual/fr/language.oop5.traits.php
 *
 * @since 1.0.0
 */
trait License_Restriction {

    // License restriction status
    // It could be called on the same instance a few timesif its shared between plugins... then the more restrictive is kept
    private $licence_restriction = false;
    private $plugin_base_name = null;

    /**
     * Apply license restriction
     *
     * @since 1.0.0
     */
    public function apply_license_restriction( $licence_restriction, $plugin_base_name ) {

        if ( !$this->licence_restriction && $licence_restriction ) {

            $this->licence_restriction = $licence_restriction;
            $this->plugin_base_name = $plugin_base_name;
        }
    }

	/**
	 * Has a license restriction ?
	 *
     * @return true is the class is restricted by invalid license
	 * @since 1.0.0
	 */
    public function is_license_restricted() {

        return $this->licence_restriction;
    }
}
