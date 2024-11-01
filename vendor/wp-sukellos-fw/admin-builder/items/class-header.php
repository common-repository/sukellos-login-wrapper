<?php

namespace Sukellos\WPFw\AdminBuilder\Items;

use Sukellos\WPFw\AdminBuilder\Item_Type;

defined( 'ABSPATH' ) or exit;

/**
 * Header item
 *
 * @since 1.0
 * @type header
 */
class Header extends Item {

    /**
     * Default settings
     * @var array
     */
    private $default_settings = array(

        'type' => Item_Type::HEADER,

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

        // Header
        $heading_id = str_replace( ' ', '-', strtolower( $this->get_name() ) );

        $html_content = '
                <div id="'.$id.'" class="sk-admin-builder-item-header '.( ( $class !== '' )?$class:'' ).'">
                    <h3 id="'.esc_attr( $heading_id ).'">'.$this->get_name().'</h3>
        ';

        if ( ! empty( $this->get_desc() ) ) {

            $html_content .= '<p class="description">'.$this->get_desc().'</p>';
        }

        $html_content .= '
            </div>
        ';

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

        wp_enqueue_style( 'sk-admin-builder-header-item-style', plugins_url( '../css/sk-item-header.css', __FILE__ ) );
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
