<?php

namespace Sukellos\WPFw\AdminBuilder\Items;

use Sukellos\WPFw\AdminBuilder\Item_Type;
use Sukellos\WPFw\Utils\WP_Log;

defined( 'ABSPATH' ) or exit;

/**
 * Content item
 *
 * @since 1.0
 * @type content
 */
class Content extends Item {

    /**
     * Default settings
     * @var array
     */
    private $default_settings = array(

        'type' => Item_Type::CONTENT,

        /**
         * HTML content
         */
        'content' => ''
    );

    /**
     * Get the default item settings
     * @return array
     */
    protected function get_default_specific_item_settings() {

        return $this->default_settings;
    }

    /**
     * Render field depending on its specific type
     *
     * @param bool $echo Whether to display or return string, default true
     */
    public function render( $echo=true ) {

        $id = $this->get_id();
        $class = $this->get_class();

        $html_content = '<div id="'.$id.'" class="sk-admin-builder-item-content '.( ( $class !== '' )?$class:'' ).'" >';

        /**
         * Allow to add content that can be treted only after admin_menu hook execution context
         *
         * @since 1.3.2
         */
        WP_Log::debug( __METHOD__, [] );
        $html_content .= apply_filters( 'sukellos_fw/admin_builder/field/'.$this->get_id(), $this->get_content() );

        if ( ! empty( $this->get_desc() ) ) {

            $html_content .= '<p class="description">'.$this->get_desc().'</p>';
        }
        $html_content .= '</div>';

        if ( $echo ) {

            echo $html_content;
        } else {

            return $html_content;
        }
    }


    /**
     *          ===============
     *      =======================
     *  ============ HOOKS ===========
     *      =======================
     *          ===============
     */


    /**
     * Enqueue the scripts
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function enqueue_scripts() {

        wp_enqueue_style( 'sk-admin-builder-content-item-style', plugins_url( '../css/sk-item-content.css', __FILE__ ) );
    }

    /**
     * Load the javascript
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function init_scripts() {}
    
}
