<?php

namespace Sukellos\WPFw\AdminBuilder;

use Sukellos\WP_Sukellos_Fw;
use Sukellos\WPFw\Utils\WP_Log;

defined( 'ABSPATH' ) or exit;

/**
 * Admin Container Class - Abstract class for admin page building
 *
 * Must be ovveride to create specific admin containers
 *
 * @since 1.0.0
 */
abstract class Admin_Container extends Container {

    /**
     * Default settings
     * @var array
     */
    private $default_admin_container_settings = array(

        'parent_id' => null, // id of parent, if blank, then this is a top level menu
    );

    /**
     * Get the default item settings
     * @return array
     */
    protected function get_default_specific_container_settings() {

        // Merge all settings
        $settings = array_merge(
            $this->default_admin_container_settings,
            $this->get_default_specific_admin_container_settings()
        );
        return $settings;
    }

    /**
     * Get the default field settings
     * @return array
     */
    abstract protected function get_default_specific_admin_container_settings();

    /**
     * Render items, one by one
     *
     * @param int $container_id id of the container, to be used for form nonce security
     * @param bool $echo Whether to display or return string, default true
     */
    public function render_items( $container_id, $echo=true ) {

        $html_content = '';

        $items = $this->get_items();
        $in_a_form = false;
        foreach ( $items as $item ) {

            // Form independent, then excluded from global form
            if ( $item->get_global_form_independent() ) {

                if ( $in_a_form ) {

                    $html_content .= $this->render_end_form( $container_id );
                    $in_a_form = false;
                }

                if ( $item->get_need_form() ) {

                    $html_content .= $this->render_start_form( $container_id );
                    $in_a_form = true;
                }

                // Render item
                $html_content .= $item->render( false );

                if ( $in_a_form ) {

                    $html_content .= $this->render_end_form( $container_id );
                    $in_a_form = false;
                }
            }
            // In global form included
            else {

                if ( $item->get_need_form() && !$in_a_form ) {

                    $html_content .= $this->render_start_form( $container_id );
                    $in_a_form = true;
                }

                // Render item
                $html_content .= $item->render( false );
            }

        }

        // If the form is still opened, close it
        if ( $in_a_form ) {

            // Reaching that point in a form if the last item was not independent, then add buttons
            $html_content .= $this->render_buttons();

            $html_content .= $this->render_end_form( $container_id );
        }

        if ( $echo ) {

            echo $html_content;
        } else {

            return $html_content;
        }
    }


    private function render_start_form( $id) {

        // OPEN FORM
        $html_content = '<form method="post" class="sk-admin-builder-form">';
        // security
        $html_content .= wp_nonce_field($id, self::NONCE_PREFIX . '_nonce', true, false);

        return $html_content;
    }

    private function render_end_form() {

        $html_content = '
                    </form>
                ';

        return $html_content;
    }

    /**
     *          ===============
     *      =======================
     *  ============ HOOKS ===========
     *      =======================
     *          ===============
     */

}
